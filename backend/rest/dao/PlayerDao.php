<?php
require_once __DIR__ . '/../config.php';
class PlayerDao {
    private $conn;
    private $table_name = "players";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (first_name, last_name, position, team_id) 
                  VALUES (:first_name, :last_name, :position, :team_id)";

        try {
            $stmt = $this->conn->prepare($query);


            $stmt->bindParam(":first_name", $data['first_name']);
            $stmt->bindParam(":last_name", $data['last_name']);
            $stmt->bindParam(":position", $data['position']);
            $stmt->bindParam(":team_id", $data['team_id']);


            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }

    public function getById($id) {
        $query = "SELECT p.player_id, p.first_name, p.last_name, p.position, 
                         p.team_id, t.team_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN teams t ON p.team_id = t.team_id
                  WHERE p.player_id = :id";

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
        $query = "SELECT p.player_id, p.first_name, p.last_name, p.position, 
                         p.team_id, t.team_name
                  FROM " . $this->table_name . " p
                  LEFT JOIN teams t ON p.team_id = t.team_id
                  ORDER BY p.last_name, p.first_name";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return [];
    }

    
    public function getByTeamId($team_id) {
        $query = "SELECT player_id, first_name, last_name, position, team_id
                  FROM " . $this->table_name . "
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


    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name = :first_name, last_name = :last_name, 
                      position = :position, team_id = :team_id
                  WHERE player_id = :player_id";

        try {
            $stmt = $this->conn->prepare($query);


            $stmt->bindParam(":first_name", $data['first_name']);
            $stmt->bindParam(":last_name", $data['last_name']);
            $stmt->bindParam(":position", $data['position']);
            $stmt->bindParam(":team_id", $data['team_id']);
            $stmt->bindParam(":player_id", $data['player_id']);


            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }

    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE player_id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }

    
    public function getPlayerStats($player_id) {
        $query = "SELECT s.stat_id, s.match_id, s.event_type, s.minute,
                         m.date_played, m.home_team_id, m.away_team_id,
                         ht.team_name as home_team_name, at.team_name as away_team_name
                  FROM statistics s
                  JOIN matches m ON s.match_id = m.match_id
                  JOIN teams ht ON m.home_team_id = ht.team_id
                  JOIN teams at ON m.away_team_id = at.team_id
                  WHERE s.player_id = :player_id
                  ORDER BY m.date_played DESC, s.minute";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":player_id", $player_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return [];
    }
}