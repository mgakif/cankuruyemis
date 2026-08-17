<?php

namespace App\Services\Seo\Schemas;

class TourListSchema implements SchemaInterface
{
    public function __construct(public array $tours) {}

    public function generate(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => collect($this->tours)->map(function ($tour, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => route('tour.single', [$tour->category->slug, $tour->slug]),
                ];
            })->all(),
        ];
    }
}
