<?php
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../services/StatisticService.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserRoutes {
    private $userService;
    private $authMiddleware;

    public function __construct() {
        $this->userService = new UserService();
        $this->authMiddleware = new AuthMiddleware();
    }

    public function registerRoutes($app) {
        // Get all users (admin only)
        $app->route('GET /api/users', function() {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRole('admin');
                
                $users = $this->userService->getAllUsers();
                
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

        // Register new user
        $app->route('POST /api/users/register', function() {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $userId = $this->userService->registerUser($data);
                
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

        // Get user profile
        $app->route('GET /api/users/@id', function($id) {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                
                // Users can only view their own profile unless they're admin
                $tokenData = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));
                if ($tokenData->user->role !== 'admin' && $tokenData->user->id !== $id) {
                    throw new Exception('Unauthorized to view this profile');
                }
                
                $user = $this->userService->getUserById($id);
                
                Flight::json([
                    'success' => true,
                    'data' => $user
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
        });

        // Update user profile
        $app->route('PUT /api/users/@id', function($id) {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                
                // Users can only update their own profile unless they're admin
                $tokenData = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));
                if ($tokenData->user->role !== 'admin' && $tokenData->user->id !== $id) {
                    throw new Exception('Unauthorized to update this profile');
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                $data['user_id'] = $id;
                
                $result = $this->userService->updateUser($data);
                
                Flight::json([
                    'success' => true,
                    'message' => 'User updated successfully'
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // Delete user (admin only)
        $app->route('DELETE /api/users/@id', function($id) {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRole('admin');
                
                $result = $this->userService->deleteUser($id);
                
                Flight::json([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });
    }
}