<?php

namespace TopSoft4U\OpenAPI;

use ReflectionMethod;

class OpenAPIOverrides
{
    private static ?OpenAPIOverrides $instance = null;

    /**
     * Allows user to override the default method of generation of the path URI.
     * For example, default is /Controller/GET_MethodName
     * Can be changed to return /methodName
     *
     * @var callable(ReflectionMethod $method): string
     */
    public $overridePathUri = null;

    /**
     * Allows user to override the default method of generation of the operationId.
     * For example: default is Controller_GET_MethodName
     * Can be changed to return methodName
     *
     * @var callable(ReflectionMethod $method): string
     */
    public $overrideOperationId = null;

    /**
     * Allows user to override the default method of generation of the property key.
     * For example: default is propertyName
     * Can be changed to return propertyname or property_name
     *
     * @var callable(string $key): string
     */
    public $overridePropertyKey = null;

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

}
