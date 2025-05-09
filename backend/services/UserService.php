<?php
// services/UserService.php
require_once __DIR__ . '/../dao/UserDao.php';

class UserService {
    private $userDao;
    
    public function __construct() {
        $this->userDao = new UserDao();
    }
    
    /**
     * Get all users from the database
     * 
     * @return array List of users
     */
    public function getAllUsers() {
        return $this->userDao->getAll();
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array User data
     * @throws Exception If user not found
     */
    public function getUserById($id) {
        $user = $this->userDao->getById($id);
        if (!$user) {
            throw new Exception("User not found", 404);
        }
        return $user;
    }
    
    /**
     * Create a new user
     * 
     * @param array $data User data
     * @return array Created user data
     * @throws Exception If validation fails or creation fails
     */
    public function createUser($data) {
        // Validation
        if (empty($data['username'])) {
            throw new Exception("Username is required", 400);
        }
        
        if (empty($data['password'])) {
            throw new Exception("Password is required", 400);
        }
        
        if (empty($data['email'])) {
            throw new Exception("Email is required", 400);
        }
        
        // Check if username already exists
        $existingUser = $this->userDao->getByUsername($data['username']);
        if ($existingUser) {
            throw new Exception("Username already exists", 400);
        }
        
        // Set default role if not provided
        if (empty($data['role'])) {
            $data['role'] = 'user';
        }
        
        $userId = $this->userDao->create($data);
        if (!$userId) {
            throw new Exception("Failed to create user", 500);
        }
        
        return $this->userDao->getById($userId);
    }
    
    /**
     * Update an existing user
     * 
     * @param int $id User ID
     * @param array $data Updated user data
     * @return array Updated user data
     * @throws Exception If user not found, validation fails, or update fails
     */
    public function updateUser($id, $data) {
        // Check if user exists
        $existingUser = $this->userDao->getById($id);
        if (!$existingUser) {
            throw new Exception("User not found", 404);
        }
        
        // Validation
        if (empty($data['username'])) {
            throw new Exception("Username is required", 400);
        }
        
        if (empty($data['email'])) {
            throw new Exception("Email is required", 400);
        }
        
        // Check if username already exists (except for the current user)
        $userByUsername = $this->userDao->getByUsername($data['username']);
        if ($userByUsername && $userByUsername['user_id'] != $id) {
            throw new Exception("Username already exists", 400);
        }
        
        $data['user_id'] = $id;
        $success = $this->userDao->update($data);
        if (!$success) {
            throw new Exception("Failed to update user", 500);
        }
        
        return $this->userDao->getById($id);
    }
    
    /**
     * Delete a user
     * 
     * @param int $id User ID
     * @return array Success message
     * @throws Exception If user not found or deletion fails
     */
    public function deleteUser($id) {
        // Check if user exists
        $existingUser = $this->userDao->getById($id);
        if (!$existingUser) {
            throw new Exception("User not found", 404);
        }
        
        $success = $this->userDao->delete($id);
        if (!$success) {
            throw new Exception("Failed to delete user", 500);
        }
        
        return ["message" => "User deleted successfully"];
    }
    
    /**
     * Login user
     * 
     * @param string $username Username
     * @param string $password Password
     * @return array User data with JWT token
     * @throws Exception If login fails
     */
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            throw new Exception("Username and password are required", 400);
        }
        
        $user = $this->userDao->verifyLogin($username, $password);
        if (!$user) {
            throw new Exception("Invalid username or password", 401);
        }
        
        // Generate JWT token
        $token = $this->generateJWT($user);
        
        return [
            "user" => $user,
            "token" => $token
        ];
    }
    
    /**
     * Generate JWT token
     * 
     * @param array $user User data
     * @return string JWT token
     */
    private function generateJWT($user) {
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);
        
        $payload = json_encode([
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'exp' => time() + 3600 // Token expires in 1 hour
        ]);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        // In a real application, use a secure key stored in environment variables
        $secret = 'your_secret_key';
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
}