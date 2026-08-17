<?php

namespace App\Models;

use App\Events\FieldSaved;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'attributes',
        'group',
        'slug',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function ($setting) {
            // dd($setting);
            event(new FieldSaved($setting));
        });

        static::deleted(function ($setting) {
            event(new FieldSaved($setting));
        });
    }
}
