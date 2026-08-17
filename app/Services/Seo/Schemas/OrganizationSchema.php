<?php

namespace App\Services\Seo\Schemas;

class OrganizationSchema implements SchemaInterface
{
    public function __construct(
        public string $name = 'Uzman Turizm',
        public string $logo = '',
        public string $url = '',
        public ?string $telephone = null,
        public string|array|null $address = null
    ) {}

    public function generate(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->name,
            'url' => $this->url ?: config('app.url'),
            'logo' => $this->logo ?: asset('logo.png'),
        ];
        if ($this->telephone) {
            $data['contactPoint'] = [
                '@type' => 'ContactPoint',
                'telephone' => $this->telephone,
                'contactType' => 'customer service',
            ];
        }
        if ($this->address) {
            if (is_array($this->address)) {
                $data['address'] = $this->address;
            } else {
                $data['address'] = [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $this->address,
                ];
            }
        }

        return $data;
    }
}
