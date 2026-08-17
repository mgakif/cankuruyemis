<?php

namespace App\Services\Seo;

class PaginationService
{
    public static function tags(int $currentPage, int $lastPage, string $baseUrl): string
    {
        $tags = [];
        if ($currentPage > 1) {
            $prevUrl = $baseUrl.($currentPage - 1 > 1 ? '?page='.($currentPage - 1) : '');
            $tags[] = '<link rel="prev" href="'.e($prevUrl).'">';
        }
        if ($currentPage < $lastPage) {
            $nextUrl = $baseUrl.'?page='.($currentPage + 1);
            $tags[] = '<link rel="next" href="'.e($nextUrl).'">';
        }

        return implode("\n", $tags);
    }
}
