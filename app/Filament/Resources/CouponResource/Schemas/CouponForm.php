<?php

namespace App\Filament\Resources\CouponResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Kupon Kodu')->disabled(),
            TextInput::make('reward_type')->label('Tür')->disabled(),
            TextInput::make('initial_quantity')->label('Başlangıç Hakkı')->disabled(),
            TextInput::make('remaining_quantity')->label('Kalan Hak')->disabled(),
            TextInput::make('status')->label('Durum')->disabled(),
            TextInput::make('created_by')->label('Oluşturan')->disabled(),
            DateTimePicker::make('expires_at')->label('Son Kullanım')->disabled(),
        ]);
    }
}
