<?php
require_once __DIR__ . '/../services/AuthService.php';

class AuthRoutes {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function registerRoutes($app) {
        $app->route('POST /api/auth/register', function() {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $this->authService->register($data);
                
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

        $app->route('POST /api/auth/login', function() {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $this->authService->login($data);
                
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

        $app->route('POST /api/auth/logout', function() {
            Flight::json([
                'success' => true,
                'message' => 'User logged out successfully'
            ]);
        });
    }
}