<?php

use App\Helper\ImageHelper;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

function setting($slug, $default = null)
{
    $slugAliases = [
        'sadece-magazada-satilan-urunleri-goster' => 'magaza-urunleri-goster',
    ];

    $resolvedSlug = $slugAliases[$slug] ?? $slug;

    try {
        // return 0;
        // Cache::flush();
        $value = Cache::rememberForever('setting_'.$slug, function () use ($resolvedSlug) {
            $setting = Setting::where('slug', $resolvedSlug)->first();
            if (! $setting) {
                return null;
            }
            if ($setting->type == 'select') {
                return $setting->attributes['options'][$setting->value] ?? null;
            }
            if ($setting->type == 'file' || $setting->type == 'image') {
                return 'storage/'.$setting->value;
            }

            return $setting->value;
        });

        return $value ?? $default;
    } catch (\Exception $e) {
        // Cache yazma hatası durumunda direkt database'den oku
        try {
            $setting = Setting::where('slug', $resolvedSlug)->first();
            if (! $setting) {
                return $default;
            }
            if ($setting->type == 'select') {
                return $setting->attributes['options'][$setting->value] ?? $default;
            }
            if ($setting->type == 'file' || $setting->type == 'image') {
                return 'storage/'.$setting->value;
            }
            return $setting->value ?? $default;
        } catch (\Exception $e2) {
            return $default;
        }
    }
}

function imageresize($upload_dir, $file, $width = null, $height = null, $optimize = 70, $webp = true)
{
    // $upload_dir = 'images/tours/'; // storage dizini
    // $file = 'storage/' . $this->image; // mevcut image adını al
    // $width = 300; // opsiyonel genişlik
    // $height = 200; // opsiyonel yükseklik
    // $optimize = 70; // optimize etme oranı
    // $webp = true; // WebP kullanımı

    // Boş dosya kontrolü
    if (empty($file)) {
        return '';
    }

    // Tam URL'den path extract et (http:// veya https:// ile başlıyorsa)
    if (preg_match('/^https?:\/\//', $file)) {
        // URL'den path'i çıkar: http://example.com/storage/image.jpg -> storage/image.jpg
        $parsedUrl = parse_url($file);
        $file = ltrim($parsedUrl['path'] ?? '', '/');
    }
    
    // asset() ile gelen path'leri düzelt (/storage/image.jpg -> storage/image.jpg)
    $file = ltrim($file, '/');
    
    // Eğer dosya yoksa ve boş değilse, default-image'i kullan
    if (!\Illuminate\Support\Facades\File::exists(public_path($file))) {
        $defaultImage = setting('default-image');
        if ($defaultImage) {
            // Default image'den de URL'yi temizle
            $defaultImage = ltrim($defaultImage, '/');
            if (\Illuminate\Support\Facades\File::exists(public_path($defaultImage))) {
                $file = $defaultImage;
            }
        }
    }

    $url = (new ImageHelper)
        ->setUploadDir($upload_dir)
        ->setFilename($file)
        ->setDimensions($width, $height) // sadece genişlik verirsen yükseklik oranlı ayarlanır
        ->useWebp(true)
        ->scaleDown() // ya da ->resizeOnly() veya ->resizeAndCrop()
        ->optimize($optimize)
        ->enableCache(true)
        ->overwrite(false)
        ->save();

    return $url;
}

function image_create($upload_dir, $file, $width = null, $height = null, $optimize = 70, $webp = true)
{
    // $upload_dir = 'images/tours/'; // storage dizini
    // $file = 'storage/' . $this->image; // mevcut image adını al
    // $width = 300; // opsiyonel genişlik
    // $height = 200; // opsiyonel yükseklik
    // $optimize = 70; // optimize etme oranı
    // $webp = true; // WebP kullanımı

    // Boş dosya kontrolü
    if (empty($file)) {
        return '';
    }

    // Tam URL'den path extract et (http:// veya https:// ile başlıyorsa)
    if (preg_match('/^https?:\/\//', $file)) {
        // URL'den path'i çıkar: http://example.com/storage/image.jpg -> storage/image.jpg
        $parsedUrl = parse_url($file);
        $file = ltrim($parsedUrl['path'] ?? '', '/');
    }
    
    // asset() ile gelen path'leri düzelt (/storage/image.jpg -> storage/image.jpg)
    $file = ltrim($file, '/');
    
    // Eğer dosya yoksa ve boş değilse, default-image'i kullan
    if (!\Illuminate\Support\Facades\File::exists(public_path($file))) {
        $defaultImage = setting('default-image');
        if ($defaultImage) {
            // Default image'den de URL'yi temizle
            $defaultImage = ltrim($defaultImage, '/');
            if (\Illuminate\Support\Facades\File::exists(public_path($defaultImage))) {
                $file = $defaultImage;
            }
        }
    }

    $url = (new ImageHelper)
        ->setUploadDir($upload_dir)
        ->setFilename($file)
        ->setDimensions($width, $height) // sadece genişlik verirsen yükseklik oranlı ayarlanır
        ->useWebp(true)
        ->resizeAndCrop() // ya da ->resizeOnly() veya ->resizeAndCrop()
        ->optimize($optimize)
        ->enableCache(true)
        ->overwrite(false)
        ->save();

    return $url;
}

if (! function_exists('title_case_tr')) {
    function title_case_tr(string $text): string
    {
        return mb_convert_case(mb_strtolower($text, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}
