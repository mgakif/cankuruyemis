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

<link rel="canonical" href="{{ $seo['canonical'] ?? url("/{$form->slug}") }}">
<meta name="robots" content="{{ \App\Services\Seo\SeoHelper::robotsContent('index,follow') }}">

@if(!empty($seo['breadCrumbSchema']))
    {!! $seo['breadCrumbSchema'] !!}
@endif
@endpush

@section('content')
<main class="flex-grow w-full max-w-[1200px] mx-auto px-4 lg:px-8 py-10 lg:py-16">
    <!-- Breadcrumbs -->
    <div class="mb-8">
        <div class="flex flex-wrap gap-2 py-4">
            <a class="text-[#9c7349] hover:text-primary dark:text-[#ccaa88] text-sm font-medium leading-normal" href="{{ route('home') }}">Anasayfa</a>
            <span class="text-[#9c7349] dark:text-[#ccaa88] text-sm font-medium leading-normal">/</span>
            <span class="text-text-dark dark:text-text-light text-sm font-bold leading-normal">{{ $form->name }}</span>
        </div>
    </div>

    <!-- Page Header -->
    <div class="mb-12">
        <h1 class="text-text-dark dark:text-text-light text-4xl lg:text-5xl font-black leading-tight tracking-[-0.033em] mb-4">
            {{ $form->name }}
        </h1>
        @if($form->description || $form->brief)
            <div class="prose prose-lg max-w-none dark:prose-invert prose-headings:text-text-dark dark:prose-headings:text-text-light prose-p:text-text-dark dark:prose-p:text-text-light prose-a:text-primary hover:prose-a:text-primary/80">
                {!! $form->description ?: $form->brief !!}
            </div>
        @endif
    </div>
    
    <!-- Form Widget -->
    <div class="bg-white dark:bg-[#1a120b] p-8 rounded-2xl shadow-sm border border-[#e8dbce] dark:border-gray-800">
        <x-form-widget :slug="$form->slug" class="bg-transparent" :hiddenFields="[]" />
    </div>
</main>
@endsection
