<?php

namespace App\Services\Coupons;

use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CouponCreator
{
    public function __construct(private readonly CouponCodeGenerator $codes)
    {
    }

    /**
     * @return array{coupon: Coupon, token: string}
     */
    public function createDrinkCoupon(
        int $quantity,
        ?string $createdBy = null,
        ?int $validDays = null,
        ?string $customerName = null,
        ?string $customerPhone = null,
    ): array {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Kupon adedi en az 1 olmalıdır.');
        }

        $validDays ??= max(1, (int) config('coupons.default_valid_days', 15));
        $token = CouponToken::generate();

        $coupon = DB::transaction(function () use ($quantity, $createdBy, $validDays, $customerName, $customerPhone, $token): Coupon {
            return Coupon::query()->create([
                'reward_type' => 'drink',
                'initial_quantity' => $quantity,
                'remaining_quantity' => $quantity,
                'expires_at' => now()->addDays($validDays)->endOfDay(),
                'status' => Coupon::STATUS_ACTIVE,
                'created_by' => $createdBy,
                'token_hash' => CouponToken::hash($token),
                'code' => $this->codes->generate(),
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
            ]);
        });

        return ['coupon' => $coupon, 'token' => $token];
    }
}
