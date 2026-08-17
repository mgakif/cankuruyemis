<?php

namespace App\Services\Seo\Schemas;

use App\Models\Article;

class StaticPageSchema implements SchemaInterface
{
    public function __construct(protected Article $article, protected ?array $breadcrumbs = null) {}

    public function generate(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $this->article->title,
            'description' => strip_tags($this->article->ozet ?? ''),
            'url' => url()->current(),
        ];

        if (! empty($this->breadcrumbs)) {
            $schema['breadcrumb'] = SharedSchemas::breadcrumb($this->breadcrumbs);
        }

        return $schema;
    }
}
