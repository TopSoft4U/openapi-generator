<?php

namespace TopSoft4U\OpenAPI;

use JsonSerializable;
use TopSoft4U\OpenAPI\Schema\OpenAPIBaseSchema;

class OpenAPIContent implements JsonSerializable
{
    public function __construct(public OpenAPIBaseSchema $schema)
    {
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        $result = [];

        foreach (OpenAPIDocument::getInstance()->contentTypes as $contentType) {
            $result[$contentType]["schema"] = $this->schema;
        }

        return $result;
    }
}
