@props(['slug'])

@php
    $menu = \Biostate\FilamentMenuBuilder\Models\Menu::where('slug', $slug)->first();
    if ($menu) {
        // NestedSet kullanıldığı için root items'ı almak için whereNull('parent_id') kullanıyoruz
        $menuItems = $menu->items()->whereNull('parent_id')->orderBy('_lft')->with('children')->get();
    } else {
        $menuItems = collect();
    }
@endphp

@if($menuItems->isNotEmpty())
    <nav class="flex items-center gap-9">
        @foreach($menuItems as $item)
            @include('components.menu.nav-item', ['item' => $item])
        @endforeach
    </nav>
@endif
