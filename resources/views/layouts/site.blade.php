<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('partials.seo', ['seo' => $seo ?? []])

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-white text-zinc-900 antialiased">
    <header class="border-b border-zinc-200">
        <div class="mx-auto max-w-5xl px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-semibold tracking-tight">
                {{ config('app.name') }}
            </a>
            <nav class="flex items-center gap-4 text-sm">
                <a class="hover:underline" href="{{ route('blog.index') }}">Blog</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @yield('content')
    </main>

    <footer class="border-t border-zinc-200">
        <div class="mx-auto max-w-5xl px-4 py-6 text-sm text-zinc-500 flex items-center justify-between">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}</span>
            <div class="flex items-center gap-4">
                <a class="hover:underline" href="{{ route('sitemap') }}">Sitemap</a>
                <a class="hover:underline" href="{{ route('robots') }}">Robots</a>
            </div>
        </div>
    </footer>
</body>
</html>

