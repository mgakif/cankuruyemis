<?php

namespace App\Services\Seo;

class RobotsService
{
    /**
     * @param  string  $content  ör: 'index, follow' veya 'noindex, nofollow'
     */
    public static function meta(string $content = 'index, follow'): string
    {
        return '<meta name="robots" content="'.e($content).'">';
    }

    /**
     * X-Robots-Tag için response header
     */
    public static function header(string $content = 'index, follow')
    {
        return response()->header('X-Robots-Tag', $content);
    }
}
