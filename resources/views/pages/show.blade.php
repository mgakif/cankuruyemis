@extends('layouts.main')

@push('head')
@php
    $skipDefaultMeta = true;
@endphp

{!! $seo['meta_tags'] ?? '' !!}

@php
    $customCanonical = true;
    $customRobots = true;
    $pageRobots = ($page->is_indexable ? 'index' : 'noindex').','.($page->is_followable ? 'follow' : 'nofollow');
@endphp

<link rel="canonical" href="{{ $seo['canonical'] ?? route('page.show', $page) }}">
<meta name="robots" content="{{ \App\Services\Seo\SeoHelper::robotsContent($pageRobots) }}">

@if(!empty($seo['breadCrumbSchema']))
    {!! $seo['breadCrumbSchema'] !!}
@endif
@endpush

@section('content')
<main class="flex-grow w-full max-w-[1200px] mx-auto px-4 pt-40 pb-10 lg:px-8 lg:pt-44 lg:pb-16">
    <!-- Page Header -->
    <div class="mb-12">
        <h1 class="text-text-dark dark:text-text-light text-4xl lg:text-5xl font-black leading-tight tracking-[-0.033em] mb-4">
            {{ $page->title }}
        </h1>
        @if($page->seo_description)
            <p class="text-[#9c7349] dark:text-gray-400 text-lg lg:text-xl font-normal leading-normal max-w-3xl">
                {{ $page->seo_description }}
            </p>
        @endif
    </div>
    
    <!-- Page Content -->
    @if($page->content)
        <div class="prose prose-lg max-w-none dark:prose-invert prose-headings:text-text-dark dark:prose-headings:text-text-light prose-p:text-text-dark dark:prose-p:text-text-light prose-a:text-primary hover:prose-a:text-primary/80 prose-strong:text-text-dark dark:prose-strong:text-text-light prose-ul:text-text-dark dark:prose-ul:text-text-light prose-ol:text-text-dark dark:prose-ol:text-text-light">
            {!! $page->content !!}
        </div>
    @else
        <div class="bg-white dark:bg-[#1a120b] p-8 rounded-2xl shadow-sm border border-[#e8dbce] dark:border-gray-800 text-center">
            <p class="text-[#9c7349] dark:text-gray-400 text-lg">
                Bu sayfa için henüz içerik eklenmemiş.
            </p>
        </div>
    @endif
</main>
@endsection
