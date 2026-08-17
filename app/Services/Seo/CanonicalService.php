<?php

namespace App\Services\Seo;

class CanonicalService
{
    public static function tag(?string $url = null): string
    {
        $canonical = $url ?? url()->current();

        return '<link rel="canonical" href="'.e($canonical).'">';
    }
}
