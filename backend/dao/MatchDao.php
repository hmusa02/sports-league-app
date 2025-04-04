<?php
require_once __DIR__ . '/../config.php';
class MatchDao {
    private $conn;
    private $table_name = "matches";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

  
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (home_team_id, away_team_id, date_played, score_home, score_away) 
                  VALUES (:home_team_id, :away_team_id, :date_played, :score_home, :score_away)";

        try {
            $stmt = $this->conn->prepare($query);


            $stmt->bindParam(":home_team_id", $data['home_team_id']);
            $stmt->bindParam(":away_team_id", $data['away_team_id']);
            $stmt->bindParam(":date_played", $data['date_played']);
            $stmt->bindParam(":score_home", $data['score_home']);
            $stmt->bindParam(":score_away", $data['score_away']);


            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }


    public function getById($id) {
        $query = "SELECT m.match_id, m.home_team_id, m.away_team_id, 
                         m.date_played, m.score_home, m.score_away,
                         ht.team_name as home_team_name, at.team_name as away_team_name
                  FROM " . $this->table_name . " m
                  JOIN teams ht ON m.home_team_id = ht.team_id
                  JOIN teams at ON m.away_team_id = at.team_id
                  WHERE m.match_id = :id";

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
        $query = "SELECT m.match_id, m.home_team_id, m.away_team_id, 
                         m.date_played, m.score_home, m.score_away,
                         ht.team_name as home_team_name, at.team_name as away_team_name
                  FROM " . $this->table_name . " m
                  JOIN teams ht ON m.home_team_id = ht.team_id
                  JOIN teams at ON m.away_team_id = at.team_id
                  ORDER BY m.date_played DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return [];
    }


    public function getUpcoming() {
        $query = "SELECT m.match_id, m.home_team_id, m.away_team_id, 
                         m.date_played, m.score_home, m.score_away,
                         ht.team_name as home_team_name, at.team_name as away_team_name
                  FROM " . $this->table_name . " m
                  JOIN teams ht ON m.home_team_id = ht.team_id
                  JOIN teams at ON m.away_team_id = at.team_id
                  WHERE m.date_played > NOW()
                  ORDER BY m.date_played ASC";

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
        $query = "SELECT m.match_id, m.home_team_id, m.away_team_id, 
                         m.date_played, m.score_home, m.score_away,
                         ht.team_name as home_team_name, at.team_name as away_team_name
                  FROM " . $this->table_name . " m
                  JOIN teams ht ON m.home_team_id = ht.team_id
                  JOIN teams at ON m.away_team_id = at.team_id
                  WHERE m.home_team_id = :team_id OR m.away_team_id = :team_id
                  ORDER BY m.date_played DESC";

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
                  SET home_team_id = :home_team_id, away_team_id = :away_team_id,
                      date_played = :date_played, score_home = :score_home, score_away = :score_away
                  WHERE match_id = :match_id";

        try {
            $stmt = $this->conn->prepare($query);


            $stmt->bindParam(":home_team_id", $data['home_team_id']);
            $stmt->bindParam(":away_team_id", $data['away_team_id']);
            $stmt->bindParam(":date_played", $data['date_played']);
            $stmt->bindParam(":score_home", $data['score_home']);
            $stmt->bindParam(":score_away", $data['score_away']);
            $stmt->bindParam(":match_id", $data['match_id']);


            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }


    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE match_id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log($e->getMessage());
        }

        return false;
    }


    public function getMatchStats($match_id) {
        $query = "SELECT s.stat_id, s.player_id, s.event_type, s.minute,
                         p.first_name, p.last_name, p.position, p.team_id,
                         t.team_name
                  FROM statistics s
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
  }