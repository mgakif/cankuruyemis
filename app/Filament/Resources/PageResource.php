<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\Toggle;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Section;


class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string | \UnitEnum | null $navigationGroup = 'İçerik';
    protected static ?string $navigationLabel = 'Sayfalar';
    protected static ?string $modelLabel = 'Sayfa';
    protected static ?string $pluralModelLabel = 'Sayfalar';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('İçerik')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Başlık')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            if (! $state) {
                                return;
                            }

                            $set('slug', Str::slug($state));
                        }),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(table: 'pages', column: 'slug', ignoreRecord: true)
                        ->rule(function () {
                            return function (string $attribute, mixed $value, \Closure $fail): void {
                                $reserved = ['admin', 'blog', 'sitemap.xml', 'robots.txt', 'up', 'storage'];
                                if (in_array((string) $value, $reserved, true)) {
                                    $fail('Bu slug rezerve edildi: '.$value);
                                }
                            };
                        }),

                    Forms\Components\Toggle::make('is_home')
                        ->label('Anasayfa olarak kullan')
                        ->default(false),

                    Forms\Components\RichEditor::make('content')
                        ->label('İçerik')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Yayın')
                ->schema([
                    Forms\Components\Toggle::make('is_published')
                        ->label('Yayınla')
                        ->default(false),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Yayın tarihi')
                        ->seconds(false)
                        ->helperText('Boş bırakılırsa, "Yayınla" açıksa hemen yayınlanır.'),
                ])
                ->columns(2),

            Section::make('SEO')
                ->schema([
                    Forms\Components\TextInput::make('seo_title')
                        ->label('SEO Title')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('seo_description')
                        ->label('SEO Description')
                        ->rows(3),

                    Forms\Components\TextInput::make('canonical_url')
                        ->label('Canonical URL')
                        ->maxLength(2048),

                    Toggle::make('is_indexable')
                        ->label('Indexable')
                        ->default(true),

                    Forms\Components\Toggle::make('is_followable')
                        ->label('Followable')
                        ->default(true),

                    Forms\Components\FileUpload::make('seo_image_path')
                        ->label('OG Image')
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatioOptions([null, '1:1', '16:9'])
                        ->disk('public')
                        ->directory('pages/seo')
                        ->visibility('public')
                        ->maxSize(4096),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_home')
                    ->label('Home')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Yayın')
                    ->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Yayın')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}

