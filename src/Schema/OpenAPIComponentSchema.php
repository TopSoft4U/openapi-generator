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
            $extraType = $docs->var->type ?? null;

            if ($extraType !== null) {
                $declaringClass = $prop->class === $class->name
                    ? $class
                    : new ReflectionClass($prop->class);
                $templates = $this->getTemplates($declaringClass);

                $stripped = $extraType;
                if (str_ends_with($stripped, "[]")) {
                    $stripped = mb_substr($stripped, 0, -2);
                }
                if (array_key_exists($stripped, $templates)) {
                    $resolved = $this->resolveTemplateParam($class, $declaringClass, $stripped, $templates[$stripped]);
                    $extraType = str_ends_with($extraType, "[]")
                        ? $resolved . "[]"
                        : $resolved;
                } elseif (!IsBuiltin($stripped)) {
                    $resolved = ResolveClassName($declaringClass, $stripped);
                    if ($resolved !== $stripped) {
                        $extraType = str_ends_with($extraType, "[]")
                            ? $resolved . "[]"
                            : $resolved;
                    }
                }
            }

            $newProp = OpenAPIBaseSchema::ExtractFromType($propType, $extraType, $docs->var->genericArgs ?? []);

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

    private function getTemplates(ReflectionClass $class): array
    {
        static $cache = [];
        $className = $class->getName();
        if (isset($cache[$className])) {
            return $cache[$className];
        }

        $classDocs = PHPParseDoc($class->getDocComment());
        $templates = [];
        foreach ($classDocs->templates as $template) {
            $templates[$template->name] = $template->bound;
        }

        return $cache[$className] = $templates;
    }

    private function getExtendsMapping(ReflectionClass $childClass, ReflectionClass $declaringClass): array
    {
        static $cache = [];
        $cacheKey = $childClass->getName() . "::" . $declaringClass->getName();
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $childDocs = PHPParseDoc($childClass->getDocComment());
        $declaringShortName = (new ReflectionClass($declaringClass->getName()))->getShortName();
        $declaringFqcn = $declaringClass->getName();

        $matchedExtends = null;
        foreach ($childDocs->extends as $extendsNode) {
            $parentClass = $extendsNode->parentClass;
            if ($parentClass === $declaringShortName || ltrim($parentClass, "\\") === ltrim($declaringFqcn, "\\")) {
                $matchedExtends = $extendsNode;
                break;
            }
            $resolvedParent = ResolveClassName($childClass, $parentClass);
            if (ltrim($resolvedParent, "\\") === ltrim($declaringFqcn, "\\")) {
                $matchedExtends = $extendsNode;
                break;
            }
        }

        if (!$matchedExtends || !$matchedExtends->genericArgs) {
            return $cache[$cacheKey] = [];
        }

        $parentTemplates = $this->getTemplates($declaringClass);
        $templateNames = array_keys($parentTemplates);
        $mapping = [];
        foreach ($matchedExtends->genericArgs as $i => $argType) {
            if (isset($templateNames[$i])) {
                $mapping[$templateNames[$i]] = $argType;
            }
        }

        return $cache[$cacheKey] = $mapping;
    }

    private function resolveTemplateParam(ReflectionClass $class, ReflectionClass $declaringClass, string $paramName, ?string $bound): string
    {
        $extendsMapping = $this->getExtendsMapping($class, $declaringClass);
        if (array_key_exists($paramName, $extendsMapping)) {
            $resolved = ResolveClassName($class, $extendsMapping[$paramName]);
            if (class_exists($resolved) || interface_exists($resolved)) {
                return $resolved;
            }
            return $resolved;
        }

        if ($bound !== null) {
            $resolved = ResolveClassName($declaringClass, $bound);
            if (class_exists($resolved) || interface_exists($resolved)) {
                return $resolved;
            }
            return $resolved;
        }

        return "mixed";
    }

    #[\Override]
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
