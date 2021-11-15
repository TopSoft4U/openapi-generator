<?php

namespace TopSoft4U\OpenAPI;

use JsonSerializable;

class OpenAPIServer implements JsonSerializable
{
    public string $url;
    public ?string $description = null;

    public function __construct(string $url, ?string $description = null)
    {
        $this->url = $url;
        $this->description = $description;
    }

    public function jsonSerialize(): array
    {
        $output = ["url" => $this->url];

        if ($this->description) {
            $output["description"] = $this->description;
        }

        return $output;
    }
}
