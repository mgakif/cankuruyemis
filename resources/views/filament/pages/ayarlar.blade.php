<x-filament-panels::page>
    <style>
        .fi-fo-field-wrp label a {
            display: inline-flex;
            align-items: center;
            margin-left: 0.5rem;
            font-size: 0.875rem;
            color: rgb(79 70 229);
            transition: color 0.2s;
        }

        .fi-fo-field-wrp label a:hover {
            color: rgb(67, 56, 202);
        }

        .setting-edit-link {
            font-size: 0.875rem;
        }
    </style>

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Ayarları Kaydet
            </x-filament::button>
        </div>
    </form>

</x-filament-panels::page>
