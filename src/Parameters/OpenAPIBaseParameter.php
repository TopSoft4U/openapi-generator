<?php

namespace TopSoft4U\OpenAPI\Parameters;

use JsonSerializable;
use TopSoft4U\OpenAPI\Schema\OpenAPIBaseSchema;
use ReflectionParameter;

abstract class OpenAPIBaseParameter implements JsonSerializable
{
    protected ?string $name;
    protected ?string $description;
    protected bool $required;
    protected ?OpenAPIBaseSchema $schema = null;

    /**
     * @throws \ReflectionException
     */
    public function __construct(ReflectionParameter $parameter)
    {
        $name = $parameter->name;
        $this->name = $name;

        $methodDocParse = PHPParseDoc($parameter->getDeclaringFunction()->getDocComment());

        $paramDoc = $methodDocParse->params[$name];

        $this->description = $paramDoc->description ?? null;
        $this->required = !$parameter->allowsNull();

        $this->schema = OpenAPIBaseSchema::ExtractFromType($parameter->getType(), $paramDoc->type ?? null);

        if ($parameter->isOptional()) {
            $this->schema->default = $parameter->getDefaultValue();
        }
    }

    public function jsonSerialize(): array
    {
        $result = [
            "required" => $this->required,
        ];

        if ($this->description) {
            $result["description"] = $this->description;
        }

        return $result;
    }
}
