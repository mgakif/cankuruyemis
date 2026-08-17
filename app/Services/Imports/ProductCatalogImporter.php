<?php

namespace App\Services\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImportBatch;
use App\Models\ProductImportRow;
use App\Services\Pricing\OnlinePriceCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductCatalogImporter
{
    protected const DEFAULT_STOCK_QUANTITY = 100;
    protected const MAX_MONETARY_VALUE = 99999999.99;

    public function __construct(
        protected XlsxCatalogReader $reader,
        protected ProductCatalogClassifier $classifier,
        protected OnlinePriceCalculator $priceCalculator,
    ) {}

    public function import(string $path, bool $dryRun = false): ProductImportBatch
    {
        $path = $this->resolveImportPath($path);
        $sheets = $this->reader->readWorkbook($path);
        $rows = $this->resolveCatalogRows($sheets);
        $onlineKeys = $this->resolveOnlineLookup($sheets);
        $useSheetBasedChannels = $onlineKeys !== [] || $this->hasOnlineSheet($sheets);

        $batch = ProductImportBatch::create([
            'source_name' => basename($path),
            'source_path' => $path,
            'status' => 'processing',
            'summary' => [
                'total_rows' => count($rows),
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
            ],
        ]);

        $summary = $batch->summary ?? [];

        foreach ($rows as $index => $row) {
            DB::transaction(function () use ($row, $index, $batch, $dryRun, &$summary, $onlineKeys, $useSheetBasedChannels) {
                $normalized = $this->normalizeRow($row);

                if ($normalized === null) {
                    ProductImportRow::create([
                        'product_import_batch_id' => $batch->id,
                        'row_number' => $index + 2,
                        'barcode' => $this->extractBarcode($row),
                        'stock_name' => $this->extractTitle($row),
                        'action' => 'skip',
                        'status' => 'skipped',
                        'message' => 'Satirdaki fiyat alanı gecerli bir para birimi degeri degil veya veritabanı sinirini asiyor.',
                        'raw_payload' => $row,
                    ]);

                    $summary['skipped']++;

                    return;
                }

                $classified = $this->classifier->classify($normalized['title']);
                $saleChannel = $this->resolveSaleChannel($normalized, $classified, $onlineKeys, $useSheetBasedChannels);

                if ($classified['action'] === 'skip') {
                    ProductImportRow::create([
                        'product_import_batch_id' => $batch->id,
                        'row_number' => $index + 2,
                        'barcode' => $normalized['barcode'],
                        'stock_name' => $normalized['title'],
                        'action' => 'skip',
                        'status' => 'skipped',
                        'message' => 'Satir operasyon veya katalog disi olarak atlandi.',
                        'raw_payload' => $row,
                    ]);

                    $summary['skipped']++;

                    return;
                }

                $category = $this->resolveCategory($classified['category'], $classified['subcategory'], $saleChannel, $dryRun);
                $product = $this->resolveProduct($normalized);

                $payload = [
                    'product_category_id' => $category?->id,
                    'title' => $normalized['title'],
                    'slug' => $product?->slug ?: $this->generateUniqueSlug($normalized['title'], $normalized['barcode']),
                    'barcode' => $normalized['barcode'],
                    'unit' => $normalized['unit'],
                    'store_price' => $normalized['store_price'],
                    'stock_quantity' => self::DEFAULT_STOCK_QUANTITY,
                    'sale_channel' => $saleChannel,
                    'position' => $product?->position ?: 0,
                    'last_imported_at' => now(),
                    'is_published' => $saleChannel !== 'hidden',
                    'published_at' => now(),
                    'content_status' => $product?->content_status ?: 'draft',
                    'short_description' => $product?->short_description,
                    'summary' => $product?->summary,
                    'price' => $normalized['store_price'],
                ];

                $workingProduct = $product ?: new Product();
                $workingProduct->fill($payload);
                $workingProduct->online_price = $saleChannel === 'online'
                    ? $this->priceCalculator->calculate(
                        $normalized['store_price'],
                        $this->priceCalculator->resolveRule($workingProduct)
                    )
                    : $normalized['store_price'];

                if (! $dryRun) {
                    $workingProduct->save();
                }

                ProductImportRow::create([
                    'product_import_batch_id' => $batch->id,
                    'row_number' => $index + 2,
                    'barcode' => $normalized['barcode'],
                    'stock_name' => $normalized['title'],
                    'product_id' => $dryRun ? null : $workingProduct->id,
                    'action' => $product ? 'update' : 'create',
                    'status' => 'imported',
                    'message' => $product ? 'Mevcut urun barkod bazli guncellendi.' : 'Yeni urun olusturuldu.',
                    'raw_payload' => array_merge($row, [
                        '_classified' => $classified,
                        '_sale_channel' => $saleChannel,
                        '_normalized' => $payload + ['online_price' => $workingProduct->online_price],
                    ]),
                ]);

                $summary[$product ? 'updated' : 'created']++;
            });
        }

        $batch->update([
            'status' => $dryRun ? 'dry_run' : 'completed',
            'summary' => $summary,
            'processed_at' => now(),
        ]);

        return $batch->fresh();
    }

    public function resolveImportPath(string $path): string
    {
        if (is_file($path)) {
            return $path;
        }

        $relativePath = ltrim(str_replace('\\', '/', $path), '/');
        $storageAppPrefix = 'storage/app/';

        if (str_contains($relativePath, $storageAppPrefix)) {
            $relativePath = substr($relativePath, strpos($relativePath, $storageAppPrefix) + strlen($storageAppPrefix));
        }

        foreach (['public', 'local'] as $disk) {
            $candidate = Storage::disk($disk)->path($relativePath);

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return $path;
    }

    protected function resolveCatalogRows(array $sheets): array
    {
        foreach (['Tüm Ürünler', 'Tum Urunler'] as $sheetName) {
            if (array_key_exists($sheetName, $sheets)) {
                return $sheets[$sheetName];
            }
        }

        foreach ($sheets as $sheetName => $rows) {
            if (! $this->isOnlineSheetName($sheetName)) {
                return $rows;
            }
        }

        $firstSheet = reset($sheets);

        return is_array($firstSheet) ? $firstSheet : [];
    }

    protected function resolveOnlineLookup(array $sheets): array
    {
        $onlineRows = [];

        foreach (['Online Ürünler', 'Online Urunler'] as $sheetName) {
            if (array_key_exists($sheetName, $sheets)) {
                $onlineRows = $sheets[$sheetName];
                break;
            }
        }

        $lookup = [];

        foreach ($onlineRows as $row) {
            $normalized = $this->normalizeRow($row);

            if ($normalized === null) {
                continue;
            }

            $key = $this->makeLookupKey($normalized);

            if ($key) {
                $lookup[$key] = true;
            }
        }

        return $lookup;
    }

    protected function hasOnlineSheet(array $sheets): bool
    {
        foreach (array_keys($sheets) as $sheetName) {
            if ($this->isOnlineSheetName($sheetName)) {
                return true;
            }
        }

        return false;
    }

    protected function resolveSaleChannel(array $normalized, array $classified, array $onlineKeys, bool $useSheetBasedChannels): string
    {
        if (! $useSheetBasedChannels) {
            return $classified['sale_channel'];
        }

        $key = $this->makeLookupKey($normalized);

        return $key && isset($onlineKeys[$key]) ? 'online' : 'store_only';
    }

    protected function makeLookupKey(array $normalized): ?string
    {
        if (! empty($normalized['barcode'])) {
            return 'barcode:'.mb_strtolower(trim((string) $normalized['barcode']));
        }

        if (empty($normalized['title'])) {
            return null;
        }

        return 'title:'.mb_strtolower(trim((string) $normalized['title'])).'|unit:'.mb_strtolower(trim((string) $normalized['unit']));
    }

    protected function isOnlineSheetName(string $sheetName): bool
    {
        $normalized = Str::ascii(mb_strtolower(trim($sheetName)));

        return $normalized === 'online urunler';
    }

    protected function normalizeRow(array $row): ?array
    {
        $barcode = $this->extractBarcode($row);
        $title = $this->extractTitle($row);
        $unit = trim((string) ($row['Birim'] ?? 'ADET'));
        $storePrice = $this->normalizeMoneyValue($row['Satış Fiyatı'] ?? null);

        if ($title === '' || $storePrice === null) {
            return null;
        }

        return [
            'barcode' => $barcode !== '' ? preg_replace('/\.0$/', '', $barcode) : null,
            'title' => preg_replace('/\s+/', ' ', $title),
            'unit' => $unit ?: 'ADET',
            'store_price' => $storePrice,
            'stock_quantity' => self::DEFAULT_STOCK_QUANTITY,
        ];
    }

    protected function extractBarcode(array $row): string
    {
        return trim((string) ($row['Barkod'] ?? ''));
    }

    protected function extractTitle(array $row): string
    {
        return trim((string) ($row['Stok Adı'] ?? ''));
    }

    protected function normalizeMoneyValue(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            $numeric = (float) $value;
        } else {
            $normalized = trim((string) $value);

            if ($normalized === '') {
                return null;
            }

            $normalized = preg_replace('/[^\d,.\-]/u', '', $normalized) ?? '';

            if ($normalized === '') {
                return null;
            }

            $lastComma = strrpos($normalized, ',');
            $lastDot = strrpos($normalized, '.');

            if ($lastComma !== false && $lastDot !== false) {
                if ($lastComma > $lastDot) {
                    $normalized = str_replace('.', '', $normalized);
                    $normalized = str_replace(',', '.', $normalized);
                } else {
                    $normalized = str_replace(',', '', $normalized);
                }
            } elseif ($lastComma !== false) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);

                if (substr_count($normalized, '.') > 1) {
                    $normalized = str_replace('.', '', $normalized);
                }
            }

            if (! is_numeric($normalized)) {
                return null;
            }

            $numeric = (float) $normalized;
        }

        if ($numeric < 0 || $numeric > self::MAX_MONETARY_VALUE) {
            return null;
        }

        return round($numeric, 2);
    }

    protected function resolveProduct(array $normalized): ?Product
    {
        if ($normalized['barcode']) {
            $product = Product::query()->where('barcode', $normalized['barcode'])->first();

            if ($product) {
                return $product;
            }
        }

        return Product::query()
            ->where('title', $normalized['title'])
            ->where('unit', $normalized['unit'])
            ->first();
    }

    protected function resolveCategory(?string $parentName, ?string $childName, string $channel, bool $dryRun): ?ProductCategory
    {
        if (! $parentName) {
            return null;
        }

        $parent = ProductCategory::query()->where('slug', Str::slug($parentName))->first();

        if (! $parent && ! $dryRun) {
            $parent = ProductCategory::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'channel' => $channel,
                'is_active' => true,
            ]);
        }

        if (! $childName) {
            return $parent;
        }

        $child = ProductCategory::query()->where('slug', Str::slug($parentName.' '.$childName))->first();

        if (! $child && ! $dryRun && $parent) {
            $child = ProductCategory::create([
                'parent_id' => $parent->id,
                'name' => $childName,
                'slug' => Str::slug($parentName.' '.$childName),
                'channel' => $channel,
                'is_active' => true,
            ]);
        }

        return $child ?: $parent;
    }

    protected function generateUniqueSlug(string $title, ?string $barcode): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug !== '' ? $baseSlug : 'urun';

        if ($barcode) {
            $barcodeSlug = Str::slug($barcode);

            if (! Product::query()->where('slug', $slug)->exists()) {
                return $slug;
            }

            $slugWithBarcode = "{$slug}-{$barcodeSlug}";

            if (! Product::query()->where('slug', $slugWithBarcode)->exists()) {
                return $slugWithBarcode;
            }
        }

        $counter = 2;
        $candidate = $slug;

        while (Product::query()->where('slug', $candidate)->exists()) {
            $candidate = "{$slug}-{$counter}";
            $counter++;
        }

        return $candidate;
    }
}
