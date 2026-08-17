<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages\ListCoupons;
use App\Filament\Resources\CouponResource\Pages\ViewCoupon;
use App\Filament\Resources\CouponResource\RelationManagers\UsagesRelationManager;
use App\Filament\Resources\CouponResource\Schemas\CouponForm;
use App\Filament\Resources\CouponResource\Tables\CouponsTable;
use App\Models\Coupon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|UnitEnum|null $navigationGroup = 'Kuponlar';

    protected static ?string $navigationLabel = 'İçecek Kuponları';

    protected static ?string $modelLabel = 'Kupon';

    protected static ?string $pluralModelLabel = 'Kuponlar';

    public static function form(Schema $schema): Schema
    {
        return CouponForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UsagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
            'view' => ViewCoupon::route('/{record}'),
        ];
    }
}
