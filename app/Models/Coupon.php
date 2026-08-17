<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_FULLY_USED = 'fully_used';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reward_type',
        'initial_quantity',
        'remaining_quantity',
        'expires_at',
        'status',
        'created_by',
        'token_hash',
        'code',
        'customer_name',
        'customer_phone',
        'cancelled_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isExpired(?Carbon $at = null): bool
    {
        return $this->expires_at->lt($at ?? now());
    }

    public function isUsable(?Carbon $at = null): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->remaining_quantity > 0
            && ! $this->isExpired($at);
    }

    public function publicStatusLabel(): string
    {
        if ($this->status === self::STATUS_ACTIVE && $this->isExpired()) {
            return 'expired';
        }

        return $this->status;
    }

    public function rewardTypeLabel(): string
    {
        return match ($this->reward_type) {
            'drink' => 'İçecek',
            default => str($this->reward_type)->headline()->toString(),
        };
    }
}
