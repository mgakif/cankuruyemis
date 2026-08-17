<?php

namespace App\Services\Pricing;

use App\Models\PriceRule;
use App\Models\Product;

class OnlinePriceCalculator
{
    public function calculate(float $storePrice, ?PriceRule $rule = null): float
    {
        if (! $rule) {
            return round($storePrice, 2);
        }

        if ($rule->operation_type === 'override' && $rule->override_price !== null) {
            $price = (float) $rule->override_price;
        } else {
            $percent = (float) ($rule->percent_adjustment ?? 0);
            $fixed = (float) ($rule->fixed_adjustment ?? 0);

            if ($percent === 0.0 && $fixed === 0.0) {
                $percent = $rule->operation_type === 'percent' ? (float) $rule->amount : 0.0;
                $fixed = $rule->operation_type === 'fixed' ? (float) $rule->amount : 0.0;
            }

            $price = $storePrice + ($storePrice * ($percent / 100)) + $fixed;
        }

        $price = $this->applyRounding($price, $rule);

        return round(max($price, 0), 2);
    }

    public function resolveRule(Product $product): ?PriceRule
    {
        return PriceRule::query()
            ->active()
            ->where(function ($query) use ($product) {
                $query
                    ->where('scope_type', 'global')
                    ->orWhere(function ($nested) use ($product) {
                        $nested
                            ->where('scope_type', 'category')
                            ->where('scope_id', $product->product_category_id);
                    });

                if ($product->exists) {
                    $query->orWhere(function ($nested) use ($product) {
                        $nested
                            ->where('scope_type', 'product')
                            ->where('scope_id', $product->id);
                    });
                }
            })
            ->orderByRaw("
                case
                    when scope_type = 'product' then 1
                    when scope_type = 'category' then 2
                    else 3
                end
            ")
            ->first();
    }

    protected function applyRounding(float $price, PriceRule $rule): float
    {
        return match ($rule->rounding_type) {
            'nearest_step' => $this->roundToStep($price, (float) ($rule->rounding_step ?: 1)),
            'up_step' => ceil($price / max((float) ($rule->rounding_step ?: 1), 0.01)) * (float) ($rule->rounding_step ?: 1),
            'down_step' => floor($price / max((float) ($rule->rounding_step ?: 1), 0.01)) * (float) ($rule->rounding_step ?: 1),
            'psychological_99' => floor($price) + 0.99,
            'psychological_95' => floor($price) + 0.95,
            default => $price,
        };
    }

    protected function roundToStep(float $price, float $step): float
    {
        $step = max($step, 0.01);

        return round($price / $step) * $step;
    }
}
