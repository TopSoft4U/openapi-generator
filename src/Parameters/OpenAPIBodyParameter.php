<?php

namespace OpenAPI\Parameters;

use OpenAPI\OpenAPIContent;
use ReflectionParameter;

class OpenAPIBodyParameter extends OpenAPIBaseParameter
{
    public ?OpenAPIContent $content = null;

    public function __construct(ReflectionParameter $parameter)
    {
        parent::__construct($parameter);

        $this->content = new OpenAPIContent($this->schema);
    }

    public function jsonSerialize(): array
    {
        $result = [
            "required" => $this->required,
            "content"  => $this->content,
        ];

        if ($this->description) {
            $result["description"] = $this->description;
        }

        return $result;
    }
}
