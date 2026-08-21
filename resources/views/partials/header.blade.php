@php
    $brandName = setting('title') ?: 'Can Kuruyemiş';
    $phones = contact_phones();
    $showStoreOnlyProducts = \App\Models\Product::shouldShowStoreOnlyOnSite();
@endphp

<div class="fixed inset-x-0 top-0 z-50 border-b border-primary/10 bg-[#2b1d12] text-white">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-4 px-4 py-2 text-xs sm:px-6 lg:px-8">
        <div class="flex items-center gap-3 text-white/85">
            <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2.5 py-1 font-semibold uppercase tracking-[0.18em]">
                Taze secim
            </span>
                    <span class="hidden sm:inline">Kuruyemis, lokum, draje ve magazaya ozel urunler tek katalogda; online her yerden, magaza urunleri yakin cevre ve Getir ile.</span>
        </div>
        <div class="flex items-center gap-4">
            @if($showStoreOnlyProducts)
                <a href="{{ route('products.index', ['selectedChannel' => 'store_only']) }}" class="font-semibold text-amber-300 transition hover:text-amber-200">
                    Yakin cevre + Getir
                </a>
            @endif
            @foreach($phones as $phone)
                <a href="tel:{{ $phone['tel'] }}" class="hidden sm:inline text-white/70 hover:text-white transition-colors">{{ $phone['display'] }}</a>
            @endforeach
        </div>
    </div>
</div>

<!-- Navigation -->
<nav class="fixed top-[37px] w-full z-50 bg-background-light/90 dark:bg-background-dark/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center gap-2">
                @if(setting('logo') || setting('footer-logo'))
                    <a href="{{ route('home') }}">
                        @if(setting('logo'))
                            <img src="{{ asset(setting('logo')) }}" alt="{{ $brandName }}" class="h-12 w-auto object-contain dark:hidden">
                        @endif
                        @if(setting('footer-logo'))
                            <img src="{{ asset(setting('footer-logo')) }}" alt="{{ $brandName }}" class="h-12 w-auto object-contain hidden dark:block">
                        @endif
                    </a>
                @else
                    <a href="{{ route('home') }}" class="text-2xl font-serif font-bold text-primary dark:text-orange-200">
                        Can Kuruyemiş
                    </a>
                @endif
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <x-menu.nav slug="ana-menu" />
            </div>

            <!-- Right Side Actions -->
            <div class="flex items-center space-x-4">
                <div class="hidden xl:flex items-center gap-2">
                    <a href="{{ route('products.index', ['selectedChannel' => 'online']) }}" class="inline-flex items-center rounded-full bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
                        Online Siparis
                    </a>
                    @if($showStoreOnlyProducts)
                        <a href="{{ route('products.index', ['selectedChannel' => 'store_only']) }}" class="inline-flex items-center rounded-full border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:border-amber-400 hover:bg-amber-100">
                            Yakin cevre + Getir
                        </a>
                    @endif
                </div>

                <!-- Dark Mode Toggle -->
                <button
                    x-data="{ dark: localStorage.getItem('darkMode') === 'true' }"
                    @click="dark = !dark; localStorage.setItem('darkMode', dark); if (dark) { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); }"
                    class="text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-orange-200 transition-colors hidden md:block"
                    aria-label="Toggle dark mode"
                >
                    <span class="material-icons-round" x-show="!dark">dark_mode</span>
                    <span class="material-icons-round" x-show="dark" x-cloak>light_mode</span>
                </button>

                <!-- Mobile Menu Button -->
                <div class="md:hidden" x-data="{ open: false }" @close-mobile-menu.window="open = false">
                    <button
                        @click="open = !open"
                        class="text-gray-600 dark:text-gray-300 hover:text-primary transition-colors"
                        aria-label="Toggle menu"
                    >
                        <span class="material-icons-round" x-show="!open">menu</span>
                        <span class="material-icons-round" x-show="open" x-cloak>close</span>
                    </button>

                    <!-- Mobile Menu Overlay -->
                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed top-0 left-0 right-0 bottom-0 z-[90] bg-black/50 backdrop-blur-sm md:hidden"
                        @click="open = false"
                    ></div>

                    <!-- Mobile Menu Panel -->
                    <div
                        x-show="open"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="translate-x-full"
                        class="fixed top-0 right-0 h-screen w-80 bg-background-light dark:bg-background-dark border-l border-gray-200 dark:border-gray-800 z-[95] overflow-y-auto p-6 md:hidden"
                        @click.stop=""
                    >
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                @if(setting('logo') || setting('footer-logo'))
                                    @if(setting('logo'))
                                        <img src="{{ asset(setting('logo')) }}" alt="{{ $brandName }}" class="h-10 w-auto dark:hidden">
                                    @endif
                                    @if(setting('footer-logo'))
                                        <img src="{{ asset(setting('footer-logo')) }}" alt="{{ $brandName }}" class="h-10 w-auto hidden dark:block">
                                    @endif
                                @else
                                    <span class="text-xl font-serif font-bold text-primary dark:text-orange-200">Can Kuruyemiş</span>
                                @endif
                            </div>
                            <button
                                @click="open = false"
                                class="text-gray-600 dark:text-gray-300 hover:text-primary transition-colors"
                                aria-label="Close menu"
                            >
                                <span class="material-icons-round">close</span>
                            </button>
                        </div>

                        <div @click.stop>
                            <x-menu.mobile-nav slug="ana-menu" />
                        </div>

                        <div class="mt-8 grid gap-3">
                            <a href="{{ route('products.index', ['selectedChannel' => 'online']) }}" class="inline-flex items-center justify-center rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white transition hover:bg-primary/90">
                                Online Siparis
                            </a>
                            @if($showStoreOnlyProducts)
                                <a href="{{ route('products.index', ['selectedChannel' => 'store_only']) }}" class="inline-flex items-center justify-center rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700 transition hover:border-amber-400 hover:bg-amber-100">
                                    Yakin cevre + Getir
                                </a>
                            @endif
                        </div>

                        @if($phones)
                            <div class="mt-6 grid gap-2">
                                @foreach($phones as $phone)
                                    <a href="tel:{{ $phone['tel'] }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3 text-sm font-semibold text-text-dark dark:text-text-light hover:text-primary transition-colors">
                                        <span class="material-icons-round text-base">call</span>
                                        {{ $phone['display'] }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-800">
                            <button
                                x-data="{ dark: localStorage.getItem('darkMode') === 'true' }"
                                @click="dark = !dark; localStorage.setItem('darkMode', dark); if (dark) { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); }"
                                class="flex h-12 w-full items-center justify-center gap-2 rounded-lg border border-gray-200 dark:border-gray-800 bg-background-light dark:bg-background-dark text-text-dark dark:text-text-light hover:text-primary transition-colors"
                                aria-label="Toggle dark mode">
                                <span class="material-icons-round" x-show="!dark">dark_mode</span>
                                <span class="material-icons-round" x-show="dark" x-cloak>light_mode</span>
                                <span class="text-sm font-medium" x-text="dark ? 'Açık Tema' : 'Koyu Tema'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
