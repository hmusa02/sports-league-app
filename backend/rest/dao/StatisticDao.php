<?php
require_once __DIR__ . '/../config.php';
class StatisticDao {
    private $conn;
    private $table_name = "statistics";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

   
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (match_id, player_id, event_type, minute) 
                  VALUES (:match_id, :player_id, :event_type, :minute)";

        try {
            $stmt = $this->conn->prepare($query);


            $stmt->bindParam(":match_id", $data['match_id']);
            $stmt->bindParam(":player_id", $data['player_id']);
            $stmt->bindParam(":event_type", $data['event_type']);
            $stmt->bindParam(":minute", $data['minute']);


            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }

   
    public function getById($id) {
        $query = "SELECT s.stat_id, s.match_id, s.player_id, s.event_type, s.minute,
                         p.first_name, p.last_name,
                         m.date_played, m.home_team_id, m.away_team_id,
                         ht.team_name as home_team_name, at.team_name as away_team_name
                  FROM " . $this->table_name . " s
                  JOIN players p ON s.player_id = p.player_id
                  JOIN matches m ON s.match_id = m.match_id
                  JOIN teams ht ON m.home_team_id = ht.team_id
                  JOIN teams at ON m.away_team_id = at.team_id
                  WHERE s.stat_id = :id";

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
        $query = "SELECT s.stat_id, s.match_id, s.player_id, s.event_type, s.minute,
                         p.first_name, p.last_name,
                         m.date_played, m.home_team_id, m.away_team_id,
                         ht.team_name as home_team_name, at.team_name as away_team_name
                  FROM " . $this->table_name . " s
                  JOIN players p ON s.player_id = p.player_id
                  JOIN matches m ON s.match_id = m.match_id
                  JOIN teams ht ON m.home_team_id = ht.team_id
                  JOIN teams at ON m.away_team_id = at.team_id
                  ORDER BY m.date_played DESC, s.minute";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return [];
    }

   
    public function getByMatchId($match_id) {
        $query = "SELECT s.stat_id, s.player_id, s.event_type, s.minute,
                         p.first_name, p.last_name, p.position, p.team_id,
                         t.team_name
                  FROM " . $this->table_name . " s
                  JOIN players p ON s.player_id = p.player_id
                  JOIN teams t ON p.team_id = t.team_id
                  WHERE s.match_id = :match_id
                  ORDER BY s.minute";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":match_id", $match_id);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return [];
    }


    public function getByPlayerId($player_id) {
        $query = "SELECT s.stat_id, s.match_id, s.event_type, s.minute,
                         m.date_played, m.home_team_id, m.away_team_id,
                         ht.team_name as home_team_name, at.team_name as away_team_name
                  FROM " . $this->table_name . " s
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

    
    public function update($data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET match_id = :match_id, player_id = :player_id,
                      event_type = :event_type, minute = :minute
                  WHERE stat_id = :stat_id";

        try {
            $stmt = $this->conn->prepare($query);


            $stmt->bindParam(":match_id", $data['match_id']);
            $stmt->bindParam(":player_id", $data['player_id']);
            $stmt->bindParam(":event_type", $data['event_type']);
            $stmt->bindParam(":minute", $data['minute']);
            $stmt->bindParam(":stat_id", $data['stat_id']);


            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }


    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE stat_id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }

    
    public function getTopScorers($limit = 10) {
        $query = "SELECT p.player_id, p.first_name, p.last_name, p.position, 
                         p.team_id, t.team_name, COUNT(*) as goals
                  FROM " . $this->table_name . " s
                  JOIN players p ON s.player_id = p.player_id
                  JOIN teams t ON p.team_id = t.team_id
                  WHERE s.event_type = 'goal'
                  GROUP BY p.player_id
                  ORDER BY goals DESC
                  LIMIT :limit";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return [];
    }
}