<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Page;
use App\Services\Seo\MetaTagService;
use App\Services\Seo\SchemaBuilder;
use App\Services\Seo\Schemas\BreadcrumbSchema;
use App\Services\Seo\SeoHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class FormPageController extends Controller
{
    public function show(string $slug): View
    {
        // Önce form kontrolü
        $form = Form::where('slug', $slug)->with('fields')->first();
        
        if ($form) {
            // Token kontrolü - eğer form token gerektiriyorsa
            if ($form->requires_token && $form->access_token) {
                $providedToken = request()->query('form');
                
                // Token yoksa veya yanlışsa 404
                if (!$providedToken || $providedToken !== $form->access_token) {
                    abort(404);
                }
            }
            
            // Form varsa form sayfasını göster
            $title = $form->name;
            $description = $form->description ?: $form->brief ?: 'Form sayfası';
            
            $breadcrumbs = [
                ['title' => 'Anasayfa', 'url' => route('home')],
                ['title' => $title, 'url' => url("/{$slug}")],
            ];
            
            $breadcrumbSchema = SchemaBuilder::make()
                ->setSchema(new BreadcrumbSchema($breadcrumbs))
                ->render();
            
            // SEO Meta Tags
            $siteName = setting('title') ?: config('app.name');
            $fullTitle = $title === $siteName ? $siteName : "{$title} | {$siteName}";
            $imageUrl = setting('og-image') ? asset(setting('og-image')) : null;

            $metaData = (object) [
                'meta_title' => $fullTitle,
                'meta_description' => Str::limit(strip_tags($description), 160),
                'cover_url' => $imageUrl,
                'url' => url("/{$slug}"),
                'og_type' => 'website',
            ];

            $metaTagService = new MetaTagService();
            $metaTags = $metaTagService->generate($metaData);
            
            // Token korumalı formlara noindex, nofollow ekle
            if ($form->requires_token) {
                $metaTags .= "\n    <meta name=\"robots\" content=\"".e(SeoHelper::robotsContent('noindex, nofollow'))."\">";
            }

            // SeoHelper ile schema'ları birleştir
            $seoVars = SeoHelper::vars([
                'breadcrumbs' => $breadcrumbSchema,
            ]);
            
            $seo = array_merge([
                'title' => $title,
                'description' => Str::limit(strip_tags($description), 160),
                'canonical' => url("/{$slug}"),
                'image' => setting('og-image'),
                'type' => 'website',
                'meta_tags' => $metaTags,
                'is_token_protected' => $form->requires_token,
            ], $seoVars);
            
            return view('forms.show', compact('form', 'seo'));
        }
        
        // Form yoksa sayfa kontrolü
        $page = Page::query()
            ->where('slug', $slug)
            ->first();
        
        if ($page && $page->is_published && (! $page->published_at || $page->published_at->isPast())) {
            // Sayfa varsa PageController'daki show metodunu kullan
            $title = $page->seo_title ?? $page->title;
            $description = $page->seo_description ?? Str::limit(strip_tags((string) $page->content), 160);
            
            $breadcrumbs = [
                ['title' => 'Anasayfa', 'url' => route('home')],
                ['title' => $title, 'url' => url("/{$slug}")],
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
                'url' => $page->canonical_url ?? url("/{$slug}"),
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
                'canonical' => $page->canonical_url ?? url("/{$slug}"),
                'image' => $page->seo_image_path ?? setting('og-image'),
                'type' => 'website',
                'meta_tags' => $metaTags,
            ], $seoVars);
            
            return view('pages.show', compact('page', 'seo'));
        }
        
        // İkisi de yoksa 404
        abort(404);
    }
}
