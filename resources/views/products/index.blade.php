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

<link rel="canonical" href="{{ $seo['canonical'] ?? route('products.index') }}">
<meta name="robots" content="{{ \App\Services\Seo\SeoHelper::robotsContent('index,follow') }}">

@if(!empty($seo['breadCrumbSchema']))
    {!! $seo['breadCrumbSchema'] !!}
@endif

@if(!empty($seo['schemaItemListJson']))
    {!! $seo['schemaItemListJson'] !!}
@endif

@endpush

@section('content')
    @livewire('product-filter')
@endsection
