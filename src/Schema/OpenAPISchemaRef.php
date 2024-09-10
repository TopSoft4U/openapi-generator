<?php

namespace TopSoft4U\OpenAPI\Schema;

use TopSoft4U\OpenAPI\OpenAPIDocument;

class OpenAPISchemaRef extends OpenAPIBaseSchema
{
    /**
     * @throws \ReflectionException
     */
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

    #[\Override]
    public function jsonSerialize(): array
    {
        return ["\$ref" => "#/components/schemas/$this->name"];
    }
}
