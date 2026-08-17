@extends('layouts.main')

@push('head')
@php
    $skipDefaultMeta = true;
@endphp

{!! $seo['meta_tags'] ?? '' !!}

@php
    $customCanonical = true;
    $customRobots = true;
@endphp

<link rel="canonical" href="{{ $seo['canonical'] ?? route('products.show', $product) }}">
<meta name="robots" content="{{ \App\Services\Seo\SeoHelper::robotsContent($seo['robots'] ?? 'index,follow') }}">

@if(!empty($seo['breadCrumbSchema']))
    {!! $seo['breadCrumbSchema'] !!}
@endif

@if(!empty($seo['jsonld']))
    <script type="application/ld+json">{!! json_encode($seo['jsonld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
@endpush

@section('content')
@php
    $whatsapp = setting('whatsapp') ? preg_replace('/[^0-9]/', '', setting('whatsapp')) : null;
    $whatsappUrl = $whatsapp ? 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode($product->title . ' urunu hakkinda bilgi almak istiyorum.') : null;
    $visualForProduct = function ($productModel) {
        $categoryName = \Illuminate\Support\Str::lower($productModel->category?->name ?? '');
        $title = \Illuminate\Support\Str::lower($productModel->title ?? '');
        $haystack = trim($categoryName . ' ' . $title);

        return match (true) {
            \Illuminate\Support\Str::contains($haystack, ['kahve']) => ['icon' => 'local_cafe', 'gradient' => 'from-[#4f3422] via-[#7b5738] to-[#d0b08b]'],
            \Illuminate\Support\Str::contains($haystack, ['lokum', 'cezerye']) => ['icon' => 'bakery_dining', 'gradient' => 'from-[#d96c7b] via-[#f39aa7] to-[#fde0d5]'],
            \Illuminate\Support\Str::contains($haystack, ['seker', 'draje', 'haribo', 'cikolata']) => ['icon' => 'cookie', 'gradient' => 'from-[#8a4b2d] via-[#bb7a4d] to-[#f6d7b8]'],
            \Illuminate\Support\Str::contains($haystack, ['market', 'magazada bulunur', 'algida', 'cola', 'icecek']) => ['icon' => 'storefront', 'gradient' => 'from-[#3c5c7d] via-[#5f85ad] to-[#d7e6f7]'],
            \Illuminate\Support\Str::contains($haystack, ['kuru meyve', 'meyve', 'uzum', 'kayisi', 'incir']) => ['icon' => 'nutrition', 'gradient' => 'from-[#8b5f21] via-[#c88c34] to-[#f2d28a]'],
            default => ['icon' => 'eco', 'gradient' => 'from-[#365b2c] via-[#5f8b4d] to-[#dce8c8]'],
        };
    };
    $nutritionFacts = collect($product->nutrition_facts ?? [])
        ->map(function ($value, $key) {
            if (is_array($value) && isset($value['label'], $value['value'])) {
                return ['label' => $value['label'], 'value' => $value['value']];
            }

            if (is_string($key)) {
                return ['label' => $key, 'value' => $value];
            }

            return null;
        })
        ->filter()
        ->take(8)
        ->values();

    $infoBadges = collect([
        $product->category?->name,
        $product->package_size,
        $product->unit,
        $product->barcode ? 'Barkod: ' . $product->barcode : null,
        $product->brand ? 'Marka: ' . $product->brand : null,
    ])->filter();

    $highlights = collect([
        $product->summary,
        $product->short_description,
        $product->ingredients ? 'Icindekiler: ' . $product->ingredients : null,
        $product->allergen_info ? 'Alerjen bilgisi: ' . $product->allergen_info : null,
    ])->filter()->take(4);
    $productVisual = $visualForProduct($product);
@endphp

<main class="bg-background-light dark:bg-background-dark">
    <section class="border-b border-[#eadfce] bg-[#f6efe7] pb-6 pt-12 dark:border-gray-800 dark:bg-gray-950/50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center gap-2 text-sm text-[#8f6d4d] dark:text-gray-400">
                <a href="{{ route('home') }}" class="transition hover:text-primary">Anasayfa</a>
                <span>/</span>
                <a href="{{ route('products.index') }}" class="transition hover:text-primary">Urunler</a>
                @if($product->category)
                    <span>/</span>
                    <span>{{ $product->category->name }}</span>
                @endif
                <span>/</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $product->title }}</span>
            </div>
        </div>
    </section>

    <section class="relative overflow-hidden bg-[#fffaf5] py-14 dark:bg-background-dark">
        <div class="absolute inset-0 opacity-70">
            <div class="absolute left-0 top-0 h-72 w-72 rounded-full bg-primary/10 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-secondary/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[minmax(0,0.95fr)_minmax(340px,1.05fr)] lg:px-8">
            <div class="order-2 lg:order-1">
                <div class="overflow-hidden rounded-[36px] border border-white/80 bg-white/80 shadow-2xl shadow-primary/5 backdrop-blur dark:border-gray-800 dark:bg-surface-dark/90">
                    <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br {{ $product->featured_image_path ? 'from-[#f3e7d7] via-[#fbf8f3] to-[#ead7bf] dark:from-gray-900 dark:via-gray-800 dark:to-gray-900' : $productVisual['gradient'] }}">
                        @if($product->featured_image_path)
                            <img
                                src="{{ asset('storage/' . $product->featured_image_path) }}"
                                alt="{{ $product->title }}"
                                class="h-full w-full object-cover"
                            />
                        @else
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-4 text-center">
                                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white/80 text-primary shadow-sm dark:bg-gray-800/80">
                                    <span class="material-icons-round text-4xl">{{ $productVisual['icon'] }}</span>
                                </div>
                                <div class="space-y-2 px-8">
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white">Gorsel yakinda</p>
                                    <p class="text-sm text-white/85">
                                        Urun gorseli henuz eklenmedi. Detaylar ve satis durumu aktif.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="absolute left-5 top-5 flex flex-wrap gap-2">
                            @if($product->category)
                                <span class="rounded-full bg-white/90 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#78573d] shadow-sm backdrop-blur">
                                    {{ $product->category->name }}
                                </span>
                            @endif

                            @if($product->isStoreOnly())
                                <span class="rounded-full bg-amber-500 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white shadow-sm">
                                    Yakin cevre + Getir
                                </span>
                            @elseif($product->isOnlineAvailable())
                                <span class="rounded-full bg-green-600 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-white shadow-sm">
                                    Her yerden siparis
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($product->gallery && count($product->gallery) > 0)
                        <div class="grid grid-cols-4 gap-3 border-t border-gray-100 p-4 dark:border-gray-800">
                            @foreach(collect($product->gallery)->take(4) as $image)
                                <div class="aspect-square overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-800">
                                    <img src="{{ asset('storage/' . $image) }}" alt="{{ $product->title }}" class="h-full w-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="order-1 flex flex-col justify-center lg:order-2">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <div class="flex flex-wrap gap-2">
                            @foreach($infoBadges as $badge)
                                <span class="rounded-full border border-[#e7d9c8] bg-white px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-[#7d6246] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                    {{ $badge }}
                                </span>
                            @endforeach
                        </div>

                        <div class="space-y-4">
                            <h1 class="max-w-3xl text-4xl font-black leading-tight tracking-[-0.03em] text-gray-900 dark:text-white md:text-6xl">
                                {{ $product->title }}
                            </h1>

                            <p class="max-w-2xl text-base leading-7 text-[#6d5643] dark:text-gray-300 md:text-lg">
                                {{ $product->short_description ?: $product->summary ?: 'Bu urun dukkan katalogumuzda aktif. Urun ozeti ve besin detaylari yakinda daha kapsamli olarak eklenecek.' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-[28px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">Satis durumu</p>
                            <p class="mt-3 text-xl font-black text-gray-900 dark:text-white">
                                {{ $product->isOnlineAvailable() ? 'Online satis aktif' : 'Sadece magazada' }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                {{ $product->isOnlineAvailable()
                                    ? 'Bu urun site uzerinden fiyatli olarak listelenir ve Turkiye genelinde siparise aciktir.'
                                    : 'Bu urun dukkan rafinda bulunur. Getir ve yakin cevre teslimati icin uygundur; online sepete eklenmez ama bilgi alinabilir.' }}
                            </p>
                            @if($product->isStoreOnly())
                                <div class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                                    Bu urun online satilmaz. Magaza ve yakin cevre teslimati ile Getir uzerinden sunulabilir.
                                </div>
                            @endif
                        </div>

                        <div class="rounded-[28px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">
                                {{ $product->isOnlineAvailable() ? 'Online fiyat' : 'Magaza bilgisi' }}
                            </p>
                            <p class="mt-3 text-3xl font-black text-primary">
                                @if($product->isOnlineAvailable())
                                    {{ number_format((float) $product->display_price, 2, ',', '.') }} TL
                                @else
                                    Bilgi al
                                @endif
                            </p>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                @if($product->isOnlineAvailable())
                                    Magaza fiyatindan ayrisan online kurallar uygulanmis olabilir ve her yerden siparise aciktir.
                                @else
                                    Magaza satis durumu, Getir ve yakin cevre teslimati icin bize ulasabilirsin.
                                @endif
                            </p>
                        </div>

                        <div class="rounded-[28px] border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">Stok varsayimi</p>
                            <p class="mt-3 text-3xl font-black text-gray-900 dark:text-white">{{ (int) $product->stock }}</p>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                Katalog senaryosunda urunler sabit stokla listeleniyor. Gercek stok teyidi icin iletisime gecebiliriz.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @if($whatsappUrl)
                            <a
                                href="{{ $whatsappUrl }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-2 rounded-full bg-green-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-green-700"
                            >
                                <span class="material-icons-round text-base">chat</span>
                                WhatsApp ile sor
                            </a>
                        @endif

                        <a
                            href="{{ route('contact.show') }}"
                            class="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary/90"
                        >
                            <span class="material-icons-round text-base">call</span>
                            Teklif ve bilgi al
                        </a>

                        <a
                            href="{{ route('products.index') }}"
                            class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-primary hover:text-primary dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                        >
                            <span class="material-icons-round text-base">west</span>
                            Kataloga don
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-14">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:px-6 lg:grid-cols-[minmax(0,1.1fr)_360px] lg:px-8">
            <div class="space-y-8">
                <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark sm:p-8">
                    <div class="flex flex-col gap-3 border-b border-dashed border-gray-200 pb-6 dark:border-gray-700">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Genel bakis</p>
                        <h2 class="text-3xl font-black tracking-[-0.03em] text-gray-900 dark:text-white">Urun detaylari</h2>
                    </div>

                    <div class="prose prose-lg mt-6 max-w-none text-[#5e4a3a] dark:prose-invert dark:text-gray-300">
                        {!! $product->description ?: '<p>Bu urun icin detayli aciklama icerigi henuz tamamlanmadi. Kategori, barkod, fiyat ve satis kanali aktif durumda. Sonraki asamada AI destekli urun icerikleri eklenecek.</p>' !!}
                    </div>
                </div>

                @if($highlights->isNotEmpty())
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark sm:p-8">
                        <div class="flex flex-col gap-3 border-b border-dashed border-gray-200 pb-6 dark:border-gray-700">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">One cikanlar</p>
                            <h2 class="text-3xl font-black tracking-[-0.03em] text-gray-900 dark:text-white">Hizli notlar</h2>
                        </div>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            @foreach($highlights as $highlight)
                                <div class="rounded-2xl bg-[#f8f3ed] p-5 text-sm leading-6 text-[#654f3e] dark:bg-gray-900 dark:text-gray-300">
                                    {{ $highlight }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($product->specifications && count($product->specifications) > 0)
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark sm:p-8">
                        <div class="flex flex-col gap-3 border-b border-dashed border-gray-200 pb-6 dark:border-gray-700">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Ozellikler</p>
                            <h2 class="text-3xl font-black tracking-[-0.03em] text-gray-900 dark:text-white">Urun bilgileri</h2>
                        </div>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            @foreach($product->specifications as $spec)
                                <div class="rounded-2xl border border-gray-100 bg-[#fcfaf7] p-5 dark:border-gray-800 dark:bg-gray-900">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">{{ $spec['key'] ?? 'Bilgi' }}</p>
                                    <p class="mt-2 text-base font-semibold text-gray-900 dark:text-white">{{ $spec['value'] ?? '-' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="space-y-6">
                <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Katalog karti</p>
                    <h2 class="mt-3 text-2xl font-black tracking-[-0.03em] text-gray-900 dark:text-white">Temel alanlar</h2>

                    <dl class="mt-6 space-y-4">
                        <div class="flex items-start justify-between gap-4 border-b border-dashed border-gray-200 pb-4 dark:border-gray-700">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Kategori</dt>
                            <dd class="text-right text-sm font-semibold text-gray-900 dark:text-white">{{ $product->category?->name ?: '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 border-b border-dashed border-gray-200 pb-4 dark:border-gray-700">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Barkod</dt>
                            <dd class="text-right text-sm font-semibold text-gray-900 dark:text-white">{{ $product->barcode ?: '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 border-b border-dashed border-gray-200 pb-4 dark:border-gray-700">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Paket</dt>
                            <dd class="text-right text-sm font-semibold text-gray-900 dark:text-white">{{ $product->package_size ?: ($product->unit ?: '-') }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 border-b border-dashed border-gray-200 pb-4 dark:border-gray-700">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Satis kanali</dt>
                            <dd class="text-right text-sm font-semibold text-gray-900 dark:text-white">{{ $product->isOnlineAvailable() ? 'Online' : 'Magaza' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Icerik durumu</dt>
                            <dd class="text-right text-sm font-semibold text-gray-900 capitalize dark:text-white">{{ $product->content_status ?: 'draft' }}</dd>
                        </div>
                    </dl>
                </div>

                @if($nutritionFacts->isNotEmpty() || $product->energy_kcal || $product->ingredients || $product->allergen_info)
                    <div class="rounded-[32px] border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-surface-dark">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Besin alani</p>
                        <h2 class="mt-3 text-2xl font-black tracking-[-0.03em] text-gray-900 dark:text-white">Icerik ozeti</h2>

                        <div class="mt-6 space-y-4">
                            @if($product->energy_kcal)
                                <div class="rounded-2xl bg-[#f8f3ed] p-4 dark:bg-gray-900">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">Enerji</p>
                                    <p class="mt-2 text-lg font-bold text-gray-900 dark:text-white">{{ number_format((float) $product->energy_kcal, 0, ',', '.') }} kcal</p>
                                </div>
                            @endif

                            @if($nutritionFacts->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach($nutritionFacts as $fact)
                                        <div class="flex items-start justify-between gap-4 rounded-2xl border border-gray-100 bg-[#fcfaf7] px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $fact['label'] }}</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ is_array($fact['value']) ? json_encode($fact['value'], JSON_UNESCAPED_UNICODE) : $fact['value'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($product->ingredients)
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">Icindekiler</p>
                                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $product->ingredients }}</p>
                                </div>
                            @endif

                            @if($product->allergen_info)
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">Alerjen bilgisi</p>
                                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $product->allergen_info }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    @if($relatedProducts->isNotEmpty())
        <section class="border-t border-[#eadfce] bg-[#faf6f1] py-14 dark:border-gray-800 dark:bg-gray-950/40">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">Benzer urunler</p>
                        <h2 class="mt-2 text-3xl font-black tracking-[-0.03em] text-gray-900 dark:text-white">Katalogta buna yakin urunler</h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="text-sm font-semibold text-primary transition hover:text-primary/80">
                        Tum urunleri gor
                    </a>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($relatedProducts->take(4) as $relatedProduct)
                        @php($relatedVisual = $visualForProduct($relatedProduct))
                        <article class="group overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-gray-800 dark:bg-surface-dark">
                            <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br {{ $relatedProduct->featured_image_path ? 'from-[#f4ede7] via-[#fbf7f2] to-[#efe4d7] dark:from-gray-900 dark:via-gray-800 dark:to-gray-900' : $relatedVisual['gradient'] }}">
                                @if($relatedProduct->featured_image_path)
                                    <img src="{{ asset('storage/' . $relatedProduct->featured_image_path) }}" alt="{{ $relatedProduct->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center text-primary">
                                        <span class="material-icons-round text-4xl">{{ $relatedVisual['icon'] }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-4 p-5">
                                <div class="space-y-2">
                                    @if($relatedProduct->category)
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">{{ $relatedProduct->category->name }}</p>
                                    @endif
                                    <h3 class="text-lg font-bold leading-tight text-gray-900 group-hover:text-primary dark:text-white">
                                        {{ $relatedProduct->title }}
                                    </h3>
                                    <p class="line-clamp-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                        {{ $relatedProduct->summary ?: $relatedProduct->short_description ?: \Illuminate\Support\Str::limit(strip_tags($relatedProduct->description ?? ''), 90) }}
                                    </p>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-semibold {{ $relatedProduct->isOnlineAvailable() ? 'text-primary' : 'text-amber-600' }}">
                                        {{ $relatedProduct->isOnlineAvailable()
                                            ? number_format((float) $relatedProduct->display_price, 2, ',', '.') . ' TL'
                                            : 'Magazada var' }}
                                    </div>
                                    <a href="{{ route('products.show', $relatedProduct) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary transition hover:text-primary/80">
                                        Incele
                                        <span class="material-icons-round text-base">arrow_forward</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>
@endsection
