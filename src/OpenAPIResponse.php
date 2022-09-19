<?php

namespace TopSoft4U\OpenAPI;

use JsonSerializable;
use TopSoft4U\OpenAPI\Schema\OpenAPIBaseSchema;

class OpenAPIResponse
{
    public ?string $contentType = null;

    public int $code;
    public string $description;

    private ?OpenAPIBaseSchema $schema = null;

    public function __construct(int $code)
    {
        $this->code = $code;
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
