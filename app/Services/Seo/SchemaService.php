<?php

namespace App\Services\Seo;

use Illuminate\Support\Collection;

class SchemaService
{
    public function generateItemListSchema(Collection $items, ?callable $mapCallback = null): string
    {
        $itemList = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $items->values()->map(function ($item, $index) use ($mapCallback) {
                $listItem = [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => route('route', $item->slug),
                ];

                if ($mapCallback) {
                    $customData = $mapCallback($item);
                    $listItem = array_merge($listItem, $customData);
                }

                return $listItem;
            })->toArray(),
        ];

        return json_encode($itemList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    }
}
