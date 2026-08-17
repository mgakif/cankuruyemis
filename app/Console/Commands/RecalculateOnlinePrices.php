<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Pricing\OnlinePriceCalculator;
use Illuminate\Console\Command;

class RecalculateOnlinePrices extends Command
{
    protected $signature = 'products:recalculate-online-prices {--channel=online : Hangi kanal icin fiyat hesaplanacak}';

    protected $description = 'Aktif fiyat kurallarini mevcut urunlere uygular.';

    public function handle(OnlinePriceCalculator $calculator): int
    {
        $channel = (string) $this->option('channel');
        $updated = 0;

        Product::query()
            ->where('sale_channel', $channel)
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($calculator, &$updated) {
                foreach ($products as $product) {
                    $rule = $calculator->resolveRule($product);
                    $price = $calculator->calculate((float) $product->store_price, $rule);

                    if ((float) $product->online_price !== $price) {
                        $product->forceFill([
                            'online_price' => $price,
                        ])->save();

                        $updated++;
                    }
                }
            });

        $this->info("Guncellenen urun sayisi: {$updated}");

        return self::SUCCESS;
    }
}
