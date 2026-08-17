<?php

namespace App\Services\Seo\Schemas;

use Illuminate\Support\Collection;

class ReviewSchema implements SchemaInterface
{
    public function __construct(
        protected string $type,               // Product, Article, Organization vs.
        protected string $name,               // İçerik adı
        protected string $url,                // İçerik URL'si
        protected ?string $image = null,      // Opsiyonel görsel
        protected ?float $ratingValue = null, // Ortalama puan
        protected ?int $reviewCount = null,   // Yorum sayısı
        protected Collection $reviews = new Collection
    ) {}

    public function generate(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $this->type,
            'name' => $this->name,
            'url' => $this->url,
        ];

        if ($this->image) {
            $schema['image'] = $this->image;
        }

        if ($this->ratingValue !== null && $this->reviewCount !== null) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $this->ratingValue,
                'reviewCount' => $this->reviewCount,
            ];
        }

        if ($this->reviews->isNotEmpty()) {
            $schema['review'] = $this->reviews->map(function ($review) {
                return [
                    '@type' => 'Review',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $review['author'] ?? 'Anonim',
                    ],
                    'datePublished' => $review['date'] ?? now()->toDateString(),
                    'reviewBody' => $review['body'] ?? '',
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $review['rating'] ?? 5,
                        'bestRating' => 5,
                    ],
                ];
            })->all();
        }

        return $schema;
    }
}
