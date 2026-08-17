<?php

namespace App\Services\Seo\Schemas;

class CollectionPageSchema implements SchemaInterface
{
    public function __construct(
        protected string $name,
        protected string $description,
        protected string $url,
        protected array $breadcrumbs = [],

    ) {}

    public function generate(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->url,
        ];

        if (! empty($this->breadcrumbs)) {
            $schema['breadcrumb'] = SharedSchemas::breadcrumb($this->breadcrumbs);
        }

        return $schema;
    }
}
