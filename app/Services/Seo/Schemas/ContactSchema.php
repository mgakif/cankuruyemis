<?php

namespace App\Services\Seo\Schemas;

class ContactSchema implements SchemaInterface
{
    public function generate(): array
    {
        $orgName = setting('title') ?: config('app.name');
        $orgUrl = url('/');
        $telephone = setting('telefon');
        
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'ContactPage',
            'url' => route('contact.show'),
        ];
        
        if ($orgName) {
            $data['mainEntity'] = [
                '@type' => 'Organization',
                'name' => $orgName,
                'legalName' => 'Zeynep Tekin',
                'url' => $orgUrl,
            ];
            
            if ($telephone) {
                $data['mainEntity']['contactPoint'] = [
                    '@type' => 'ContactPoint',
                    'telephone' => $telephone,
                    'contactType' => 'customer service',
                    'areaServed' => 'TR',
                    'availableLanguage' => 'Turkish',
                ];
            }
        }
        
        return $data;
    }
}
