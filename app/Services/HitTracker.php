<?php

namespace App\Services;

use App\Models\Hit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class HitTracker
{
    public static function track(int $id, string $pageType): void
    {
        $sessionKey = "hit_{$pageType}_{$id}";

        // Zaten sayılmışsa tekrar sayma
        if (Session::has($sessionKey)) {
            return;
        }

        $today = now()->toDateString(); // 'Y-m-d'

        $query = Hit::where('page', $pageType)
            ->where('page_id', $id);

        // 'site' özel durumu: tarih bazlı takip et
        if ($pageType === 'site') {
            $query->whereDate('date', $today);
        }

        $hit = $query->first();

        if ($hit) {
            $hit->increment('hit');
        } else {
            Hit::create([
                'page' => $pageType,
                'page_id' => $id,
                'date' => $today,
                'hit' => 1,
            ]);
        }
        Cache::forget("hits_count_{$pageType}_{$id}");
        Session::put($sessionKey, true);
    }
}
