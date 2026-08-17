<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Katalog';
    protected static ?string $navigationLabel = 'Urunler';
    protected static ?string $modelLabel = 'Urun';
    protected static ?string $pluralModelLabel = 'Urunler';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Temel Bilgiler')
                ->schema([
                    Forms\Components\Select::make('product_category_id')
                        ->label('Kategori')
                        ->options(ProductCategory::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('sale_channel')
                        ->label('Satis Kanali')
                        ->options([
                            'online' => 'Online',
                            'store_only' => 'Sadece Magazada',
                            'hidden' => 'Gizli',
                        ])
                        ->required()
                        ->default('online'),

                    Forms\Components\TextInput::make('title')
                        ->label('Urun Adi')
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
                        ->maxLength(255)
                        ->unique(table: 'products', column: 'slug', ignoreRecord: true),

                    Forms\Components\TextInput::make('barcode')
                        ->label('Barkod')
                        ->maxLength(255)
                        ->unique(table: 'products', column: 'barcode', ignoreRecord: true)
                        ->helperText('Excel import eslestirmesinde ana anahtar olarak kullanilir.'),

                    Forms\Components\TextInput::make('sku')
                        ->label('SKU')
                        ->maxLength(255)
                        ->unique(table: 'products', column: 'sku', ignoreRecord: true),

                    Forms\Components\TextInput::make('brand')
                        ->label('Marka')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('unit')
                        ->label('Birim')
                        ->maxLength(20)
                        ->placeholder('KG veya ADET'),

                    Forms\Components\TextInput::make('package_size')
                        ->label('Paket / Gramaj')
                        ->maxLength(255)
                        ->placeholder('250 g, 500 g, 1 kg'),

                    Forms\Components\TextInput::make('position')
                        ->label('Siralama')
                        ->numeric()
                        ->default(0),
                ])
                ->columns(3),

            Section::make('Fiyat ve Stok')
                ->schema([
                    Forms\Components\TextInput::make('store_price')
                        ->label('Magaza Fiyati')
                        ->numeric()
                        ->prefix('TL')
                        ->required()
                        ->default(0),

                    Forms\Components\TextInput::make('online_price')
                        ->label('Online Fiyati')
                        ->numeric()
                        ->prefix('TL')
                        ->required()
                        ->default(0)
                        ->helperText('Kuralla hesaplanabilir, gerekirse manuel override edebilirsin.'),

                    Forms\Components\TextInput::make('stock_quantity')
                        ->label('Stok Miktari')
                        ->numeric()
                        ->default(0),
                ])
                ->columns(3),

            Section::make('Icerik')
                ->schema([
                    Forms\Components\Textarea::make('short_description')
                        ->label('Kisa Aciklama')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('summary')
                        ->label('Ozet')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('description')
                        ->label('Detayli Aciklama')
                        ->columnSpanFull(),

                    Forms\Components\Repeater::make('specifications')
                        ->label('Urun Ozellikleri')
                        ->schema([
                            Forms\Components\TextInput::make('key')->label('Alan')->required(),
                            Forms\Components\TextInput::make('value')->label('Deger')->required(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),

                    Forms\Components\TagsInput::make('tags')
                        ->label('Etiketler')
                        ->columnSpanFull(),

                    Forms\Components\TagsInput::make('hashtags')
                        ->label('Hashtagler')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Besin Degerleri')
                ->schema([
                    Forms\Components\TextInput::make('energy_kcal')
                        ->label('Enerji (kcal / 100g)')
                        ->numeric(),

                    Forms\Components\KeyValue::make('nutrition_facts')
                        ->label('Besin Tablosu')
                        ->keyLabel('Alan')
                        ->valueLabel('Deger')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('ingredients')
                        ->label('Icindekiler')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('allergen_info')
                        ->label('Alerjen Bilgisi')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible(),

            Section::make('Gorseller ve SEO')
                ->schema([
                    Forms\Components\FileUpload::make('featured_image_path')
                        ->label('Kapak Gorseli')
                        ->image()
                        ->disk('public')
                        ->directory('products/featured')
                        ->visibility('public'),

                    Forms\Components\FileUpload::make('gallery')
                        ->label('Galeri')
                        ->image()
                        ->multiple()
                        ->disk('public')
                        ->directory('products/gallery')
                        ->visibility('public')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('seo_title')
                        ->label('SEO Title')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('seo_description')
                        ->label('SEO Description')
                        ->rows(3),

                    Forms\Components\FileUpload::make('seo_image_path')
                        ->label('SEO Gorseli')
                        ->image()
                        ->disk('public')
                        ->directory('products/seo')
                        ->visibility('public'),
                ])
                ->columns(2),

            Section::make('Yayin')
                ->schema([
                    Forms\Components\Select::make('content_status')
                        ->label('Icerik Durumu')
                        ->options([
                            'draft' => 'Taslak',
                            'ready_for_ai' => 'AI Hazir',
                            'generated' => 'AI Uretildi',
                            'reviewed' => 'Kontrol Edildi',
                        ])
                        ->default('draft')
                        ->required(),

                    Forms\Components\Toggle::make('is_published')
                        ->label('Yayinda')
                        ->default(false),

                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Yayin Tarihi')
                        ->seconds(false),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                Tables\Columns\TextColumn::make('position')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Urun')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('barcode')
                    ->label('Barkod')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sale_channel')
                    ->label('Kanal')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'store_only' => 'Magaza',
                        'hidden' => 'Gizli',
                        default => 'Online',
                    }),

                Tables\Columns\TextColumn::make('store_price')
                    ->label('Magaza')
                    ->money('TRY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('online_price')
                    ->label('Online')
                    ->money('TRY')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stok')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Yayin')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('sale_channel')
                    ->label('Satis Kanali')
                    ->options([
                        'online' => 'Online',
                        'store_only' => 'Sadece Magazada',
                        'hidden' => 'Gizli',
                    ]),

                SelectFilter::make('content_status')
                    ->label('Icerik Durumu')
                    ->options([
                        'draft' => 'Taslak',
                        'ready_for_ai' => 'AI Hazir',
                        'generated' => 'AI Uretildi',
                        'reviewed' => 'Kontrol Edildi',
                    ]),

                TernaryFilter::make('is_published')
                    ->label('Yayin Durumu')
                    ->placeholder('Hepsi')
                    ->trueLabel('Yayinda')
                    ->falseLabel('Taslak'),

                Filter::make('stock_state')
                    ->label('Stok')
                    ->form([
                        Forms\Components\Select::make('mode')
                            ->label('Stok Durumu')
                            ->options([
                                'in_stock' => 'Stokta var',
                                'out_of_stock' => 'Stok yok / eksi',
                                'negative_stock' => 'Eksi stok',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['mode'] ?? null) {
                            'in_stock' => $query->where('stock_quantity', '>', 0),
                            'out_of_stock' => $query->where('stock_quantity', '<=', 0),
                            'negative_stock' => $query->where('stock_quantity', '<', 0),
                            default => $query,
                        };
                    }),

                Filter::make('missing_media')
                    ->label('Gorsel Eksik')
                    ->query(fn (Builder $query): Builder => $query->whereNull('featured_image_path')),

                Filter::make('missing_seo')
                    ->label('SEO Eksik')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $seoQuery) {
                        $seoQuery
                            ->whereNull('seo_title')
                            ->orWhere('seo_title', '')
                            ->orWhereNull('seo_description')
                            ->orWhere('seo_description', '');
                    })),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('markAsStoreOnly')
                        ->label('Secilileri Sadece Magazada Yap')
                        ->icon('heroicon-o-building-storefront')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (EloquentCollection $records): void {
                            $records->each->update([
                                'sale_channel' => 'store_only',
                            ]);
                        }),

                    BulkAction::make('markAsOnline')
                        ->label('Secilileri Online Yap')
                        ->icon('heroicon-o-globe-alt')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (EloquentCollection $records): void {
                            $records->each->update([
                                'sale_channel' => 'online',
                            ]);
                        }),

                    BulkAction::make('markAsReadyForAi')
                        ->label('Secilileri AI Hazir Yap')
                        ->icon('heroicon-o-sparkles')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function (EloquentCollection $records): void {
                            $records->each->update([
                                'content_status' => 'ready_for_ai',
                            ]);
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
