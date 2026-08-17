<?php

namespace App\Services\Seo\Schemas;

use App\Models\Product;

class ProductSchema implements SchemaInterface
{
    public function __construct(
        protected Product $product,
        protected array $breadcrumbs = [],
        private array $reviews = [],
    ) {}

    public function generate(): array
    {
        $description = $this->product->seo_description
            ?: ($this->product->short_description ?: strip_tags($this->product->description ?? ''));

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $this->product->title,
            'description' => $description,
            'url' => route('products.show', $this->product),
            'sku' => $this->product->sku,
        ];

        // Image
        $images = [];
        if ($this->product->featured_image_path) {
            $images[] = asset('storage/' . $this->product->featured_image_path);
        }
        
        // Gallery images
        if (!empty($this->product->gallery) && is_array($this->product->gallery)) {
            foreach ($this->product->gallery as $galleryImage) {
                $imageUrl = asset('storage/' . $galleryImage);
                // Featured image ile aynı değilse ekle
                if (!in_array($imageUrl, $images)) {
                    $images[] = $imageUrl;
                }
            }
        }
        
        if (!empty($images)) {
            // Tek resim varsa string, birden fazla varsa array
            $schema['image'] = count($images) === 1 ? $images[0] : $images;
        }

        // Offer
        if ($this->product->isOnlineAvailable()) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => $this->product->online_price ?: $this->product->store_price,
                'priceCurrency' => 'TRY',
                'availability' => $this->product->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => route('products.show', $this->product),
            ];
        }

        // Breadcrumbs
        if (!empty($this->breadcrumbs)) {
            $schema['breadcrumb'] = SharedSchemas::breadcrumb($this->breadcrumbs);
        }

        // Reviews
        if (!empty($this->reviews)) {
            $reviewCount = count($this->reviews);
            $ratingValue = $reviewCount > 0 
                ? round(array_sum(array_column($this->reviews, 'rating')) / $reviewCount, 2) 
                : null;
            
            if ($ratingValue !== null) {
                $schema['aggregateRating'] = [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $ratingValue,
                    'reviewCount' => $reviewCount,
                    'bestRating' => 5,
                    'worstRating' => 1,
                ];
            }

            $schema['review'] = array_map(function ($review) {
                return [
                    '@type' => 'Review',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $review['author'] ?? $review->author ?? 'Anonim',
                    ],
                    'datePublished' => $review['date'] 
                        ?? (isset($review->created_at) ? $review->created_at->toDateString() : null)
                        ?? now()->toDateString(),
                    'reviewBody' => $review['body'] ?? $review->body ?? '',
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $review['rating'] ?? $review->rating ?? 5,
                        'bestRating' => 5,
                        'worstRating' => 1,
                    ],
                ];
            }, $this->reviews);
        }

        return $schema;
    }
}
