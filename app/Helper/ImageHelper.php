<?php

namespace App\Helper;

use Exception;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageHelper
{
    protected $uploadDir;

    protected $filename;

    protected $width = null;

    protected $height = null;

    protected $webp = true;

    protected $quality = 80;

    protected $mode = 'resize'; // 'resize', 'crop', 'pad', 'blurpad'

    protected $padColor = 'ffffff';

    protected $outputFilename = null;

    protected $cacheEnabled = true;

    protected $overwrite = false;

    protected $manager;

    protected $customFileName;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    public function setCustomFileName(?string $customFileName = null)
    {
        $this->customFileName = $customFileName;

        return $this;
    }

    public function setUploadDir(string $dir): self
    {
        $this->uploadDir = rtrim($dir, '/').'/';

        return $this;
    }

    public function setFilename($filename): self
    {
        // Tam URL'den path extract et (http:// veya https:// ile başlıyorsa)
        if (preg_match('/^https?:\/\//', $filename)) {
            // URL'den path'i çıkar: http://example.com/storage/image.jpg -> storage/image.jpg
            $parsedUrl = parse_url($filename);
            $filename = ltrim($parsedUrl['path'] ?? '', '/');
        }
        
        // asset() ile gelen path'leri düzelt (/storage/image.jpg -> storage/image.jpg)
        $this->filename = ltrim($filename, '/');

        return $this;
    }

    public function setDimensions(?int $width = null, ?int $height = null): self
    {
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function useWebp(bool $webp = true): self
    {
        $this->webp = $webp;

        return $this;
    }

    public function optimize(int $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function resizeOnly(): self
    {
        $this->mode = 'resize';

        return $this;
    }

    public function scaleDown(): self
    {
        $this->mode = 'scaledown';

        return $this;
    }

    public function resizeAndCrop(): self
    {
        $this->mode = 'crop';

        return $this;
    }

    public function resizeAndPad(string $color = 'ffffff'): self
    {
        $this->mode = 'pad';
        $this->padColor = strtolower($color);

        return $this;
    }

    public function blurAndPad(): self
    {
        $this->mode = 'blurpad';

        return $this;
    }

    public function shouldBlur(): self
    {
        $sourcePath = public_path($this->filename);

        if (! File::exists($sourcePath)) {
            return $this;
        }

        try {
            $image = $this->manager->read($sourcePath);
            $originalWidth = $image->width();
            $originalHeight = $image->height();

            if (! $this->width || ! $this->height) {
                $this->mode = 'resize';

                return $this;
            }

            // Oranları hesapla
            $sourceRatio = $originalWidth / $originalHeight;
            $targetRatio = $this->width / $this->height;

            // Oranlar birbirine yaklaşık mı? (küçük sapmalara izin verelim)
            if (abs($sourceRatio - $targetRatio) < 0.01) {
                $this->mode = 'resize';
            } else {
                $this->mode = 'blurpad';
            }

        } catch (\Exception $e) {
            // Loglama yapılabilir
            $this->mode = 'resize'; // güvenli varsayılan
        }

        return $this;
    }

    public function setOutputFilename(string $name): self
    {
        $this->outputFilename = $name;

        return $this;
    }

    public function enableCache(bool $state = true): self
    {
        $this->cacheEnabled = $state;

        return $this;
    }

    public function overwrite(bool $state = true): self
    {
        $this->overwrite = $state;

        return $this;
    }

    public function save(): string
    {
        // Boş filename kontrolü
        if (empty($this->filename) || ($this->filename == 'storage/')) {
            $defaultImage = setting('default-image');
            if ($defaultImage) {
                $defaultImage = ltrim($defaultImage, '/');
                if (File::exists(public_path($defaultImage))) {
                    $this->filename = $defaultImage;
                } else {
                    return ''; // Return an empty string if default image also doesn't exist
                }
            } else {
                return '';
            }
        }
        
        // Dosya kontrolü
        if (! File::exists(public_path($this->filename))) {
            // Resim bulunamazsa settings'teki default-image'i kullan
            $defaultImage = setting('default-image');
            if ($defaultImage) {
                $defaultImage = ltrim($defaultImage, '/');
                if (File::exists(public_path($defaultImage))) {
                    $this->filename = $defaultImage;
                } else {
                    return ''; // Return an empty string if default image also doesn't exist
                }
            } else {
                return '';
            }
        }

        $thumbPath = 'th_'.($this->width ?? 'auto').'_'.($this->height ?? 'auto').'/';
        $_file = $this->customFileName
            ?? ($this->webp
                ? pathinfo($this->filename, PATHINFO_FILENAME).'.webp'
                : basename($this->filename));

        $targetDir = public_path($this->uploadDir.$thumbPath);
        $targetFile = $targetDir.$_file;

        $alreadyExists = File::exists($this->uploadDir.$thumbPath.$_file);

        if ($alreadyExists && ! $this->overwrite && $this->cacheEnabled) {
            return asset($this->uploadDir.$thumbPath.$_file);
        }

        if (! file_exists($targetDir)) {
            try {
                File::makeDirectory($targetDir, 0775, true);
            } catch (Exception $e) {
                if ($e->getMessage() != 'mkdir(): File exists') {
                    echo 'Error creating directory: '.$e->getMessage();
                }
            }
        }

        // Dosya mevcut klasörde yoksa default-image'i kontrol et
        $sourcePath = null;
        if (File::exists(public_path($this->filename))) {
            $sourcePath = public_path($this->filename);
        } elseif (File::exists($this->filename)) {
            $sourcePath = $this->filename;
        } else {
            // Dosya bulunamadıysa default-image'i kullan
            $defaultImage = setting('default-image');
            if ($defaultImage) {
                $defaultImage = ltrim($defaultImage, '/');
                if (File::exists(public_path($defaultImage))) {
                    $sourcePath = public_path($defaultImage);
                } elseif (File::exists($defaultImage)) {
                    $sourcePath = $defaultImage;
                }
            }
        }

        if (
            $sourcePath &&
            File::exists($sourcePath) &&
            ! File::isDirectory($sourcePath)
        ) {
            copy($sourcePath, $targetFile);

            $img = $this->manager->read($targetFile);

            if ($this->webp) {
                $img->toWebp($this->quality);
            }

            switch ($this->mode) {
                case 'crop':
                    $img->cover($this->width ?? 1000, $this->height ?? 1000);
                    break;

                case 'pad':
                    $resizeColor = $this->padColor === 'transparent' ? '00000000' : $this->padColor;
                    $img->resize($this->width, $this->height, function ($c) {
                        $c->aspectRatio();
                        $c->upsize();
                    })->resizeCanvas(
                        $this->width ?? 1000,
                        $this->height ?? 1000,
                        'center',
                        false,
                        $resizeColor
                    );
                    break;

                case 'blurpad':
                    $background = clone $img;

                    $background->resize($this->width, $this->height, function ($c) {
                        $c->aspectRatio();
                    })->blur(100); // bulanıklık seviyesi ayarlanabilir

                    $foreground = clone $img;
                    $foreground->scaledown($this->width, $this->height, function ($c) {
                        $c->aspectRatio();
                        $c->upsize();
                    });

                    $background->place($foreground, 'center');

                    $img = $background;
                    break;

                case 'resize':
                default:
                    $img->resize($this->width, $this->height, function ($c) {
                        $c->aspectRatio();
                        $c->upsize();
                    });
                    break;

                case 'scaledown':
                    $img->scaleDown($this->width, $this->height);
                    break;
            }

            $img->save(null, $this->quality);
        } else {
            // Dosya ve default-image de bulunamazsa boş string döndür
            return '';
        }

        return asset($this->uploadDir.$thumbPath.$_file);
    }
}
