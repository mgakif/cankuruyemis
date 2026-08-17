<?php

namespace App\Services\Coupons;

use App\Models\Coupon;

class CouponCodeGenerator
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function generate(): string
    {
        do {
            $code = $this->chunk($this->randomPart(4).$this->randomPart(2));
        } while (Coupon::query()->where('code', $code)->exists());

        return $code;
    }

    private function randomPart(int $length): string
    {
        $value = '';
        $max = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < $length; $i++) {
            $value .= self::ALPHABET[random_int(0, $max)];
        }

        return $value;
    }

    private function chunk(string $value): string
    {
        return substr($value, 0, 4).'-'.substr($value, 4);
    }
}
