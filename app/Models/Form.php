<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['name', 'slug', 'email', 'placeholder',
        'name_key', 'email_key', 'description', 'brief', 'success_message', 'trello', 'trello_id', 'recaptcha', 
        'requires_token', 'access_token'];
    
    protected $casts = [
        'recaptcha' => 'boolean',
        'requires_token' => 'boolean',
    ];

    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('position');
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }
}
