<?php

namespace TopSoft4U\OpenAPI\Parameters;

use Exception;
use TopSoft4U\OpenAPI\OpenAPIDocument;
use TopSoft4U\OpenAPI\Schema\OpenAPISchemaRef;
use TopSoft4U\OpenAPI\Schema\OpenAPISchemaTyped;
use ReflectionParameter;

class OpenAPIQueryParameter extends OpenAPIBaseParameter
{
    private string $in = "query";
    private ?string $style = null;

    /**
     * @throws \Exception
     */
    public function __construct(ReflectionParameter $parameter)
    {
        parent::__construct($parameter);

        if ($this->name === OpenAPIDocument::$requestBodyKey) {
            throw new Exception("$this->name is not a query parameter");
        }

        if ($this->schema instanceof OpenAPISchemaTyped) {
            if ($this->schema->type == "array") {
                $this->name .= "[]";
                $this->schema->items = OpenAPISchemaTyped::ExtractFromTypeName("string"); // Request params are always string
            }
        } else if ($this->schema instanceof OpenAPISchemaRef) {
            $this->style = "deepObject";
        }
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        $result = array_merge(parent::jsonSerialize(), [
            "in"     => $this->in,
            "name"   => $this->name,
            "schema" => $this->schema,
        ]);

        if ($this->style) {
            $result["style"] = $this->style;
        }

        return $result;
    }
}
