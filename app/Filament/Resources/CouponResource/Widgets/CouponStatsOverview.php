<?php

namespace App\Filament\Resources\CouponResource\Widgets;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CouponStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Toplam Kupon', Coupon::query()->count()),
            Stat::make('Dağıtılan Hak', Coupon::query()->sum('initial_quantity')),
            Stat::make('Kullanılan Hak', CouponUsage::query()->sum('quantity')),
            Stat::make('Kalan Aktif Hak', Coupon::query()
                ->where('status', Coupon::STATUS_ACTIVE)
                ->where('expires_at', '>=', now())
                ->sum('remaining_quantity')),
            Stat::make('Süresi Dolan Kullanılmamış Hak', Coupon::query()
                ->where('status', Coupon::STATUS_ACTIVE)
                ->where('expires_at', '<', now())
                ->sum('remaining_quantity')),
        ];
    }
}
