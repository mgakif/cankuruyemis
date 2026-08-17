<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_name',
        'source_path',
        'status',
        'summary',
        'created_by',
        'processed_at',
    ];

    protected $casts = [
        'summary' => 'array',
        'processed_at' => 'datetime',
    ];

    public function rows(): HasMany
    {
        return $this->hasMany(ProductImportRow::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
