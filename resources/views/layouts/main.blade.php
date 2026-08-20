<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('darkMode') === 'true' }">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="facebook-domain-verification" content="k8t8m1bzuneqi8msgkqd4re9shosax" />
    
    @if(setting('favicon'))
        <link rel="icon" href="{{ asset(setting('favicon')) }}" type="image/x-icon">
    @endif

    @php
        // @stack('head') içinde title tag'ı varsa default meta tagları gösterme
        // Bu kontrol için stack içeriğini kontrol edemiyoruz, bu yüzden
        // sayfalarda @push('head') kullanıldığında $skipDefaultMeta = true set edilmeli
        $hasCustomMeta = isset($skipDefaultMeta) && $skipDefaultMeta;
    @endphp

    @if(!$hasCustomMeta)
        <title>{{ setting('title') ?: config('app.name') }}</title>
        
        @if(setting('description'))
            <meta name="description" content="{{ setting('description') }}">
        @endif

        @if(setting('og-image'))
            <meta property="og:image" content="{{ asset(setting('og-image')) }}">
        @endif
    @endif

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet"/>

    <!-- Material Icons Round -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    @stack('head')
    
    <!-- Global SEO Schemas -->
    {!! $schemaOrg ?? '' !!}
    {!! $schemaSitelinks ?? '' !!}
    {!! $travelAgencySchema ?? '' !!}
    
    <!-- Canonical (sayfalarda özel canonical yoksa global canonical göster) -->
    @if(empty($customCanonical))
        {!! $canonical ?? '' !!}
    @endif
    
    <!-- Robots (sayfalarda özel robots yoksa global robots göster) -->
    @if(empty($customRobots))
        {!! $robots ?? '' !!}
    @endif
    
    <!-- FAQ Schema -->
    {!! $faqSchema ?? '' !!}
    
    <script>
        // Dark mode'u sayfa yüklenmeden önce ayarla (FOUC önleme)
        (function() {
            // Önce tüm dark class'larını kaldır
            document.documentElement.classList.remove('dark');
            
            const stored = localStorage.getItem('darkMode');
            // Sadece 'true' string'i ise dark mode aç
            if (stored === 'true') {
                document.documentElement.classList.add('dark');
            } else {
                // 'false' veya null ise light mode (dark class'ı zaten kaldırıldı)
                // localStorage'ı 'false' olarak kaydet
                localStorage.setItem('darkMode', 'false');
            }
        })();
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark text-text-dark dark:text-text-light font-sans overflow-x-hidden antialiased transition-colors duration-300">
    @include('partials.header')
    <!-- Wrapper -->
    <main class="relative flex min-h-screen w-full flex-col">
        @yield('content')
    </main>
    @include('partials.footer')
    
    <!-- WhatsApp Floating Button -->
    @if(setting('whatsapp') && setting('whatsapp-dugmesi'))
        @php
            $whatsappNumber = preg_replace('/[^0-9]/', '', setting('whatsapp')); // Sadece rakamları al
            $whatsappUrl = 'https://wa.me/' . $whatsappNumber;
        @endphp
        <a 
            href="{{ $whatsappUrl }}" 
            target="_blank" 
            rel="noopener noreferrer"
            class="fixed bottom-6 right-6 z-50 flex items-center justify-center shadow-lg transition-all hover:scale-110 hover:shadow-xl"
            aria-label="WhatsApp ile iletişime geçin"
        >
            <img 
                src="{{ asset(setting('whatsapp-dugmesi')) }}" 
                alt="WhatsApp" 
                class="block w-40 h-auto"
            >
        </a>
    @endif
    
    @livewireScripts
    @stack('scripts')
</body>
</html>
