<?php

namespace App\Services\Coupons;

use Illuminate\Support\Str;

class CouponToken
{
    public static function generate(): string
    {
        return Str::random(48);
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
