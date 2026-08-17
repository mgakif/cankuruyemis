<?php

namespace App\Services\Seo\Schemas;

class FaqSchema implements SchemaInterface
{
    public function __construct(protected array $faqData) {}

    public function generate(): array
    {
        if (empty($this->faqData)) {
            return [];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['question'] ?? $item->question ?? '',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'] ?? $item->answer ?? '',
                ],
            ], $this->faqData),
        ];
    }
}
