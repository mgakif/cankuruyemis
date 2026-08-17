<div class="flex items-center justify-between mb-1">
    <span>{{ $label }}</span>
    @if($showEditButton)
        <x-filament::button
            :href="$editUrl"
            tag="a"
            icon="heroicon-o-pencil"
            size="xs"
            color="primary"
            class="ml-2"
            :tooltip="__('Düzenle')"
        >
            Düzenle
        </x-filament::button>
    @endif
</div>

