<?php

namespace TopSoft4U\OpenAPI\Schema;

use TopSoft4U\OpenAPI\OpenAPIDocument;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use TopSoft4U\OpenAPI\OpenAPIOverrides;

class OpenAPIComponentSchema extends OpenAPISchemaTyped
{
    /** @var string[] */
    private array $required = [];
    /** @var \TopSoft4U\OpenAPI\Schema\OpenAPISchemaTyped[] */
    private array $properties = [];

    /** @var string[] */
    private array $inheritedBy = [];

    private ?OpenAPISchemaRef $parent = null;

    /**
     * @throws \ReflectionException
     */
    public function __construct($typeName)
    {
        parent::__construct($typeName);
        OpenAPIDocument::getInstance()->registerSchema($this);

        $class = new ReflectionClass($typeName);
        $this->extractProperties($class);

        if (OpenAPIDocument::getInstance()->useInheritance) {
            while ($class = new ReflectionClass($typeName)) {
                $parentClass = $class->getParentClass();
                if (!$parentClass) {
                    break;
                }

                $ignored = in_array($parentClass->getName(), OpenAPIDocument::getInstance()->ignoreParentClasses);
                if ($parentClass->isInternal() || $ignored) {
                    break;
                }

                $this->parent = new OpenAPISchemaRef($parentClass->getName(), $typeName);
                $typeName = $parentClass->getName();
            }
        }
    }

    public function addChildren(string $modelName)
    {
        if (!OpenAPIDocument::getInstance()->useInheritance) {
            return;
        }

        $this->inheritedBy[] = $modelName;
    }

    private function extractProperties(ReflectionClass $class)
    {
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if ($prop->isStatic() || !$prop->isPublic()) {
                continue;
            }

            if (!$prop->isDefault()) {
                continue;
            }

            // Inherited
            if (OpenAPIDocument::getInstance()->useInheritance) {
                if ($prop->class != $class->name) {
                    continue;
                }
            }

            $propType = $prop->getType();
            if (!assert($propType instanceof ReflectionNamedType)) {
                continue;
            }

            $docs = PHPParseDoc($prop->getDocComment());
            $newProp = OpenAPIBaseSchema::ExtractFromType($propType, $docs->var->type ?? null, $docs->var->genericArgs ?? []);

            if (isset($docs->description)) {
                $newProp->description = $docs->description;
            }

            if ($docs->deprecated) {
                $newProp->deprecated = true;
            }

            if (!$propType->allowsNull()) {
                $this->required[] = $prop->name;
            }

            $this->properties[$prop->name] = $newProp;
        }

        foreach ($class->getDefaultProperties() as $key => $value) {
            if (!array_key_exists($key, $this->properties)) {
                continue;
            }

            if (!isset($this->properties[$key]->default)) {
                $this->properties[$key]->default = $value;
            }
        }
    }

    public function jsonSerialize(): array
    {
        $result = parent::jsonSerialize();

        if ($this->description) {
            $result["description"] = $this->description;
        }
        if ($this->properties) {
            $overrides = OpenAPIOverrides::getInstance();
            if ($func = $overrides->overridePropertyKey) {
                // $this->properties is array of string key and OpenAPISchemaTyped value
                // replace the key with the overridden key and replace it in the result
                $properties = [];
                foreach ($this->properties as $key => $value) {
                    $properties[$func($key)] = $value;
                }
                $result["properties"] = $properties;
            } else {
                $result["properties"] = $this->properties;
            }
        }
        if ($this->required) {
            $result["required"] = $this->required;
        }
        if (isset($this->default)) {
            $result["default"] = $this->default;
        }

        if ($this->inheritedBy) {
            $result["discriminator"]["propertyName"] = "type";
        }

        if ($this->parent) {
            $result = [
                "allOf" => [
                    $this->parent,
                    $result,
                ],
            ];
        }

        return $result;
    }
}
