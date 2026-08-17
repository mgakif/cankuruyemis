<?php

namespace App\Services\Imports;

class ProductCatalogClassifier
{
    public function classify(string $name): array
    {
        $normalized = mb_strtoupper(trim($name));

        if ($normalized === '' || in_array($normalized, ['ARA ODEME', 'ARA ÖDEME', 'NOT'], true)) {
            return [
                'action' => 'skip',
                'sale_channel' => 'hidden',
                'category' => null,
                'subcategory' => null,
            ];
        }

        if ($this->containsAny($normalized, ['ALGIDA', 'TWISTER', 'SPOONFUL', 'HARIBO', 'KOLONYA', 'SAKIZ', 'NESCAFE'])) {
            return [
                'action' => 'import',
                'sale_channel' => 'store_only',
                'category' => 'Market Ürünleri',
                'subcategory' => 'Mağazada Bulunur',
            ];
        }

        if ($this->containsAny($normalized, ['KAHVE', 'DİBEK', 'OSMANLI'])) {
            return [
                'action' => 'import',
                'sale_channel' => 'store_only',
                'category' => 'Kahve',
                'subcategory' => 'Mağazada Bulunur',
            ];
        }

        if ($this->containsAny($normalized, ['LOKUM', 'CEZERYE'])) {
            return [
                'action' => 'import',
                'sale_channel' => 'online',
                'category' => 'Lokum ve Cezerye',
                'subcategory' => 'Lokum',
            ];
        }

        if ($this->containsAny($normalized, ['DRAJE', 'SEKER', 'ŞEKER', 'JÖLE', 'JELIBON', 'JELİBON', 'ÇİKOLATA', 'MADLEN', 'BONİBON', 'LOLİPOP', 'AKIDE'])) {
            return [
                'action' => 'import',
                'sale_channel' => 'online',
                'category' => 'Şekerleme ve Draje',
                'subcategory' => 'Draje ve Şekerleme',
            ];
        }

        if ($this->containsAny($normalized, ['UZUM', 'ÜZÜM', 'İNCIR', 'ANANAS', 'PAPAYA', 'HURMA', 'KAYISI', 'YABAN MERSİNİ', 'BLUEBERRY'])) {
            return [
                'action' => 'import',
                'sale_channel' => 'online',
                'category' => 'Kuru Meyve',
                'subcategory' => 'Kurutulmuş Meyve',
            ];
        }

        if ($this->containsAny($normalized, ['BADEM'])) {
            return [
                'action' => 'import',
                'sale_channel' => 'online',
                'category' => 'Kuruyemiş',
                'subcategory' => 'Badem',
            ];
        }

        if ($this->containsAny($normalized, ['FINDIK'])) {
            return [
                'action' => 'import',
                'sale_channel' => 'online',
                'category' => 'Kuruyemiş',
                'subcategory' => 'Fındık',
            ];
        }

        if ($this->containsAny($normalized, ['FISTIK', 'FISTIĞ', 'FISTIĞI', 'ANTEP'])) {
            return [
                'action' => 'import',
                'sale_channel' => 'online',
                'category' => 'Kuruyemiş',
                'subcategory' => 'Fıstık',
            ];
        }

        if ($this->containsAny($normalized, ['ÇEKİRDEK', 'CEKIRDEK', 'KABAK', 'DAKOTA'])) {
            return [
                'action' => 'import',
                'sale_channel' => 'online',
                'category' => 'Kuruyemiş',
                'subcategory' => 'Çekirdek',
            ];
        }

        if ($this->containsAny($normalized, ['LEBLEB'])) {
            return [
                'action' => 'import',
                'sale_channel' => 'online',
                'category' => 'Kuruyemiş',
                'subcategory' => 'Leblebi',
            ];
        }

        if ($this->containsAny($normalized, ['KAJU', 'CEVIZ', 'CEVİZ', 'MISIR', 'KARIŞIK', 'KURUYEMIŞ'])) {
            return [
                'action' => 'import',
                'sale_channel' => 'online',
                'category' => 'Kuruyemiş',
                'subcategory' => 'Karışık ve Özel',
            ];
        }

        return [
            'action' => 'import',
            'sale_channel' => 'store_only',
            'category' => 'Diğer Ürünler',
            'subcategory' => 'Manuel İnceleme',
        ];
    }

    protected function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
