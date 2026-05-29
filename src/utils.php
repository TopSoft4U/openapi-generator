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

function ResolveClassName(\ReflectionClass $context, string $name): string
{
    if ($name === "" || $name === "mixed" || $name === "null" || $name === "object" || str_starts_with($name, "?")) {
        return $name;
    }

    if (str_starts_with($name, "\\")) {
        return ltrim($name, "\\");
    }

    if (class_exists($name) || interface_exists($name)) {
        return $name;
    }

    $imports = ResolveClassName_GetUseImports($context->getFileName());
    if (isset($imports[$name])) {
        return $imports[$name];
    }

    $firstPart = explode("\\", $name)[0];
    if (isset($imports[$firstPart])) {
        $resolved = $imports[$firstPart] . mb_substr($name, mb_strlen($firstPart));
        if (class_exists($resolved) || interface_exists($resolved)) {
            return $resolved;
        }
    }

    $namespace = $context->getNamespaceName();
    if ($namespace) {
        $fqcn = $namespace . "\\" . $name;
        if (class_exists($fqcn) || interface_exists($fqcn)) {
            return $fqcn;
        }
    }

    return $name;
}

function ResolveClassName_GetUseImports(?string $fileName): array
{
    static $cache = [];
    if ($fileName === null) {
        return [];
    }
    if (isset($cache[$fileName])) {
        return $cache[$fileName];
    }

    $imports = [];
    $content = file_get_contents($fileName);
    if ($content === false) {
        return $cache[$fileName] = [];
    }

    $tokens = token_get_all($content);
    $inUse = false;
    $useBuffer = [];
    $useAlias = null;

    for ($i = 0, $count = count($tokens); $i < $count; $i++) {
        $token = $tokens[$i];

        if (!is_array($token)) {
            if ($token === ";" && $inUse) {
                $fqn = implode("", $useBuffer);
                if ($useAlias !== null) {
                    $imports[$useAlias] = ltrim($fqn, "\\");
                } else {
                    $parts = explode("\\", $fqn);
                    $imports[end($parts)] = ltrim($fqn, "\\");
                }
                $inUse = false;
                $useBuffer = [];
                $useAlias = null;
            }
            continue;
        }

        if ($token[0] === T_USE && !$inUse) {
            $inUse = true;
            $useBuffer = [];
            $useAlias = null;
            continue;
        }

        if (!$inUse) {
            continue;
        }

        if ($token[0] === T_AS) {
            $useAlias = "";
            continue;
        }

        if ($token[0] === T_NAME_QUALIFIED || $token[0] === T_NAME_FULLY_QUALIFIED || $token[0] === T_STRING || $token[0] === T_NS_SEPARATOR) {
            if ($useAlias !== null && $token[0] === T_STRING) {
                $useAlias = $token[1];
            } else {
                $useBuffer[] = $token[1];
            }
        }

        if ($token[0] === T_CLASS || $token[0] === T_FUNCTION) {
            break;
        }
    }

    return $cache[$fileName] = $imports;
}

$codeMessages = [
    200 => "OK",
    204 => "Empty",
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

    $prefix = $code >= 400 ? "Error" : "Status";
    return $codeMessages[$code] ?? "$prefix $code";
}
