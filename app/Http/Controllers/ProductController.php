<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Seo\MetaTagService;
use App\Services\Seo\SchemaBuilder;
use App\Services\Seo\Schemas\BreadcrumbSchema;
use App\Services\Seo\Schemas\ProductListSchema;
use App\Services\Seo\Schemas\ProductSchema;
use App\Services\Seo\SeoHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Cache::rememberForever('products_index', function () {
            return Product::query()
                ->published()
                ->visibleOnSite()
                ->orderBy('position')
                ->get();
        });

        // Breadcrumbs
        $breadcrumbs = [
            ['title' => 'Anasayfa', 'url' => route('home')],
            ['title' => 'Ürünler', 'url' => route('products.index')],
        ];

        // BreadcrumbSchema oluştur
        $breadcrumbSchema = SchemaBuilder::make()
            ->setSchema(new BreadcrumbSchema($breadcrumbs))
            ->render();

        // ProductListSchema oluştur
        $productListSchema = SchemaBuilder::make()
            ->setSchema(new ProductListSchema($products))
            ->render();

        $seoData = Cache::rememberForever('products_index_seo', function () {
            return [
                'title' => 'Ürünler',
                'description' => 'Ürün kataloğumuz',
                'canonical' => route('products.index'),
                'type' => 'website',
            ];
        });

        // MetaTagService için data objesi oluştur
        $siteName = setting('title') ?: config('app.name');
        $fullTitle = $seoData['title'] === $siteName ? $siteName : "{$seoData['title']} | {$siteName}";
        $imageUrl = setting('og-image') ? asset(setting('og-image')) : null;

        $metaData = (object) [
            'meta_title' => $fullTitle,
            'meta_description' => $seoData['description'],
            'cover_url' => $imageUrl,
            'url' => $seoData['canonical'],
            'og_type' => 'website',
        ];

        $metaTagService = new MetaTagService();
        $metaTags = $metaTagService->generate($metaData);

        // SeoHelper ile schema'ları birleştir
        $seoVars = SeoHelper::vars([
            'breadcrumbs' => $breadcrumbSchema,
            'schemaItemListJson' => $productListSchema,
        ]);

        $seo = array_merge($seoData, ['meta_tags' => $metaTags], $seoVars);

        return view('products.index', compact('products', 'seo'));
    }

    public function show(Product $product): View
    {
        abort_unless(
            $product->is_published
                && $product->sale_channel !== 'hidden'
                && ($product->sale_channel !== 'store_only' || Product::shouldShowStoreOnlyOnSite())
                && (! $product->published_at || $product->published_at->isPast()),
            404
        );

        $cacheKey = "product_show_{$product->slug}";
        $seoData = Cache::rememberForever($cacheKey, function () use ($product) {
            $description = $product->seo_description
                ?: ($product->short_description ?: Str::limit(strip_tags($product->description ?? ''), 160));

            $robots = [];
            if (! $product->is_indexable) {
                $robots[] = 'noindex';
            }
            if (! $product->is_followable) {
                $robots[] = 'nofollow';
            }
            $robotsString = ! empty($robots) ? implode(',', $robots) : 'index,follow';
            $robotsString = SeoHelper::robotsContent($robotsString);

            // Breadcrumbs
            $breadcrumbs = [
                ['title' => 'Anasayfa', 'url' => route('home')],
                ['title' => 'Ürünler', 'url' => route('products.index')],
                ['title' => $product->title, 'url' => route('products.show', $product)],
            ];

            // BreadcrumbSchema oluştur
            $breadcrumbSchema = SchemaBuilder::make()
                ->setSchema(new BreadcrumbSchema($breadcrumbs))
                ->render();

            // ProductSchema kullan
            $productSchema = new ProductSchema($product, $breadcrumbs, []);
            $jsonLd = $productSchema->generate();

            return [
                'title' => $product->seo_title ?: $product->title,
                'description' => $description,
                'canonical' => $product->canonical_url ?: route('products.show', $product),
                'image' => $product->seo_image_path ?: $product->featured_image_path,
                'type' => 'product',
                'robots' => $robotsString,
                'jsonld' => $jsonLd,
                'breadcrumbSchema' => $breadcrumbSchema,
            ];
        });

        // MetaTagService için data objesi oluştur
        $siteName = setting('title') ?: config('app.name');
        $fullTitle = $seoData['title'] === $siteName ? $siteName : "{$seoData['title']} | {$siteName}";
        $imageUrl = $seoData['image'] ? asset('storage/' . $seoData['image']) : null;

        $metaData = (object) [
            'meta_title' => $fullTitle,
            'meta_description' => $seoData['description'],
            'cover_url' => $imageUrl,
            'url' => $seoData['canonical'],
            'og_type' => 'product',
        ];

        $metaTagService = new MetaTagService();
        $metaTags = $metaTagService->generate($metaData);

        // SeoHelper ile schema'ları birleştir
        $seoVars = SeoHelper::vars([
            'breadcrumbs' => $seoData['breadcrumbSchema'] ?? '',
        ]);

        $seo = array_merge($seoData, ['meta_tags' => $metaTags], $seoVars);

        $relatedProducts = Product::query()
            ->published()
            ->visibleOnSite()
            ->where('id', '!=', $product->id)
            ->when($product->product_category_id, fn ($query) => $query->where('product_category_id', $product->product_category_id))
            ->orderBy('position')
            ->limit(8)
            ->get();

        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::query()
                ->published()
                ->visibleOnSite()
                ->where('id', '!=', $product->id)
                ->orderBy('position')
                ->limit(8)
                ->get();
        }

        return view('products.show', compact('product', 'seo', 'relatedProducts'));
    }
}
