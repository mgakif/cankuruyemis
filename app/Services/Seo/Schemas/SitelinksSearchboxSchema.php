<?php

namespace App\Services\Seo\Schemas;

class SitelinksSearchboxSchema implements SchemaInterface
{
    public function generate(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/arama?q={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }
}
