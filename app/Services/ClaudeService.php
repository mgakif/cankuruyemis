<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUri;
    protected int $timeout;
    protected int $maxTokens;

    public function __construct()
    {
        $this->apiKey = config('anthropic.api_key');
        $this->model = config('anthropic.model', 'claude-sonnet-4-5-20250929');
        $this->baseUri = config('anthropic.base_uri', 'https://api.anthropic.com/v1');
        $this->timeout = config('anthropic.request_timeout', 60);
        $this->maxTokens = config('anthropic.max_tokens', 4096);
    }

    /**
     * Generate landing page HTML and SEO data using Claude API
     */
    public function generateLandingPage(string $title, ?string $customInstructions = null, ?array $pageData = null): array
    {
        $systemPrompt = "Sen ödüllü bir UI/UX tasarımcısı ve ikna edici metin yazarı olan kıdemli bir Frontend geliştiricisisin.
Uzmanlık alanın: Tailwind CSS kullanarak 'Lüks', 'Güven veren' ve 'Ferah' tasarımlar yapmak.
Görsel hiyerarşiye, whitespace (beyaz boşluk) kullanımına ve tipografiye çok önem verirsin.";

        $userPrompt = "Konu: {$title}\n";
        
        if ($customInstructions) {
            $userPrompt .= "Özel İstekler: {$customInstructions}\n";
        }

        if ($pageData) {
            $userPrompt .= "\nMevcut Veriler:\n";
            if (isset($pageData['price'])) {
                $userPrompt .= "- Fiyat: {$pageData['price']}\n";
            }
        }

        $userPrompt .= "\n**KRİTİK TEKNİK KURALLAR:**
1. Çıktı SADECE geçerli bir JSON objesi olmalı. Başka hiçbir metin veya markdown olmamalı.
2. JSON Formatı: { \"html_code\": \"...\", \"seo_title\": \"...\", \"seo_description\": \"...\", \"keywords\": [...] }
3. HTML içinde görsel yolları yerine Blade değişkenleri kullan: {{ \$data['hero_image'] }}, {{ \$data['gallery_1'] }}.
4. İletişim bilgileri için ASLA statik metin yazma. Şu direktifleri kullan: @setting('phone'), @setting('whatsapp'), {{ asset('storage/logo.png') }}.
5. Tasarımda modern Tailwind classları kullan (örn: backdrop-blur-md, shadow-2xl, hover:scale-105, transition-all).
6. İçerik dili: Türkçe. Metinler duygusal, samimi ve satış odaklı olsun.
7. HTML çıktısı @extends içermemeli, sadece <div> ile başlayan içerik bloğu olmalı.
8. Ana tema rengi: Vişne rengi (#8B2F56) - Primary olarak kullan.
9. İkincil tema rengi: Turkuaz (#00839B) - Sadece özel vurgular için kullan.
10. Tasarım premium, lüks ve güven veren bir hissiyat yaratmalı.";

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post("{$this->baseUri}/messages", [
                    'model' => $this->model,
                    'max_tokens' => $this->maxTokens,
                    'system' => $systemPrompt,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $userPrompt,
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Claude API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \Exception('Claude API hatası: ' . $response->status() . ' - ' . $response->body());
            }

            $responseData = $response->json();
            $content = $responseData['content'][0]['text'] ?? '';

            // JSON'u parse et
            $jsonData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Eğer JSON değilse, içeriği temizle ve tekrar dene
                $cleanedContent = $this->extractJsonFromText($content);
                $jsonData = json_decode($cleanedContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Claude API\'den geçersiz JSON yanıtı alındı: ' . json_last_error_msg());
                }
            }

            return [
                'html_template' => $jsonData['html_code'] ?? '',
                'seo_title' => $jsonData['seo_title'] ?? $title,
                'seo_description' => $jsonData['seo_description'] ?? '',
                'seo_keywords' => $jsonData['keywords'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Claude Service Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Extract JSON from text that might contain markdown or other formatting
     */
    protected function extractJsonFromText(string $text): string
    {
        // Remove markdown code blocks
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);

        // Try to find JSON object
        if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $text, $matches)) {
            return $matches[0];
        }

        return $text;
    }
}
