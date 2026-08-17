<?php

namespace App\Services\Seo\Schemas;

class HomeSchema implements SchemaInterface
{
    public function generate(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name'),
            'url' => config('app.url'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => config('app.url').'/tours?arama={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }
}
