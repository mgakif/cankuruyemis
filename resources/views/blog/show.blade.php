@extends('layouts.site')

@section('content')
    <article class="prose prose-zinc max-w-none">
        <a class="no-underline text-sm text-zinc-500 hover:underline" href="{{ route('blog.index') }}">&larr; Blog</a>

        <h1 class="mt-4">{{ $post->title }}</h1>

        @if($post->published_at)
            <p class="-mt-3 text-sm text-zinc-500">
                <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d.m.Y') }}</time>
            </p>
        @endif

        @if($post->excerpt)
            <p class="lead">{{ $post->excerpt }}</p>
        @endif

        @if($post->content)
            {!! $post->content !!}
        @endif
    </article>
@endsection

@extends('layouts.app')

@section('content')
    <article class="prose max-w-none">
        <div class="not-prose mb-6">
            <a class="text-sm text-slate-600 hover:text-slate-950" href="{{ route('blog.index') }}">← Blog</a>
        </div>

        <h1>{{ $post->title }}</h1>

        <p class="text-sm text-slate-500">
            {{ optional($post->published_at)->format('d.m.Y') }}
        </p>

        {!! $post->content !!}
    </article>
@endsection

