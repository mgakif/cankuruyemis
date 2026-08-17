<?php

namespace App\ViewComposers;

use App\Services\Seo\SeoHelper;
use Illuminate\View\View;

class GlobalSeoComposer
{
    public function compose(View $view): void
    {
        // Global SEO değişkenlerini al
        $globalSeo = SeoHelper::globalVars();
        
        // View'a ekle
        foreach ($globalSeo as $key => $value) {
            $view->with($key, $value);
        }
    }
}
