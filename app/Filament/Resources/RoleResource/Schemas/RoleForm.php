<?php

namespace App\Filament\Resources\RoleResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Rol Adı')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('permissions')
                    ->label('İzinler')
                    ->multiple()
                    ->relationship('permissions', 'name')
                    ->preload(),
            ]);
    }
}

