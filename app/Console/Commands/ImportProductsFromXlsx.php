<?php

namespace App\Console\Commands;

use App\Services\Imports\ProductCatalogImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportProductsFromXlsx extends Command
{
    protected $signature = 'products:import-xlsx
        {path=storage/app/public/urun.xlsx : Import edilecek xlsx dosyasinin yolu}
        {--dry-run : Veritabanina yazmadan sonuc ozetini olustur}';

    protected $description = 'Excel dosyasindaki urunleri barkod bazli eslestirerek ice aktarir.';

    public function handle(ProductCatalogImporter $importer): int
    {
        $inputPath = $this->argument('path');
        $path = str_starts_with($inputPath, DIRECTORY_SEPARATOR)
            ? $inputPath
            : (str_contains($inputPath, 'storage/app')
                ? base_path($inputPath)
                : Storage::disk('public')->path($inputPath));

        if (! file_exists($path)) {
            $this->error("Dosya bulunamadi: {$path}");

            return self::FAILURE;
        }

        $batch = $importer->import($path, (bool) $this->option('dry-run'));
        $summary = $batch->summary ?? [];

        $this->table(
            ['Batch', 'Status', 'Created', 'Updated', 'Skipped'],
            [[
                $batch->id,
                $batch->status,
                $summary['created'] ?? 0,
                $summary['updated'] ?? 0,
                $summary['skipped'] ?? 0,
            ]],
        );

        $this->info('Import tamamlandi.');

        return self::SUCCESS;
    }
}
