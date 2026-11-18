@php
    $brandName = setting('title') ?: 'Can Kuruyemiş';
    $showStoreOnlyProducts = \App\Models\Product::shouldShowStoreOnlyOnSite();
    $categoryLinks = [
        ['label' => 'Kuruyemiş', 'params' => ['selectedCategories' => ['kuruyemis']]],
        ['label' => 'Kuru Meyve', 'params' => ['selectedCategories' => ['kuru-meyve']]],
        ['label' => 'Lokum ve Cezerye', 'params' => ['selectedCategories' => ['lokum-ve-cezerye']]],
        ['label' => 'Şekerleme ve Draje', 'params' => ['selectedCategories' => ['sekerleme-ve-draje']]],
        ['label' => 'Kahve', 'params' => ['selectedCategories' => ['kahve']]],
    ];

    if ($showStoreOnlyProducts) {
        $categoryLinks[] = ['label' => 'Mağazada Var', 'params' => ['selectedChannel' => 'store_only']];
    }
@endphp

<!-- Footer -->
<footer class="bg-surface-dark text-white pt-16 pb-8 border-t border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
            <!-- Company Info -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 mb-4">
                    @if(setting('logo') || setting('footer-logo'))
                        <a href="{{ route('home') }}">
                            @if(setting('footer-logo'))
                                <img src="{{ asset(setting('footer-logo')) }}" alt="{{ $brandName }}" class="h-10 w-auto brightness-0 invert opacity-90">
                            @else
                                <img src="{{ asset(setting('logo')) }}" alt="{{ $brandName }}" class="h-10 w-auto brightness-0 invert opacity-90">
                            @endif
                        </a>
                    @else
                        <a href="{{ route('home') }}" class="text-xl font-serif font-bold text-orange-100">
                            Can Kuruyemiş
                        </a>
                    @endif
                </div>
                <p class="text-gray-400 text-sm leading-relaxed">
                    {{ setting('description') ?: 'Can Kuruyemiş; kuruyemiş, kuru meyve, lokum, draje, kahve ve mağazada bulunan ek ürünleri tek katalogta sunar.' }}
                </p>
                @if(setting('sosyal-medya-linkleri'))
                    <div class="flex space-x-4 pt-2">
                        {!! setting('sosyal-medya-linkleri') !!}
                    </div>
                @endif
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-lg font-serif mb-6 text-orange-100">Hızlı Linkler</h4>
                <ul class="space-y-3 text-sm text-gray-400">
                    <li><a href="{{ route('home') }}" class="hover:text-secondary transition-colors">Ana Sayfa</a></li>
                    <li><a href="{{ route('products.index') }}" class="hover:text-secondary transition-colors">Tüm Ürünler</a></li>
                    @if(Route::has('page.show'))
                        <li><a href="{{ route('page.show', 'hakkimizda') }}" class="hover:text-secondary transition-colors">Hakkımızda</a></li>
                        <li><a href="{{ route('page.show', 'iletisim') }}" class="hover:text-secondary transition-colors">İletişim</a></li>
                        <li><a href="{{ route('blog.index') }}" class="hover:text-secondary transition-colors">Blog</a></li>
                    @endif
                </ul>
            </div>

            <!-- Categories -->
            <div>
                <h4 class="text-lg font-serif mb-6 text-orange-100">Kategoriler</h4>
                <ul class="space-y-3 text-sm text-gray-400">
                    @foreach($categoryLinks as $categoryLink)
                        <li>
                            <a href="{{ route('products.index', $categoryLink['params']) }}" class="hover:text-secondary transition-colors">
                                {{ $categoryLink['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h4 class="text-lg font-serif mb-6 text-orange-100">İletişim</h4>
                <ul class="space-y-4 text-sm text-gray-400">
                    <li class="flex items-start gap-3">
                        <span class="material-icons-round text-secondary mt-0.5">business</span>
                        <span>
                            Yasal işletme sahibi / Ticari unvan: Zeynep Tekin<br>
                            Marka: Can Kuruyemiş
                        </span>
                    </li>
                    @if(setting('adres'))
                        <li class="flex items-start gap-3">
                            <span class="material-icons-round text-secondary mt-0.5">location_on</span>
                            <span>{!! nl2br(setting('adres')) !!}</span>
                        </li>
                    @endif
                    @foreach(contact_phones() as $phone)
                        <li class="flex items-center gap-3">
                            <span class="material-icons-round text-secondary">phone</span>
                            <a href="tel:{{ $phone['tel'] }}" class="hover:text-secondary transition-colors">{{ $phone['display'] }}</a>
                        </li>
                    @endforeach
                    @if(setting('e-mail'))
                        <li class="flex items-center gap-3">
                            <span class="material-icons-round text-secondary">email</span>
                            <span>{{ setting('e-mail') }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-gray-500">
            <p>© {{ date('Y') }} {{ $brandName }}. Tüm hakları saklıdır. <span aria-hidden="true">·</span> <span class="platform-credit">Yazılım ve altyapı: <a href="https://bilgigram.com/" target="_blank" rel="noopener nofollow" style="color:inherit;font-weight:600"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 320" fill="currentColor" aria-hidden="true" focusable="false" style="display:inline-block;width:.72em;height:.72em;vertical-align:baseline;margin-inline-end:.28em"><path transform="translate(0,320) scale(1,-1)" d="M139 319 c-15 -2 -36 -8 -51 -16 l-8 -4 0 -69 c0 -80 0 -87 5 -99 9 -23 29 -41 53 -49 8 -2 34 -3 42 -1 l5 2 0 6 c1 9 1 39 0 39 -15 -8 -14 -7 -25 -7 -17 0 -28 7 -36 22 l-3 7 -1 62 0 61 5 1 c19 6 48 7 65 2 63 -17 102 -81 87 -143 -3 -11 -4 -80 -2 -83 4 -3 25 28 34 50 11 25 14 64 9 90 -17 84 -94 139 -179 129z M38 264 c-27 -33 -38 -63 -37 -105 0 -71 42 -129 110 -152 19 -6 26 -7 53 -7 30 1 40 3 65 15 l12 5 0 52 c0 72 -1 104 -4 113 -10 26 -29 44 -55 52 -9 3 -34 3 -42 1 l-4 -2 -1 -22 c0 -12 0 -22 0 -22 1 0 4 1 7 3 21 10 43 2 54 -18 l3 -6 1 -38 c1 -21 1 -49 1 -62 l0 -24 -3 -1 c-20 -8 -51 -8 -74 -1 -60 21 -94 79 -80 141 3 14 3 34 2 65 l0 22 -8 -9z"/></svg>Bilgigram</a></span></p>
            <div class="flex space-x-6 mt-4 md:mt-0">
                @if(Route::has('page.show'))
                    <a href="{{ route('page.show', 'gizlilik-sozlesmesi') }}" class="hover:text-gray-300">Gizlilik Politikası</a>
                    <a href="{{ route('page.show', 'kullanim-sartlari') }}" class="hover:text-gray-300">Kullanım Şartları</a>
                    <a href="{{ route('page.show', 'kargo-bilgileri') }}" class="hover:text-gray-300">Kargo Bilgileri</a>
                @endif
            </div>
        </div>
    </div>
</footer>
