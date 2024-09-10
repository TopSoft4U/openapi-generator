<?php

namespace TopSoft4U\OpenAPI;

use JsonSerializable;

class OpenAPIRef implements JsonSerializable
{
    public function __construct(private readonly string $name)
    {
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            "\$ref" => "#/components/schemas/$this->name",
        ];
    }
}
