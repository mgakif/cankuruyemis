<?php

namespace App\Filament\Resources\FaqResource\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->columns([
                TextColumn::make('question')->label('Soru')->sortable()->searchable(),
                TextColumn::make('order')->label('Sıra')->sortable(),
                IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->defaultSort('order')
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}

