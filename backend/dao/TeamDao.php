<?php
require_once __DIR__ . '/../config.php';
class TeamDao {
    private $conn;
    private $table_name = "teams";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

   
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (team_name, city, coach_id) 
                  VALUES (:team_name, :city, :coach_id)";

        try {
            $stmt = $this->conn->prepare($query);


            $stmt->bindParam(":team_name", $data['team_name']);
            $stmt->bindParam(":city", $data['city']);
            $stmt->bindParam(":coach_id", $data['coach_id']);


            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }

    
    public function getById($id) {
        $query = "SELECT t.team_id, t.team_name, t.city, t.coach_id, 
                         u.username as coach_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.coach_id = u.user_id
                  WHERE t.team_id = :id";

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


    public function getAll() {
        $query = "SELECT t.team_id, t.team_name, t.city, t.coach_id, 
                         u.username as coach_name
                  FROM " . $this->table_name . " t
                  LEFT JOIN users u ON t.coach_id = u.user_id
                  ORDER BY t.team_name";

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
                  SET team_name = :team_name, city = :city, coach_id = :coach_id
                  WHERE team_id = :team_id";

        try {
            $stmt = $this->conn->prepare($query);


            $stmt->bindParam(":team_name", $data['team_name']);
            $stmt->bindParam(":city", $data['city']);
            $stmt->bindParam(":coach_id", $data['coach_id']);
            $stmt->bindParam(":team_id", $data['team_id']);


            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }

   
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE team_id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }

    
    public function getTeamPlayers($team_id) {
        $query = "SELECT player_id, first_name, last_name, position
                  FROM players
                  WHERE team_id = :team_id
                  ORDER BY last_name, first_name";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":team_id", $team_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return [];
    }
}