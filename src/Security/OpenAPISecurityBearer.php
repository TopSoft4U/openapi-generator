<?php

namespace TopSoft4U\OpenAPI\Security;

class OpenAPISecurityBearer extends OpenAPISecurity
{
    public ?string $scheme = null;

    public function __construct()
    {
        $this->type = "http";
        $this->scheme = "bearer";
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        $result = array_merge(parent::jsonSerialize(), [
            "scheme" => $this->scheme,
        ]);

        return array_filter($result, fn($value) => $value !== null);
    }
}
