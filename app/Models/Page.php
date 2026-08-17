<?php

namespace App\Models;

use App\Models\Concerns\HasPublishing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
    use HasPublishing;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'template',
        'is_home',
        'is_published',
        'published_at',
        'seo_title',
        'seo_description',
        'canonical_url',
        'is_indexable',
        'is_followable',
        'seo_image_path',
    ];

    protected $casts = [
        'is_home' => 'bool',
        'is_published' => 'bool',
        'is_indexable' => 'bool',
        'is_followable' => 'bool',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function seoTitle(): string
    {
        return $this->seo_title ?: $this->title;
    }

    public function seoDescription(): ?string
    {
        return $this->seo_description;
    }

    public function canonicalUrl(): string
    {
        return $this->canonical_url ?: route('page.show', $this);
    }
}

