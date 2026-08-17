<?php

namespace App\Filament\Resources\PriceRuleResource\Pages;

use App\Filament\Resources\PriceRuleResource;
use App\Services\Pricing\OnlinePriceCalculator;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPriceRules extends ListRecords
{
    protected static string $resource = PriceRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('applyRules')
                ->label('Kurallari Uygula')
                ->icon('heroicon-o-bolt')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (OnlinePriceCalculator $calculator): void {
                    $updated = 0;

                    \App\Models\Product::query()
                        ->where('sale_channel', 'online')
                        ->orderBy('id')
                        ->chunkById(100, function ($products) use ($calculator, &$updated) {
                            foreach ($products as $product) {
                                $rule = $calculator->resolveRule($product);
                                $price = $calculator->calculate((float) $product->store_price, $rule);

                                if ((float) $product->online_price !== $price) {
                                    $product->forceFill(['online_price' => $price])->save();
                                    $updated++;
                                }
                            }
                        });

                    Notification::make()
                        ->title('Fiyat kurallari uygulandi')
                        ->body("Guncellenen online urun: {$updated}")
                        ->success()
                        ->send();
                }),
        ];
    }
}
