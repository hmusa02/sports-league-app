<?php
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../../utils/ValidationHelper.php';
require_once __DIR__ . '/../../middleware/SecurityMiddleware.php';

class AuthRoutes {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function registerRoutes($app) {
        $app->route('POST /api/auth/register', function() {
            try {
                // Apply specific rate limiting for registration
                SecurityMiddleware::checkRateLimit('register');
                
                $rawData = file_get_contents('php://input');
                
                if (empty($rawData)) {
                    Flight::json([
                        'success' => false,
                        'message' => 'No data provided'
                    ], 400);
                    return;
                }
                
                $data = json_decode($rawData, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Invalid JSON data'
                    ], 400);
                    return;
                }
                
                // Validate and sanitize data
                $validation = ValidationHelper::validateUserRegistration($data);
                
                if (!$validation['valid']) {
                    SecurityMiddleware::logSecurityEvent('invalid_registration_data', [
                        'errors' => $validation['errors']
                    ]);
                    
                    Flight::json([
                        'success' => false,
                        'message' => 'Validation errors: ' . implode(', ', $validation['errors'])
                    ], 400);
                    return;
                }
                
                $response = $this->authService->register($validation['data']);
                
                if ($response['success']) {
                    SecurityMiddleware::logSecurityEvent('successful_registration', [
                        'email' => $validation['data']['email']
                    ]);
                    
                    Flight::json([
                        'success' => true,
                        'message' => 'User registered successfully',
                        'data' => $response['data']
                    ], 201);
                } else {
                    SecurityMiddleware::logSecurityEvent('failed_registration', [
                        'email' => $validation['data']['email'] ?? '',
                        'error' => $response['error']
                    ]);
                    
                    Flight::json([
                        'success' => false,
                        'message' => $response['error']
                    ], 400);
                }
            } catch (Exception $e) {
                SecurityMiddleware::logSecurityEvent('registration_exception', [
                    'error' => $e->getMessage()
                ]);
                
                error_log('Registration error: ' . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => 'Registration failed'
                ], 500);
            }
        });

        $app->route('POST /api/auth/login', function() {
            try {
                // Apply specific rate limiting for login
                SecurityMiddleware::checkRateLimit('login');
                
                $rawData = file_get_contents('php://input');
                
                if (empty($rawData)) {
                    Flight::json([
                        'success' => false,
                        'message' => 'No data provided'
                    ], 400);
                    return;
                }
                
                $data = json_decode($rawData, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Invalid JSON data'
                    ], 400);
                    return;
                }
                
                // Validate email
                $emailValidation = ValidationHelper::validateEmail($data['email'] ?? '');
                if (!$emailValidation['valid']) {
                    SecurityMiddleware::logSecurityEvent('invalid_login_email', [
                        'email' => $data['email'] ?? '',
                        'error' => $emailValidation['message']
                    ]);
                    
                    Flight::json([
                        'success' => false,
                        'message' => $emailValidation['message']
                    ], 400);
                    return;
                }
                
                // Validate password
                $passwordValidation = ValidationHelper::validatePassword($data['password'] ?? '');
                if (!$passwordValidation['valid']) {
                    SecurityMiddleware::logSecurityEvent('invalid_login_password', [
                        'email' => $emailValidation['email']
                    ]);
                    
                    Flight::json([
                        'success' => false,
                        'message' => $passwordValidation['message']
                    ], 400);
                    return;
                }
                
                $sanitizedData = [
                    'email' => $emailValidation['email'],
                    'password' => $data['password'] // Don't sanitize password
                ];
                
                $response = $this->authService->login($sanitizedData);
                
                if ($response['success']) {
                    SecurityMiddleware::logSecurityEvent('successful_login', [
                        'email' => $sanitizedData['email'],
                        'user_id' => $response['user']['user_id'] ?? null
                    ]);
                    
                    Flight::json([
                        'success' => true,
                        'message' => 'User logged in successfully',
                        'token' => $response['token'],
                        'user' => $response['user']
                    ]);
                } else {
                    SecurityMiddleware::logSecurityEvent('failed_login', [
                        'email' => $sanitizedData['email'],
                        'error' => $response['error']
                    ]);
                    
                    Flight::json([
                        'success' => false,
                        'message' => $response['error']
                    ], 401);
                }
            } catch (Exception $e) {
                SecurityMiddleware::logSecurityEvent('login_exception', [
                    'email' => $sanitizedData['email'] ?? '',
                    'error' => $e->getMessage()
                ]);
                
                error_log('Login error: ' . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => 'Login failed'
                ], 500);
            }
        });

        $app->route('POST /api/auth/logout', function() {
            SecurityMiddleware::logSecurityEvent('logout', []);
            
            Flight::json([
                'success' => true,
                'message' => 'User logged out successfully'
            ]);
        });
    }
}