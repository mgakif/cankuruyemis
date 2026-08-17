<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->paginate(10)
            ->withQueryString();

        $seo = [
            'title' => 'Blog',
            'description' => 'Blog yazıları',
            'canonical' => route('blog.index'),
            'type' => 'website',
        ];

        return view('blog.index', compact('posts', 'seo'));
    }

    public function show(Post $post): View
    {
        abort_unless(
            $post->is_published && (! $post->published_at || $post->published_at->isPast()),
            404
        );

        $description = $post->seo_description
            ?: ($post->excerpt ?: Str::limit(strip_tags($post->content), 160));

        $seo = [
            'title' => $post->seo_title ?: $post->title,
            'description' => $description,
            'canonical' => route('blog.show', $post),
            'image' => $post->seo_image_path,
            'type' => 'article',
            'published_time' => optional($post->published_at)->toIso8601String(),
        ];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'description' => $description,
            'datePublished' => optional($post->published_at)->toIso8601String(),
            'dateModified' => optional($post->updated_at)->toIso8601String(),
            'mainEntityOfPage' => route('blog.show', $post),
        ];

        return view('blog.show', compact('post', 'seo', 'jsonLd'));
    }
}

