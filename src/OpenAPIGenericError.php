<?php

namespace OpenAPI;

use OpenAPI\Schema\OpenAPIBaseSchema;

class OpenAPIGenericError
{
    public int $code;
    public OpenAPIBaseSchema $schema;

    public function __construct(int $code, string $type)
    {
        $this->code = $code;
        $this->schema = OpenAPIBaseSchema::ExtractFromTypeName($type);
    }
}
