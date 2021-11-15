<?php

namespace TopSoft4U\OpenAPI;

use JsonSerializable;
use TopSoft4U\OpenAPI\Schema\OpenAPIBaseSchema;

class OpenAPIContent implements JsonSerializable
{
    public OpenAPIBaseSchema $schema;

    public function __construct(OpenAPIBaseSchema $schema)
    {
        $this->schema = $schema;
    }

    public function jsonSerialize(): array
    {
        $result = [];

        foreach (OpenAPIDocument::getInstance()->contentTypes as $contentType) {
            $result[$contentType]["schema"] = $this->schema;
        }

        return $result;
    }
}
