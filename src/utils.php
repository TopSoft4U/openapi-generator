<?php

use TopSoft4U\PhpDocParser\PHPDocParser;
use TopSoft4U\PhpDocParser\PHPDocResult;

function PHPParseDoc(string $docComment): PHPDocResult
{
    $parser = new PhpDocParser();
    return $parser->parse($docComment);
}

function IsBuiltin(string $type): bool
{
    return in_array($type, ["string", "float", "bool", "boolean", "int", "integer", "null", "object", "iterable", "mixed", "array", "double"]);
}

$codeMessages = [
    200 => "OK",
    400 => "Bad Request",
    401 => "Unauthorized",
    402 => "Payment Required ",
    403 => "Forbidden",
    404 => "Not Found",
    405 => "Method Not Allowed",
    406 => "Not Acceptable",
    407 => "Proxy Authentication Required",
    408 => "Request Timeout",
    409 => "Conflict",
    410 => "Gone",
    411 => "Length Required",
    412 => "Precondition Failed",
    413 => "Payload Too Large",
    414 => "URI Too Long",
    415 => "Unsupported Media Type",
    416 => "Range Not Satisfiable",
    417 => "Expectation Failed",
    418 => "I'm a teapot",
    421 => "Misdirected Request",
    422 => "Unprocessable Entity (WebDAV)",
    423 => "Locked (WebDAV)",
    424 => "Failed Dependency (WebDAV)",
    425 => "Too Early ",
    426 => "Upgrade Required",
    428 => "Precondition Required",
    429 => "Too Many Requests",
    431 => "Request Header Fields Too Large",
    451 => "Unavailable For Legal Reasons",
    500 => "Internal Server Error",
];

function ResponseCodeDescription(int $code): string
{
    global $codeMessages;
    return $codeMessages[$code] ?? "Error $code";
}
