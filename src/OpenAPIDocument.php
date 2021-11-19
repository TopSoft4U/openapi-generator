<?php

namespace TopSoft4U\OpenAPI;

use Exception;
use JsonSerializable;
use TopSoft4U\OpenAPI\Schema\OpenAPIComponentSchema;
use TopSoft4U\OpenAPI\Security\OpenAPISecurity;
use ReflectionClass;
use ReflectionMethod;

class OpenAPIDocument implements JsonSerializable
{
    private static ?OpenAPIDocument $instance = null;
    public static string $requestBodyKey = "requestBody";

    /** @var string[] */
    public array $contentTypes = ["application/json"];

    public string $title = "PHP OpenAPI";
    public string $version = "0.1";

    public bool $useInheritance = true;

    /** @var string[] Fully qualified class names with namespaces */
    public array $ignoreParentClasses = [];

    /** @var OpenAPIGenericError[] */
    public array $genericErrors = [];

    private string $controllerDir;
    private string $modelDir;

    private array $paths = [];
    private array $components = [];

    /** @var \TopSoft4U\OpenAPI\Schema\OpenAPIComponentSchema[] */
    private array $schemas = [];

    public static function getInstance(): OpenAPIDocument
    {
        if (!self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function addPath(string $uri, string $requestType, OpenAPIPath $path): void
    {
        $this->paths[$uri][$requestType] = $path;
    }

    protected function __clone()
    {
    }

    protected function __construct()
    {
    }

    public function setDirectories(string $contDir, string $modelDir)
    {
        $this->controllerDir = $contDir;
        $this->modelDir = $modelDir;
    }

    /** @var ReflectionMethod[] */
    private array $routes = [];

    /**
     * @throws \ReflectionException
     */
    private function parseControllers()
    {
        $this->routes = [];

        $files = glob("$this->controllerDir" . DIRECTORY_SEPARATOR . "*.php");
        foreach ($files as $file) {
            include($file);
            $classes = get_declared_classes();
            $className = end($classes);

            $class = new ReflectionClass($className);
            if ($class->isAbstract()) {
                continue;
            }

            $classMethods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($classMethods as $method) {
                if ($method->class !== $className) {
                    continue;
                }

                if ($method->isStatic()) {
                    continue;
                }

                $this->routes[] = $method;
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function generatePaths()
    {
        foreach ($this->routes as $route) {
            $path = new OpenAPIPath($route);
            $this->paths[$path->uri][mb_strtolower($path->requestType)] = $path;
        }
    }

    public function getSchema(string $name): ?OpenAPIComponentSchema
    {
        return $this->schemas[$name] ?? null;
    }

    public function registerSchema(OpenAPIComponentSchema $schema)
    {
        $this->schemas[$schema->name] = $schema;
    }

    /**
     * @throws \Exception
     */
    public function process()
    {
        if (!$this->controllerDir || !$this->modelDir) {
            throw new Exception("Please set paths before parsing");
        }

        $this->parseControllers();

        $this->generatePaths();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            $this,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    /** @var OpenAPIServer[] */
    private array $servers = [];

    public function addServer(OpenAPIServer $server)
    {
        $this->servers[] = $server;
    }

    private array $security = [];
    private array $securitySchemes = [];

    public function addSecurity(string $name, OpenAPISecurity $security)
    {
        $this->securitySchemes[$name] = $security;
        $this->components["securitySchemes"][$name] = $security;
        $this->security[] = [
            $name => [],
        ];
    }

    public function jsonSerialize(): array
    {
        $result = [
            "openapi"    => "3.0.0",
            "info"       => [
                "title"   => $this->title,
                "version" => $this->version,
            ],
            "servers"    => $this->servers,
            "security"   => $this->security,
            "paths"      => $this->paths,
            "components" => [
                "schemas" => $this->schemas,
            ],
        ];

        if ($this->securitySchemes) {
            $result["components"]["securitySchemes"] = $this->securitySchemes;
        }

        return $result;
    }
}
