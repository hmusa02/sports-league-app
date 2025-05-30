<?php
// Fix the base path for MAMP/Apache
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = str_replace('/index.php', '', $script_name);

// Remove base path from request URI for proper routing
if (strpos($request_uri, $base_path) === 0) {
    $_SERVER['REQUEST_URI'] = substr($request_uri, strlen($base_path));
}

// Enable CORS for frontend
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Authentication, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Import Auth components
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/rest/services/AuthService.php';
require_once __DIR__ . '/data/roles.php';

// Import All Services
require_once 'rest/services/UserService.php';
require_once 'rest/services/TeamService.php';
require_once 'rest/services/PlayerService.php';
require_once 'rest/services/MatchService.php';
require_once 'rest/services/StatisticService.php';

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

// AUTH ROUTES
Flight::route('POST /api/auth/register', function() {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $authService = new AuthService();
        $response = $authService->register($data);
        
        if ($response['success']) {
            Flight::json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $response['data']
            ], 201);
        } else {
            Flight::json([
                'success' => false,
                'message' => $response['error']
            ], 400);
        }
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

Flight::route('POST /api/auth/login', function() {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $authService = new AuthService();
        $response = $authService->login($data);
        
        if ($response['success']) {
            Flight::json([
                'success' => true,
                'message' => 'User logged in successfully',
                'data' => $response['data']
            ]);
        } else {
            Flight::json([
                'success' => false,
                'message' => $response['error']
            ], 401);
        }
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

// USER ROUTES
Flight::route('POST /api/users/register', function() {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        require_once __DIR__ . '/rest/services/UserService.php';
        $userService = new UserService();
        
        $userId = $userService->registerUser($data);
        
        Flight::json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => ['user_id' => $userId]
        ], 201);
        
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
});

Flight::route('GET /api/users', function() {
    try {
        // Koristi user koji je već postavljen od middleware-a
        $user = Flight::get('user');
        if (!$user || $user->role !== 'admin') {
            Flight::json(['success' => false, 'message' => 'Admin access required'], 403);
            return;
        }
        
        require_once __DIR__ . '/rest/services/UserService.php';
        $userService = new UserService();
        
        $users = $userService->getAllUsers();
        
        Flight::json([
            'success' => true,
            'data' => $users
        ]);
        
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

// TEAM ROUTES
Flight::route('GET /api/teams', function() {
    try {
        // Koristi user koji je već postavljen od middleware-a
        $user = Flight::get('user');
        if (!$user || !in_array($user->role, ['admin', 'user'])) {
            Flight::json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        require_once __DIR__ . '/rest/services/TeamService.php';
        $teamService = new TeamService();
        
        $teams = $teamService->getAllTeams();
        
        Flight::json([
            'success' => true,
            'data' => $teams
        ]);
        
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

Flight::route('POST /api/teams', function() {
    try {
        // Koristi user koji je već postavljen od middleware-a
        $user = Flight::get('user');
        if (!$user || $user->role !== 'admin') {
            Flight::json(['success' => false, 'message' => 'Admin access required'], 403);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        require_once __DIR__ . '/rest/services/TeamService.php';
        $teamService = new TeamService();
        
        $teamId = $teamService->createTeam($data);
        
        Flight::json([
            'success' => true,
            'message' => 'Team created successfully',
            'data' => ['team_id' => $teamId]
        ], 201);
        
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
});

// PLAYER ROUTES
Flight::route('GET /api/players', function() {
    try {
        // Koristi user koji je već postavljen od middleware-a
        $user = Flight::get('user');
        if (!$user || !in_array($user->role, ['admin', 'user'])) {
            Flight::json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        require_once __DIR__ . '/rest/services/PlayerService.php';
        $playerService = new PlayerService();
        
        $players = $playerService->getAllPlayers();
        
        Flight::json([
            'success' => true,
            'data' => $players
        ]);
        
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

Flight::route('POST /api/players', function() {
    try {
        // Koristi user koji je već postavljen od middleware-a
        $user = Flight::get('user');
        if (!$user || $user->role !== 'admin') {
            Flight::json(['success' => false, 'message' => 'Admin access required'], 403);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        require_once __DIR__ . '/rest/services/PlayerService.php';
        $playerService = new PlayerService();
        
        $playerId = $playerService->createPlayer($data);
        
        Flight::json([
            'success' => true,
            'message' => 'Player created successfully',
            'data' => ['player_id' => $playerId]
        ], 201);
        
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
});

// MATCH ROUTES
Flight::route('GET /api/matches', function() {
    try {
        // Koristi user koji je već postavljen od middleware-a
        $user = Flight::get('user');
        if (!$user || !in_array($user->role, ['admin', 'user'])) {
            Flight::json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        require_once __DIR__ . '/rest/services/MatchService.php';
        $matchService = new MatchService();
        
        $matches = $matchService->getAllMatches();
        
        Flight::json([
            'success' => true,
            'data' => $matches
        ]);
        
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

Flight::route('POST /api/matches', function() {
    try {
        // Koristi user koji je već postavljen od middleware-a - KLJUČNA PROMJENA!
        $user = Flight::get('user');
        if (!$user || $user->role !== 'admin') {
            Flight::json(['success' => false, 'message' => 'Admin access required'], 403);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        require_once __DIR__ . '/rest/services/MatchService.php';
        $matchService = new MatchService();
        
        $matchId = $matchService->createMatch($data);
        
        Flight::json([
            'success' => true,
            'message' => 'Match created successfully',
            'data' => ['match_id' => $matchId]
        ], 201);
        
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
});

// STATISTICS ROUTES
Flight::route('GET /api/statistics', function() {
    try {
        // Koristi user koji je već postavljen od middleware-a
        $user = Flight::get('user');
        if (!$user || !in_array($user->role, ['admin', 'user'])) {
            Flight::json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        require_once __DIR__ . '/rest/services/StatisticService.php';
        $statisticService = new StatisticService();
        
        $statistics = $statisticService->getAllStatistics();
        
        Flight::json([
            'success' => true,
            'data' => $statistics
        ]);
        
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});

Flight::route('POST /api/statistics', function() {
    try {
        // Koristi user koji je već postavljen od middleware-a
        $user = Flight::get('user');
        if (!$user || $user->role !== 'admin') {
            Flight::json(['success' => false, 'message' => 'Admin access required'], 403);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        require_once __DIR__ . '/rest/services/StatisticService.php';
        $statisticService = new StatisticService();
        
        $statisticId = $statisticService->createStatistic($data);
        
        Flight::json([
            'success' => true,
            'message' => 'Statistic created successfully',
            'data' => ['stat_id' => $statisticId]
        ], 201);
        
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
});

// UTILITY ROUTES
Flight::route('GET /', function() use ($base_path) {
    Flight::json([
        'success' => true,
        'message' => 'Sports League API is running',
        'timestamp' => date('Y-m-d H:i:s'),
        'base_path' => $base_path,
        'endpoints' => [
            'health' => $base_path . '/api/health',
            'api_info' => $base_path . '/api',
            'auth_login' => $base_path . '/api/auth/login',
            'auth_register' => $base_path . '/api/auth/register',
            'users' => $base_path . '/api/users',
            'teams' => $base_path . '/api/teams',
            'players' => $base_path . '/api/players',
            'matches' => $base_path . '/api/matches',
            'statistics' => $base_path . '/api/statistics'
        ]
    ]);
});

// Health check endpoint
Flight::route('GET /api/health', function() use ($base_path) {
    Flight::json([
        'success' => true,
        'message' => 'Sports League API is running',
        'timestamp' => date('Y-m-d H:i:s'),
        'server' => 'MAMP/Apache',
        'base_path' => $base_path
    ]);
});

// API Info endpoint
Flight::route('GET /api', function() use ($base_path) {
    Flight::json([
        'success' => true,
        'message' => 'Sports League API',
        'version' => '1.0.0',
        'server' => 'MAMP/Apache',
        'base_url' => $base_path,
        'endpoints' => [
            'health' => $base_path . '/api/health',
            'auth_login' => $base_path . '/api/auth/login',
            'auth_register' => $base_path . '/api/auth/register',
            'users' => $base_path . '/api/users',
            'teams' => $base_path . '/api/teams',
            'players' => $base_path . '/api/players',
            'matches' => $base_path . '/api/matches',
            'statistics' => $base_path . '/api/statistics'
        ]
    ]);
});

// Test database connection
Flight::route('GET /api/test-db', function() {
    try {
        require_once __DIR__ . '/rest/config.php';
        $database = new Database();
        $conn = $database->getConnection();
        
        if ($conn) {
            Flight::json([
                'success' => true,
                'message' => 'Database connection successful',
                'server' => 'MAMP'
            ]);
        } else {
            Flight::json([
                'success' => false,
                'message' => 'Database connection failed'
            ], 500);
        }
    } catch (Exception $e) {
        Flight::json([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ], 500);
    }
});

// Error handler
Flight::map('error', function(Exception $ex) {
    Flight::json([
        'success' => false,
        'message' => $ex->getMessage(),
        'error' => 'Internal server error',
        'server' => 'MAMP'
    ], 500);
});

// Not found handler
Flight::map('notFound', function() {
    Flight::json([
        'success' => false,
        'message' => 'Route not found',
        'server' => 'MAMP',
        'requested_uri' => $_SERVER['REQUEST_URI']
    ], 404);
});

// Start FlightPHP
Flight::start();