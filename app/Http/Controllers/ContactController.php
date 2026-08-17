<?php

namespace App\Http\Controllers;

use App\Services\Seo\MetaTagService;
use App\Services\Seo\SchemaBuilder;
use App\Services\Seo\Schemas\BreadcrumbSchema;
use App\Services\Seo\Schemas\ContactSchema;
use App\Services\Seo\SeoHelper;
use Illuminate\Contracts\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        $title = 'İletişim';
        $description = 'Bizimle iletişime geçin. Sorularınız mı var? Projelerinizi tartışmak veya sadece merhaba demek için ekibimiz size yardımcı olmaya hazır.';
        $siteName = setting('title') ?: config('app.name');
        $fullTitle = $title === $siteName ? $siteName : "{$title} | {$siteName}";
        $canonical = route('contact.show');
        $image = setting('og-image') ? asset(setting('og-image')) : null;

        $breadcrumbs = [
            ['title' => 'Anasayfa', 'url' => route('home')],
            ['title' => $title, 'url' => $canonical],
        ];

        $breadcrumbSchema = SchemaBuilder::make()
            ->setSchema(new BreadcrumbSchema($breadcrumbs))
            ->render();

        // ContactSchema oluştur
        $contactSchema = SchemaBuilder::make()
            ->setSchema(new ContactSchema())
            ->render();

        // MetaTagService için data objesi oluştur
        $metaData = (object) [
            'meta_title' => $fullTitle,
            'meta_description' => $description,
            'cover_url' => $image,
            'url' => $canonical,
            'og_type' => 'website',
        ];

        $metaTagService = new MetaTagService();
        $metaTags = $metaTagService->generate($metaData);

        // SeoHelper ile schema'ları birleştir
        $seoVars = SeoHelper::vars([
            'breadcrumbs' => $breadcrumbSchema,
            'schema' => $contactSchema,
        ]);

        $seo = array_merge([
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'image' => setting('og-image'),
            'type' => 'website',
            'meta_tags' => $metaTags,
        ], $seoVars);

        return view('contact.index', compact('seo'));
    }
}
