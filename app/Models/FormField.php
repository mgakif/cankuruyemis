<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $fillable = [
        'form_id', 'label', 'name', 'type', 'required',
        'options', 'default_value', 'placeholder', 'show_on_form', 'position', 'group',
        'step', 'column_width'
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'show_on_form' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
