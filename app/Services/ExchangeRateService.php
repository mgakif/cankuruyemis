<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    protected string $url = 'https://www.altinkaynak.com/Doviz/Kur/Guncel';

    /**
     * Altınkaynak sitesinden döviz kurlarını çeker
     */
    public function fetchRates(): array
    {
        try {
            $response = Http::timeout(30)->get($this->url);

            if (!$response->successful()) {
                throw new \Exception("HTTP request failed with status: {$response->status()}");
            }

            $html = $response->body();
            return $this->parseRates($html);
        } catch (\Exception $e) {
            Log::error('Exchange rate fetch failed', [
                'error' => $e->getMessage(),
                'url' => $this->url,
            ]);
            throw $e;
        }
    }

    /**
     * HTML'den döviz kurlarını parse eder
     */
    protected function parseRates(string $html): array
    {
        $rates = [];
        $currencyMap = [
            'USD' => ['name' => 'Amerikan Doları', 'code' => 'USD'],
            'EUR' => ['name' => 'Avrupa Para Birimi', 'code' => 'EUR'],
            'SAR' => ['name' => 'S. Arabistan Riyali', 'code' => 'SAR'],
        ];

        // DOMDocument kullanarak HTML'i parse et
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new \DOMXPath($dom);

        // Tablo satırlarını bul
        $rows = $xpath->query('//table//tr');

        foreach ($rows as $row) {
            $rowText = $row->textContent;
            
            // Her para birimi için kontrol et
            foreach ($currencyMap as $code => $currency) {
                if (stripos($rowText, $currency['name']) !== false || 
                    stripos($rowText, $code) !== false) {
                    
                    // Bu satırdaki tüm <td> elementlerini al
                    $cells = $xpath->query('.//td', $row);
                    $values = [];
                    
                    foreach ($cells as $cell) {
                        $text = trim($cell->textContent);
                        // Sayısal değerleri bul (virgül veya nokta ile)
                        if (preg_match('/^[\d,\.]+$/', $text)) {
                            $value = (float) str_replace(',', '.', $text);
                            if ($value > 0) {
                                $values[] = $value;
                            }
                        }
                    }

                    // Genellikle ilk değer alış, ikinci değer satış
                    // Ama bazen sadece satış değeri olabilir
                    if (count($values) >= 2) {
                        $buyRate = $values[0];
                        $sellRate = $values[1];
                    } elseif (count($values) === 1) {
                        // Sadece bir değer varsa, bu satış fiyatı olabilir
                        $buyRate = null;
                        $sellRate = $values[0];
                    } else {
                        continue;
                    }

                    if ($sellRate && $sellRate > 0) {
                        $rates[$code] = [
                            'currency_code' => $code,
                            'currency_name' => $currency['name'],
                            'buy_rate' => $buyRate,
                            'sell_rate' => $sellRate,
                        ];
                    }
                    
                    break; // Bu para birimi bulundu, diğerlerine bakma
                }
            }
        }

        libxml_clear_errors();
        return $rates;
    }

    /**
     * Döviz kurlarını veritabanına kaydeder
     * Aynı tarih ve para birimi için mevcut kaydı günceller, yeni kayıt eklemez
     */
    public function saveRates(array $rates): void
    {
        $today = now()->toDateString();
        $fetchedAt = now();

        foreach ($rates as $rate) {
            // updateOrCreate kullanarak: varsa güncelle, yoksa oluştur
            ExchangeRate::updateOrCreate(
                [
                    'currency_code' => $rate['currency_code'],
                    'rate_date' => $today,
                ],
                [
                    'currency_name' => $rate['currency_name'],
                    'buy_rate' => $rate['buy_rate'] ?? null,
                    'sell_rate' => $rate['sell_rate'],
                    'fetched_at' => $fetchedAt,
                ]
            );
        }

        // Cache'i temizle
        ExchangeRate::clearCache();
    }

    /**
     * Döviz kurlarını çekip kaydeder
     */
    public function fetchAndSave(): bool
    {
        try {
            $rates = $this->fetchRates();
            
            if (empty($rates)) {
                Log::warning('No exchange rates found', ['url' => $this->url]);
                return false;
            }

            $this->saveRates($rates);
            
            Log::info('Exchange rates fetched and saved', [
                'currencies' => array_keys($rates),
                'count' => count($rates),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to fetch and save exchange rates', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
