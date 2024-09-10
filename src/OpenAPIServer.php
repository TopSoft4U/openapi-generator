<?php

namespace TopSoft4U\OpenAPI;

use JsonSerializable;

class OpenAPIServer implements JsonSerializable
{
    public function __construct(public string $url, public ?string $description = null)
    {
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        $output = ["url" => $this->url];

        if ($this->description) {
            $output["description"] = $this->description;
        }

        return $output;
    }
}
