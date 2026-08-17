<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class ReCaptcha implements Rule
{
    public function passes($attribute, $value)
    {
        // Değer boşsa direkt false dön
        if (empty($value)) {
            return false;
        }
        
        $secretKey = config('services.recaptcha.secret_key');
        
        // Secret key yoksa false dön
        if (empty($secretKey)) {
            \Log::error('reCAPTCHA secret key is not configured');
            return false;
        }
        
        // Debug: Gelen değeri logla
        \Log::info('reCAPTCHA Request', [
            'value_length' => strlen($value),
            'value_preview' => substr($value, 0, 50) . '...',
            'ip' => request()->ip(),
        ]);
        
        try {
            $requestData = [
                'secret' => $secretKey,
                'response' => $value,
                'remoteip' => request()->ip(),
            ];
            
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', $requestData);
            
            $result = $response->json();
            
            // Debug için log
            \Log::info('reCAPTCHA Verification Result', [
                'success' => $result['success'] ?? false,
                'error_codes' => $result['error-codes'] ?? [],
                'challenge_ts' => $result['challenge_ts'] ?? null,
                'hostname' => $result['hostname'] ?? null,
            ]);
            
            // Başarılı değilse hata kodlarını logla
            if (empty($result['success'])) {
                \Log::warning('reCAPTCHA verification failed', [
                    'error_codes' => $result['error-codes'] ?? [],
                    'ip' => request()->ip(),
                    'value_preview' => substr($value, 0, 50) . '...',
                ]);
            }
            
            return !empty($result['success']);
            
        } catch (\Exception $e) {
            \Log::error('reCAPTCHA verification exception: ' . $e->getMessage());
            return false;
        }
    }

    public function message()
    {
        return 'Lütfen robot olmadığınızı doğrulayın.';
    }
}
