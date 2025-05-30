<?php
require_once __DIR__ . '/../dao/TeamDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class TeamService {
    private $teamDao;
    private $userDao;

    public function __construct() {
        $this->teamDao = new TeamDao();
        $this->userDao = new UserDao();
    }

    public function createTeam($data) {
        // Validation
        if (empty($data['team_name'])) {
            throw new Exception('Team name is required');
        }

        if (strlen($data['team_name']) < 2) {
            throw new Exception('Team name must be at least 2 characters long');
        }

        // Validate coach_id if provided
        if (isset($data['coach_id']) && !empty($data['coach_id'])) {
            if (!is_numeric($data['coach_id'])) {
                throw new Exception('Invalid coach ID');
            }

            $coach = $this->userDao->getById($data['coach_id']);
            if (!$coach) {
                throw new Exception('Coach not found');
            }
        }

        // Set city default if not provided
        if (!isset($data['city'])) {
            $data['city'] = null;
        }

        return $this->teamDao->create($data);
    }

    public function getTeamById($id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid team ID');
        }

        $team = $this->teamDao->getById($id);
        if (!$team) {
            throw new Exception('Team not found');
        }

        return $team;
    }

    public function getAllTeams() {
        return $this->teamDao->getAll();
    }

    public function updateTeam($data) {
        if (!isset($data['team_id']) || !is_numeric($data['team_id'])) {
            throw new Exception('Valid team ID is required');
        }

        // Check if team exists
        $existingTeam = $this->teamDao->getById($data['team_id']);
        if (!$existingTeam) {
            throw new Exception('Team not found');
        }

        // Validation
        if (empty($data['team_name'])) {
            throw new Exception('Team name is required');
        }

        if (strlen($data['team_name']) < 2) {
            throw new Exception('Team name must be at least 2 characters long');
        }

        // Validate coach_id if provided
        if (isset($data['coach_id']) && !empty($data['coach_id'])) {
            if (!is_numeric($data['coach_id'])) {
                throw new Exception('Invalid coach ID');
            }

            $coach = $this->userDao->getById($data['coach_id']);
            if (!$coach) {
                throw new Exception('Coach not found');
            }
        }

        return $this->teamDao->update($data);
    }

    public function deleteTeam($id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid team ID');
        }

        $team = $this->teamDao->getById($id);
        if (!$team) {
            throw new Exception('Team not found');
        }

        // Check if team has players
        $players = $this->teamDao->getTeamPlayers($id);
        if (!empty($players)) {
            throw new Exception('Cannot delete team that has players. Remove players first.');
        }

        return $this->teamDao->delete($id);
    }

    public function getTeamPlayers($teamId) {
        if (!is_numeric($teamId)) {
            throw new Exception('Invalid team ID');
        }

        $team = $this->teamDao->getById($teamId);
        if (!$team) {
            throw new Exception('Team not found');
        }

        return $this->teamDao->getTeamPlayers($teamId);
    }

    public function getTeamStats($teamId) {
        if (!is_numeric($teamId)) {
            throw new Exception('Invalid team ID');
        }

        $team = $this->teamDao->getById($teamId);
        if (!$team) {
            throw new Exception('Team not found');
        }

        // This would require MatchDao integration for full stats
        return [
            'team' => $team,
            'players_count' => count($this->teamDao->getTeamPlayers($teamId))
        ];
    }
}