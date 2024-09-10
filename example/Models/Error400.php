<?php

class Error400 extends Exception implements \JsonSerializable
{
    public string $error;

    public function __construct(public string $field = "", $error = "")
    {
        parent::__construct($error);
        $this->error = $error;
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        $array["field"] = $this->field;
        $array["error"] = $this->error;

        return $array;
    }
}