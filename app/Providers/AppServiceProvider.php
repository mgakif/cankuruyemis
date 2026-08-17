<?php

namespace App\Providers;

use App\ViewComposers\FaqSchemaComposer;
use App\ViewComposers\GlobalSeoComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Global SEO schema'ları tüm view'lara ekle
        View::composer('*', GlobalSeoComposer::class);
        
        // FAQ Schema'yı tüm view'lara ekle
        View::composer('*', FaqSchemaComposer::class);
    }
}
