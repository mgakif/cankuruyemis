<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $seo = $seo ?? [];
        $seoTitle = $seo['title'] ?? config('seo.default_title');
        $seoDescription = $seo['description'] ?? config('seo.default_description');
        $seoCanonical = $seo['canonical'] ?? url()->current();
        $seoType = $seo['type'] ?? 'website';
        $seoImage = $seo['image'] ?? config('seo.default_image');
        $seoRobots = \App\Services\Seo\SeoHelper::robotsContent($seo['robots'] ?? 'index,follow');
        $seoSiteName = config('seo.site_name', config('app.name'));
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ $seoRobots }}">
    <link rel="canonical" href="{{ $seoCanonical }}">

    <meta property="og:site_name" content="{{ $seoSiteName }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:type" content="{{ $seoType }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    @if($seoImage)
        <meta property="og:image" content="{{ $seoImage }}">
    @endif

    <meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
    @if(config('seo.twitter_site'))
        <meta name="twitter:site" content="{{ config('seo.twitter_site') }}">
    @endif
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    @if($seoImage)
        <meta name="twitter:image" content="{{ $seoImage }}">
    @endif

    @if(!empty($seo['published_time']))
        <meta property="article:published_time" content="{{ $seo['published_time'] }}">
    @endif

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @stack('head')
</head>
<body class="min-h-screen bg-white text-slate-900">
    <header class="border-b border-slate-200">
        <div class="mx-auto max-w-5xl px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-semibold tracking-tight">
                {{ config('app.name') }}
            </a>
            <nav class="flex items-center gap-4 text-sm text-slate-700">
                <a class="hover:text-slate-950" href="{{ route('blog.index') }}">Blog</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-10">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200">
        <div class="mx-auto max-w-5xl px-4 py-8 text-sm text-slate-600 flex items-center justify-between">
            <span>&copy; {{ now()->year }} {{ config('app.name') }}</span>
            <span class="flex gap-3">
                <a class="hover:text-slate-950" href="{{ route('sitemap') }}">Sitemap</a>
                <a class="hover:text-slate-950" href="{{ route('robots') }}">Robots</a>
            </span>
        </div>
    </footer>

    @isset($jsonLd)
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
    @endisset

    @stack('scripts')
</body>
</html>
