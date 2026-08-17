<?php

namespace App\Models;

use App\Events\FieldSaved;
use App\Models\Concerns\HasPublishing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;
    use HasPublishing;

    protected $fillable = [
        'product_category_id',
        'title',
        'slug',
        'barcode',
        'brand',
        'unit',
        'package_size',
        'sku',
        'short_description',
        'description',
        'summary',
        'price',
        'store_price',
        'online_price',
        'stock',
        'stock_quantity',
        'sale_channel',
        'position',
        'featured_image_path',
        'gallery',
        'specifications',
        'technical_specs',
        'nutrition_facts',
        'energy_kcal',
        'ingredients',
        'allergen_info',
        'content_status',
        'last_imported_at',
        'is_published',
        'published_at',
        'seo_title',
        'seo_description',
        'canonical_url',
        'is_indexable',
        'is_followable',
        'seo_image_path',
        'list_text',
        'group_title',
        'tags',
        'hashtags',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'store_price' => 'decimal:2',
        'online_price' => 'decimal:2',
        'stock' => 'integer',
        'stock_quantity' => 'decimal:3',
        'position' => 'integer',
        'gallery' => 'array',
        'specifications' => 'array',
        'technical_specs' => 'array',
        'nutrition_facts' => 'array',
        'tags' => 'array',
        'hashtags' => 'array',
        'energy_kcal' => 'decimal:2',
        'is_published' => 'bool',
        'is_indexable' => 'bool',
        'is_followable' => 'bool',
        'published_at' => 'datetime',
        'last_imported_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getEffectivePriceAttribute(): string
    {
        return (string) ($this->online_price ?: $this->store_price ?: $this->price);
    }

    public function getDisplayPriceAttribute(): string
    {
        return $this->effective_price;
    }

    public function isOnlineAvailable(): bool
    {
        return $this->sale_channel === 'online';
    }

    public function isStoreOnly(): bool
    {
        return $this->sale_channel === 'store_only';
    }

    public static function shouldShowStoreOnlyOnSite(): bool
    {
        $value = setting('sadece-magazada-satilan-urunleri-goster');

        if ($value === null || $value === '') {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = mb_strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'on', 'yes', 'evet'], true);
    }

    public function scopeVisibleOnSite(Builder $query): Builder
    {
        return $query->where(function (Builder $visibleQuery) {
            $visibleQuery->where('sale_channel', 'online');

            if (self::shouldShowStoreOnlyOnSite()) {
                $visibleQuery->orWhere('sale_channel', 'store_only');
            }
        });
    }

    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('sale_channel', 'online');
    }

    public function seoTitle(): string
    {
        return $this->seo_title ?: $this->title;
    }

    public function seoDescription(): ?string
    {
        return $this->seo_description ?: $this->short_description;
    }

    public function canonicalUrl(): string
    {
        return $this->canonical_url ?: route('products.show', $this);
    }

    protected static function booted()
    {
        static::saving(function (self $product) {
            if ($product->store_price !== null && ($product->price === null || (float) $product->price === 0.0)) {
                $product->price = $product->store_price;
            }

            if ($product->stock_quantity !== null) {
                $product->stock = max(0, (int) floor((float) $product->stock_quantity));
            }
        });

        static::saved(function ($product) {
            event(new FieldSaved($product));
        });

        static::deleted(function ($product) {
            event(new FieldSaved($product));
        });
    }
}
