<?php

namespace TopSoft4U\OpenAPI;

use JsonSerializable;
use TopSoft4U\OpenAPI\Parameters\OpenAPIBodyParameter;
use TopSoft4U\OpenAPI\Parameters\OpenAPIQueryParameter;
use TopSoft4U\OpenAPI\Schema\OpenAPIBaseSchema;
use ReflectionClass;
use ReflectionMethod;
use Throwable;
use TopSoft4U\OpenAPI\Schema\OpenAPISchemaTyped;

class OpenAPIPath implements JsonSerializable
{
    public string $requestType;
    public string $uri;
    public ?string $description = null;

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

        $openApiDocument = OpenAPIDocument::getInstance();
        if ($openApiDocument->overridePathUri) {
            $func = $openApiDocument->overridePathUri;
            $this->uri = $func($method);
        } else
            $this->uri = "/$group/$action";

        if ($openApiDocument->overrideOperationId) {
            $func = $openApiDocument->overrideOperationId;
            $this->operationId = $func($method);
        } else
            $this->operationId = "$requestType$this->uri";

        $this->tags = [$group];

        // Check if method has any beforeAction
        $this->parseBeforeActionMethod($method);

        $this->extractParams($method->getParameters());
        $this->extractResponses($method);
    }

    /**
     * @param \ReflectionParameter[] $parameters
     *
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
        $this->description = $docs->description;

        $returnType = $method->getReturnType();

        $statusCode = 200;
        if (!$returnType || $returnType->getName() == "void")
            $statusCode = 204;

        $hasReturnType = $returnType && $statusCode == 200;

        $response = new OpenAPIResponse($statusCode);
        $response->description = $docs->return->description ?? ResponseCodeDescription($statusCode);

        if ($hasReturnType || isset($docs->return->type)) {
            $schema = OpenAPIBaseSchema::ExtractFromType($returnType, $docs->return->type ?? null);
            $response->setSchema($schema);
        }

        $this->addResponse($response);

        if ($hasReturnType && $returnType->allowsNull()) {
            $statusCode = 204;
            $response = new OpenAPIResponse($statusCode);
            $response->description = ResponseCodeDescription($statusCode);
            $this->addResponse($response);
        }

        $this->extractExceptions($docs);

        foreach (OpenAPIDocument::getInstance()->genericErrors as $genericError) {
            $error = new OpenAPIResponse($genericError->code);
            $error->setSchema($genericError->schema);
            $error->description = ResponseCodeDescription($genericError->code);
            $this->addResponse($error);
        }

        foreach ($docs->custom as $customPHPDocNode) {
            if ($customPHPDocNode->tagName === "@OpenAPICustomResponse") {
                [$code, $contentType, $type] = explode(" ", $customPHPDocNode->value, 3);
                $statusCode = (int)$code;
                $contentType = str_replace("\/", "/", $contentType);

                $response = new OpenAPIResponse($statusCode);
                $response->description = ResponseCodeDescription($statusCode);
                $response->contentType = $contentType;

                $schema = new OpenAPISchemaTyped("string");
                $schema->format = $type;

                $response->setSchema($schema);
                $this->addResponse($response);
            }
        }
    }

    private function addResponse(OpenAPIResponse $response)
    {
        // Don't add duplicate responses
        foreach ($this->responses as $r) {
            if ($r->code == $response->code)
                return;
        }

        $this->responses[] = $response;
    }

    public function jsonSerialize(): array
    {
        $result = [
            "tags"        => $this->tags,
            "operationId" => $this->operationId,
        ];

        if ($this->parameters)
            $result["parameters"] = $this->parameters;

        if ($this->requestBody)
            $result["requestBody"] = $this->requestBody;

        if ($this->description)
            $result["description"] = $this->description;

        if ($this->responses) {
            $result["responses"] = [];
            foreach ($this->responses as $response) {
                $code = (string)$response->code;
                $result["responses"][$code] ??= [];
                if ($response->description)
                    $result["responses"][$code]["description"] = $response->description;

                if ($schema = $response->getSchema()) {
                    foreach (OpenAPIDocument::getInstance()->contentTypes as $contentType) {
                        $result["responses"][$code]["content"][$response->contentType ?? $contentType]["schema"] = $schema;
                        $contentType = $response->contentType ?? $contentType;
                        $result["responses"][$code]["content"][$contentType]["schema"] = $schema;
                    }
                }
            }
        }

        return $result;
    }

    public function extractExceptions(\TopSoft4U\PhpDocParser\PHPDocResult $docs): void
    {
        foreach ($docs->throws as $throw) {
            $excClassName = $throw->type;

            $excClass = new ReflectionClass($excClassName);
            if (!in_array("Throwable", $excClass->getInterfaceNames()))
                continue;

            if (!$excClass->isInstantiable())
                continue;

            // Not supported constructor type
            if (!$excClass->getConstructor() || $excClass->getConstructor()->getNumberOfRequiredParameters() > 0)
                continue;

            $ex = $excClass->newInstance();
            if (!assert($ex instanceof Throwable)) {
                continue;
            }

            if ($ex->getCode() < 100)
                continue;

            $excSchema = OpenAPIBaseSchema::ExtractFromTypeName($excClassName);
            $exc = new OpenAPIResponse($ex->getCode());
            $exc->description = $throw->description ?? ResponseCodeDescription($ex->getCode());
            $exc->setSchema($excSchema);
            $this->addResponse($exc);
        }
    }

    /**
     * Parses the before action method, if it exists, and extracts any documented exceptions.
     */
    public function parseBeforeActionMethod(ReflectionMethod $method): void
    {
        $openAPIDocument = OpenAPIDocument::getInstance();
        if (!$openAPIDocument->beforeActionSuffix)
            return;

        $beforeActionMethod = $method->name . $openAPIDocument->beforeActionSuffix;
        $methodClass = $method->class;

        if (!method_exists($methodClass, $beforeActionMethod))
            return;

        $beforeMethod = new ReflectionMethod($methodClass, $beforeActionMethod);
        $docString = $beforeMethod->getDocComment();
        $docs = PHPParseDoc($docString);

        $this->extractExceptions($docs);
    }
}
