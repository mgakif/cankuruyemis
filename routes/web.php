<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormPageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\Coupons\CouponController;
use App\Http\Controllers\Coupons\TelegramCouponController;
use App\Services\Seo\SeoHelper;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/kaydet', [HomeController::class, 'cacheClear'])->name('cacheClear');
Route::get('/sitemap-regenerate', [SitemapController::class, 'regenerate'])->name('sitemap.regenerate');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/urunlerimiz', [ProductController::class, 'index'])->name('products.index');
Route::get('/urun/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/iletisim', [ContactController::class, 'show'])->name('contact.show');
Route::get('/hakkimizda', [PageController::class, 'hakkimizda'])->name('about.show');

Route::post('/form/{slug}/submit', [FormController::class, 'submit'])->name('form.submit');

Route::post('/telegram/coupons/drink', [TelegramCouponController::class, 'storeDrink'])
    ->name('telegram.coupons.drink');
Route::post('/telegram/coupons/redeem', [TelegramCouponController::class, 'redeem'])
    ->name('telegram.coupons.redeem');

Route::get('/coupon/{token}', [CouponController::class, 'show'])->name('coupons.show');
Route::get('/coupon-code/{code}', [CouponController::class, 'showByCode'])->name('coupons.code');
Route::post('/coupon/{token}/use', [CouponController::class, 'use'])
    ->middleware('auth')
    ->name('coupons.use');
Route::post('/coupon/{token}/override-use', [CouponController::class, 'overrideUse'])
    ->middleware('auth')
    ->name('coupons.override-use');
Route::post('/coupon-code/{code}/use', [CouponController::class, 'useByCode'])
    ->middleware('auth')
    ->name('coupons.code.use');
Route::post('/coupon-code/{code}/override-use', [CouponController::class, 'overrideUseByCode'])
    ->middleware('auth')
    ->name('coupons.code.override-use');

Route::get('/robots.txt', function () {
    $body = SeoHelper::searchEngineIndexingEnabled()
        ? "User-agent: *\nAllow: /\nSitemap: " . url('/sitemap.xml') . "\n"
        : "User-agent: *\nDisallow: /\nSitemap: " . url('/sitemap.xml') . "\n";

    return response($body, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Form Submissions Routes (Basic auth recommended, but not required)
Route::prefix('form-submissions')->group(function () {
    // Form 10 istatistikleri
    Route::get('/form-10/stats', [FormSubmissionController::class, 'form10Stats'])
        ->name('form-submissions.form10.stats');
    
    // Form 10 CSV indir
    Route::get('/form-10/download-csv', [FormSubmissionController::class, 'downloadForm10CSV'])
        ->name('form-submissions.form10.download-csv');
    
    // Form 10 JSON dışa aktar (API)
    Route::get('/form-10/export-json', [FormSubmissionController::class, 'exportForm10Json'])
        ->name('form-submissions.form10.export-json');
    
    // Form 10 CSV dışa aktarma sayfası
    Route::get('/form-10/export', [FormSubmissionController::class, 'exportForm10'])
        ->name('form-submissions.form10.export');
    
    // Form 10 kayıtlarını listele
    Route::get('/form-10', [FormSubmissionController::class, 'showForm10'])
        ->name('form-submissions.form10');
    
    // Form 10 kayıt detayı (MUST be last to avoid catching other routes)
    Route::get('/form-10/{id}', [FormSubmissionController::class, 'showForm10Detail'])
        ->name('form-submissions.form10.detail');
});

// Form/Page catch-all - checks form first, then page (keep LAST)
Route::get('/{slug}', [FormPageController::class, 'show'])
    ->where('slug', '^(?!admin$|blog$|products$|urunlerimiz$|urun$|iletisim$|sitemap\.xml$|sitemap-regenerate$|robots\.txt$|up$|storage|kaydet|form-submissions|telegram|coupon|coupon-code).+')
    ->name('page.show');

// Note: Form/Page catch-all is handled above by FormPageController
