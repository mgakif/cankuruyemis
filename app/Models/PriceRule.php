<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'scope_type',
        'scope_id',
        'operation_type',
        'amount',
        'percent_adjustment',
        'fixed_adjustment',
        'override_price',
        'rounding_type',
        'rounding_step',
        'priority',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percent_adjustment' => 'decimal:2',
        'fixed_adjustment' => 'decimal:2',
        'override_price' => 'decimal:2',
        'rounding_step' => 'decimal:2',
        'priority' => 'integer',
        'is_active' => 'bool',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'scope_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'scope_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('priority');
    }
}
