<?php

namespace App\Http\Controllers\Coupons;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\Coupons\CouponRedeemer;
use App\Services\Coupons\CouponToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CouponController extends Controller
{
    public function show(string $token): View
    {
        $coupon = $this->findByToken($token);

        return view('coupons.show', [
            'coupon' => $coupon,
            'identifier' => $token,
            'mode' => 'token',
        ]);
    }

    public function showByCode(string $code): View
    {
        $coupon = $this->findByCode($code);

        return view('coupons.show', [
            'coupon' => $coupon,
            'identifier' => $coupon->code,
            'mode' => 'code',
        ]);
    }

    public function use(string $token, Request $request, CouponRedeemer $redeemer): RedirectResponse
    {
        $coupon = $this->findByToken($token);

        return $this->redeem($coupon, $request, $redeemer);
    }

    public function useByCode(string $code, Request $request, CouponRedeemer $redeemer): RedirectResponse
    {
        $coupon = $this->findByCode($code);

        return $this->redeem($coupon, $request, $redeemer);
    }

    public function overrideUse(string $token, Request $request, CouponRedeemer $redeemer): RedirectResponse
    {
        $coupon = $this->findByToken($token);

        return $this->redeemWithExpirationOverride($coupon, $request, $redeemer);
    }

    public function overrideUseByCode(string $code, Request $request, CouponRedeemer $redeemer): RedirectResponse
    {
        $coupon = $this->findByCode($code);

        return $this->redeemWithExpirationOverride($coupon, $request, $redeemer);
    }

    private function redeem(Coupon $coupon, Request $request, CouponRedeemer $redeemer): RedirectResponse
    {
        $quantity = (int) $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ])['quantity'];

        try {
            $redeemer->redeem($coupon, $quantity, $request);

            return back()->with('status', "{$quantity} hak kullanıldı.");
        } catch (RuntimeException $exception) {
            return back()->withErrors(['coupon' => $exception->getMessage()]);
        }
    }

    private function redeemWithExpirationOverride(Coupon $coupon, Request $request, CouponRedeemer $redeemer): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $redeemer->redeem($coupon, (int) $data['quantity'], $request, true, $data['note'] ?? 'Süresi geçmiş kupon istisnai olarak kullandırıldı.');

            return back()->with('status', 'İstisnai kullanım kaydedildi.');
        } catch (RuntimeException $exception) {
            return back()->withErrors(['coupon' => $exception->getMessage()]);
        }
    }

    private function findByToken(string $token): Coupon
    {
        return Coupon::query()
            ->where('token_hash', CouponToken::hash($token))
            ->with('usages')
            ->firstOrFail();
    }

    private function findByCode(string $code): Coupon
    {
        return Coupon::query()
            ->where('code', strtoupper($code))
            ->with('usages')
            ->firstOrFail();
    }
}
