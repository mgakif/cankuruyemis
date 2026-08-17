<?php

namespace App\Filament\Resources\CouponResource\Tables;

use App\Models\Coupon;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kupon Kodu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reward_type')
                    ->label('Tür')
                    ->formatStateUsing(fn (?string $state): string => $state === 'drink' ? 'İçecek' : (string) $state)
                    ->badge(),
                TextColumn::make('initial_quantity')
                    ->label('Başlangıç')
                    ->sortable(),
                TextColumn::make('remaining_quantity')
                    ->label('Kalan')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->formatStateUsing(fn (Coupon $record): string => match ($record->publicStatusLabel()) {
                        Coupon::STATUS_ACTIVE => 'Aktif',
                        Coupon::STATUS_FULLY_USED => 'Tamamen Kullanılmış',
                        Coupon::STATUS_EXPIRED => 'Süresi Dolmuş',
                        Coupon::STATUS_CANCELLED => 'İptal',
                        default => $record->status,
                    })
                    ->badge(),
                TextColumn::make('created_by')
                    ->label('Oluşturan')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('expires_at')
                    ->label('Son Kullanım')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Oluşturma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        Coupon::STATUS_ACTIVE => 'Aktif',
                        Coupon::STATUS_FULLY_USED => 'Tamamen Kullanılmış',
                        Coupon::STATUS_CANCELLED => 'İptal',
                    ]),
                Filter::make('expired')
                    ->label('Süresi Dolmuş')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', Coupon::STATUS_ACTIVE)
                        ->where('expires_at', '<', now())),
                Filter::make('active_remaining')
                    ->label('Aktif ve Hak Var')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', Coupon::STATUS_ACTIVE)
                        ->where('remaining_quantity', '>', 0)
                        ->where('expires_at', '>=', now())),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                Action::make('manualLookup')
                    ->label('Manuel Sorgu')
                    ->url(fn (Coupon $record): string => route('coupons.code', ['code' => $record->code]))
                    ->openUrlInNewTab(),
            ]);
    }
}
