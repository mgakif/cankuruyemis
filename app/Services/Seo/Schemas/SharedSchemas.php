<?php

namespace App\Services\Seo\Schemas;

class SharedSchemas
{
    public static function organization(): array
    {
        return [
            '@type' => 'Organization',
            'name' => setting('app-name'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset(setting('logo')),
            ],
        ];
    }

    public static function breadcrumb(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(function ($item, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['title'],
                    'item' => $item['url'],
                ];
            }, $items, array_keys($items)),
        ];
    }

    public static function reviews(array $reviews, ?float $ratingValue = null, ?int $reviewCount = null): array
    {
        $schema = [];
        if ($ratingValue !== null && $reviewCount !== null) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $ratingValue,
                'reviewCount' => $reviewCount,
            ];
        }
        $schema['review'] = array_map(function ($review) {
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
        }, $reviews);

        return $schema;
    }
}
