<?php

namespace App\Services\Seo\Schemas;

use App\Models\Product;

class ProductListSchema implements SchemaInterface
{
    public function __construct(public $products) {}

    public function generate(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => collect($this->products)->map(function ($product, $index) {
                $item = [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'item' => [
                        '@type' => 'Product',
                        'name' => $product->title,
                        'url' => route('products.show', $product),
                    ],
                ];

                // Image varsa ekle
                if ($product->featured_image_path) {
                    $item['item']['image'] = asset('storage/' . $product->featured_image_path);
                }

                // Description varsa ekle
                $description = $product->short_description ?: strip_tags($product->description ?? '');
                if ($description) {
                    $item['item']['description'] = $description;
                }

                return $item;
            })->values()->all(),
        ];
    }
}
