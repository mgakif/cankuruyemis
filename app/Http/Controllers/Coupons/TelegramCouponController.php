<?php

namespace App\Http\Controllers\Coupons;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\Coupons\CouponCreator;
use App\Services\Coupons\CouponRedeemer;
use App\Services\Coupons\CouponToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TelegramCouponController extends Controller
{
    public function storeDrink(Request $request, CouponCreator $creator): JsonResponse
    {
        $this->ensureTelegramSecret($request);

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'valid_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'created_by' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            ['coupon' => $coupon, 'token' => $token] = $creator->createDrinkCoupon(
                quantity: (int) $data['quantity'],
                createdBy: $data['created_by'] ?? null,
                validDays: isset($data['valid_days']) ? (int) $data['valid_days'] : null,
                customerName: $data['customer_name'] ?? null,
                customerPhone: $data['customer_phone'] ?? null,
            );
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['quantity' => $exception->getMessage()]);
        }

        return response()->json([
            'id' => $coupon->id,
            'reward_type' => $coupon->reward_type,
            'initial_quantity' => $coupon->initial_quantity,
            'remaining_quantity' => $coupon->remaining_quantity,
            'expires_at' => $coupon->expires_at->toIso8601String(),
            'expires_at_display' => $coupon->expires_at->format('d.m.Y'),
            'code' => $coupon->code,
            'url' => route('coupons.show', ['token' => $token]),
            'manual_url' => route('coupons.code', ['code' => $coupon->code]),
            'telegram_url' => $this->telegramDeepLink($coupon->code),
        ], 201);
    }

    public function redeem(Request $request, CouponRedeemer $redeemer): JsonResponse
    {
        $this->ensureTelegramSecret($request);

        $data = $request->validate([
            'code' => ['required_without:token', 'string', 'max:32'],
            'token' => ['required_without:code', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'used_by' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
            'expiration_override' => ['nullable', 'boolean'],
        ]);

        $coupon = Coupon::query()
            ->when(isset($data['code']), fn ($query) => $query->where('code', strtoupper((string) $data['code'])))
            ->when(isset($data['token']), fn ($query) => $query->where('token_hash', CouponToken::hash((string) $data['token'])))
            ->firstOrFail();

        try {
            $coupon = $redeemer->redeem(
                coupon: $coupon,
                quantity: (int) $data['quantity'],
                request: $request,
                expirationOverride: (bool) ($data['expiration_override'] ?? false),
                note: $data['note'] ?? 'Telegram üzerinden kullandırıldı.',
                usedBy: $data['used_by'] ?? null,
            );
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages(['coupon' => $exception->getMessage()]);
        }

        return response()->json([
            'id' => $coupon->id,
            'code' => $coupon->code,
            'reward_type' => $coupon->reward_type,
            'initial_quantity' => $coupon->initial_quantity,
            'remaining_quantity' => $coupon->remaining_quantity,
            'status' => $coupon->status,
            'expires_at_display' => $coupon->expires_at->format('d.m.Y'),
        ]);
    }

    private function ensureTelegramSecret(Request $request): void
    {
        $configuredSecret = (string) config('coupons.telegram_secret');
        $providedSecret = (string) $request->header('X-Coupon-Secret');

        if ($configuredSecret === '' || ! hash_equals($configuredSecret, $providedSecret)) {
            abort(403);
        }
    }

    private function telegramDeepLink(string $code): ?string
    {
        $username = trim((string) config('coupons.telegram_bot_username'));
        if ($username === '') {
            return null;
        }

        return "https://t.me/{$username}?start=kupon_{$code}";
    }
}
