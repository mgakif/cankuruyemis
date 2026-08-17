<?php

namespace App\Services\Seo\Schemas;

class TravelAgencySchema implements SchemaInterface
{
    public function __construct(
        public string $name,
        public string $imageUrl,
        public int $imageWidth,
        public int $imageHeight,
        public string $telephone,
        public string $url,
        public array $address,
        public ?string $priceRange = null,
        public array $openingHoursSpecification = [],
        public ?array $geo = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
    ) {}

    public function generate(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'TravelAgency',
            'name' => $this->name,
            'image' => [
                '@type' => 'ImageObject',
                'url' => $this->imageUrl,
                'width' => $this->imageWidth,
                'height' => $this->imageHeight,
            ],
            'telephone' => $this->telephone,
            'url' => $this->url,
            'address' => array_merge(['@type' => 'PostalAddress'], $this->address),
            'priceRange' => $this->priceRange,
            'openingHoursSpecification' => $this->openingHoursSpecification,
        ];

        if ($this->geo) {
            $data['geo'] = array_merge([
                '@type' => 'GeoCoordinates',
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ], $this->geo);
        }

        return $data;
    }
}
