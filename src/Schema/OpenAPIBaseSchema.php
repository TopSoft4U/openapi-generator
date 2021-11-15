<?php

namespace TopSoft4U\OpenAPI\Schema;

use JsonSerializable;
use ReflectionNamedType;
use ReflectionType;

abstract class OpenAPIBaseSchema implements JsonSerializable
{
    public string $name;
    public ?string $description = null;
    public $default = null;
    public ?int $minimum;

    public function __construct(string $typeName)
    {
        $this->name = self::PathToModelName($typeName);
    }

    public static function PathToModelName(string $fullName)
    {
        $parts = explode("\\", $fullName);
        return end($parts);
    }

    /**
     * @throws \ReflectionException
     */
    public static function ExtractFromType(ReflectionType $type, ?string $extraType = null): OpenAPIBaseSchema
    {
        assert($type instanceof ReflectionNamedType);

        if ($type->isBuiltin() || $type->getName() == "DateTime") {
            $schema = new OpenAPISchemaTyped($extraType ?: $type->getName());
            $schema->nullable = $type->allowsNull();
        } else {
            $schema = new OpenAPISchemaRef($type->getName());
        }

//        $schema->default;

        return $schema;
    }

    /**
     * @throws \ReflectionException
     */
    public static function ExtractFromTypeName(string $type, ?string $extraType = null): OpenAPIBaseSchema
    {
        if (IsBuiltin($type)) {
            $schema = new OpenAPISchemaTyped($extraType ?: $type);
            $schema->nullable = mb_strpos("?", $type) !== false;
        } else {
            $schema = new OpenAPISchemaRef($type);
        }

        return $schema;
    }
}
