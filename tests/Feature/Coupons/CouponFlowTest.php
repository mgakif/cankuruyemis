<?php

namespace Tests\Feature\Coupons;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use App\Services\Coupons\CouponToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['coupons.telegram_secret' => 'test-secret']);
        config(['coupons.telegram_bot_username' => 'safakyazdirBot']);
    }

    public function test_telegram_endpoint_requires_secret(): void
    {
        $this->postJson(route('telegram.coupons.drink'), ['quantity' => 1])
            ->assertForbidden();
    }

    public function test_telegram_endpoint_creates_drink_coupon_with_hashed_token(): void
    {
        $response = $this->postJson(route('telegram.coupons.drink'), [
            'quantity' => 2,
            'created_by' => 'telegram:265546834',
        ], [
            'X-Coupon-Secret' => 'test-secret',
        ])->assertCreated();

        $payload = $response->json();
        $coupon = Coupon::query()->firstOrFail();

        $this->assertSame('drink', $coupon->reward_type);
        $this->assertSame(2, $coupon->initial_quantity);
        $this->assertSame(2, $coupon->remaining_quantity);
        $this->assertSame('telegram:265546834', $coupon->created_by);
        $this->assertNotEmpty($payload['url']);
        $this->assertStringContainsString('/coupon/', $payload['url']);
        $this->assertSame("https://t.me/safakyazdirBot?start=kupon_{$coupon->code}", $payload['telegram_url']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $coupon->token_hash);
        $token = basename(parse_url($payload['url'], PHP_URL_PATH));
        $this->assertNotSame((string) $coupon->id, $token);
        $this->assertNotSame(route('coupons.show', ['token' => $coupon->id]), $payload['url']);
    }

    public function test_coupon_can_be_redeemed_until_fully_used(): void
    {
        $payload = $this->createCoupon(2);
        $token = basename(parse_url($payload['url'], PHP_URL_PATH));
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('coupons.use', ['token' => $token]), ['quantity' => 1])
            ->assertSessionHas('status');

        $coupon = Coupon::query()->firstOrFail();
        $this->assertSame(1, $coupon->remaining_quantity);
        $this->assertSame(Coupon::STATUS_ACTIVE, $coupon->status);

        $this->actingAs($user)
            ->post(route('coupons.use', ['token' => $token]), ['quantity' => 1])
            ->assertSessionHas('status');

        $coupon->refresh();
        $this->assertSame(0, $coupon->remaining_quantity);
        $this->assertSame(Coupon::STATUS_FULLY_USED, $coupon->status);
        $this->assertSame(2, CouponUsage::query()->sum('quantity'));

        $this->actingAs($user)
            ->post(route('coupons.use', ['token' => $token]), ['quantity' => 1])
            ->assertSessionHasErrors('coupon');

        $this->assertSame(2, CouponUsage::query()->sum('quantity'));
    }

    public function test_telegram_endpoint_redeems_coupon_by_code(): void
    {
        $this->createCoupon(2);
        $coupon = Coupon::query()->firstOrFail();

        $response = $this->postJson(route('telegram.coupons.redeem'), [
            'code' => strtolower($coupon->code),
            'quantity' => 1,
            'used_by' => 'telegram:265546834:mgakif',
        ], [
            'X-Coupon-Secret' => 'test-secret',
        ])->assertOk();

        $coupon->refresh();
        $usage = CouponUsage::query()->firstOrFail();

        $this->assertSame(1, $coupon->remaining_quantity);
        $this->assertSame(Coupon::STATUS_ACTIVE, $coupon->status);
        $this->assertSame(1, $usage->quantity);
        $this->assertSame('telegram:265546834:mgakif', $usage->used_by);
        $this->assertSame(1, $response->json('remaining_quantity'));
    }

    public function test_expired_coupon_requires_override_and_logs_it(): void
    {
        $payload = $this->createCoupon(1);
        $token = basename(parse_url($payload['url'], PHP_URL_PATH));
        $coupon = Coupon::query()->firstOrFail();
        $coupon->update(['expires_at' => now()->subDays(3)]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('coupons.use', ['token' => $token]), ['quantity' => 1])
            ->assertSessionHasErrors('coupon');

        $coupon->refresh();
        $this->assertSame(1, $coupon->remaining_quantity);

        $this->actingAs($user)
            ->post(route('coupons.override-use', ['token' => $token]), [
                'quantity' => 1,
                'note' => 'Müşteri mağazadaydı.',
            ])
            ->assertSessionHas('status');

        $coupon->refresh();
        $usage = CouponUsage::query()->firstOrFail();
        $this->assertSame(0, $coupon->remaining_quantity);
        $this->assertSame(Coupon::STATUS_FULLY_USED, $coupon->status);
        $this->assertTrue($usage->expiration_override);
        $this->assertNotNull($usage->expired_days);
    }

    public function test_manual_code_lookup_finds_coupon_without_plain_token(): void
    {
        $payload = $this->createCoupon(1);
        $coupon = Coupon::query()->firstOrFail();

        $this->get(route('coupons.code', ['code' => $coupon->code]))
            ->assertOk()
            ->assertSee($coupon->code);

        $user = User::factory()->create();
        $this->actingAs($user)
            ->post(route('coupons.code.use', ['code' => $coupon->code]), ['quantity' => 1])
            ->assertSessionHas('status');

        $coupon->refresh();
        $this->assertSame(0, $coupon->remaining_quantity);
        $this->assertSame(Coupon::STATUS_FULLY_USED, $coupon->status);

        $token = basename(parse_url($payload['url'], PHP_URL_PATH));
        $this->assertSame(CouponToken::hash($token), $coupon->token_hash);
    }

    /**
     * @return array<string, mixed>
     */
    private function createCoupon(int $quantity): array
    {
        return $this->postJson(route('telegram.coupons.drink'), [
            'quantity' => $quantity,
            'created_by' => 'test',
        ], [
            'X-Coupon-Secret' => 'test-secret',
        ])->assertCreated()->json();
    }
}
