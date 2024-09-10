<?php

namespace TopSoft4U\OpenAPI\Security;

use JsonSerializable;

abstract class OpenAPISecurity implements JsonSerializable
{
    public ?string $description = null;
    public ?string $type = null;
    public ?string $name = null;

    #[\Override]
    public function jsonSerialize(): array
    {
        $result = [
            "type" => $this->type,
            "name" => $this->name,
            "description" => $this->description,
        ];

        return array_filter($result, fn($value) => $value !== null);
    }
}
