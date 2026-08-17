<?php

namespace App\Services\Seo\Schemas;

use App\Models\Category;

class TourCategorySchema implements SchemaInterface
{
    public function __construct(protected Category $category) {}

    public function generate(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $this->category->name,
            'description' => $this->category->description,
            'url' => route('category.show', $this->category->slug),
        ];
    }
}
