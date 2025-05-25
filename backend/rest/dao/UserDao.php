<?php
require_once __DIR__ . '/../config.php';
class UserDao {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($data) {

        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);


        $query = "INSERT INTO " . $this->table_name . " 
                  (username, password, email, role) 
                  VALUES (:username, :password, :email, :role)";

        try {
            $stmt = $this->conn->prepare($query);


            $stmt->bindParam(":username", $data['username']);
            $stmt->bindParam(":password", $hashed_password);
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

    
    public function getById($id) {
        $query = "SELECT user_id, username, email, role, created_at 
                  FROM " . $this->table_name . " 
                  WHERE user_id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return null;
    }

   
    public function getByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE username = :username";

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

   
    public function getAll() {
        $query = "SELECT user_id, username, email, role, created_at 
                  FROM " . $this->table_name . "
                  ORDER BY created_at DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return [];
    }

  
    public function update($data) {

        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, email = :email, role = :role ";
        

        if(isset($data['password']) && !empty($data['password'])) {
            $query .= ", password = :password ";
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $query .= "WHERE user_id = :user_id";

        try {
            $stmt = $this->conn->prepare($query);


            $stmt->bindParam(":username", $data['username']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":role", $data['role']);
            $stmt->bindParam(":user_id", $data['user_id']);
            

            if(isset($data['password']) && !empty($data['password'])) {
                $stmt->bindParam(":password", $hashed_password);
            }


            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }


    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }

    
    public function verifyLogin($username, $password) {
        $user = $this->getByUsername($username);
        
        if($user && password_verify($password, $user['password'])) {

            unset($user['password']);
            return $user;
        }
        
        return false;
    }
}