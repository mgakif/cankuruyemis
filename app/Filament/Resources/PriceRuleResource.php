<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceRuleResource\Pages;
use App\Models\PriceRule;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PriceRuleResource extends Resource
{
    protected static ?string $model = PriceRule::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Katalog';
    protected static ?string $navigationLabel = 'Fiyat Kurallari';
    protected static ?string $modelLabel = 'Fiyat Kurali';
    protected static ?string $pluralModelLabel = 'Fiyat Kurallari';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calculator';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Kural')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Ad')
                        ->required(),

                    Forms\Components\Select::make('scope_type')
                        ->label('Kapsam')
                        ->options([
                            'global' => 'Tum Katalog',
                            'category' => 'Kategori',
                            'product' => 'Urun',
                        ])
                        ->required()
                        ->default('global')
                        ->live(),

                    Forms\Components\Select::make('scope_id')
                        ->label('Hedef')
                        ->options(function (callable $get) {
                            return match ($get('scope_type')) {
                                'category' => ProductCategory::query()->orderBy('name')->pluck('name', 'id'),
                                'product' => Product::query()->orderBy('title')->pluck('title', 'id'),
                                default => [],
                            };
                        })
                        ->searchable()
                        ->preload()
                        ->visible(fn (callable $get) => in_array($get('scope_type'), ['category', 'product'], true)),

                    Forms\Components\Select::make('operation_type')
                        ->label('Islem Tipi')
                        ->options([
                            'combined' => 'Yuzde + Sabit Tutar',
                            'override' => 'Direkt Fiyat',
                        ])
                        ->required()
                        ->default('combined')
                        ->live(),

                    Forms\Components\TextInput::make('percent_adjustment')
                        ->label('Yuzde Artis / Azalis')
                        ->numeric()
                        ->default(0)
                        ->helperText('Ornek: 15 veya -10')
                        ->visible(fn (callable $get) => $get('operation_type') !== 'override'),

                    Forms\Components\TextInput::make('fixed_adjustment')
                        ->label('Sabit Tutar')
                        ->numeric()
                        ->default(0)
                        ->helperText('Ornek: 50 veya -20')
                        ->visible(fn (callable $get) => $get('operation_type') !== 'override'),

                    Forms\Components\TextInput::make('override_price')
                        ->label('Direkt Online Fiyat')
                        ->numeric()
                        ->helperText('Bu alan doluysa diger artislari yok sayar.')
                        ->visible(fn (callable $get) => $get('operation_type') === 'override'),

                    Forms\Components\Select::make('rounding_type')
                        ->label('Yuvarlama')
                        ->options([
                            'none' => 'Yok',
                            'nearest_step' => 'En yakin adim',
                            'up_step' => 'Yukari adim',
                            'down_step' => 'Asagi adim',
                            'psychological_99' => '.99 bitir',
                            'psychological_95' => '.95 bitir',
                        ])
                        ->default('none'),

                    Forms\Components\TextInput::make('rounding_step')
                        ->label('Adim')
                        ->numeric()
                        ->helperText('Ornek: 1, 5, 10'),

                    Forms\Components\TextInput::make('priority')
                        ->label('Oncelik')
                        ->numeric()
                        ->default(100),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notlar')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('priority')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Kural')->searchable(),
                Tables\Columns\TextColumn::make('scope_type')->label('Kapsam')->badge(),
                Tables\Columns\TextColumn::make('operation_type')->label('Tip')->badge(),
                Tables\Columns\TextColumn::make('percent_adjustment')
                    ->label('%')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', '.')),
                Tables\Columns\TextColumn::make('fixed_adjustment')
                    ->label('Sabit')
                    ->money('TRY'),
                Tables\Columns\TextColumn::make('override_price')
                    ->label('Override')
                    ->money('TRY'),
                Tables\Columns\TextColumn::make('priority')->label('Oncelik')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPriceRules::route('/'),
            'create' => Pages\CreatePriceRule::route('/create'),
            'edit' => Pages\EditPriceRule::route('/{record}/edit'),
        ];
    }
}
