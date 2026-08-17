@extends('layouts.main')

@push('head')
@php
    $skipDefaultMeta = true;
    $customCanonical = true;
    $customRobots = true;
@endphp

{!! $seo['meta_tags'] ?? '' !!}

<link rel="canonical" href="{{ $seo['canonical'] ?? route('blog.index') }}">
<meta name="robots" content="{{ \App\Services\Seo\SeoHelper::robotsContent('index,follow') }}">
@endpush

@section('content')
    <div class="space-y-6">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight">Blog</h1>
                <p class="text-zinc-600">Yayınlanan yazılar</p>
            </div>
        </div>

        <div class="grid gap-4">
            @forelse($posts as $post)
                <article class="rounded-lg border border-zinc-200 p-5 hover:border-zinc-300 transition">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-lg font-semibold">
                            <a class="hover:underline" href="{{ route('blog.show', $post) }}">{{ $post->title }}</a>
                        </h2>
                        @if($post->published_at)
                            <time class="text-xs text-zinc-500" datetime="{{ $post->published_at->toDateString() }}">
                                {{ $post->published_at->format('d.m.Y') }}
                            </time>
                        @endif
                    </div>
                    @if($post->excerpt)
                        <p class="mt-2 text-sm text-zinc-600">
                            {{ \Illuminate\Support\Str::of($post->excerpt)->stripTags()->squish()->limit(200) }}
                        </p>
                    @endif
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-zinc-600">
                    Henüz yayınlanmış blog yazısı yok.
                </div>
            @endforelse
        </div>

        <div>
            {{ $posts->links() }}
        </div>
    </div>
@endsection
