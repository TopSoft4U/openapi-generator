<?php

class Error400 extends Exception implements \JsonSerializable
{
    public string $field;
    public string $error;

    public function __construct($field = "", $error = "")
    {
        parent::__construct($error);

        $this->field = $field;
        $this->error = $error;
    }

    public function jsonSerialize(): array
    {
        $array["field"] = $this->field;
        $array["error"] = $this->error;

        return $array;
    }
}