<?php
require_once __DIR__ . '/../dao/PlayerDao.php';
require_once __DIR__ . '/../dao/TeamDao.php';

class PlayerService {
    private $playerDao;
    private $teamDao;

    public function __construct() {
        $this->playerDao = new PlayerDao();
        $this->teamDao = new TeamDao();
    }

    public function createPlayer($data) {
        // Validation
        if (empty($data['first_name']) || empty($data['last_name'])) {
            throw new Exception('First name and last name are required');
        }

        if (strlen($data['first_name']) < 2 || strlen($data['last_name']) < 2) {
            throw new Exception('Names must be at least 2 characters long');
        }

        // Validate team_id if provided
        if (isset($data['team_id']) && !empty($data['team_id'])) {
            if (!is_numeric($data['team_id'])) {
                throw new Exception('Invalid team ID');
            }

            $team = $this->teamDao->getById($data['team_id']);
            if (!$team) {
                throw new Exception('Team not found');
            }
        }

        // Validate position if provided
        $validPositions = ['Goalkeeper', 'Defender', 'Midfielder', 'Forward'];
        if (isset($data['position']) && !empty($data['position'])) {
            if (!in_array($data['position'], $validPositions)) {
                throw new Exception('Invalid position. Must be: ' . implode(', ', $validPositions));
            }
        }

        return $this->playerDao->create($data);
    }

    public function getPlayerById($id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid player ID');
        }

        $player = $this->playerDao->getById($id);
        if (!$player) {
            throw new Exception('Player not found');
        }

        return $player;
    }

    public function getAllPlayers() {
        return $this->playerDao->getAll();
    }

    public function getPlayersByTeam($teamId) {
        if (!is_numeric($teamId)) {
            throw new Exception('Invalid team ID');
        }

        $team = $this->teamDao->getById($teamId);
        if (!$team) {
            throw new Exception('Team not found');
        }

        return $this->playerDao->getByTeamId($teamId);
    }

    public function updatePlayer($data) {
        if (!isset($data['player_id']) || !is_numeric($data['player_id'])) {
            throw new Exception('Valid player ID is required');
        }

        // Check if player exists
        $existingPlayer = $this->playerDao->getById($data['player_id']);
        if (!$existingPlayer) {
            throw new Exception('Player not found');
        }

        // Validation
        if (empty($data['first_name']) || empty($data['last_name'])) {
            throw new Exception('First name and last name are required');
        }

        if (strlen($data['first_name']) < 2 || strlen($data['last_name']) < 2) {
            throw new Exception('Names must be at least 2 characters long');
        }

        // Validate team_id if provided
        if (isset($data['team_id']) && !empty($data['team_id'])) {
            if (!is_numeric($data['team_id'])) {
                throw new Exception('Invalid team ID');
            }

            $team = $this->teamDao->getById($data['team_id']);
            if (!$team) {
                throw new Exception('Team not found');
            }
        }

        // Validate position if provided
        $validPositions = ['Goalkeeper', 'Defender', 'Midfielder', 'Forward'];
        if (isset($data['position']) && !empty($data['position'])) {
            if (!in_array($data['position'], $validPositions)) {
                throw new Exception('Invalid position. Must be: ' . implode(', ', $validPositions));
            }
        }

        return $this->playerDao->update($data);
    }

    public function deletePlayer($id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid player ID');
        }

        $player = $this->playerDao->getById($id);
        if (!$player) {
            throw new Exception('Player not found');
        }

        return $this->playerDao->delete($id);
    }

    public function getPlayerStats($playerId) {
        if (!is_numeric($playerId)) {
            throw new Exception('Invalid player ID');
        }

        $player = $this->playerDao->getById($playerId);
        if (!$player) {
            throw new Exception('Player not found');
        }

        return $this->playerDao->getPlayerStats($playerId);
    }

    public function getPlayersByPosition($position) {
        $validPositions = ['Goalkeeper', 'Defender', 'Midfielder', 'Forward'];
        if (!in_array($position, $validPositions)) {
            throw new Exception('Invalid position. Must be: ' . implode(', ', $validPositions));
        }

        $allPlayers = $this->playerDao->getAll();
        return array_filter($allPlayers, function($player) use ($position) {
            return $player['position'] === $position;
        });
    }
}