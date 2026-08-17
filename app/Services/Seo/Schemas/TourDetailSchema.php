<?php

namespace App\Services\Seo\Schemas;

use App\Models\Tour;

class TourDetailSchema implements SchemaInterface
{
    public function __construct(protected Tour $tour) {}

    public function generate(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $this->tour->title,
            'description' => $this->tour->meta_description ?? strip_tags($this->tour->description),
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'TRY',
                'price' => $this->tour->price,
                'url' => route('tour.show', $this->tour->slug),
                'availability' => 'https://schema.org/InStock',
                'validFrom' => now()->toAtomString(),
            ],
        ];
    }
}
