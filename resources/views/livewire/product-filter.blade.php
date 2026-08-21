<div>
    @php
        $visualForProduct = function ($product) {
            $categoryName = \Illuminate\Support\Str::lower($product->category?->name ?? '');
            $title = \Illuminate\Support\Str::lower($product->title ?? '');
            $haystack = trim($categoryName . ' ' . $title);

            return match (true) {
                \Illuminate\Support\Str::contains($haystack, ['kahve']) => [
                    'icon' => 'local_cafe',
                    'gradient' => 'from-[#4f3422] via-[#7b5738] to-[#d0b08b]',
                    'chip' => 'text-[#fff4e7]',
                ],
                \Illuminate\Support\Str::contains($haystack, ['lokum', 'cezerye']) => [
                    'icon' => 'bakery_dining',
                    'gradient' => 'from-[#d96c7b] via-[#f39aa7] to-[#fde0d5]',
                    'chip' => 'text-white',
                ],
                \Illuminate\Support\Str::contains($haystack, ['seker', 'draje', 'haribo', 'cikolata']) => [
                    'icon' => 'cookie',
                    'gradient' => 'from-[#8a4b2d] via-[#bb7a4d] to-[#f6d7b8]',
                    'chip' => 'text-white',
                ],
                \Illuminate\Support\Str::contains($haystack, ['market', 'magazada bulunur', 'algida', 'cola', 'icecek']) => [
                    'icon' => 'storefront',
                    'gradient' => 'from-[#3c5c7d] via-[#5f85ad] to-[#d7e6f7]',
                    'chip' => 'text-white',
                ],
                \Illuminate\Support\Str::contains($haystack, ['kuru meyve', 'meyve', 'uzum', 'kayisi', 'incir']) => [
                    'icon' => 'nutrition',
                    'gradient' => 'from-[#8b5f21] via-[#c88c34] to-[#f2d28a]',
                    'chip' => 'text-white',
                ],
                default => [
                    'icon' => 'eco',
                    'gradient' => 'from-[#365b2c] via-[#5f8b4d] to-[#dce8c8]',
                    'chip' => 'text-white',
                ],
            };
        };
    @endphp

    <section class="relative overflow-hidden bg-[#f6efe7] pb-24 pt-16 dark:bg-background-dark">
        <div class="absolute inset-0 opacity-60">
            <div class="absolute -left-20 top-0 h-64 w-64 rounded-full bg-primary/10 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-secondary/10 blur-3xl"></div>
        </div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[minmax(0,1.3fr)_minmax(320px,0.7fr)] lg:items-end">
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.24em] text-primary backdrop-blur">
                        Kuruyemis Katalogu
                    </div>
                    <div class="space-y-4">
                        <h1 class="max-w-3xl font-serif text-4xl text-primary dark:text-orange-100 md:text-6xl">
                            Dukkan rafindan online siparise uzanan urun seckimiz
                        </h1>
                        <p class="max-w-2xl text-base leading-7 text-[#6a5240] dark:text-gray-300 md:text-lg">
                            Kuruyemis, draje, lokum, kahve ve magazada bulunan ekstra urunleri tek katalogda topladik.
                            Online siparise acik urunler her yerden alinabilir; magazaya ozel urunler ise yakin cevre ve Getir teslimatiyla sunulur.
                        </p>
                    </div>
                </div>

                <div class="rounded-[28px] border border-white/70 bg-white/85 p-5 shadow-xl shadow-primary/5 backdrop-blur dark:border-gray-800 dark:bg-surface-dark/90">
                    <div class="grid gap-4 {{ $showStoreOnlyProducts ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }}">
                        <div class="rounded-2xl bg-[#fff6ef] p-4 dark:bg-gray-800">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#9c7349]">Toplam</p>
                            <p class="mt-3 text-3xl font-black text-primary dark:text-orange-100">{{ $totalProducts }}</p>
                            <p class="mt-1 text-sm text-[#7b6351] dark:text-gray-400">Gorunur urun</p>
                        </div>
                        <div class="rounded-2xl bg-[#f2f8ec] p-4 dark:bg-gray-800">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#54784b]">Online</p>
                            <p class="mt-3 text-3xl font-black text-[#2e7d32]">{{ $channelCounts['online'] ?? 0 }}</p>
                            <p class="mt-1 text-sm text-[#5d745a] dark:text-gray-400">Sepete acik</p>
                        </div>
                        @if($showStoreOnlyProducts)
                            <div class="rounded-2xl bg-[#fff7e6] p-4 dark:bg-gray-800">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#b26a10]">Yakin cevre</p>
                                <p class="mt-3 text-3xl font-black text-[#b26a10]">{{ $channelCounts['store_only'] ?? 0 }}</p>
                                <p class="mt-1 text-sm text-[#8a6c47] dark:text-gray-400">Dukkan + Getir</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-background-light py-12 dark:bg-background-dark">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 rounded-[28px] border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto_auto] lg:items-center">
                    <label class="relative block">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-gray-400">
                            <span class="material-icons-round text-xl">search</span>
                        </span>
                        <input
                            type="search"
                            wire:model.live.debounce.400ms="search"
                            placeholder="Urun, kategori veya aciklama icinde ara"
                            class="w-full rounded-2xl border border-gray-200 bg-[#fbfaf8] py-4 pl-12 pr-4 text-sm text-gray-900 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        />
                    </label>

                    <div class="flex items-center gap-3">
                        <label for="sort" class="hidden text-sm font-medium text-gray-500 sm:block">Sirala</label>
                        <select
                            wire:model.live="sortBy"
                            id="sort"
                            class="w-full rounded-2xl border border-gray-200 bg-[#fbfaf8] px-4 py-4 text-sm text-gray-900 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                        >
                            <option value="popularity">One Cikanlar</option>
                            <option value="price_asc">Fiyat: Dusukten Yuksege</option>
                            <option value="price_desc">Fiyat: Yuksekten Dusuge</option>
                            <option value="newest">En Yeniler</option>
                        </select>
                    </div>

                    <button
                        wire:click="toggleFilters"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 px-5 py-4 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary dark:border-gray-700 dark:text-gray-200 lg:hidden"
                    >
                        <span class="material-icons-round text-base">tune</span>
                        Filtreler
                    </button>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    @php
                            $channelOptions = [
                            'all' => ['label' => 'Tum urunler', 'count' => $totalProducts, 'tone' => 'default'],
                            'online' => ['label' => 'Her yerden', 'count' => $channelCounts['online'] ?? 0, 'tone' => 'online'],
                        ];

                        if ($showStoreOnlyProducts) {
                            $channelOptions['store_only'] = ['label' => 'Yakin cevre + Getir', 'count' => $channelCounts['store_only'] ?? 0, 'tone' => 'store'];
                        }
                    @endphp

                    @foreach($channelOptions as $channelKey => $channelOption)
                        @php
                            $isActive = $selectedChannel === $channelKey;
                            $baseClasses = 'inline-flex items-center gap-3 rounded-full border px-4 py-2.5 text-sm font-semibold transition';
                            $toneClasses = match ($channelOption['tone']) {
                                'online' => $isActive
                                    ? 'border-green-700 bg-green-700 text-white'
                                    : 'border-green-200 bg-green-50 text-green-700 hover:border-green-300',
                                'store' => $isActive
                                    ? 'border-amber-600 bg-amber-500 text-white'
                                    : 'border-amber-200 bg-amber-50 text-amber-700 hover:border-amber-300',
                                default => $isActive
                                    ? 'border-primary bg-primary text-white'
                                    : 'border-gray-200 bg-white text-gray-700 hover:border-primary hover:text-primary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200',
                            };
                        @endphp
                        <button wire:click="$set('selectedChannel', '{{ $channelKey }}')" class="{{ $baseClasses }} {{ $toneClasses }}">
                            <span>{{ $channelOption['label'] }}</span>
                            <span class="rounded-full bg-black/10 px-2 py-0.5 text-xs {{ $isActive ? 'text-white/90' : '' }}">
                                {{ $channelOption['count'] }}
                            </span>
                        </button>
                    @endforeach
                </div>

                @if($search || !empty($selectedCategories) || $selectedChannel !== 'all' || $minPrice != $priceRangeMin || $maxPrice != $priceRangeMax)
                    <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-dashed border-gray-200 pt-5 dark:border-gray-700">
                        <span class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Aktif filtreler</span>

                        @if($search)
                            <span class="inline-flex items-center gap-2 rounded-full bg-[#f4ede7] px-3 py-1.5 text-sm text-[#7a5a3c] dark:bg-gray-800 dark:text-gray-200">
                                Arama: {{ $search }}
                            </span>
                        @endif

                        @foreach($selectedCategories as $selectedCategory)
                            @php
                                $categoryLabel = optional($categories->firstWhere('slug', $selectedCategory))->name ?? $selectedCategory;
                            @endphp
                            <span class="inline-flex items-center gap-2 rounded-full bg-[#f4ede7] px-3 py-1.5 text-sm text-[#7a5a3c] dark:bg-gray-800 dark:text-gray-200">
                                {{ $categoryLabel }}
                            </span>
                        @endforeach

                        @if($minPrice != $priceRangeMin || $maxPrice != $priceRangeMax)
                            <span class="inline-flex items-center gap-2 rounded-full bg-[#f4ede7] px-3 py-1.5 text-sm text-[#7a5a3c] dark:bg-gray-800 dark:text-gray-200">
                                Fiyat: {{ number_format($minPrice, 0, ',', '.') }} - {{ number_format($maxPrice, 0, ',', '.') }} TL
                            </span>
                        @endif

                        <button wire:click="clearFilters" class="text-sm font-semibold text-primary transition hover:text-primary/80">
                            Tumunu temizle
                        </button>
                    </div>
                @endif
            </div>

            <div class="flex flex-col gap-8 lg:flex-row lg:items-start">
                <aside class="w-full lg:w-72 lg:flex-shrink-0">
                    <div class="{{ $showFilters ? 'block' : 'hidden lg:block' }} space-y-6">
                        <div class="rounded-[28px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="font-serif text-2xl text-primary dark:text-orange-100">Filtreler</h2>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Katalogu istedigin gibi daralt.</p>
                                </div>
                                <button wire:click="clearFilters" class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400 transition hover:text-primary">
                                    Sifirla
                                </button>
                            </div>

                            @if($categories->count() > 0)
                                <div class="mt-6 border-t border-gray-100 pt-6 dark:border-gray-800">
                                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-gray-400">Kategoriler</h3>
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach($categories as $category)
                                            @php
                                                $selected = in_array($category->slug, $selectedCategories, true);
                                                $nextCategories = $selected
                                                    ? array_values(array_diff($selectedCategories, [$category->slug]))
                                                    : array_values(array_unique([...$selectedCategories, $category->slug]));
                                                $categoryQuery = collect([
                                                    'search' => $search ?: null,
                                                    'selectedChannel' => $selectedChannel !== 'all' ? $selectedChannel : null,
                                                    'sortBy' => $sortBy !== 'popularity' ? $sortBy : null,
                                                    'minPrice' => $minPrice != $priceRangeMin ? $minPrice : null,
                                                    'maxPrice' => $maxPrice != $priceRangeMax ? $maxPrice : null,
                                                    'selectedCategories' => $nextCategories ?: null,
                                                ])->reject(fn ($value) => $value === null || $value === '' || $value === [])->all();
                                            @endphp
                                            <a
                                                href="{{ route('products.index', $categoryQuery) }}"
                                                wire:key="category-filter-{{ $category->id }}"
                                                wire:click.prevent="toggleCategory('{{ $category->slug }}')"
                                                class="inline-flex cursor-pointer items-center gap-2 rounded-full border px-3 py-2 text-left text-sm transition
                                                    {{ $selected
                                                        ? 'border-primary bg-primary text-white'
                                                        : 'border-gray-200 bg-[#fbfaf8] text-gray-700 hover:border-primary hover:text-primary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200' }}"
                                                aria-pressed="{{ $selected ? 'true' : 'false' }}"
                                            >
                                                <span>{{ $category->name }}</span>
                                                <span
                                                    class="rounded-full px-2 py-0.5 text-[11px] {{ $selected ? 'bg-white/20 text-white' : 'bg-black/10 text-current' }}"
                                                >
                                                    {{ $categoryCounts[$category->slug] ?? 0 }}
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mt-6 border-t border-gray-100 pt-6 dark:border-gray-800">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-gray-400">Fiyat araligi</h3>
                                    <span class="text-sm font-semibold text-primary">
                                        {{ number_format($minPrice, 0, ',', '.') }} - {{ number_format($maxPrice, 0, ',', '.') }} TL
                                    </span>
                                </div>

                                <div class="mt-5 space-y-4">
                                    @php
                                        $priceQuery = fn (?int $min, ?int $max) => collect([
                                            'search' => $search ?: null,
                                            'selectedChannel' => $selectedChannel !== 'all' ? $selectedChannel : null,
                                            'sortBy' => $sortBy !== 'popularity' ? $sortBy : null,
                                            'selectedCategories' => $selectedCategories ?: null,
                                            'minPrice' => $min !== null && $min !== (int) $priceRangeMin ? $min : null,
                                            'maxPrice' => $max !== null && $max !== (int) $priceRangeMax ? $max : null,
                                        ])->reject(fn ($value) => $value === null || $value === '' || $value === [])->all();

                                        $midPrice = min(500, (int) $priceRangeMax);
                                        $lowPrice = min(250, (int) $priceRangeMax);
                                    @endphp

                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('products.index', $priceQuery(null, null)) }}"
                                           wire:click.prevent="resetPriceRange"
                                           class="rounded-2xl border px-3 py-2 text-center text-xs font-semibold transition {{ $minPrice == $priceRangeMin && $maxPrice == $priceRangeMax ? 'border-primary bg-primary text-white' : 'border-gray-200 bg-[#fbfaf8] text-gray-700 hover:border-primary hover:text-primary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200' }}">
                                            Tum fiyatlar
                                        </a>
                                        <a href="{{ route('products.index', $priceQuery((int) $priceRangeMin, $lowPrice)) }}"
                                           wire:click.prevent="setPriceRange({{ (int) $priceRangeMin }}, {{ $lowPrice }})"
                                           class="rounded-2xl border border-gray-200 bg-[#fbfaf8] px-3 py-2 text-center text-xs font-semibold text-gray-700 transition hover:border-primary hover:text-primary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                            {{ number_format($lowPrice, 0, ',', '.') }} TL altı
                                        </a>
                                        <a href="{{ route('products.index', $priceQuery($lowPrice, $midPrice)) }}"
                                           wire:click.prevent="setPriceRange({{ $lowPrice }}, {{ $midPrice }})"
                                           class="rounded-2xl border border-gray-200 bg-[#fbfaf8] px-3 py-2 text-center text-xs font-semibold text-gray-700 transition hover:border-primary hover:text-primary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                            {{ number_format($lowPrice, 0, ',', '.') }} - {{ number_format($midPrice, 0, ',', '.') }} TL
                                        </a>
                                        <a href="{{ route('products.index', $priceQuery($midPrice, (int) $priceRangeMax)) }}"
                                           wire:click.prevent="setPriceRange({{ $midPrice }}, {{ (int) $priceRangeMax }})"
                                           class="rounded-2xl border border-gray-200 bg-[#fbfaf8] px-3 py-2 text-center text-xs font-semibold text-gray-700 transition hover:border-primary hover:text-primary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                            {{ number_format($midPrice, 0, ',', '.') }} TL üstü
                                        </a>
                                    </div>

                                    <form method="GET" action="{{ route('products.index') }}" wire:submit.prevent="applyPriceRange">
                                        @if($search)
                                            <input type="hidden" name="search" value="{{ $search }}">
                                        @endif
                                        @if($selectedChannel !== 'all')
                                            <input type="hidden" name="selectedChannel" value="{{ $selectedChannel }}">
                                        @endif
                                        @if($sortBy !== 'popularity')
                                            <input type="hidden" name="sortBy" value="{{ $sortBy }}">
                                        @endif
                                        @foreach($selectedCategories as $selectedCategory)
                                            <input type="hidden" name="selectedCategories[]" value="{{ $selectedCategory }}">
                                        @endforeach

                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-400">Min TL</span>
                                            <input
                                                type="number"
                                                name="minPrice"
                                                wire:model.blur="minPrice"
                                                class="w-full rounded-2xl border border-gray-200 bg-[#fbfaf8] px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                            />
                                        </label>
                                        <label class="block">
                                            <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-gray-400">Max TL</span>
                                            <input
                                                type="number"
                                                name="maxPrice"
                                                wire:model.blur="maxPrice"
                                                class="w-full rounded-2xl border border-gray-200 bg-[#fbfaf8] px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                            />
                                        </label>
                                    </div>

                                        <button
                                            type="submit"
                                            class="mt-3 w-full rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary/90"
                                        >
                                            Fiyatı uygula
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="min-w-0 flex-1">
                    <div class="mb-6 flex flex-col gap-3 rounded-[28px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm uppercase tracking-[0.18em] text-gray-400">Liste gorunumu</p>
                            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $products->total() }} urun bulundu
                            </p>
                        </div>
                        <p class="max-w-xl text-sm leading-6 text-gray-500 dark:text-gray-400">
                            Online urunlerde fiyat gorunur. Yakin cevreye ve Getir'e acik urunleri ise ziyaret oncesi inceleyebilir, detay sayfasindan iletisime gecebilirsin.
                        </p>
                    </div>

                    <div wire:loading class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-800 dark:bg-blue-950/30 dark:text-blue-300">
                        Urunler guncelleniyor, birkac saniye icinde yeni liste hazir olacak.
                    </div>

                    @if($products->count() > 0)
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($products as $product)
                                @php($visual = $visualForProduct($product))
                                <article class="group flex h-full flex-col overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-gray-800 dark:bg-surface-dark">
                                    <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br {{ $product->featured_image_path ? 'from-[#f4ede7] via-[#fbf7f2] to-[#efe4d7] dark:from-gray-900 dark:via-gray-800 dark:to-gray-900' : $visual['gradient'] }}">
                                        @if($product->featured_image_path)
                                            <img
                                                src="{{ asset('storage/' . $product->featured_image_path) }}"
                                                alt="{{ $product->title }}"
                                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            />
                                        @else
                                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-center">
                                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white/85 text-primary shadow-sm">
                                                    <span class="material-icons-round text-3xl">{{ $visual['icon'] }}</span>
                                                </div>
                                                <div class="space-y-1 px-6">
                                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] {{ $visual['chip'] }}">
                                                        {{ optional($product->category)->name ?: 'Katalog urunu' }}
                                                    </p>
                                                    <p class="text-sm text-white/85">
                                                        Gorsel yakinda eklenecek
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                                            @if($product->category)
                                                <span class="rounded-full bg-white/90 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#785a3e] shadow-sm backdrop-blur">
                                                    {{ $product->category->name }}
                                                </span>
                                            @endif

                                            @if($product->isStoreOnly())
                                                <span class="rounded-full bg-amber-400 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-white shadow-sm">
                                                    Yakin cevre + Getir
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex flex-1 flex-col p-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <h2 class="text-xl font-bold leading-tight text-gray-900 transition group-hover:text-primary dark:text-white">
                                                {{ $product->title }}
                                            </h2>
                                            @if($product->isOnlineAvailable())
                                                <div class="text-right">
                                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-400">Online fiyat</p>
                                                    <p class="mt-1 text-xl font-black text-primary">
                                                        {{ number_format((float) $product->display_price, 2, ',', '.') }} TL
                                                    </p>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                                            @if($product->barcode)
                                                <span class="rounded-full bg-[#f5f0ea] px-3 py-1 dark:bg-gray-800">Barkod: {{ $product->barcode }}</span>
                                            @endif
                                            @if($product->package_size)
                                                <span class="rounded-full bg-[#f5f0ea] px-3 py-1 dark:bg-gray-800">{{ $product->package_size }}</span>
                                            @elseif($product->unit)
                                                <span class="rounded-full bg-[#f5f0ea] px-3 py-1 dark:bg-gray-800">{{ $product->unit }}</span>
                                            @endif
                                        </div>

                                        <p class="mt-4 line-clamp-3 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                            {{ $product->list_text ?: $product->summary ?: $product->short_description ?: \Illuminate\Support\Str::limit(strip_tags($product->description ?? 'Bu urun icin detayli aciklama yakinda eklenecek.'), 120) }}
                                        </p>

                                        <div class="mt-6 flex items-center justify-between gap-4 pt-4">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                @if($product->isOnlineAvailable())
                                                    Sepete uygun
                                                @else
                                                    <div class="space-y-1">
                                                        <p class="font-semibold text-amber-700 dark:text-amber-400">Online satilmaz</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">Yakin cevre + Getir ile sunulur</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <a
                                                href="{{ route('products.show', $product) }}"
                                                class="inline-flex items-center gap-2 rounded-full bg-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary/90"
                                            >
                                                Incele
                                                <span class="material-icons-round text-base">arrow_forward</span>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="mt-12">
                            {{ $products->links() }}
                        </div>
                    @else
                        <div class="rounded-[32px] border border-dashed border-gray-300 bg-white px-6 py-16 text-center shadow-sm dark:border-gray-700 dark:bg-surface-dark">
                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#f4ede7] text-primary dark:bg-gray-800">
                                <span class="material-icons-round text-4xl">inventory_2</span>
                            </div>
                            <h2 class="mt-6 text-2xl font-bold text-gray-900 dark:text-white">Bu filtrelerle eslesen urun bulamadik</h2>
                            <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-gray-500 dark:text-gray-400">
                                Aramayi biraz genisletelim ya da kategori secimini sifirlayalim. Katalogta online ve magazaya ozel urunler birlikte listeleniyor.
                            </p>
                            <button
                                wire:click="clearFilters"
                                class="mt-6 inline-flex items-center gap-2 rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary/90"
                            >
                                Filtreleri temizle
                                <span class="material-icons-round text-base">restart_alt</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @push('head')
    <style>
        .range-thumb::-webkit-slider-thumb {
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background: var(--color-primary);
            border: 2px solid #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            cursor: pointer;
        }

        .range-thumb::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background: var(--color-primary);
            border: 2px solid #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            cursor: pointer;
        }
    </style>
    @endpush
</div>
