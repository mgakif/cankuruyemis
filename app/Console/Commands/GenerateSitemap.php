<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'app:generate-sitemap {--path= : Output path (default public/sitemap.xml)}';

    protected $description = 'Generate sitemap.xml (pages + blog posts + products)';

    public function handle(): int
    {
        $path = $this->option('path') ?: public_path('sitemap.xml');

        $sitemap = Sitemap::create()
            ->add(
                Url::create(route('home'))
                    ->setPriority(1.0)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            )
            ->add(
                Url::create(route('blog.index'))
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            )
            ->add(
                Url::create(route('products.index'))
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            )
            ->add(
                Url::create(route('contact.show'))
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            )
            ->add(
                Url::create(route('about.show'))
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            );
            

        Page::query()
            ->published()
            ->where('slug', '!=', 'home')
            ->orderBy('updated_at')
            ->each(function (Page $page) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('page.show', $page))
                        ->setLastModificationDate($page->updated_at)
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                );
            });

        Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->each(function (Post $post) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('blog.show', $post))
                        ->setLastModificationDate($post->updated_at)
                        ->setPriority(0.6)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );
            });

        Product::query()
            ->published()
            ->visibleOnSite()
            ->orderBy('position')
            ->each(function (Product $product) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('products.show', $product))
                        ->setLastModificationDate($product->updated_at)
                        ->setPriority(0.8)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                );
            });

        $sitemap->writeToFile($path);

        $this->info("Sitemap generated: {$path}");

        return self::SUCCESS;
    }
}
