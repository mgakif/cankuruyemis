<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Section;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string | \UnitEnum | null $navigationGroup = 'İçerik';
    protected static ?string $navigationLabel = 'Blog Yazıları';
    protected static ?string $modelLabel = 'Yazı';
    protected static ?string $pluralModelLabel = 'Yazılar';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-newspaper';

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
                        ->unique(table: 'posts', column: 'slug', ignoreRecord: true)
                        ->rule(function () {
                            return function (string $attribute, mixed $value, \Closure $fail): void {
                                $reserved = ['admin', 'blog', 'sitemap.xml', 'robots.txt', 'up', 'storage'];
                                if (in_array((string) $value, $reserved, true)) {
                                    $fail('Bu slug rezerve edildi: '.$value);
                                }
                            };
                        }),

                    Forms\Components\Textarea::make('excerpt')
                        ->label('Özet')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\RichEditor::make('content')
                        ->label('İçerik')
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('featured_image_path')
                        ->label('Kapak Görseli')
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatioOptions([null, '16:9', '4:3', '1:1'])
                        ->disk('public')
                        ->directory('posts/featured')
                        ->visibility('public')
                        ->maxSize(4096)
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

                    Forms\Components\Toggle::make('is_indexable')
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
                        ->directory('posts/seo')
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}

