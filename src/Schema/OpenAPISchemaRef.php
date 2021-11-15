<?php

namespace OpenAPI\Schema;

use OpenAPI\OpenAPIDocument;

class OpenAPISchemaRef extends OpenAPIBaseSchema
{
    public function __construct($typeName, ?string $childTypeName = null)
    {
        parent::__construct($typeName);

        if (!($schemaDefinition = OpenAPIDocument::getInstance()->getSchema($this->name))) {
            $schemaDefinition = new OpenAPIComponentSchema($typeName);
        }

        if ($childTypeName) {
            $childName = self::PathToModelName($childTypeName);
            $schemaDefinition->addChildren($childName);
        }

        OpenAPIDocument::getInstance()->registerSchema($schemaDefinition);
    }

    public function jsonSerialize(): array
    {
        return ["\$ref" => "#/components/schemas/$this->name"];
    }
}
