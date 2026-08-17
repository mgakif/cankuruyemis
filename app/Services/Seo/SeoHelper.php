<?php

namespace App\Services\Seo;

use App\Services\Seo\Schemas\OrganizationSchema;
use App\Services\Seo\Schemas\SitelinksSearchboxSchema;

class SeoHelper
{
    /**
     * SEO ortak değişkenlerini döndürür
     */
    public static function vars(array $params = []): array
    {
        $schemaFaq = $params['schemaFaq'] ?? '';
        $schemaItemListJson = $params['schemaItemListJson'] ?? '';
        $schemaTourList = $params['schemaTourList'] ?? '';
        $schemaCollectionPage = $params['schemaCollectionPage'] ?? '';
        $schema = $params['schema'] ?? '';
        $breadCrumbSchema = $params['breadcrumbs'] ?? '';
        $schemaOrg = $params['schemaOrg'] ?? '';
        $canonical = $params['canonical'] ?? '';
        $robots = $params['robots'] ?? '';
        $schemaSitelinks = $params['schemaSitelinks'] ?? '';
        $travelAgencySchema = $params['travelAgencySchema'] ?? '';

        return compact(
            'schemaFaq',
            'schemaItemListJson',
            'schemaCollectionPage',
            'schema',
            'schemaTourList',
            'breadCrumbSchema',
            'schemaOrg',
            'canonical',
            'robots',
            'schemaSitelinks',
            'travelAgencySchema'
        );
    }

    /**
     * Sadece ortak (global) SEO schema ve meta tag'leri döndürür
     */
    public static function globalVars(): array
    {
        $logo = setting('logo') ? asset(setting('logo')) : null;
        $orgName = setting('title') ?: config('app.name');
        $orgUrl = url('/');
        $orgTel = setting('telefon');
        $orgAddressSchema = null;
        
        if (setting('adres')) {
            $orgAddressSchema = [
                '@type' => 'PostalAddress',
                'streetAddress' => setting('adres'),
            ];
        }

        $schemaOrg = '';
        if ($orgName && $orgUrl) {
            $schemaOrg = SchemaBuilder::make()->setSchema(new OrganizationSchema(
                $orgName, 
                $logo ?: asset('logo.png'), 
                $orgUrl, 
                $orgTel, 
                $orgAddressSchema
            ))->render();
        }

        $canonical = CanonicalService::tag();
        
        $robots = RobotsService::meta(self::robotsContent('index, follow'));
        
        $schemaSitelinks = SchemaBuilder::make()->setSchema(new SitelinksSearchboxSchema)->render();
        
        // TravelAgencySchema sadece gerekli alanlar varsa oluştur
        // Not: Bu projede TravelAgency yerine Organization kullanılıyor, bu yüzden boş bırakıyoruz
        $travelAgencySchema = '';

        return compact('schemaOrg', 'canonical', 'robots', 'schemaSitelinks', 'travelAgencySchema');
    }

    public static function robotsContent(string $default = 'index, follow'): string
    {
        return self::searchEngineIndexingEnabled() ? $default : 'noindex, nofollow';
    }

    public static function searchEngineIndexingEnabled(): bool
    {
        $value = setting('arama-motorlarina-acik');

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        $normalized = mb_strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'on', 'yes', 'evet'], true);
    }

    public static function meta($mtitle, $description, $image)
    {
        return (new MetaTagService)->generate((object) [
            'meta_title' => $mtitle,
            'meta_description' => $description,
            'cover_url' => $image ? asset($image) : '',
            'title' => $mtitle,
            'description' => $description,
        ]);
    }
}
