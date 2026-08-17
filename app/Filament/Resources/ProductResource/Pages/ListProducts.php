<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Services\Imports\ProductCatalogImporter;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    public function getTabs(): array
    {
        $storeOnlyVisible = Product::shouldShowStoreOnlyOnSite();

        $tabs = [
            'all' => Tab::make('Tum Urunler')
                ->badge(Product::query()->count()),

            'online' => Tab::make('Online')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sale_channel', 'online'))
                ->badge(Product::query()->where('sale_channel', 'online')->count()),

            'online_missing_content' => Tab::make('Icerigi Eksik Online')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('sale_channel', 'online')
                    ->where(function (Builder $contentQuery) {
                        $contentQuery
                            ->whereNull('short_description')
                            ->orWhere('short_description', '')
                            ->orWhereNull('summary')
                            ->orWhere('summary', '')
                            ->orWhereNull('description')
                            ->orWhere('description', '')
                            ->orWhereNull('seo_title')
                            ->orWhere('seo_title', '')
                            ->orWhereNull('seo_description')
                            ->orWhere('seo_description', '');
                    }))
                ->badge(Product::query()
                    ->where('sale_channel', 'online')
                    ->where(function (Builder $contentQuery) {
                        $contentQuery
                            ->whereNull('short_description')
                            ->orWhere('short_description', '')
                            ->orWhereNull('summary')
                            ->orWhere('summary', '')
                            ->orWhereNull('description')
                            ->orWhere('description', '')
                            ->orWhereNull('seo_title')
                            ->orWhere('seo_title', '')
                            ->orWhereNull('seo_description')
                            ->orWhere('seo_description', '');
                    })
                    ->count()),
        ];

        if ($storeOnlyVisible) {
            $tabs['store_only'] = Tab::make('Sadece Magazada')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sale_channel', 'store_only'))
                ->badge(Product::query()->where('sale_channel', 'store_only')->count());
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('dryRunImport')
                ->label('Excel Dry Run')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('gray')
                ->form([
                    FileUpload::make('xlsx')
                        ->label('Excel Dosyasi')
                        ->disk('public')
                        ->directory('imports/products')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->required(),
                ])
                ->action(function (array $data, ProductCatalogImporter $importer): void {
                    $batch = $importer->import($data['xlsx'], true);
                    $summary = $batch->summary ?? [];

                    Notification::make()
                        ->title('Dry run tamamlandi')
                        ->body("Olusacak: ".($summary['created'] ?? 0).", guncellenecek: ".($summary['updated'] ?? 0).", atlanacak: ".($summary['skipped'] ?? 0))
                        ->success()
                        ->send();
                }),
            Actions\Action::make('importExcel')
                ->label('Excel Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->requiresConfirmation()
                ->modalDescription('Excel dosyasinda "Tüm Ürünler" ana katalog olarak okunur, "Online Ürünler" sayfasindaki urunler online olarak isaretlenir. Barkoda gore mevcut urunler guncellenir, yoksa yeni urun olusturulur.')
                ->form([
                    FileUpload::make('xlsx')
                        ->label('Excel Dosyasi')
                        ->disk('public')
                        ->directory('imports/products')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->required(),
                ])
                ->action(function (array $data, ProductCatalogImporter $importer): void {
                    $batch = $importer->import($data['xlsx'], false);
                    $summary = $batch->summary ?? [];

                    Notification::make()
                        ->title('Import tamamlandi')
                        ->body("Olusan: ".($summary['created'] ?? 0).", guncellenen: ".($summary['updated'] ?? 0).", atlanan: ".($summary['skipped'] ?? 0))
                        ->success()
                        ->send();
                }),
        ];
    }
}
