<?php

namespace App\Services\Seo\Schemas;

class BreadcrumbSchema implements SchemaInterface
{
    public function __construct(public array $items) {}

    public function generate(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($this->items)->map(function ($item, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['title'],
                    'item' => $item['url'],
                ];
            })->values()->all(),
        ];
    }
}
