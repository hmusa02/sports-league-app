<?php
// Fix the base path for MAMP/Apache
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = str_replace('/index.php', '', $script_name);

// Remove base path from request URI for proper routing
if (strpos($request_uri, $base_path) === 0) {
    $_SERVER['REQUEST_URI'] = substr($request_uri, strlen($base_path));
}

// Import security middleware first
require_once __DIR__ . '/middleware/SecurityMiddleware.php';

// Apply security checks - this sets headers and validates requests
SecurityMiddleware::applySecurityChecks();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Import route classes
require_once __DIR__ . '/rest/routes/AuthRoutes.php';
require_once __DIR__ . '/rest/routes/UserRoutes.php';
require_once __DIR__ . '/rest/routes/TeamRoutes.php';
require_once __DIR__ . '/rest/routes/PlayerRoutes.php';
require_once __DIR__ . '/rest/routes/MatchRoutes.php';
require_once __DIR__ . '/rest/routes/StatisticRoutes.php';

// Import middleware
require_once __DIR__ . '/middleware/AuthMiddleware.php';

// Set Flight base URL for MAMP
Flight::set('flight.base_url', $base_path);

// Register Auth Middleware
Flight::register('authMiddleware', 'AuthMiddleware');

// Auth Middleware for all routes except public ones
Flight::route('/*', function() {
    $url = Flight::request()->url;
    
    // Public routes that don't need authentication
    if (strpos($url, '/api/auth/login') === 0 || 
        strpos($url, '/api/auth/register') === 0 ||
        strpos($url, '/api/health') === 0 ||
        strpos($url, '/api/test-db') === 0 ||
        strpos($url, '/api/statistics/top-scorers') === 0 ||
        (strpos($url, '/api/statistics/player/') === 0 && strpos($url, '/summary') !== false) ||
        strpos($url, '/api/players') === 0 ||
        strpos($url, '/api/matches') === 0 ||
        $url === '/' ||
        $url === '/api') {
        return true;
    } else {
        // Protected routes - require JWT token
        $authMiddleware = new AuthMiddleware();
        $token = $authMiddleware->checkAuthHeader();
        $authMiddleware->verifyToken($token);
        return true;
    }
});

// Register routes from route classes
$authRoutes = new AuthRoutes();
$authRoutes->registerRoutes(Flight::app());

$userRoutes = new UserRoutes();
$userRoutes->registerRoutes(Flight::app());

$teamRoutes = new TeamRoutes();
$teamRoutes->registerRoutes(Flight::app());

$playerRoutes = new PlayerRoutes();
$playerRoutes->registerRoutes(Flight::app());

$matchRoutes = new MatchRoutes();
$matchRoutes->registerRoutes(Flight::app());

$statisticRoutes = new StatisticRoutes();
$statisticRoutes->registerRoutes(Flight::app());

// Start Flight
Flight::start();