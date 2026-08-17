<?php

namespace App\Listeners;

use App\Events\FieldSaved;
use Illuminate\Support\Facades\Cache;

class RemoveParticularCache
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FieldSaved $event): void
    {
        $model = $event->model;

        if ($model instanceof \App\Models\Setting) {
            if ($model->isDirty(['value'])) {
                Cache::forget("setting_{$model->slug}");
            }
        }

        if ($model instanceof \App\Models\Menu) {
            if ($model->isDirty(['value'])) {
                Cache::forget("menu_array_{$model->slug}");
            }
        }

        if ($model instanceof \App\Models\PageSection) {
            Cache::forget('home_page');
        }

        // Blog ile ilgili cache'leri temizle
        if ($model instanceof \App\Models\Article) {
            // Article ile ilgili cache'leri temizle
            Cache::forget("article_{$model->slug}");
            Cache::forget('latest_articles');

            // İlgili kategori ve etiketlerin cache'lerini temizle
            if ($model->category) {
                Cache::forget("blog_articles_in_category_{$model->category->slug}_".request('page', 1));
            }

            foreach ($model->tags as $tag) {
                Cache::forget("blog_articles_with_tag_{$tag->slug}_".request('page', 1));
            }

            // İlgili yazılar cache'ini temizle
            Cache::forget("related_articles_{$model->id}");
        }

        if ($model instanceof \App\Models\Category) {
            Cache::forget("blog_category_{$model->slug}");
            Cache::forget('blog_categories');
            Cache::forget("blog_articles_in_category_{$model->slug}_".request('page', 1));
        }

        if ($model instanceof \App\Models\Tag) {
            Cache::forget("blog_tag_{$model->slug}");
            Cache::forget("blog_articles_with_tag_{$model->slug}_".request('page', 1));
        }

        // Product ile ilgili cache'leri temizle
        if ($model instanceof \App\Models\Product) {
            // Ürün listesi cache'ini temizle
            Cache::forget('products_index');
            Cache::forget('products_index_seo');
            
            // Anasayfa ürünleri cache'ini temizle
            Cache::forget('home_products');
            
            // Ürün detay cache'ini temizle
            Cache::forget("product_show_{$model->slug}");
        }

        // FAQ ile ilgili cache'leri temizle
        if ($model instanceof \App\Models\Faq) {
            Cache::forget('home_faqs');
        }
    }
}
