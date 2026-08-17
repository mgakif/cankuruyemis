<?php

namespace App\Filament\Resources\CouponResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'usages';

    protected static ?string $title = 'Kullanım Geçmişi';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quantity')->label('Adet')->sortable(),
                TextColumn::make('used_by')->label('Kullandıran')->searchable(),
                TextColumn::make('used_at')->label('Kullanım')->dateTime('d.m.Y H:i')->sortable(),
                IconColumn::make('expiration_override')->label('İstisna')->boolean(),
                TextColumn::make('expired_days')->label('Kaç Gün Geçmişti'),
                TextColumn::make('ip_address')->label('IP')->toggleable(),
            ])
            ->defaultSort('used_at', 'desc');
    }
}
