<?php

namespace App\Services\Seo;

class HreflangService
{
    /**
     * @param  array<string, string>  $locales  ['tr' => 'https://site.com/tr/page', 'en' => 'https://site.com/en/page']
     */
    public static function tags(array $locales): string
    {
        $tags = [];
        foreach ($locales as $locale => $url) {
            $tags[] = '<link rel="alternate" hreflang="'.e($locale).'" href="'.e($url).'">';
        }

        return implode("\n", $tags);
    }
}
