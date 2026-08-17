<?php

namespace App\Helper;

use Closure;
use Illuminate\Support\Str;

class Slug
{
    public static function make(): Closure
    {
        return function ($get, $set, ?string $old, ?string $state): void {
            if (($get('slug') ?? '') !== Str::slug($old)) {
                return;
            }

            $set('slug', Str::slug($state));
        };
    }
}
