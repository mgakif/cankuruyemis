<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_id', 'data', 'name', 'email', 'answered',
    ];

    protected $casts = [
        'data' => 'array',
        'answered' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    protected static function booted()
    {
        static::deleting(function ($submission) {
            if (is_array($submission->data)) {
                foreach ($submission->data as $value) {
                    if (is_string($value) && str_starts_with($value, 'uploads/')) {
                        Storage::disk('public')->delete($value);
                    }
                }
            }
        });
    }
}  //
