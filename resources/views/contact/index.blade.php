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

<link rel="canonical" href="{{ $seo['canonical'] }}">
<meta name="robots" content="{{ \App\Services\Seo\SeoHelper::robotsContent('index,follow') }}">

@if(!empty($seo['breadCrumbSchema']))
    {!! $seo['breadCrumbSchema'] !!}
@endif

@if(!empty($seo['schema']))
    {!! $seo['schema'] !!}
@endif
@endpush

@section('content')
<main class="flex-grow w-full max-w-[1200px] mx-auto px-4 lg:px-8 py-10 lg:py-16">
    <!-- Page Heading -->
    <div class="mb-12 text-center lg:text-left">
        <h1 class="text-text-dark dark:text-text-light text-4xl lg:text-5xl font-black leading-tight tracking-[-0.033em] mb-4">
            Bizimle İletişime Geçin
        </h1>
        <p class="text-[#9c7349] dark:text-gray-400 text-lg lg:text-xl font-normal leading-normal max-w-2xl">
            Sorularınız mı var? Projelerinizi tartışmak veya sadece merhaba demek için ekibimiz size yardımcı olmaya hazır.
        </p>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16">
        <!-- Left Column: Contact Form -->
        <div class="lg:col-span-7">
            <div class="bg-white dark:bg-[#1a120b] p-6 lg:p-10 rounded-2xl shadow-sm border border-[#e8dbce] dark:border-gray-800">
                <h2 class="text-2xl font-bold mb-6 text-text-dark dark:text-text-light">Mesaj Gönderin</h2>
                <x-form-widget slug="iletisim-formu" class="bg-transparent" :hiddenFields="[]" />
            </div>
        </div>
        
        <!-- Right Column: Info & Map -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            <!-- Info Cards Stack -->
            <div class="flex flex-col gap-4">
                <!-- Business Information Card -->
                <div class="flex items-start gap-4 rounded-xl border border-[#e8dbce] dark:border-gray-800 bg-white dark:bg-[#1a120b] p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary shrink-0">
                        <span class="material-symbols-outlined text-[24px]">business</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <h2 class="text-text-dark dark:text-text-light text-lg font-bold leading-tight">İşletme Bilgileri</h2>
                        <p class="text-[#9c7349] dark:text-gray-400 text-sm font-normal leading-relaxed">
                            Yasal işletme sahibi / Ticari unvan: Zeynep Tekin<br>
                            Marka: Can Kuruyemiş
                        </p>
                    </div>
                </div>

                <!-- Address Card -->
                <div class="flex items-start gap-4 rounded-xl border border-[#e8dbce] dark:border-gray-800 bg-white dark:bg-[#1a120b] p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary shrink-0">
                        <span class="material-symbols-outlined text-[24px]">location_on</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <h2 class="text-text-dark dark:text-text-light text-lg font-bold leading-tight">Adres</h2>
                        <p class="text-[#9c7349] dark:text-gray-400 text-sm font-normal leading-relaxed">
                            @if(setting('adres'))
                                {!! nl2br(e(setting('adres'))) !!}
                            @else
                                Örnek Mahallesi, Teknoloji Caddesi No: 123,<br/>34000 İstanbul, Türkiye
                            @endif
                        </p>
                    </div>
                </div>
                
                <!-- Phone Card -->
                @if(setting('telefon'))
                <a class="flex items-start gap-4 rounded-xl border border-[#e8dbce] dark:border-gray-800 bg-white dark:bg-[#1a120b] p-5 shadow-sm hover:shadow-md transition-shadow group" href="tel:{{ setting('telefon') }}">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-colors shrink-0">
                        <span class="material-symbols-outlined text-[24px]">call</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <h2 class="text-text-dark dark:text-text-light text-lg font-bold leading-tight">Telefon</h2>
                        <p class="text-[#9c7349] dark:text-gray-400 text-sm font-normal leading-relaxed group-hover:text-primary transition-colors">
                            {{ setting('telefon') }}
                        </p>
                    </div>
                </a>
                @endif
                
                <!-- Email Card -->
                @if(setting('e-mail'))
                <a class="flex items-start gap-4 rounded-xl border border-[#e8dbce] dark:border-gray-800 bg-white dark:bg-[#1a120b] p-5 shadow-sm hover:shadow-md transition-shadow group" href="mailto:{{ setting('e-mail') }}">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-colors shrink-0">
                        <span class="material-symbols-outlined text-[24px]">mail</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <h2 class="text-text-dark dark:text-text-light text-lg font-bold leading-tight">E-posta</h2>
                        <p class="text-[#9c7349] dark:text-gray-400 text-sm font-normal leading-relaxed group-hover:text-primary transition-colors">
                            {{ setting('e-mail') }}
                        </p>
                    </div>
                </a>
                @endif
            </div>
            
            <!-- Map Section -->
            <div class="relative w-full h-[300px] rounded-xl overflow-hidden border border-[#e8dbce] dark:border-gray-800 shadow-sm mt-2">
                <iframe 
                    allowfullscreen="" 
                    data-alt="Map showing the location of the company" 
                    height="100%" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade" 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1053.3057450383894!2d29.003583485277808!3d41.09247851470928!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab67c656a63a9%3A0x92150d7114b2eabc!2zU3VsdGFuIFNlbGltLCBFc2tpIELDvHnDvGtkZXJlIENkLiBObzo1NywgMzQ0MTUgS2HEn8SxdGhhbmUvxLBzdGFuYnVs!5e0!3m2!1str!2str!4v1768509789813!5m2!1str!2str" 
                    style="border:0; filter: grayscale(1) contrast(1.2) opacity(0.9);" 
                    width="100%">
                </iframe>
            </div>
        </div>
    </div>
</main>
@endsection
