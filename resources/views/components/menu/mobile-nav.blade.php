@props(['slug'])

@php
    $showStoreOnlyProducts = \App\Models\Product::shouldShowStoreOnlyOnSite();

    try {
        $menu = \Biostate\FilamentMenuBuilder\Models\Menu::where('slug', $slug)->first();
        if ($menu) {
            // NestedSet kullanıldığı için root items'ı alıyoruz
            // NodeTrait'in whereIsRoot() methodunu kullanabiliriz veya parent_id null olanları alabiliriz
            $menuItems = $menu->items()
                ->whereNull('parent_id')
                ->defaultOrder()
                ->get()
                ->each(function($item) {
                    // Children'ları recursive olarak yükle
                    $item->load('children');
                });
        } else {
            $menuItems = collect();
        }
    } catch (\Exception $e) {
        $menuItems = collect();
    }
@endphp

@if($menuItems->isNotEmpty())
    <nav class="flex flex-col gap-4">
        @foreach($menuItems as $item)
            @include('components.menu.mobile-nav-item', ['item' => $item])
        @endforeach
    </nav>
@else
    <!-- Fallback menu if ana-menu doesn't exist -->
    <nav class="flex flex-col gap-4">
        <a href="{{ route('home') }}" class="text-text-dark dark:text-text-light hover:text-primary text-sm font-medium transition-colors py-2">Ana Sayfa</a>
        <a href="{{ route('products.index') }}" class="text-text-dark dark:text-text-light hover:text-primary text-sm font-medium transition-colors py-2">Urunler</a>
        <a href="{{ route('products.index', ['selectedChannel' => 'online']) }}" class="text-text-dark dark:text-text-light hover:text-primary text-sm font-medium transition-colors py-2">Online Siparis</a>
        @if($showStoreOnlyProducts)
            <a href="{{ route('products.index', ['selectedChannel' => 'store_only']) }}" class="text-text-dark dark:text-text-light hover:text-primary text-sm font-medium transition-colors py-2">Yakin cevre + Getir</a>
        @endif
        <a href="{{ route('contact.show') }}" class="text-text-dark dark:text-text-light hover:text-primary text-sm font-medium transition-colors py-2">Iletisim</a>
    </nav>
@endif
