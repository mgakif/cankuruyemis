<?php

namespace App\Models;

use App\Events\FieldSaved;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'question',
        'answer',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'order' => 'integer',
    ];

    protected static function booted()
    {
        static::saved(function ($faq) {
            event(new FieldSaved($faq));
        });

        static::deleted(function ($faq) {
            event(new FieldSaved($faq));
        });
    }
}
