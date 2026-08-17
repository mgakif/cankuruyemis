<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $path = public_path('sitemap.xml');

        // Local ortamda sitemap yoksa oluştur
        if (! file_exists($path) && app()->environment('local')) {
            Artisan::call('app:generate-sitemap');
        }

        abort_unless(file_exists($path), 404);

        return response()->file($path, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function regenerate(): RedirectResponse
    {
        Artisan::call('app:generate-sitemap');

        return redirect()->back()->with('success', 'Sitemap başarıyla yeniden oluşturuldu.');
    }
}
