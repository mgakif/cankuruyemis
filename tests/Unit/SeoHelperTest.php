<?php

namespace Tests\Unit;

use App\Services\Seo\SeoHelper;
use PHPUnit\Framework\TestCase;

class SeoHelperTest extends TestCase
{
    public function test_it_does_not_overwrite_page_seo_values_with_empty_defaults(): void
    {
        $pageSeo = [
            'canonical' => 'https://cankuruyemismersin.com/urun/antep-fistigi',
            'robots' => 'index,follow',
        ];

        $seo = array_merge($pageSeo, SeoHelper::vars([
            'breadcrumbs' => '<script type="application/ld+json">{}</script>',
        ]));

        $this->assertSame($pageSeo['canonical'], $seo['canonical']);
        $this->assertSame($pageSeo['robots'], $seo['robots']);
    }

    public function test_it_keeps_explicit_canonical_and_robots_values(): void
    {
        $seo = SeoHelper::vars([
            'canonical' => 'https://cankuruyemismersin.com/iletisim',
            'robots' => 'noindex,nofollow',
        ]);

        $this->assertSame('https://cankuruyemismersin.com/iletisim', $seo['canonical']);
        $this->assertSame('noindex,nofollow', $seo['robots']);
    }
}
