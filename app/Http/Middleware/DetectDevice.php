<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Jenssegers\Agent\Agent;
use Symfony\Component\HttpFoundation\Response;

class DetectDevice
{
    /**
     * Lightweight bot detection for view-layer decisions (e.g. defer-loading).
     * IMPORTANT: Keep conservative — only used to decide whether to render extra SSR content for bots.
     */
    public static function isBot(?string $userAgent = null): bool
    {
        try {
            $ua = $userAgent ?? request()->userAgent();
        } catch (\Throwable $e) {
            $ua = $userAgent;
        }

        if (!is_string($ua) || $ua === '') {
            return false;
        }

        $ua = strtolower($ua);

        // Common crawlers / link preview bots
        return (bool) preg_match(
            '/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|facebot|twitterbot|linkedinbot|pinterest|whatsapp|telegrambot|discordbot|googlebot|bingbot|yandex|baiduspider|duckduckbot|semrush|ahrefs|mj12bot|siteaudit/i',
            $ua
        );
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $agent = new Agent;

        $deviceType = $agent->isMobile() ? 'mobile' : 'desktop';
        View::share('deviceType', $deviceType);
        // Dilersen tablet ayrı olarak kontrol edebilirsin:
        // if ($agent->isTablet()) $deviceType = 'tablet';

        // Oturuma veya request objesine kaydedebilirsin
        session(['device_type' => $deviceType]);

        return $next($request);
    }
}
