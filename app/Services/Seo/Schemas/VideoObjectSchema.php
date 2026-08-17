<?php

namespace App\Services\Seo\Schemas;

class VideoObjectSchema implements SchemaInterface
{
    public function __construct(
        public string $name,
        public string $description,
        public string $thumbnailUrl,
        public string $uploadDate,
        public string $contentUrl,
        public ?string $embedUrl = null,
        public ?string $duration = null
    ) {}

    public function generate(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $this->name,
            'description' => $this->description,
            'thumbnailUrl' => $this->thumbnailUrl,
            'uploadDate' => $this->uploadDate,
            'contentUrl' => $this->contentUrl,
        ];
        if ($this->embedUrl) {
            $data['embedUrl'] = $this->embedUrl;
        }
        if ($this->duration) {
            $data['duration'] = $this->duration;
        }

        return $data;
    }
}
