<?php

namespace App\Filament\Resources\FaqResource\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('question')
                    ->label('Soru')
                    ->required()
                    ->maxLength(255),
                Textarea::make('answer')
                    ->label('Cevap')
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
                TextInput::make('order')
                    ->label('Sıra')
                    ->numeric()
                    ->default(0)
                    ->helperText('Sıralama için kullanılır. Daha düşük değerler önce gösterilir.'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->helperText('Aktif olmayan FAQ\'lar frontend\'de gösterilmez.'),
            ]);
    }
}

