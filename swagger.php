<?php
require 'vendor/autoload.php';

// Set up Swagger scanning configuration
$openapi = \OpenApi\Generator::scan([
    __DIR__ . '/routes',
    __DIR__ . '/services'
]);

// Output OpenAPI specification as JSON
header('Content-Type: application/json');
echo $openapi->toJson();