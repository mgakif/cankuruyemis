<?php

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Resources\CouponResource;
use App\Filament\Resources\CouponResource\Widgets\CouponStatsOverview;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CouponStatsOverview::class,
        ];
    }
}
