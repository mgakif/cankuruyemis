<?php

namespace App\Services\Seo\Schemas;

use App\Models\Hotel;

class HotelSchema implements SchemaInterface
{
    public function __construct(protected Hotel $hotel) {}

    public function generate(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LodgingBusiness',
            'name' => $this->hotel->name,
            'description' => strip_tags($this->hotel->description),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->hotel->address,
                'addressLocality' => $this->hotel->city,
                'addressCountry' => 'SA',
            ],
            'url' => route('hotel.single', $this->hotel->slug),
        ];
    }
}
