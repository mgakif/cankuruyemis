<?php

namespace App\Services\Seo;

use App\Services\Seo\Schemas\SchemaInterface;

class SchemaBuilder
{
    protected ?SchemaInterface $schema = null;

    public static function make(): static
    {
        return new static;
    }

    public function setSchema(SchemaInterface $schema): static
    {
        $this->schema = $schema;

        return $this;
    }

    public function render(): string
    {
        if (! $this->schema) {
            return '';
        }

        return '<script type="application/ld+json">'
            .json_encode(
                $this->schema->generate(),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            )
            .'</script>';
    }
}
