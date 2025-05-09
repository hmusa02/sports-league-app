<?php
// index.php
require 'vendor/autoload.php';
require_once __DIR__ . '/config.php';

// CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Simplify JSON handling
Flight::route('*', function(){
    $request = Flight::request();
    $method = $request->method;
    
    // Parse JSON for POST and PUT requests
    if ($method == 'POST' || $method == 'PUT') {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $rawData = file_get_contents('php://input');
            if (!empty($rawData)) {
                $jsonData = json_decode($rawData, true);
                if ($jsonData) {
                    Flight::set('jsonData', $jsonData);
                }
            }
        }
    }
});

// Helper to get JSON data
Flight::map('getJsonData', function() {
    return Flight::get('jsonData', []);
});

// JSON response helper
Flight::map('json', function($data, $code = 200) {
    Flight::response()
        ->status($code)
        ->header('Content-Type', 'application/json')
        ->write(json_encode($data, JSON_PRETTY_PRINT))
        ->send();
});

// Error handler
Flight::map('error', function($ex) {
    $code = $ex->getCode() ?: 500;
    Flight::json([
        'error' => $ex->getMessage()
    ], $code);
});

// Load routes
require_once __DIR__ . '/routes/team_routes.php';
require_once __DIR__ . '/routes/player_routes.php';
require_once __DIR__ . '/routes/match_routes.php';
require_once __DIR__ . '/routes/statistic_routes.php';
require_once __DIR__ . '/routes/user_routes.php';

Flight::start();