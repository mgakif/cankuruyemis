<?php

namespace App\Services\Seo\Schemas;

class HowToSchema implements SchemaInterface
{
    /**
     * @param  array<int, array{title: string, text: string, image?: string}>  $steps
     */
    public function __construct(public array $steps, public ?string $name = null, public ?string $description = null) {}

    public function generate(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $this->name,
            'description' => $this->description,
            'step' => collect($this->steps)->map(function ($step) {
                $data = [
                    '@type' => 'HowToStep',
                    'name' => $step['title'],
                    'text' => $step['text'],
                ];
                if (! empty($step['image'])) {
                    $data['image'] = $step['image'];
                }

                return $data;
            })->all(),
        ];
    }
}
