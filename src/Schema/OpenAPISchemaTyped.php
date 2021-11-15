<?php

namespace TopSoft4U\OpenAPI\Schema;

class OpenAPISchemaTyped extends OpenAPIBaseSchema
{
    public string $type;
    private ?string $format = null;
    protected bool $nullable = false;

    public ?OpenAPIBaseSchema $items = null;

    public function __construct($typeName)
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
                $this->type = "array";

                if ($extraClass) {
                    $schema = OpenAPIBaseSchema::ExtractFromTypeName($extraClass);
                    $this->items = $schema;
                }
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
            default:
            case "object":
                $this->type = "object";
                break;
        }
    }

    public function jsonSerialize(): array
    {
        $result = ["type" => $this->type];

        if ($this->format) {
            $result["format"] = $this->format;
        }
        if ($this->items) {
            $result["items"] = $this->items;
        }
        if ($this->nullable) {
            $result["nullable"] = $this->nullable;
        }
        if (isset($this->default)) {
            $result["default"] = $this->default;
        }

        return $result;
    }
}
