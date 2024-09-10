<?php

namespace TopSoft4U\OpenAPI;

use JsonSerializable;
use TopSoft4U\OpenAPI\Schema\OpenAPIBaseSchema;

class OpenAPIResponse
{
    public ?string $contentType = null;
    public string $description;

    private ?OpenAPIBaseSchema $schema = null;

    public function __construct(public int $code)
    {
    }

    public function getSchema(): ?OpenAPIBaseSchema
    {
        return $this->schema;
    }

    public function setSchema(?OpenAPIBaseSchema $schema): void
    {
        $this->schema = $schema;
    }
}
