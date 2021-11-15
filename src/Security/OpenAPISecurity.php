<?php

namespace TopSoft4U\OpenAPI\Security;

use JsonSerializable;

abstract class OpenAPISecurity implements JsonSerializable
{
    abstract public function jsonSerialize(): array;
}
