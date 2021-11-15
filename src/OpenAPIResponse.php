<?php

namespace OpenAPI;

use JsonSerializable;
use OpenAPI\Schema\OpenAPIBaseSchema;

class OpenAPIResponse implements JsonSerializable
{
    public int $code;
    public string $description;

    private ?OpenAPIBaseSchema $schema = null;

    public function __construct(int $code)
    {
        $this->code = $code;
    }

    public function setSchema(?OpenAPIBaseSchema $schema): void
    {
        $this->schema = $schema;
    }

    public function jsonSerialize(): array
    {
        $result = [];

        if ($this->description) {
            $result["description"] = $this->description;
        }

        if ($this->schema) {
            foreach (OpenAPIDocument::getInstance()->contentTypes as $contentType) {
                $result["content"][$contentType]["schema"] = $this->schema;
            }
        }

        return $result;
    }
}
