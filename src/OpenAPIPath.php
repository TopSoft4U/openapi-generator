<?php

namespace TopSoft4U\OpenAPI;

use JsonSerializable;
use TopSoft4U\OpenAPI\Parameters\OpenAPIBodyParameter;
use TopSoft4U\OpenAPI\Parameters\OpenAPIQueryParameter;
use TopSoft4U\OpenAPI\Schema\OpenAPIBaseSchema;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

class OpenAPIPath implements JsonSerializable
{
    public string $requestType;
    public string $uri;

    private string $operationId;

    private array $tags;


    /** @var OpenAPIResponse[] */
    private array $responses = [];
    private array $parameters = [];

    private ?OpenAPIBodyParameter $requestBody = null;

    /**
     * @throws \Exception
     */
    public function __construct(ReflectionMethod $method)
    {
        $parts = explode("\\", $method->class);
        $controller = end($parts);

        $group = str_replace("Controller", "", $controller);

        [$requestType, $action] = explode("_", $method->name, 2);

        $this->requestType = $requestType;
        $this->uri = "/$group/$action";

        $this->operationId = "$requestType$this->uri";
        $this->tags = [$group];

        $this->extractParams($method->getParameters());
        $this->extractResponses($method);
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @throws \Exception
     */
    private function extractParams(array $parameters)
    {
        foreach ($parameters as $pInfo) {
            if ($pInfo->name == OpenAPIDocument::$requestBodyKey) {
                $this->requestBody = new OpenAPIBodyParameter($pInfo);
                continue;
            }

            $this->parameters[] = new OpenAPIQueryParameter($pInfo);
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function extractResponses(ReflectionMethod $method)
    {
        $docs = PHPParseDoc($method->getDocComment());
        $returnType = $method->getReturnType();

        $response = new OpenAPIResponse($returnType ? 200 : 204);
        $response->description = $docs->return->description ?? ResponseCodeDescription(200);

        if ($returnType || isset($docs->return->type)) {
            $schema = OpenAPIBaseSchema::ExtractFromType($returnType, $docs->return->type ?? null);
            $response->setSchema($schema);
        }

        $this->addResponse($response);

        foreach ($docs->throws as $throw) {
            $excClassName = $throw->type;

            $excClass = new ReflectionClass($excClassName);
            if (!in_array("Throwable", $excClass->getInterfaceNames())) {
                continue;
            }

            $ex = $excClass->newInstance();
            if (!assert($ex instanceof Throwable)) {
                continue;
            }

            $schema = OpenAPIBaseSchema::ExtractFromTypeName($excClassName);
            $error = new OpenAPIResponse($ex->getCode());
            $error->description = $throw->description ?? ResponseCodeDescription($ex->getCode());
            $error->setSchema($schema);
            $this->addResponse($error);
        }

        foreach (OpenAPIDocument::getInstance()->genericErrors as $genericError) {
            $error = new OpenAPIResponse($genericError->code);
            $error->setSchema($genericError->schema);
            $error->description = ResponseCodeDescription($genericError->code);
            $this->addResponse($error);
        }
    }

    private function addResponse(OpenAPIResponse $response)
    {
        $this->responses["$response->code"] = $response;
    }

    public function jsonSerialize(): array
    {
        $result = [
            "tags"        => $this->tags,
            "operationId" => $this->operationId,
        ];

        if ($this->parameters) {
            $result["parameters"] = $this->parameters;
        }
        if ($this->requestBody) {
            $result["requestBody"] = $this->requestBody;
        }
        if ($this->responses) {
            $result["responses"] = $this->responses;
        }

        return $result;
    }
}
