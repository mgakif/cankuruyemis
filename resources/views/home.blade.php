@extends('layouts.main')

@push('head')
@php
    $skipDefaultMeta = true;
    $customCanonical = true;
    $customRobots = true;
@endphp

{!! $seo['meta_tags'] ?? '' !!}

<link rel="canonical" href="{{ $seo['canonical'] ?? route('home') }}">
<meta name="robots" content="{{ \App\Services\Seo\SeoHelper::robotsContent('index,follow') }}">
@endpush

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<!-- Hero Section -->
<section class="relative pt-10 pb-16 md:pt-16 md:pb-24 overflow-hidden">
    <div class="absolute inset-0 z-0">
        @if(setting('og-image'))
            <img alt="{{ setting('title') ?: config('app.name') }} - Arkaplan"
                 class="w-full h-full object-cover opacity-10 dark:opacity-20"
                 src="{{ asset(setting('og-image')) }}"/>
        @endif
        <div class="absolute inset-0 bg-gradient-to-b from-background-light via-transparent to-background-light dark:from-background-dark dark:via-transparent dark:to-background-dark"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col md:flex-row items-center">
        <div class="md:w-1/2 md:pr-12 text-center md:text-left">
            <span class="inline-block py-1 px-3 rounded-full bg-secondary/10 text-secondary text-sm font-semibold tracking-wide mb-4 dark:bg-secondary/20 dark:text-green-300">
                100% Doğal & Taze
            </span>
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-serif text-primary dark:text-orange-100 leading-tight mb-6">
                Doğanın En İyi <br/>
                <span class="text-secondary dark:text-green-400">Lezzetleri.</span>
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-300 mb-8 max-w-lg mx-auto md:mx-0">
                {{ setting('description-uzun') ?: 'El ile seçilmiş premium kuruyemişler, kurutulmuş meyveler ve organik lezzetler. Tazeliğin tadını doğrudan kapınıza kadar yaşayın.' }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                <a href="{{ route('products.index') }}"
                   class="bg-primary hover:bg-opacity-90 text-white px-8 py-4 rounded-full font-medium transition-all shadow-lg hover:shadow-xl hover:-translate-y-1 flex items-center justify-center gap-2">
                    Alışverişe Başla <span class="material-icons-round text-sm">arrow_forward</span>
                </a>
                @if(Route::has('page.show'))
                    <a href="{{ route('page.show', 'hakkimizda') }}"
                       class="bg-white dark:bg-surface-dark text-primary dark:text-orange-100 border border-gray-200 dark:border-gray-700 px-8 py-4 rounded-full font-medium hover:bg-gray-50 dark:hover:bg-accent-dark transition-all flex items-center justify-center">
                        Hikayemiz
                    </a>
                @endif
            </div>
        </div>

        <div class="md:w-1/2 mt-12 md:mt-0 relative">
            <div class="relative w-full aspect-square max-w-lg mx-auto">
                <div class="absolute inset-0 bg-secondary/10 dark:bg-secondary/20 rounded-full blur-3xl transform scale-90"></div>
                <img alt="Karışık kuruyemişler"
                     class="relative z-10 w-full h-full object-contain drop-shadow-2xl hover:scale-105 transition-transform duration-500"
                     src="{{ setting('hero-image') ?: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBFwWXhQ6_CLaT_e1pf-U-NKZeHcbKmU2mi1y5cNL404YV1YF5_WOCjnF43cA4Vgj4XM2ssSrGt_tg1qV9B6EMfUFg2TgplQafLkwzWMK_aue9Wy2VtUOLn21JGK-6ymbDWipw-_kuHVxZCWq40LDm3DROFAh0rzq6sWkPIRaHw6WxCcL5fKr028YgSqP5mf3nbtZvrOmN_XtegIbf3rm05TuSWEBUCqDnrwQZpOWtCguxgBW9KtNazLY9MAFTuoZS17XeDNRolXPA' }}"/>
                <div class="absolute bottom-10 -left-4 z-20 bg-white dark:bg-surface-dark p-4 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 flex items-center gap-3 animate-bounce"
                     style="animation-duration: 3s;">
                    <div class="bg-green-100 dark:bg-green-900 p-2 rounded-full">
                        <span class="material-icons-round text-secondary dark:text-green-400">eco</span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Organik</p>
                        <p class="text-primary dark:text-white font-bold">Sertifikalı</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-12 bg-accent-light dark:bg-surface-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="flex items-start gap-4 p-6 rounded-2xl bg-white dark:bg-accent-dark shadow-sm hover:shadow-md transition-shadow">
                <div class="bg-orange-100 dark:bg-orange-900/30 p-3 rounded-xl text-primary dark:text-orange-300">
                    <span class="material-icons-round text-3xl">local_shipping</span>
                </div>
                <div>
                    <h3 class="font-serif text-xl text-gray-900 dark:text-white mb-2">Ücretsiz Kargo</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">50₺ üzeri tüm siparişlerde ücretsiz teslimat. Kapınıza kadar taze.</p>
                </div>
            </div>

            <div class="flex items-start gap-4 p-6 rounded-2xl bg-white dark:bg-accent-dark shadow-sm hover:shadow-md transition-shadow">
                <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-xl text-secondary dark:text-green-400">
                    <span class="material-icons-round text-3xl">verified</span>
                </div>
                <div>
                    <h3 class="font-serif text-xl text-gray-900 dark:text-white mb-2">Kalite Garantisi</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Her ürün için en yüksek kaliteyi garanti ediyoruz.</p>
                </div>
            </div>

            <div class="flex items-start gap-4 p-6 rounded-2xl bg-white dark:bg-accent-dark shadow-sm hover:shadow-md transition-shadow">
                <div class="bg-yellow-100 dark:bg-yellow-900/30 p-3 rounded-xl text-yellow-700 dark:text-yellow-400">
                    <span class="material-icons-round text-3xl">card_giftcard</span>
                </div>
                <div>
                    <h3 class="font-serif text-xl text-gray-900 dark:text-white mb-2">Hediye Paketleme</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Özel günler için güzel paketleme seçenekleri.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Shop by Category Section -->
<section class="py-20 bg-background-light dark:bg-background-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-serif text-primary dark:text-orange-100 mb-4">Kategorilere Göre Alışveriş</h2>
            <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Özenle seçilmiş geniş kurutulmuş meyve ve kuruyemiş yelpazemizi keşfedin.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <a href="{{ route('products.index') }}?category=raw-nuts" class="group relative overflow-hidden rounded-2xl aspect-[4/5] shadow-lg">
                <img src="{{ asset('images/categories/cig-findik.png') }}" alt="Çiğ Fındık" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6">
                    <h3 class="text-white text-xl font-bold mb-1">Çiğ Fındık</h3>
                    <p class="text-gray-300 text-sm opacity-0 group-hover:opacity-100 transition-opacity transform translate-y-2 group-hover:translate-y-0">
                        Badem, Ceviz, Kaju
                    </p>
                </div>
            </a>

            <a href="{{ route('products.index') }}?category=dried-fruits" class="group relative overflow-hidden rounded-2xl aspect-[4/5] shadow-lg">
                <img src="{{ asset('images/categories/kurutulmus-meyveler.png') }}" alt="Kurutulmuş Meyveler" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6">
                    <h3 class="text-white text-xl font-bold mb-1">Kurutulmuş Meyveler</h3>
                    <p class="text-gray-300 text-sm opacity-0 group-hover:opacity-100 transition-opacity transform translate-y-2 group-hover:translate-y-0">
                        Kayısı, İncir, Üzüm
                    </p>
                </div>
            </a>

            <a href="{{ route('products.index') }}?category=roasted-nuts" class="group relative overflow-hidden rounded-2xl aspect-[4/5] shadow-lg">
                <img src="{{ asset('images/categories/kavrulmus-karisimlar.png') }}" alt="Kavrulmuş Karışımlar" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6">
                    <h3 class="text-white text-xl font-bold mb-1">Kavrulmuş Karışımlar</h3>
                    <p class="text-gray-300 text-sm opacity-0 group-hover:opacity-100 transition-opacity transform translate-y-2 group-hover:translate-y-0">
                        Tuzlu, Baharatlı, Tatlı
                    </p>
                </div>
            </a>

            <a href="{{ route('products.index') }}?category=turkish-delight" class="group relative overflow-hidden rounded-2xl aspect-[4/5] shadow-lg">
                <img src="{{ asset('images/categories/geleneksel-tatlilar.png') }}" alt="Geleneksel Tatlılar" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6">
                    <h3 class="text-white text-xl font-bold mb-1">Geleneksel Tatlılar</h3>
                    <p class="text-gray-300 text-sm opacity-0 group-hover:opacity-100 transition-opacity transform translate-y-2 group-hover:translate-y-0">
                        Lokum, Şekerleme
                    </p>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- Popular Products Section -->
@if($products && $products->count() > 0)
<section id="products" class="py-20 bg-accent-light dark:bg-surface-dark relative">
    <div class="absolute top-0 left-0 w-full overflow-hidden leading-none rotate-180">
        <svg class="relative block w-[calc(100%+1.3px)] h-[50px] text-background-light dark:text-background-dark fill-current"
             preserveAspectRatio="none" viewBox="0 0 1200 120" xmlns="http://www.w3.org/2000/svg">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-4xl font-serif text-primary dark:text-orange-100 mb-2">Popüler Seçimler</h2>
                <p class="text-gray-600 dark:text-gray-400">Bu haftanın müşteri favorileri.</p>
            </div>
            <a href="{{ route('products.index') }}"
               class="hidden md:flex items-center text-secondary hover:text-primary dark:text-green-400 dark:hover:text-green-300 font-medium transition-colors">
                Tüm Ürünleri Gör <span class="material-icons-round ml-1">arrow_right_alt</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($products->take(4) as $product)
                <div class="bg-white dark:bg-accent-dark rounded-2xl p-4 shadow-sm hover:shadow-xl transition-all duration-300 group">
                    <div class="relative aspect-square rounded-xl overflow-hidden mb-4 bg-gray-100 dark:bg-gray-800">
                        @if($product->featured_image_path)
                            <img alt="{{ $product->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 src="{{ asset('storage/' . $product->featured_image_path) }}"/>
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-200 dark:bg-gray-700">
                                <span class="material-icons-round text-6xl text-gray-400">image</span>
                            </div>
                        @endif
                        <button class="absolute top-3 right-3 bg-white dark:bg-surface-dark p-2 rounded-full shadow-md text-gray-400 hover:text-red-500 transition-colors">
                            <span class="material-icons-round text-lg">favorite</span>
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-gray-900 dark:text-white text-lg">{{ $product->title }}</h3>
                            @if($product->isOnlineAvailable())
                                <span class="text-primary font-bold">{{ number_format((float) $product->display_price, 2, ',', '.') }}₺</span>
                            @elseif($product->isStoreOnly())
                                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-amber-100 text-amber-800">Yakin cevre + Getir</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                            {{ $product->list_text ?: Str::limit(strip_tags($product->description ?? ''), 60) }}
                        </p>
                        <a href="{{ route('products.show', $product) }}"
                           class="w-full mt-4 bg-gray-100 dark:bg-gray-700 hover:bg-primary hover:text-white dark:hover:bg-primary text-gray-900 dark:text-white py-2 rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                            <span class="material-icons-round text-sm">visibility</span>
                            İncele
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-12 text-center md:hidden">
            <a href="{{ route('products.index') }}"
               class="inline-flex items-center text-secondary font-medium hover:text-primary transition-colors">
                Tüm Ürünleri Gör <span class="material-icons-round ml-1">arrow_right_alt</span>
            </a>
        </div>
    </div>
</section>
@endif

@include('partials.faq')

<!-- CTA Section -->
<section class="py-20 relative overflow-hidden">
    <div class="absolute inset-0 bg-primary">
        <div class="w-full h-full opacity-20 mix-blend-overlay bg-gradient-to-r from-orange-500 to-amber-500"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="bg-white/10 backdrop-blur-sm rounded-3xl p-8 md:p-12 border border-white/20 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="text-center md:text-left text-white max-w-2xl">
                <span class="uppercase tracking-widest text-sm font-semibold text-secondary mb-2 block">Sınırlı Süreli Teklif</span>
                <h2 class="text-3xl md:text-5xl font-serif mb-4">İlk Siparişinizde %20 İndirim Kazanın</h2>
                <p class="text-orange-50 text-lg mb-0">
                    Bugün Can Kuruyemiş ailesine katılın ve farkı tadın.
                    Ödeme sayfasında <span class="font-bold text-white bg-white/20 px-2 rounded">TAZE20</span> kodunu kullanın.
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('products.index') }}"
                   class="bg-secondary hover:bg-green-600 text-white text-lg px-8 py-4 rounded-full font-bold shadow-lg transition-all transform hover:scale-105">
                    Koleksiyonu Görüntüle
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
