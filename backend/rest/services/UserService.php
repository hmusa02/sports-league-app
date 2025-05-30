<?php
require_once __DIR__ . '/../dao/UserDao.php';

class UserService {
    private $userDao;

    public function __construct() {
        $this->userDao = new UserDao();
    }

    public function registerUser($data) {
        // Validation
        if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            throw new Exception('Username, password and email are required');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        if (strlen($data['password']) < 6) {
            throw new Exception('Password must be at least 6 characters long');
        }

        // Check if user already exists
        $existingUser = $this->userDao->getByUsername($data['username']);
        if ($existingUser) {
            throw new Exception('Username already exists');
        }

        // Set default role if not provided
        if (!isset($data['role'])) {
            $data['role'] = 'user';
        }

        // Validate role
        if (!in_array($data['role'], ['user', 'admin'])) {
            throw new Exception('Invalid role. Must be user or admin');
        }

        return $this->userDao->create($data);
    }

    public function loginUser($username, $password) {
        if (empty($username) || empty($password)) {
            throw new Exception('Username and password are required');
        }

        $user = $this->userDao->verifyLogin($username, $password);
        if (!$user) {
            throw new Exception('Invalid username or password');
        }

        return $user;
    }

    public function getUserById($id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid user ID');
        }

        $user = $this->userDao->getById($id);
        if (!$user) {
            throw new Exception('User not found');
        }

        return $user;
    }

    public function getAllUsers() {
        return $this->userDao->getAll();
    }

    public function updateUser($data) {
        if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
            throw new Exception('Valid user ID is required');
        }

        // Check if user exists
        $existingUser = $this->userDao->getById($data['user_id']);
        if (!$existingUser) {
            throw new Exception('User not found');
        }

        // Validation
        if (empty($data['username']) || empty($data['email'])) {
            throw new Exception('Username and email are required');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Check password length if provided
        if (isset($data['password']) && !empty($data['password']) && strlen($data['password']) < 6) {
            throw new Exception('Password must be at least 6 characters long');
        }

        // Validate role
        if (isset($data['role']) && !in_array($data['role'], ['user', 'admin'])) {
            throw new Exception('Invalid role. Must be user or admin');
        }

        return $this->userDao->update($data);
    }

    public function deleteUser($id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid user ID');
        }

        $user = $this->userDao->getById($id);
        if (!$user) {
            throw new Exception('User not found');
        }

        return $this->userDao->delete($id);
    }

    public function getUserByUsername($username) {
        if (empty($username)) {
            throw new Exception('Username is required');
        }

        return $this->userDao->getByUsername($username);
    }
}