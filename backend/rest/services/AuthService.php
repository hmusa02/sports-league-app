<?php
require_once __DIR__ . '/../dao/AuthDao.php';
require_once __DIR__ . '/../config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService {
    private $auth_dao;

    public function __construct() {
        $this->auth_dao = new AuthDao();
    }

    public function register($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email format.'];
        }

        if (strlen($data['password']) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters long.'];
        }

        $email_exists = $this->auth_dao->get_user_by_email($data['email']);
        if($email_exists) {
            return ['success' => false, 'error' => 'Email already registered.'];
        }

        if (!isset($data['role'])) {
            $data['role'] = 'user';
        }

        if (!isset($data['username'])) {
            $data['username'] = explode('@', $data['email'])[0];
        }

        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $user_id = $this->auth_dao->create_user($data);

        if ($user_id) {
            unset($data['password']);
            $data['user_id'] = $user_id;
            return ['success' => true, 'data' => $data];
        }

        return ['success' => false, 'error' => 'Registration failed.'];
    }

    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $user = $this->auth_dao->get_user_by_email($data['email']);
        if(!$user) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        if(!password_verify($data['password'], $user['password'])) {
            return ['success' => false, 'error' => 'Invalid email or password.'];
        }

        unset($user['password']);

        $jwt_payload = [
            'user' => $user,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24)
        ];

        $token = JWT::encode(
            $jwt_payload,
            Config::JWT_SECRET(),
            'HS256'
        );

        return ['success' => true, 'data' => array_merge($user, ['token' => $token])];
    }
}