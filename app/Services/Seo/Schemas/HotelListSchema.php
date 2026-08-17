<?php

namespace App\Services\Seo\Schemas;

class HotelListSchema implements SchemaInterface
{
    public array $hotels;

    public function __construct(array $hotels)
    {
        $this->hotels = $hotels;
    }

    public function generate(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => collect($this->hotels)->map(function ($hotel, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'item' => [
                        '@type' => 'Hotel',
                        'name' => $hotel->name,
                        'address' => $hotel->address ?? $hotel->city,
                        'url' => route('hotel.single', [$hotel->slug]),
                        'image' => asset('storage/'.$hotel->first_image),
                    ],
                ];
            })->all(),
        ];
    }
}
