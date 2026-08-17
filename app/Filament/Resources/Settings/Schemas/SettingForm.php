<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use App\Models\Setting;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                ->label(__('Ayar adı'))
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(\App\Helper\Slug::make())
                ->unique(ignoreRecord: true),

            TextInput::make('slug')
                ->label(__('slug')),

            Select::make('type')
                ->label(__('Field Type'))
                ->searchable()
                ->options([
                    'text' => 'Tek Satır',
                    'textarea' => 'Çoklu Satır',
                    'richtext' => 'Zengin Metin',
                    'number' => 'Sayı',
                    'email' => 'E-posta',
                    'tel' => 'Telefon',
                    'url' => 'URL',
                    'color' => 'Renk',
                    'date' => 'Tarih',
                    'datetime' => 'Tarih Saat',
                    'time' => 'Saat',
                    'checkbox' => 'Onay Kutusu',
                    'toggle' => 'Açma Kapama',
                    'radio' => 'Radyo',
                    'boolean' => 'Evet Hayır',
                    'select' => 'Seçim',
                    'file' => 'Dosya',
                    'image' => 'Resim',
                ])
                ->live(),

            TextInput::make('group')
                ->label(__('Group'))
                ->datalist(Setting::pluck('group')->toArray())
                ->default(fn () => request()->get('group', '')),

            Repeater::make('attributes.options')
                ->label(__('default.Options'))
                ->grid(2)
                ->simple(
                    TextInput::make('key')
                        ->required(),
                )
                ->visible(function (callable $get) {
                    return $get('type') == 'select';
                }),
            ]);
    }
}
