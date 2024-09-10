<?php

namespace TopSoft4U\OpenAPI\Security;

class OpenAPISecurityApiKey extends OpenAPISecurity
{
    public ?string $in = null;

    public function __construct()
    {
        $this->type = "apiKey";
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        $result = array_merge(parent::jsonSerialize(), [
            "in" => $this->in,
        ]);

        return array_filter($result, fn($value) => $value !== null);
    }
}
