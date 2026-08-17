<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImportRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_import_batch_id',
        'row_number',
        'barcode',
        'stock_name',
        'product_id',
        'action',
        'status',
        'message',
        'raw_payload',
    ];

    protected $casts = [
        'row_number' => 'integer',
        'raw_payload' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductImportBatch::class, 'product_import_batch_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
