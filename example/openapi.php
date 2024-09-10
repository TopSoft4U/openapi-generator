<?php

// Autoloader for library
require_once "../vendor/autoload.php";

// Import example classes manually
// Use autoloader in production
require_once "Models/TestResponse.php";
require_once "Models/TestPostRequest.php";
require_once "Models/Error400.php";

use TopSoft4U\OpenAPI\OpenAPIDocument;
use TopSoft4U\OpenAPI\OpenAPIGenericError;
use TopSoft4U\OpenAPI\OpenAPIServer;
use TopSoft4U\OpenAPI\Security\OpenAPISecurityBearer;

global $ProQoS;

$openApi = OpenAPIDocument::getInstance();
$openApi->title = "Example OpenAPI document";
$openApi->addServer(new OpenAPIServer("http://localhost"));
$openApi->addSecurity("User", new OpenAPISecurityBearer());

// You can opt out of the inheritance if you need openapi docs without that (I personally needed that to generate correct Dart classes
if (isset($_GET["with_inheritance"])) {
    $openApi->useInheritance = false;
}

$openApi->genericErrors[] = new OpenAPIGenericError(400, Error400::class);

$openApi->setDirectories(__DIR__ . DIRECTORY_SEPARATOR . "Controllers");
$openApi->process();