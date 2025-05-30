<?php
require_once __DIR__ . '/../config.php';

class AuthDao {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function get_user_by_email($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return null;
    }

    public function get_user_by_username($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return null;
    }

    public function create_user($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password, email, role) 
                  VALUES (:username, :password, :email, :role)";

        try {
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":username", $data['username']);
            $stmt->bindParam(":password", $data['password']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":role", $data['role']);

            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }
}