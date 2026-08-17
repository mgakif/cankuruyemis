@php
    $title = trim($seo['title'] ?? '') ?: config('app.name');
    $siteName = config('app.name');
    $description = $seo['description'] ?? null;
    $canonical = $seo['canonical'] ?? url()->current();
    $robots = \App\Services\Seo\SeoHelper::robotsContent($seo['robots'] ?? 'index,follow');

    $ogType = data_get($seo, 'og.type', 'website');
    $ogImage = data_get($seo, 'og.image') ?? ($seo['image'] ?? null);
    $ogImageUrl = null;
    if ($ogImage) {
        $ogImageUrl = \Illuminate\Support\Str::startsWith($ogImage, ['http://', 'https://', '//'])
            ? $ogImage
            : ( \Illuminate\Support\Str::startsWith($ogImage, '/storage/')
                ? asset(ltrim($ogImage, '/'))
                : asset('storage/' . ltrim($ogImage, '/'))
            );
    }

    $fullTitle = $title === $siteName ? $siteName : "{$title} | {$siteName}";
@endphp

<title>{{ $fullTitle }}</title>

@if($description)
    <meta name="description" content="{{ \Illuminate\Support\Str::of($description)->stripTags()->squish()->limit(160) }}">
@endif

<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $robots }}">

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $fullTitle }}">
@if($description)
    <meta property="og:description" content="{{ \Illuminate\Support\Str::of($description)->stripTags()->squish()->limit(200) }}">
@endif
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:type" content="{{ $ogType }}">
@if($ogImageUrl)
    <meta property="og:image" content="{{ $ogImageUrl }}">
@endif

<meta name="twitter:card" content="{{ $ogImageUrl ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $fullTitle }}">
@if($description)
    <meta name="twitter:description" content="{{ \Illuminate\Support\Str::of($description)->stripTags()->squish()->limit(200) }}">
@endif
@if($ogImageUrl)
    <meta name="twitter:image" content="{{ $ogImageUrl }}">
@endif

@if(!empty($seo['jsonld']))
    <script type="application/ld+json">{!! json_encode($seo['jsonld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
