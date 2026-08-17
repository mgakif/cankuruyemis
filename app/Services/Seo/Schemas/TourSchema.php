<?php

namespace App\Services\Seo\Schemas;

use App\Models\Tour;

class TourSchema implements SchemaInterface
{
    public function __construct(
        protected Tour $tour,
        protected array $breadcrumbs = [],
        private array $reviews = [],
    ) {}

    public function generate(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $this->tour->name,
            'description' => $this->tour->meta_description ?? $this->tour->generateMetaFromDetails(),
            'url' => route('tour.single', [$this->tour->category->slug, $this->tour->slug]),
            'image' => $this->tour->image ? asset('storage/'.$this->tour->image) : null,
            'offers' => [
                '@type' => 'Offer',
                'price' => $this->tour->single_price,
                'priceCurrency' => $this->tour->currency,
                'availability' => 'https://schema.org/InStock',
                'url' => route('tour.single', [$this->tour->category->slug, $this->tour->slug]),
                'validFrom' => $this->tour->start_date->subDays(300)->toAtomString() ?? now()->toAtomString(),
                'validThrough' => $this->tour->end_date->subDays(1)->toAtomString(),
                'priceValidUntil' => $this->tour->end_date->toAtomString(),
                'hasMerchantReturnPolicy' => [
                    '@type' => 'MerchantReturnPolicy',
                    'applicableCountry' => 'TR',
                    'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                    'merchantReturnDays' => 7,
                    'returnMethod' => 'https://schema.org/ReturnByMail',
                    'returnFees' => 'https://schema.org/ReturnShippingFees',
                    'returnShippingFeesAmount' => [
                        '@type' => 'MonetaryAmount',
                        'value' => 1,
                        'currency' => 'USD',
                    ],
                ],
                'shippingDetails' => [
                    '@type' => 'OfferShippingDetails',
                    'shippingRate' => [
                        '@type' => 'MonetaryAmount',
                        'value' => 1,
                        'currency' => 'USD',
                    ],
                    'shippingDestination' => [
                        '@type' => 'DefinedRegion',
                        'addressCountry' => 'TR',
                    ],
                    'deliveryTime' => [
                        '@type' => 'ShippingDeliveryTime',
                        'handlingTime' => [
                            '@type' => 'QuantitativeValue',
                            'minValue' => 0,
                            'maxValue' => 1,
                            'unitCode' => 'DAY',
                        ],
                        'transitTime' => [
                            '@type' => 'QuantitativeValue',
                            'minValue' => 1,
                            'maxValue' => 2,
                            'unitCode' => 'DAY',
                        ],
                    ],
                ],
            ],
        ];

        if (! empty($this->breadcrumbs)) {
            $schema['breadcrumb'] = SharedSchemas::breadcrumb($this->breadcrumbs);
        }

        if (! empty($this->reviews)) {
            $reviewCount = count($this->reviews);
            $ratingValue = $reviewCount > 0 ? round(array_sum(array_column($this->reviews, 'rating')) / $reviewCount, 2) : null;
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $ratingValue,
                'reviewCount' => $reviewCount,
            ];
            $schema['review'] = array_map(function ($review) {
                return [
                    '@type' => 'Review',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $review['author'] ?? $review->name ?? 'Anonim',
                    ],
                    'datePublished' => $review['date'] ?? optional($review->created_at)->toDateString() ?? now()->toDateString(),
                    'reviewBody' => $review['body'] ?? $review->body ?? '',
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $review['rating'] ?? $review->rating,
                    ],
                ];
            }, $this->reviews);
        }

        return $schema;
    }
}
