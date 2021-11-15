<?php

namespace TopSoft4U\OpenAPI\Security;

require_once "OpenAPISecurity.php";

class OpenAPISecurityBearer extends OpenAPISecurity
{
    public function jsonSerialize(): array
    {
        return [
            "type"   => "http",
            "scheme" => "bearer",
        ];
    }
}
