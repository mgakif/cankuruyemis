<?php

namespace App\Filament\Resources\FormResource\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class FormForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Form Ayarları')
                    ->tabs([
                        Tabs\Tab::make('Genel Bilgiler')
                            ->schema([
                                TextInput::make('name')->label('Form Adı')
                                    ->required()
                                    ->maxLength(255)
                                    ->before(function (callable $get, callable $set, ?string $state) {
                                        if (! $get('slug')) {
                                            $set('slug', \Str::slug($state));
                                        }
                                    })
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (callable $get, callable $set, ?string $old, ?string $state) {
                                        if (($get('slug') ?? '') !== \Str::slug($old)) {
                                            return;
                                        }

                                        $set('slug', \Str::slug($state));
                                    }),
                                TextInput::make('slug')->label('URL')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('name_key')
                                    ->label('İsim Alanı')
                                    ->helperText('Örnek: {ad} {soyad} veya {isim} gibi')
                                    ->maxLength(255),
                                TextInput::make('email_key')
                                    ->label('E-posta Alanı')
                                    ->helperText('Örnek: {email} veya {e_posta} gibi')
                                    ->maxLength(255),
                                RichEditor::make('description')->label('Açıklama'),
                                TextInput::make('email')
                                    ->label('E-mail')
                                    ->helperText('Form doldurulunca e-posta gelmesi için doldurulan alan.')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('brief')->label('Kısa Açıklama')
                                    ->maxLength(255),
                                TextInput::make('trello_id')->label('Trello Kartı ID')
                                    ->helperText('Trello Kartı ID, liste ID ile birlikte kullanılır.')
                                    ->maxLength(255),
                                
                                Toggle::make('recaptcha')
                                    ->label('Google reCAPTCHA Aktif')
                                    ->helperText('Form gönderiminde Google reCAPTCHA doğrulaması yapılır.')
                                    ->default(false),

                                Textarea::make('success_message')->label('Başarı Mesajı')
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Erişim Kontrolü')
                            ->schema([
                                Toggle::make('requires_token')
                                    ->label('Token Gereksinimi')
                                    ->helperText('Aktif edildiğinde, form sadece doğru token ile erişilebilir olur. SEO botlarından ve direkt erişimden korunur.')
                                    ->default(false)
                                    ->live()
                                    ->columnSpanFull(),

                                TextInput::make('access_token')
                                    ->label('Erişim Token')
                                    ->helperText('Örnek: whatsapptandanyonlendirilen - Form URL\'sine ?form=TOKEN eklenerek erişilir')
                                    ->maxLength(255)
                                    ->visible(fn (callable $get) => $get('requires_token'))
                                    ->required(fn (callable $get) => $get('requires_token'))
                                    ->columnSpanFull()
                                    ->suffix('Örnek Link Önizleme')
                                    ->hint(function (callable $get) {
                                        $slug = $get('slug');
                                        $token = $get('access_token');
                                        if ($slug && $token) {
                                            return url("/{$slug}?form={$token}");
                                        }
                                        return 'Token ve URL girildiğinde önizleme görünecek';
                                    })
                                    ->hintColor('primary'),
                            ]),

                        Tabs\Tab::make('Form Alanları')
                            ->schema([
                                Repeater::make('fields')
                                    ->label('Form Alanları')
                                    ->relationship('fields')
                                    ->cloneable()
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('Etiket')
                                            ->required(),

                                        TextInput::make('name')
                                            ->label('Alan Adı')
                                            ->required(),

                                        Select::make('type')
                                            ->label('Tür')
                                            ->options([
                                                'text' => 'Text',
                                                'textarea' => 'Textarea',
                                                'email' => 'Email',
                                                'number' => 'Sayısal',
                                                'select' => 'Select',
                                                'checkbox' => 'Checkbox',
                                                'radio' => 'Radio',
                                                'toggle' => 'Toggle',
                                                'file' => 'File',
                                                'image' => 'Image',
                                                'color' => 'Color',
                                                'date' => 'Date',
                                                'datetime' => 'Datetime',
                                                'time' => 'Time',
                                                'il' => 'İl',
                                                'ilce' => 'İlçe',
                                                'description' => 'Açıklama',
                                            ])
                                            ->required(),

                                        Toggle::make('required')
                                            ->label('Zorunlu mu?'),

                                        TextInput::make('position')
                                            ->label('Sıra')
                                            ->numeric(),
                                        
                                        Textarea::make('placeholder')
                                            ->label('Metin Alanı')
                                            ->visible(function (callable $get) {
                                                return $get('type') == 'description';
                                            }),

                                        TextInput::make('group')
                                            ->label('Grup adı')
                                            ->helperText('Formu Gruplamak isterseniz buraya aynı değerleri girin'),
                                        TextInput::make('step')
                                            ->label('Adım')
                                            ->helperText('Form sihirbazı şeklinde yapmak için adım numaralarını girin'),

                                        Select::make('column_width')
                                            ->label('Kolon')
                                            ->options([
                                                1 => '1 kolon',
                                                2 => '2 kolon',
                                                3 => '3 kolon',
                                                4 => '4 kolon',
                                                5 => '5 kolon',
                                                6 => '6 kolon',
                                                7 => '7 kolon',
                                                8 => '8 kolon',
                                                9 => '9 kolon',
                                                10 => '10 kolon',
                                                11 => '11 kolon',
                                                12 => '12 kolon',
                                            ])
                                            ->default(6),
                                        Repeater::make('options.key')
                                            ->label('Seçenekler')
                                            ->grid(2)
                                            ->simple(
                                                TextInput::make('key')
                                                    ->required(),
                                            )
                                            ->visible(function (callable $get) {
                                                return $get('type') == 'select' || $get('type') == 'radio' || $get('type') == 'checkbox';
                                            }),
                                        Repeater::make('options.file_types')
                                            ->label('Seçenekler')
                                            ->grid(2)
                                            ->simple(
                                                TextInput::make('key')
                                                    ->required(),
                                            )
                                            ->visible(function (callable $get) {
                                                return $get('type') == 'file';
                                            }),
                                    ])
                                    ->columns(1)
                                    ->collapsed()
                                    ->orderColumn('position')
                                    ->addActionLabel('Alan Ekle')
                                    ->itemLabel(fn (array $state): ?string => $state['label'].'   -    '.$state['column_width'] ?? null),
                            ]),
                    ]),
            ]);
    }
}
