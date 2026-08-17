<?php

namespace App\Traits;

use App\Models\Hit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

trait TracksHits
{
    public function trackHit(?string $pageType = null): void
    {
        $pageType = $pageType ?? $this->getTable();
        $pageId = $this->id;

        $sessionKey = 'hit_'.$pageType.'_'.$pageId;

        if (! Session::has($sessionKey)) {
            $date = Carbon::today()->toDateString();

            $hit = Hit::firstOrNew([
                'page' => $pageType,
                'page_id' => $pageId,
                'date' => $date,
            ]);

            $hit->hit = ($hit->hit ?? 0) + 1;
            $hit->save();
            Cache::forget("hits_count_{$pageType}_{$this->id}");
            Session::put($sessionKey, true);
        }
    }

    public function getHitsCount(?string $pageType = null): int
    {
        $pageType = $pageType ?? $this->getTable();
        $cacheKey = "hits_count_{$pageType}_{$this->id}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($pageType) {
            return Hit::where('page', $pageType)
                ->where('page_id', $this->id)
                ->sum('hit');
        });
    }
}
