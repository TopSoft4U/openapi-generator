<?php

namespace TopSoft4U\OpenAPI;

use JsonSerializable;

class OpenAPIRef implements JsonSerializable
{
    private string $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function jsonSerialize(): array
    {
        return [
            "\$ref" => "#/components/schemas/$this->name",
        ];
    }
}
