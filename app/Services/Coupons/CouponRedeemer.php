<?php

namespace App\Services\Coupons;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class CouponRedeemer
{
    public function redeem(Coupon $coupon, int $quantity, Request $request, bool $expirationOverride = false, ?string $note = null, ?string $usedBy = null): Coupon
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Kullanım adedi en az 1 olmalıdır.');
        }

        return DB::transaction(function () use ($coupon, $quantity, $request, $expirationOverride, $note, $usedBy): Coupon {
            /** @var Coupon $locked */
            $locked = Coupon::query()->whereKey($coupon->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === Coupon::STATUS_CANCELLED) {
                throw new RuntimeException('Bu kupon iptal edilmiştir.');
            }

            if ($locked->status === Coupon::STATUS_FULLY_USED || $locked->remaining_quantity < 1) {
                throw new RuntimeException('Bu kupon tamamen kullanılmıştır.');
            }

            if ($locked->isExpired() && ! $expirationOverride) {
                $locked->forceFill(['status' => Coupon::STATUS_EXPIRED])->save();
                throw new RuntimeException('Kuponun süresi dolmuştur.');
            }

            if ($quantity > $locked->remaining_quantity) {
                throw new RuntimeException('Kuponda yeterli hak kalmamıştır.');
            }

            $expiredDays = $locked->isExpired()
                ? max(0, $locked->expires_at->diffInDays(now()))
                : null;

            $locked->remaining_quantity -= $quantity;
            if ($locked->remaining_quantity === 0) {
                $locked->status = Coupon::STATUS_FULLY_USED;
            }
            $locked->save();

            CouponUsage::query()->create([
                'coupon_id' => $locked->id,
                'quantity' => $quantity,
                'user_id' => $request->user()?->id,
                'used_by' => $usedBy ?? $request->user()?->email ?? $request->user()?->name,
                'used_at' => now(),
                'expiration_override' => $expirationOverride,
                'expired_days' => $expiredDays,
                'note' => $note,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);

            return $locked->refresh();
        });
    }
}
