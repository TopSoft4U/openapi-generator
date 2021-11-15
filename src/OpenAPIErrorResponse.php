<?php

namespace TopSoft4U\OpenAPI;

class OpenAPIErrorResponse extends OpenAPIResponse
{
    public function __construct(int $code, string $description)
    {
        parent::__construct($code);
        $this->description = $description;
    }

//    public static function AddSchema()
//    {
////        OpenAPIDocument::getInstance()
//    }

    public function jsonSerialize(): array
    {
        $result = [
            "$this->code" => [],
        ];

        if ($this->description) {
            $result["$this->code"]["description"] = $this->description;
        }

//        foreach (OpenAPIDocument::getInstance()->contentTypes as $contentType) {
//        }

        return $result;
    }
}
