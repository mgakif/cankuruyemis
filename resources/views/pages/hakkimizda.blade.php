@extends('layouts.main')

@push('head')
@php
    $skipDefaultMeta = true;
    $customCanonical = true;
    $customRobots = true;
@endphp

{!! $seo['meta_tags'] ?? '' !!}

<link rel="canonical" href="{{ $seo['canonical'] ?? route('about.show') }}">
<meta name="robots" content="{{ \App\Services\Seo\SeoHelper::robotsContent('index,follow') }}">
@endpush

@section('content')
<!-- Hero Section -->
<section class="relative w-full">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="relative overflow-hidden rounded-2xl min-h-[560px] flex items-center justify-center">
            <!-- Background Image with Overlay -->
            <div class="absolute inset-0 bg-cover bg-center z-0"
                @if(setting('hakkimizda-hero-resmi'))
                    style="background-image: url('{{ asset(setting('hakkimizda-hero-resmi')) }}');"
                @else
                    style="background-image: url('{{ asset(setting('og-image') ?: '/images/default-hero.jpg') }}');"
                @endif
            ></div>
            <div class="absolute inset-0 bg-black/40 z-10"></div>

            <!-- Content -->
            <div class="relative z-20 text-center max-w-3xl px-4">
                <span class="inline-block py-1 px-3 rounded-full bg-primary/20 backdrop-blur-md border border-primary/30 text-white text-xs font-bold uppercase tracking-wider mb-6">
                    {{ setting('kurulus-yili') ?: '2024' }}'ten Beri
                </span>
                <h1 class="text-white text-5xl md:text-6xl lg:text-7xl font-black font-serif leading-tight tracking-tight mb-6">
                    {{ setting('hakkimizda-baslik') ?: 'Tazelik, Kalite ve Güven' }}
                </h1>
                <p class="text-white/90 text-lg md:text-xl font-medium leading-relaxed max-w-2xl mx-auto mb-10">
                    {{ setting('hakkimizda-alt-baslik') ?: '2024 yılında girişimci Zeynep Tekin tarafından Mersin’de kurulan Can Kuruyemiş; özenle seçilmiş kuruyemiş, kuru meyve ve özel lezzetlerle gönül rahatlığıyla alışveriş edebileceğiniz sıcak bir duraktır.' }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#hikaye" class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-8 text-base font-bold text-white shadow-lg transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        Hikayemiz
                    </a>
                    <a href="{{ route('products.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-white/10 backdrop-blur-sm border border-white/30 px-8 text-base font-bold text-white shadow-lg transition-transform hover:bg-white/20 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2">
                        Favorileri Keşfet
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Story Grid -->
<section id="hikaye" class="py-20 bg-background-light dark:bg-background-dark">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="flex flex-col gap-8">
                <div>
                    <h2 class="text-primary font-bold uppercase tracking-widest text-sm mb-2">Misyonumuz</h2>
                    <h3 class="text-4xl font-serif font-bold text-text-dark dark:text-white mb-6 leading-tight">
                        {{ setting('misyon-baslik') ?: 'Doğanın İyiliğini Korumak' }}
                    </h3>
                    <div class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed space-y-4">
                        {!! setting('hakkimizda') ?: '<p>Can Kuruyemiş olarak işimizin temelinde <strong>tazelik, kalite ve güven</strong> olduğuna inanıyoruz.</p><p>Müşterilerimize sunduğumuz her ürünü özenle seçiyor; kuruyemişten kuru meyveye, atıştırmalıklardan özel lezzetlere kadar geniş ürün yelpazemizde kalite standardımızı korumaya önem veriyoruz. Amacımız yalnızca ürün satmak değil, müşterilerimizin gönül rahatlığıyla alışveriş yapabileceği sıcak ve güvenilir bir ortam oluşturmak.</p><p>Kuruyemişte lezzetin en önemli unsurunun tazelik olduğunu biliyoruz. Bu nedenle ürünlerimizin saklama koşullarından sunumuna kadar her aşamada titiz davranıyoruz. Geleneksel lezzetleri günümüzün hizmet anlayışıyla bir araya getirerek hem günlük alışverişlerinizde hem de misafir sofralarınızda keyifle tercih edebileceğiniz ürünler sunuyoruz.</p><p>Can Kuruyemiş’te müşterilerimiz bizim için yalnızca alışveriş yapan kişiler değil, uzun yıllar boyunca aynı güvenle hizmet vermek istediğimiz komşularımız ve dostlarımızdır. Bu nedenle güler yüzlü hizmeti, dürüstlüğü ve müşteri memnuniyetini işimizin ayrılmaz bir parçası olarak görüyoruz.</p>' !!}
                    </div>
                </div>

                <div class="rounded-2xl border border-primary/20 bg-primary/5 dark:bg-primary/10 p-6">
                    <p class="text-primary font-bold uppercase tracking-widest text-xs mb-2">Kurucu</p>
                    <h4 class="text-2xl font-serif font-bold text-text-dark dark:text-white">Zeynep Tekin</h4>
                    <p class="text-sm text-primary font-medium mb-3">Girişimci · Çevre Mühendisi · 1 Çocuk Annesi</p>
                    <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                        Can Kuruyemiş, 2024 yılında girişimci Zeynep Tekin tarafından kuruldu. Çevre mühendisi kimliği ve aile odaklı bakış açısıyla tazelik, kalite ve güveni işinin merkezine koyuyor; müşterilerimizi komşu ve dost olarak görüyoruz.
                    </p>
                </div>

                <!-- Timeline -->
                <div class="mt-8 border-l-2 border-secondary pl-8 space-y-10 relative">
                    <div class="relative">
                        <span class="absolute -left-[41px] top-0 flex size-5 items-center justify-center rounded-full bg-primary ring-4 ring-white dark:ring-background-dark"></span>
                        <h4 class="text-lg font-bold text-text-dark dark:text-white">{{ setting('kurulus-yili') ?: '2024' }} Yılında Kurulduk</h4>
                        <p class="text-gray-600 dark:text-gray-400">{{ setting('timeline-1') ?: 'Çevre mühendisi ve bir çocuk annesi girişimci Zeynep Tekin tarafından Mersin’de kurulduk.' }}</p>
                    </div>
                    <div class="relative">
                        <span class="absolute -left-[41px] top-0 flex size-5 items-center justify-center rounded-full bg-secondary ring-4 ring-white dark:ring-background-dark"></span>
                        <h4 class="text-lg font-bold text-text-dark dark:text-white">Tazelik ve Kalite Standardı</h4>
                        <p class="text-gray-600 dark:text-gray-400">{{ setting('timeline-2') ?: 'Saklama koşullarından sunuma kadar her aşamada titiz davranıyor; kuruyemişten kuru meyveye özenle seçilmiş ürünler sunuyoruz.' }}</p>
                    </div>
                    <div class="relative">
                        <span class="absolute -left-[41px] top-0 flex size-5 items-center justify-center rounded-full bg-secondary ring-4 ring-white dark:ring-background-dark"></span>
                        <h4 class="text-lg font-bold text-text-dark dark:text-white">Komşuluk ve Güven</h4>
                        <p class="text-gray-600 dark:text-gray-400">{{ setting('timeline-3') ?: 'Müşterilerimizi uzun yıllar aynı güvenle hizmet vermek istediğimiz komşularımız ve dostlarımız olarak görüyoruz.' }}</p>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-4 mt-12">
                        <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-gray-200 dark:bg-gray-700">
                            @if(setting('hakkimizda-resim-1'))
                                <img alt="Kuruyemişler" class="h-full w-full object-cover hover:scale-105 transition-transform duration-500" src="{{ asset(setting('hakkimizda-resim-1')) }}"/>
                            @else
                                <div class="h-full w-full flex items-center justify-center">
                                    <span class="material-icons-round text-6xl text-gray-400">image</span>
                                </div>
                            @endif
                        </div>
                        <div class="p-6 bg-accent-light dark:bg-accent-dark rounded-2xl">
                            <div class="text-primary mb-2">
                                <span class="material-icons-round text-4xl">eco</span>
                            </div>
                            <div class="font-bold text-2xl text-text-dark dark:text-white">Taze</div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Özenle Seçilmiş Ürün</div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="p-6 bg-primary/10 rounded-2xl">
                            <div class="text-primary mb-2">
                                <span class="material-icons-round text-4xl">handshake</span>
                            </div>
                            <div class="font-bold text-2xl text-text-dark dark:text-white">{{ setting('kurulus-yili') ?: '2024' }}</div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Kuruluş Yılı</div>
                        </div>
                        <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-gray-200 dark:bg-gray-700">
                            @if(setting('hakkimizda-resim-2'))
                                <img alt="Kuru meyveler" class="h-full w-full object-cover hover:scale-105 transition-transform duration-500" src="{{ asset(setting('hakkimizda-resim-2')) }}"/>
                            @else
                                <div class="h-full w-full flex items-center justify-center">
                                    <span class="material-icons-round text-6xl text-gray-400">image</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-20 bg-accent-light dark:bg-accent-dark">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-serif font-black text-text-dark dark:text-white mb-4">Temel Değerlerimiz</h2>
            <p class="text-gray-600 dark:text-gray-300 text-lg">İşimizin temelinde tazelik, kalite ve güven vardır; her ürünü bu anlayışla seçer ve sunarız.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Value 1 -->
            <div class="bg-background-light dark:bg-background-dark p-8 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-xl transition-shadow group">
                <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-icons-round text-2xl">spa</span>
                </div>
                <h3 class="text-xl font-bold text-text-dark dark:text-white mb-3">Tazelik</h3>
                <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                    {{ setting('deger-1') ?: 'Kuruyemişte lezzetin en önemli unsurunun tazelik olduğunu biliyoruz. Saklama koşullarından sunuma kadar her aşamada titiz davranıyoruz.' }}
                </p>
            </div>

            <!-- Value 2 -->
            <div class="bg-background-light dark:bg-background-dark p-8 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-xl transition-shadow group">
                <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-icons-round text-2xl">verified</span>
                </div>
                <h3 class="text-xl font-bold text-text-dark dark:text-white mb-3">Kalite</h3>
                <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                    {{ setting('deger-2') ?: 'Kuruyemişten kuru meyveye, atıştırmalıklardan özel lezzetlere kadar her ürünü özenle seçiyor; kalite standardımızı korumaya önem veriyoruz.' }}
                </p>
            </div>

            <!-- Value 3 -->
            <div class="bg-background-light dark:bg-background-dark p-8 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-xl transition-shadow group">
                <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                    <span class="material-icons-round text-2xl">handshake</span>
                </div>
                <h3 class="text-xl font-bold text-text-dark dark:text-white mb-3">Güven</h3>
                <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                    {{ setting('deger-3') ?: 'Güler yüzlü hizmet, dürüstlük ve müşteri memnuniyeti işimizin ayrılmaz parçasıdır. Sizi komşumuz ve dostumuz olarak görüyoruz.' }}
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Meet the Family (Team) -->
@if(setting('takim-goster'))
<section class="py-20 bg-background-light dark:bg-background-dark">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
            <div class="max-w-xl">
                <h2 class="text-3xl font-serif font-bold text-text-dark dark:text-white mb-4">Ekibimizle Tanışın</h2>
                <p class="text-gray-600 dark:text-gray-300 text-lg">Favori sağlıklı atıştırmalıklarınızın ardındaki tutkulu bireyler. Sağlıklı gıda sevgisiyle birleşiyoruz.</p>
            </div>
            @if(setting('takim-ilan-link'))
            <a class="text-primary font-bold hover:text-primary/80 inline-flex items-center gap-2" href="{{ setting('takim-ilan-link') }}">
                Ekibimize Katıl <span class="material-icons-round text-lg">arrow_forward</span>
            </a>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @for($i = 1; $i <= 4; $i++)
                @if(setting("takim-{$i}-isim"))
                <div class="group">
                    <div class="relative overflow-hidden rounded-xl aspect-square mb-4 bg-gray-100 dark:bg-gray-800">
                        @if(setting("takim-{$i}-resim"))
                            <img alt="{{ setting("takim-{$i}-isim") }}" class="h-full w-full object-cover grayscale group-hover:grayscale-0 transition-all duration-300" src="{{ asset(setting("takim-{$i}-resim")) }}"/>
                        @else
                            <div class="h-full w-full flex items-center justify-center">
                                <span class="material-icons-round text-6xl text-gray-400">person</span>
                            </div>
                        @endif
                    </div>
                    <h3 class="text-lg font-bold text-text-dark dark:text-white">{{ setting("takim-{$i}-isim") }}</h3>
                    <p class="text-sm text-primary font-medium mb-2">{{ setting("takim-{$i}-pozisyon") }}</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ setting("takim-{$i}-aciklama") }}</p>
                </div>
                @endif
            @endfor
        </div>
    </div>
</section>
@endif

<!-- From the Source Gallery -->
@if(setting('galeri-goster'))
<section class="py-20 bg-background-light dark:bg-background-dark overflow-hidden">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-10 flex items-center justify-between">
            <h2 class="text-2xl font-serif font-bold text-text-dark dark:text-white">Kaynaktan Görüntüler</h2>
            @if(setting('instagram-link'))
            <a class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-primary" href="{{ setting('instagram-link') }}" target="_blank">
                Bizi Takip Edin @{{ setting('instagram-kullanici-adi', 'cankuruyemis') }}
            </a>
            @endif
        </div>

        <div class="columns-1 sm:columns-2 md:columns-3 gap-4 space-y-4">
            @for($i = 1; $i <= 5; $i++)
                @if(setting("galeri-{$i}"))
                <div class="break-inside-avoid rounded-xl overflow-hidden group relative">
                    <img alt="Galeri resmi {{ $i }}" class="w-full object-cover transition-transform duration-500 group-hover:scale-110" src="{{ asset(setting("galeri-{$i}")) }}"/>
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
                </div>
                @endif
            @endfor

            <div class="break-inside-avoid rounded-xl overflow-hidden group relative">
                <div class="bg-primary text-white p-8 flex flex-col justify-center h-full min-h-[200px] text-center">
                    <span class="material-icons-round text-4xl mb-4">favorite</span>
                    <h3 class="text-xl font-bold">Sevgiyle Yapılmış</h3>
                    <p class="text-white/80 mt-2">Her paket kalite için elle kontrol edilir.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@include('partials.faq')

<!-- Newsletter -->
@if(setting('newsletter-goster'))
<section class="py-16 bg-accent-light dark:bg-accent-dark">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="bg-primary rounded-2xl p-8 md:p-12 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-64 h-64 bg-black/10 rounded-full blur-3xl"></div>

            <div class="relative z-10 max-w-xl text-center md:text-left">
                <h2 class="text-3xl font-bold text-white mb-2">Ailemize Katılın</h2>
                <p class="text-white/90 text-lg">İlk siparişinizde %10 indirim kazanın, ayrıca lezzetli tarifler ve kaynak hikayeleri e-postanıza gelsin.</p>
            </div>

            <div class="relative z-10 w-full max-w-md">
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input class="flex-1 rounded-xl border-none px-4 py-3 text-text-dark focus:ring-2 focus:ring-offset-2 focus:ring-offset-primary focus:ring-white"
                           placeholder="E-posta adresiniz"
                           type="email"
                           name="email"
                           required/>
                    <button type="submit" class="bg-text-dark text-white font-bold py-3 px-6 rounded-xl hover:bg-black transition-colors whitespace-nowrap">
                        Abone Ol
                    </button>
                </form>
                <p class="text-white/60 text-xs mt-3 text-center sm:text-left">Gizliliğinize saygı duyuyoruz. İstediğiniz zaman abonelikten çıkabilirsiniz.</p>
            </div>
        </div>
    </div>
</section>
@endif
@endsection
