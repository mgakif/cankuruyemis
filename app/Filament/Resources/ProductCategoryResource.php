<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductCategoryResource\Pages;
use App\Models\ProductCategory;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Katalog';
    protected static ?string $navigationLabel = 'Kategoriler';
    protected static ?string $modelLabel = 'Kategori';
    protected static ?string $pluralModelLabel = 'Kategoriler';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Kategori')
                ->schema([
                    Forms\Components\Select::make('parent_id')
                        ->label('Ust Kategori')
                        ->options(ProductCategory::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('name')
                        ->label('Ad')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            if ($state) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(table: 'product_categories', column: 'slug', ignoreRecord: true),

                    Forms\Components\Select::make('channel')
                        ->label('Varsayilan Kanal')
                        ->options([
                            'online' => 'Online',
                            'store_only' => 'Sadece Magazada',
                            'hidden' => 'Gizli',
                        ])
                        ->default('online')
                        ->required(),

                    Forms\Components\TextInput::make('position')
                        ->label('Siralama')
                        ->numeric()
                        ->default(0),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),

                    Forms\Components\Textarea::make('description')
                        ->label('Aciklama')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Kategori')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('parent.name')->label('Ust Kategori')->toggleable(),
                Tables\Columns\TextColumn::make('channel')->label('Kanal')->badge(),
                Tables\Columns\TextColumn::make('position')->label('#')->sortable(),
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
            'index' => Pages\ListProductCategories::route('/'),
            'create' => Pages\CreateProductCategory::route('/create'),
            'edit' => Pages\EditProductCategory::route('/{record}/edit'),
        ];
    }
}
