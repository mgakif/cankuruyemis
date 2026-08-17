<?php

namespace App\Services\Seo;

class MetaTagService
{
    public function generate(object $data): string
    {
        $tags = [];

        // Title
        if (! empty($data->meta_title)) {
            $tags[] = '<title>'.e($data->meta_title).'</title>';
        }

        // Meta Description
        if (! empty($data->meta_description)) {
            $tags[] = '<meta name="description" content="'.e($data->meta_description).'">';
        }

        // Meta Image (optional, useful for Twitter and OG)
        if (! empty($data->cover_url)) {
            $tags[] = '<meta name="image" content="'.e($data->cover_url).'">';
        }

        // Open Graph (Facebook, WhatsApp, LinkedIn)
        if (! empty($data->meta_title)) {
            $ogType = $data->og_type ?? 'website';
            $tags[] = '<meta property="og:type" content="'.e($ogType).'">';
            $tags[] = '<meta property="og:title" content="'.e($data->meta_title).'">';
        }

        if (! empty($data->meta_description)) {
            $tags[] = '<meta property="og:description" content="'.e($data->meta_description).'">';
        }

        if (! empty($data->cover_url)) {
            $tags[] = '<meta property="og:image" content="'.e($data->cover_url).'">';
        }

        if (! empty($data->url)) {
            $tags[] = '<meta property="og:url" content="'.e($data->url).'">';
        }

        $siteName = function_exists('setting') && setting('title') ? setting('title') : config('app.name');
        $tags[] = '<meta property="og:site_name" content="'.e($siteName).'">';

        // Twitter Card
        $twitterCard = ! empty($data->cover_url) ? 'summary_large_image' : 'summary';
        $tags[] = '<meta name="twitter:card" content="'.$twitterCard.'">';

        if (! empty($data->meta_title)) {
            $tags[] = '<meta name="twitter:title" content="'.e($data->meta_title).'">';
        }

        if (! empty($data->meta_description)) {
            $tags[] = '<meta name="twitter:description" content="'.e($data->meta_description).'">';
        }

        if (! empty($data->cover_url)) {
            $tags[] = '<meta name="twitter:image" content="'.e($data->cover_url).'">';
        }

        if (! empty(config('seo.twitter_username'))) {
            $tags[] = '<meta name="twitter:site" content="'.e(config('seo.twitter_username')).'">';
        }

        return implode("\n", $tags);
    }
}

