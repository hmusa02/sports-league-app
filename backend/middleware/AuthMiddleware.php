<?php
require_once __DIR__ . '/../rest/config.php';
require_once __DIR__ . '/../data/roles.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    
    public function verifyToken($token) {
        if(!$token) {

            error_log("AuthMiddleware: No token provided");
            Flight::halt(401, json_encode(['success' => false, 'message' => 'Missing authentication header']));
        }

        try {
            $decoded_token = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));
            Flight::set('user', $decoded_token->user);
            Flight::set('jwt_token', $token);
            error_log("AuthMiddleware: Token verified successfully for user: " . $decoded_token->user->email);
            return true;
        } catch (Exception $e) {
            error_log("AuthMiddleware: Token verification failed: " . $e->getMessage());
            Flight::halt(401, json_encode(['success' => false, 'message' => 'Invalid token: ' . $e->getMessage()]));
        }
    }

    public function checkAuthHeader() {
        $headers = getallheaders();
        $token = null;

        // Debug: log headers
        error_log("AuthMiddleware: All headers: " . json_encode($headers));

        if (isset($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
            if (strpos($auth_header, 'Bearer ') === 0) {
                $token = substr($auth_header, 7);
                error_log("AuthMiddleware: Found Bearer token: " . substr($token, 0, 10) . "...");
            } else {
                error_log("AuthMiddleware: Authorization header doesn't start with Bearer");
            }
        } elseif (isset($headers['Authentication'])) {
            $token = $headers['Authentication'];
            error_log("AuthMiddleware: Found Authentication token: " . substr($token, 0, 10) . "...");
        } else {
            error_log("AuthMiddleware: No Authorization or Authentication header found");
            
            // Check for lowercase versions (some servers normalize headers)
            if (isset($headers['authorization'])) {
                $auth_header = $headers['authorization'];
                if (strpos($auth_header, 'Bearer ') === 0) {
                    $token = substr($auth_header, 7);
                    error_log("AuthMiddleware: Found lowercase authorization Bearer token");
                }
            } elseif (isset($headers['authentication'])) {
                $token = $headers['authentication'];
                error_log("AuthMiddleware: Found lowercase authentication token");
            }
        }

        return $token;
    }

    public function authorizeRole($requiredRole) {
        $user = Flight::get('user');
        error_log("AuthMiddleware: Checking role. User: " . json_encode($user) . ", Required: " . $requiredRole);
        
        if (!$user || $user->role !== $requiredRole) {
            error_log("AuthMiddleware: Role check failed. User role: " . ($user ? $user->role : 'null'));
            Flight::halt(403, json_encode(['success' => false, 'message' => 'Access denied: insufficient privileges']));
        }
        
        error_log("AuthMiddleware: Role check passed");
    }

    public function authorizeRoles($roles) {
        $user = Flight::get('user');
        error_log("AuthMiddleware: Checking roles. User: " . json_encode($user) . ", Required roles: " . json_encode($roles));
        
        if (!$user || !in_array($user->role, $roles)) {
            error_log("AuthMiddleware: Roles check failed. User role: " . ($user ? $user->role : 'null'));
            Flight::halt(403, json_encode(['success' => false, 'message' => 'Forbidden: role not allowed']));
        }
        
        error_log("AuthMiddleware: Roles check passed");
    }

    public function authorizePermission($permission) {
        $user = Flight::get('user');
        error_log("AuthMiddleware: Checking permission. User: " . json_encode($user) . ", Required permission: " . $permission);
        
        if (!$user || !isset($user->permissions) || !in_array($permission, $user->permissions)) {
            error_log("AuthMiddleware: Permission check failed");
            Flight::halt(403, json_encode(['success' => false, 'message' => 'Access denied: permission missing']));
        }
        
        error_log("AuthMiddleware: Permission check passed");
    }


    public function isAdmin() {
        $user = Flight::get('user');
        return $user && $user->role === 'admin';
    }


    public function isAuth() {
        $user = Flight::get('user');
        return $user !== null;
    }
}