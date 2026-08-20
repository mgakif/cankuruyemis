<?php

namespace App\Services\Seo\Schemas;

use App\Models\Article;

class BlogPostSchema implements SchemaInterface
{
    public function __construct(protected Article $blog, protected array $breadcrumbs = []) {}

    public function generate(): array
    {

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $this->blog->title,
            'image' => asset('storage/'.$this->blog->file),
            'author' => [
                '@type' => 'Person',
                'name' => $this->blog->author_name ?? setting('app-name'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => setting('app-name'),
                'legalName' => 'Zeynep Tekin',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset(setting('logo')),
                ],
            ],
            'datePublished' => optional($this->blog->created_at)->toIso8601String(),
            'dateModified' => optional($this->blog->updated_at)->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('route', $this->blog->slug),
            ],
        ];

        if (! empty($this->breadcrumbs)) {
            $schema['breadcrumb'] = SharedSchemas::breadcrumb($this->breadcrumbs);
        }

        return $schema;
    }
}
