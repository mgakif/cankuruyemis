<?php

namespace App\Services;

use App\Contracts\MetaGeneratable;
use Illuminate\Support\Facades\URL;

class MetaService
{
    public static function for(MetaGeneratable $model): void
    {

        $title = $model->generateMetaTitle() ?? setting('title');
        $description = $model->generateMetaDescription() ?? setting('description');
        $image = $model->generateMetaImage() ?? setting('ana-sayfa-resmi');

        app(self::class)->set(compact('title', 'description', 'image'));
    }

    public function set(array $data): void
    {
        $description = html_entity_decode($data['description'] ?? setting('description'));
        $title = html_entity_decode($data['title'] ?? setting('title'));
        $image = $data['image'] ?? setting('ana-sayfa-resmi');

        meta()
            ->set('title', $title)
            ->set('description', $description)
            ->set('og:type', 'website')
            ->set('og:title', $title)
            ->set('og:description', $description)
            ->set('og:image', asset($image))
            ->set('og:image:width', '1600')
            ->set('og:image:height', '630')
            ->set('og:locale', 'tr_TR')
            ->set('og:url', URL::full());
    }
}
