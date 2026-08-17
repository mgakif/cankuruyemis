<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\Imports\ProductCatalogImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use ZipArchive;

class ProductCatalogImporterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
    }

    public function test_it_uses_online_sheet_as_whitelist_for_sale_channel(): void
    {
        $path = $this->createXlsx([
            'Tüm Ürünler' => [
                ['Barkod', 'Stok Adı', 'Birim', 'Satış Fiyatı'],
                ['111', 'Lokum Special', 'ADET', '10,00'],
                ['222', 'Algida Dondurma', 'ADET', '20,00'],
            ],
            'Online Ürünler' => [
                ['Barkod', 'Stok Adı', 'Birim', 'Satış Fiyatı'],
                ['222', 'Algida Dondurma', 'ADET', '20,00'],
            ],
        ]);

        $batch = app(ProductCatalogImporter::class)->import($path, false);

        $this->assertSame('completed', $batch->status);
        $this->assertSame(2, $batch->summary['created'] ?? 0);
        $this->assertSame(0, $batch->summary['updated'] ?? 0);
        $this->assertSame(0, $batch->summary['skipped'] ?? 0);

        $this->assertDatabaseHas('products', [
            'barcode' => '111',
            'title' => 'Lokum Special',
            'sale_channel' => 'store_only',
        ]);

        $this->assertDatabaseHas('products', [
            'barcode' => '222',
            'title' => 'Algida Dondurma',
            'sale_channel' => 'online',
        ]);

        $onlineProduct = Product::query()->where('barcode', '222')->firstOrFail();
        $storeOnlyProduct = Product::query()->where('barcode', '111')->firstOrFail();

        $this->assertSame('20.00', $onlineProduct->online_price);
        $this->assertSame('10.00', $storeOnlyProduct->online_price);
    }

    public function test_it_skips_rows_with_prices_outside_database_bounds(): void
    {
        $path = $this->createXlsx([
            'Tüm Ürünler' => [
                ['Barkod', 'Stok Adı', 'Birim', 'Satış Fiyatı'],
                ['333', 'Problemli Urun', 'ADET', '8682035101352'],
            ],
            'Online Ürünler' => [
                ['Barkod', 'Stok Adı', 'Birim', 'Satış Fiyatı'],
            ],
        ]);

        $batch = app(ProductCatalogImporter::class)->import($path, false);

        $this->assertSame('completed', $batch->status);
        $this->assertSame(0, $batch->summary['created'] ?? 0);
        $this->assertSame(0, $batch->summary['updated'] ?? 0);
        $this->assertSame(1, $batch->summary['skipped'] ?? 0);
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_import_rows', 1);
        $this->assertDatabaseHas('product_import_rows', [
            'barcode' => '333',
            'stock_name' => 'Problemli Urun',
            'status' => 'skipped',
        ]);
    }

    protected function createXlsx(array $sheets): string
    {
        $path = tempnam(sys_get_temp_dir(), 'catalog_');
        $xlsxPath = $path.'.xlsx';

        rename($path, $xlsxPath);

        $sharedStrings = [];
        $sharedLookup = [];
        $sheetXml = [];

        foreach ($sheets as $sheetName => $rows) {
            $cells = [];

            foreach ($rows as $rowIndex => $row) {
                $cellXml = [];

                foreach (array_values($row) as $columnIndex => $value) {
                    $index = $this->sharedStringIndex((string) $value, $sharedStrings, $sharedLookup);
                    $reference = $this->columnNameFromIndex($columnIndex).($rowIndex + 1);
                    $cellXml[] = sprintf('<c r="%s" t="s"><v>%d</v></c>', $reference, $index);
                }

                $cells[] = sprintf('<row r="%d">%s</row>', $rowIndex + 1, implode('', $cellXml));
            }

            $sheetXml[] = [
                'name' => $sheetName,
                'xml' => sprintf(
                    '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>%s</sheetData></worksheet>',
                    implode('', $cells)
                ),
            ];
        }

        $zip = new ZipArchive();
        $zip->open($xlsxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml(count($sheetXml)));
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml($sheetXml));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml(count($sheetXml)));
        $zip->addFromString('xl/sharedStrings.xml', $this->sharedStringsXml($sharedStrings));

        foreach ($sheetXml as $index => $sheet) {
            $zip->addFromString('xl/worksheets/sheet'.($index + 1).'.xml', $sheet['xml']);
        }

        $zip->close();

        return $xlsxPath;
    }

    protected function sharedStringIndex(string $value, array &$sharedStrings, array &$sharedLookup): int
    {
        if (array_key_exists($value, $sharedLookup)) {
            return $sharedLookup[$value];
        }

        $sharedLookup[$value] = count($sharedStrings);
        $sharedStrings[] = $value;

        return $sharedLookup[$value];
    }

    protected function sharedStringsXml(array $sharedStrings): string
    {
        $items = array_map(function (string $value): string {
            return '<si><t>'.htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></si>';
        }, $sharedStrings);

        return sprintf(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="%d" uniqueCount="%d">%s</sst>',
            count($sharedStrings),
            count($sharedStrings),
            implode('', $items)
        );
    }

    protected function workbookXml(array $sheetXml): string
    {
        $sheets = [];

        foreach ($sheetXml as $index => $sheet) {
            $name = htmlspecialchars($sheet['name'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $sheetId = $index + 1;
            $sheets[] = sprintf('<sheet name="%s" sheetId="%d" r:id="rId%d"/>', $name, $sheetId, $sheetId);
        }

        return sprintf(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>%s</sheets></workbook>',
            implode('', $sheets)
        );
    }

    protected function workbookRelsXml(int $sheetCount): string
    {
        $relationships = [];

        for ($i = 1; $i <= $sheetCount; $i++) {
            $relationships[] = sprintf(
                '<Relationship Id="rId%d" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet%d.xml"/>',
                $i,
                $i
            );
        }

        return sprintf(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">%s</Relationships>',
            implode('', $relationships)
        );
    }

    protected function rootRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    protected function contentTypesXml(int $sheetCount): string
    {
        $overrides = [];

        for ($i = 1; $i <= $sheetCount; $i++) {
            $overrides[] = sprintf('<Override PartName="/xl/worksheets/sheet%d.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>', $i);
        }

        return sprintf(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/_rels/.rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>%s</Types>',
            implode('', $overrides)
        );
    }

    protected function columnNameFromIndex(int $index): string
    {
        $index++;
        $name = '';

        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $name = chr(65 + $remainder).$name;
            $index = intdiv($index - 1, 26);
        }

        return $name;
    }

    protected function createTables(): void
    {
        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('channel', 20)->default('online');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_category_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('barcode')->nullable()->index();
            $table->string('unit')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('store_price', 10, 2)->nullable();
            $table->decimal('online_price', 10, 2)->nullable();
            $table->decimal('stock_quantity', 10, 3)->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->string('sale_channel', 20)->default('online');
            $table->unsignedInteger('position')->default(0);
            $table->string('short_description')->nullable();
            $table->text('summary')->nullable();
            $table->string('content_status', 20)->default('draft');
            $table->timestamp('last_imported_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_import_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('source_name');
            $table->string('source_path')->nullable();
            $table->string('status', 20)->default('pending');
            $table->json('summary')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_import_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_import_batch_id');
            $table->unsignedInteger('row_number');
            $table->string('barcode')->nullable();
            $table->string('stock_name')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->string('action', 20)->default('skip');
            $table->string('status', 20)->default('pending');
            $table->text('message')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('price_rules', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->string('scope_type', 20)->default('global');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->string('operation_type', 20)->nullable();
            $table->decimal('override_price', 10, 2)->nullable();
            $table->decimal('percent_adjustment', 10, 2)->nullable();
            $table->decimal('fixed_adjustment', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('rounding_type', 20)->nullable();
            $table->decimal('rounding_step', 10, 2)->nullable();
            $table->timestamps();
        });
    }
}
