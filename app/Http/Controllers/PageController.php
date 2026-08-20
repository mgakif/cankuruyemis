<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Product;
use App\Services\Seo\MetaTagService;
use App\Services\Seo\SchemaBuilder;
use App\Services\Seo\Schemas\BreadcrumbSchema;
use App\Services\Seo\SeoHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function home(): View
    {
        $page = Page::query()
            ->published()
            ->where('slug', 'home')
            ->first();

        // Get featured products
        $products = Cache::rememberForever('home_products', function () {
            return Product::query()
                ->published()
                ->visibleOnSite()
                ->orderBy('position')
                ->limit(5)
                ->get();
        });

        // Get active FAQs
        $faqs = Cache::rememberForever('home_faqs', function () {
            return Faq::query()
                ->where('is_active', true)
                ->orderBy('order')
                ->get();
        });

        // SEO Meta Tags
        $siteName = setting('title') ?: config('app.name');
        $title = $page ? ($page->seo_title ?: $page->title) : $siteName;
        $description = $page 
            ? ($page->seo_description ?: Str::limit(strip_tags((string) $page->content), 160))
            : (setting('description') ?: config('seo.default_description', ''));
        $fullTitle = $title === $siteName ? $siteName : "{$title} | {$siteName}";
        $imageUrl = ($page ? ($page->seo_image_path ?? setting('og-image')) : setting('og-image')) 
            ? asset(($page ? ($page->seo_image_path ?? setting('og-image')) : setting('og-image')))
            : null;

        $metaData = (object) [
            'meta_title' => $fullTitle,
            'meta_description' => $description,
            'cover_url' => $imageUrl,
            'url' => route('home'),
            'og_type' => 'website',
        ];

        $metaTagService = new MetaTagService();
        $metaTags = $metaTagService->generate($metaData);

        if (! $page) {
            $seo = [
                'title' => $title,
                'description' => $description,
                'canonical' => route('home'),
                'image' => setting('og-image'),
                'type' => 'website',
                'meta_tags' => $metaTags,
            ];

            return view('home', compact('seo', 'products', 'faqs'));
        }

        $seo = [
            'title' => $title,
            'description' => $description,
            'canonical' => route('home'),
            'image' => $page->seo_image_path ?? setting('og-image'),
            'type' => 'website',
            'meta_tags' => $metaTags,
        ];

        return view('pages.show', compact('page', 'seo'));
    }

    public function show(Page $page): View
    {

        abort_unless(
            $page->is_published && (! $page->published_at || $page->published_at->isPast()),
            404
        );

        // Avoid duplicate content if someone creates a "home" page and also visits /home
        if ($page->slug === 'home') {
            return redirect()->route('home', status: 301);
        }

        // Special view for hakkimizda page
        if ($page->slug === 'hakkimizda') {
            return view('pages.hakkimizda');
        }

        $title = $page->seo_title ?? $page->title;
        $description = $page->seo_description ?? Str::limit(strip_tags((string) $page->content), 160);

        $breadcrumbs = [
            ['title' => 'Anasayfa', 'url' => route('home')],
            ['title' => $title, 'url' => route('page.show', $page)],
        ];

        $breadcrumbSchema = SchemaBuilder::make()
            ->setSchema(new BreadcrumbSchema($breadcrumbs))
            ->render();

        // SEO Meta Tags
        $siteName = setting('title') ?: config('app.name');
        $fullTitle = $title === $siteName ? $siteName : "{$title} | {$siteName}";
        $imageUrl = ($page->seo_image_path ?? setting('og-image')) 
            ? asset($page->seo_image_path ?? setting('og-image'))
            : null;

        $metaData = (object) [
            'meta_title' => $fullTitle,
            'meta_description' => $description,
            'cover_url' => $imageUrl,
            'url' => $page->canonical_url ?? route('page.show', $page),
            'og_type' => 'website',
        ];

        $metaTagService = new MetaTagService();
        $metaTags = $metaTagService->generate($metaData);

        // SeoHelper ile schema'ları birleştir
        $seoVars = SeoHelper::vars([
            'breadcrumbs' => $breadcrumbSchema,
        ]);

        $seo = array_merge([
            'title' => $title,
            'description' => $description,
            'canonical' => $page->canonical_url ?? route('page.show', $page),
            'image' => $page->seo_image_path ?? setting('og-image'),
            'type' => 'website',
            'meta_tags' => $metaTags,
        ], $seoVars);

        return view('pages.show', compact('page', 'seo'));
    }

    public function hakkimizda(): View
    {
        $title = 'Hakkımızda';
        $description = 'Can Kuruyemiş, 2024 yılında girişimci Zeynep Tekin tarafından Mersin’de kuruldu. Tazelik, kalite ve güvenle kuruyemiş, kuru meyve ve özel lezzetler sunuyoruz.';
        
        // SEO Meta Tags
        $siteName = setting('title') ?: config('app.name');
        $fullTitle = $title === $siteName ? $siteName : "{$title} | {$siteName}";
        $imageUrl = setting('og-image') ? asset(setting('og-image')) : null;

        $metaData = (object) [
            'meta_title' => $fullTitle,
            'meta_description' => $description,
            'cover_url' => $imageUrl,
            'url' => route('about.show'),
            'og_type' => 'website',
        ];

        $metaTagService = new MetaTagService();
        $metaTags = $metaTagService->generate($metaData);

        $seo = [
            'title' => $title,
            'description' => $description,
            'canonical' => route('about.show'),
            'image' => setting('og-image'),
            'type' => 'website',
            'meta_tags' => $metaTags,
        ];
        
        return view('pages.hakkimizda', compact('seo'));
    }
}
