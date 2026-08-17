<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class Ayarlar extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.ayarlar';

    protected static string|UnitEnum|null $navigationGroup = 'Araçlar';

    public ?array $data = [];

    protected function getHeaderActions(): array
    {
        return []; // Artık tab içindeki butonları kullanıyoruz
    }

    public function mount(): void
    {
        $settings = Setting::all()
            ->groupBy('group')
            ->map(function ($items) {
                return $items->pluck('value', 'key');
            })
            ->toArray();
        $mergedSettings = [];
        // merge them.
        foreach ($settings as $group => $items) {
            foreach ($items as $key => $value) {
                $setting = Setting::where('key', $key)->first();
                $mergedSettings[$group][$key] = $value;
            }
        }
        // dd($mergedSettings);
        $this->form->fill($mergedSettings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs($this->generateTabs()),
            ])
            ->statePath('data');
    }

    protected function generateTabs(): array
    {
        $settings = Setting::all()->groupBy('group');

        return $settings->map(function (Collection $moduleSettings, string $module) {
            $tabSchema = [];

            // Sadece süper admin için her tab'ın başına "Yeni Ayar Ekle" butonu ekle
            if (Auth::check() && Auth::user()->email === 'mgakif@gmail.com') {
                $addUrl = route('filament.admin.resources.settings.create', ['group' => $module]);
                $tabSchema[] = Placeholder::make('add_new_setting')
                    ->label('')
                    ->content(new \Illuminate\Support\HtmlString(
                        view('filament.components.add-setting-button', ['url' => $addUrl])->render()
                    ));
            }

            // Ayar alanlarını ekle
            $settingFields = $moduleSettings->map(function ($setting) {
                return $this->generateField($setting);
            })->toArray();

            $tabSchema = array_merge($tabSchema, $settingFields);

            return Tab::make($module)
                ->label(str($module)->title()->replace('_', ' '))
                ->schema($tabSchema);
        })->toArray();
    }

    public function generateField($setting)
    {
        $label = str($setting->key)->title()->replace('_', ' ');
        $name = "{$setting->group}.{$setting->key}";

        // Düzenleme bağlantısını içeren etiket oluşturma
        $editUrl = route('filament.admin.resources.settings.edit', ['record' => $setting->id]);
        $showEditButton = Auth::check() && Auth::user()->email === 'mgakif@gmail.com';
        
        $editHtml = view('filament.components.setting-label', [
            'label' => $label,
            'editUrl' => $editUrl,
            'showEditButton' => $showEditButton,
        ])->render();

        return match ($setting->type) {
            'text' => TextInput::make($name)
                ->label(fn () => new \Illuminate\Support\HtmlString($editHtml))
                ->extraAttributes(['class' => 'setting-field'])
                ->columnSpan('full'),

            'textarea' => Textarea::make($name)
                ->label(fn () => new \Illuminate\Support\HtmlString($editHtml))
                ->extraAttributes(['class' => 'setting-field'])
                ->columnSpan('full'),
            'richtext' => RichEditor::make($name)
                ->label(fn () => new \Illuminate\Support\HtmlString($editHtml))
                ->toolbarButtons([
                    'attachFiles',
                    'blockquote',
                    'bold',
                    'bulletList',
                    'codeBlock',
                    'h2',
                    'h3',
                    'italic',
                    'link',
                    'orderedList',
                    'redo',
                    'strike',
                    'underline',
                    'undo',
                ])
                ->extraAttributes(['class' => 'setting-field'])
                ->columnSpan('full'),

            'boolean' => Toggle::make($name)
                ->label(fn () => new \Illuminate\Support\HtmlString($editHtml))
                ->extraAttributes(['class' => 'setting-field'])
                ->columnSpan('full'),

            'select' => Select::make($name)
                ->label(fn () => new \Illuminate\Support\HtmlString($editHtml))
                ->options(function () use ($setting) {
                    return $setting->attributes['options'];
                })
                ->extraAttributes(['class' => 'setting-field'])
                ->columnSpan('full'),

            'file' => FileUpload::make($name)
                ->label(fn () => new \Illuminate\Support\HtmlString($editHtml))
                ->directory('settings')
                ->downloadable()
                ->openable()
                ->disk('public')
                ->uploadingMessage('yükleniyor...')
                ->extraAttributes(['class' => 'setting-field'])
                ->columnSpan('full'),

            'image' => FileUpload::make($name)
                ->label(fn () => new \Illuminate\Support\HtmlString($editHtml))
                ->imageEditor()
                ->downloadable()
                ->openable()
                ->uploadingMessage('yükleniyor...')
                ->image()
                ->disk('public')
                ->directory('settings')
                ->extraAttributes(['class' => 'setting-field'])
                ->columnSpan('full'),

            default => TextInput::make($name)
                ->label(fn () => new \Illuminate\Support\HtmlString($editHtml))
                ->extraAttributes(['class' => 'setting-field'])
                ->columnSpan('full')
        };
    }

    public function save(): void
    {
        $settings = [];

        foreach ($this->form->getState() as $group) {
            if (is_array($group)) {
                foreach ($group as $key => $value) {
                    $settings[$key] = $value;
                }
            }
        }
        // dd($settings); bazı ayarlar
        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $setting->value = $value;
                $setting->save();
            }
        }

        Notification::make()
            ->title('Ayarlar başarıyla kaydedildi...')
            ->success()
            ->send();
    }
}
