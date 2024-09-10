<?php

namespace TopSoft4U\OpenAPI\Schema;

class OpenAPISchemaTyped extends OpenAPIBaseSchema
{
    public string $type;
    public ?string $format = null;
    protected bool $nullable = false;

    /** @var \TopSoft4U\OpenAPI\Schema\OpenAPIBaseSchema|bool|null */
    private $additionalProperties = null;

    public ?OpenAPIBaseSchema $items = null;

    public function __construct(string $typeName, array $genericArgs = [])
    {
        parent::__construct($typeName);

        $extraClass = null;
        $typeName = str_replace(" ", "", $typeName);
        if (str_contains($typeName, "|null")) {
            $typeName = str_replace("|null", "", $typeName);
            $this->nullable = true;
        }

        $allTypes = explode("|", $typeName);
        if (count($allTypes) > 1) {
            // TODO union
            $x = 1;
            $typeName = $allTypes[0];
        }

        if (str_ends_with($typeName, "[]")) {
            $extraClass = mb_substr($typeName, 0, -2);
            $typeName = "array";
        }

        switch ($typeName) {
            case "array":
                if (count($genericArgs) == 2) {
                    $this->type = "object";
                    $this->default = (object) [];
                    [, $valueType] = $genericArgs;
                    $this->additionalProperties = OpenAPIBaseSchema::ExtractFromTypeName($valueType);
                } else {
                    $this->type = "array";

                    if ($extraClass) {
                        $schema = OpenAPIBaseSchema::ExtractFromTypeName($extraClass);
                        $this->items = $schema;
                    }
                }
                break;
            case "binary":
                $this->type = "string";
                $this->format = "binary";
                break;
            case "bool":
            case "boolean":
                $this->type = "boolean";
                break;
            case "float":
                $this->type = "number";
                $this->format = "float";
                break;
            case "double":
                $this->type = "number";
                $this->format = "double";
                break;
            case "int":
            case "integer":
                $this->type = "integer";
//                $this->format = "int32";
                break;
            case "string":
                $this->type = "string";
                break;
            case "DateTime":
                $this->type = "string";
                $this->format = "date-time";
                break;
            case "NULL":
                $this->type = "null";
                break;
            case "object":
                $this->type = "object";
                if ($genericArgs) {
                    [, $valueType] = $genericArgs;
                    $this->additionalProperties = OpenAPIBaseSchema::ExtractFromTypeName($valueType);
                } else {
                    $this->additionalProperties = true;
                }
                break;
            default:
                $this->type = "object";
                $this->additionalProperties = false;
                break;
        }
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        $result = ["type" => $this->type];

        if ($this->format) {
            $result["format"] = $this->format;
        }

        if ($this->items) {
            $result["items"] = $this->items;
        } else if ($this->type == "array") {
            $result["items"] = (object) null;
        }

        if ($this->nullable) {
            $result["nullable"] = $this->nullable;
        }
        if (isset($this->default)) {
            $result["default"] = $this->default;
        }

        if ($this->deprecated) {
            $result["deprecated"] = $this->deprecated;
        }

        if (isset($this->additionalProperties)) {
            $result["additionalProperties"] = $this->additionalProperties;
        }

        return $result;
    }
}
