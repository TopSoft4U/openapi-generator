<?php

namespace TopSoft4U\OpenAPI;

use TopSoft4U\OpenAPI\Schema\OpenAPIBaseSchema;

class OpenAPIGenericError
{
    public OpenAPIBaseSchema $schema;

    public function __construct(public int $code, string $type)
    {
        $this->schema = OpenAPIBaseSchema::ExtractFromTypeName($type);
    }
}
