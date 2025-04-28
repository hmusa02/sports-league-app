<?php
// services/TeamService.php
require_once __DIR__ . '/../dao/TeamDao.php';

class TeamService {
    private $teamDao;
    
    public function __construct() {
        $this->teamDao = new TeamDao();
    }
    
    /**
     * Get all teams from the database
     * 
     * @return array List of teams
     */
    public function getAllTeams() {
        return $this->teamDao->getAll();
    }
    
    /**
     * Get team by ID
     * 
     * @param int $id Team ID
     * @return array Team data
     * @throws Exception If team not found
     */
    public function getTeamById($id) {
        $team = $this->teamDao->getById($id);
        if (!$team) {
            throw new Exception("Team not found", 404);
        }
        return $team;
    }
    
    /**
     * Create a new team
     * 
     * @param array $data Team data
     * @return array Created team data
     * @throws Exception If validation fails or creation fails
     */
    public function createTeam($data) {
        // Validation
        if (empty($data['team_name'])) {
            throw new Exception("Team name is required", 400);
        }
        
        $teamId = $this->teamDao->create($data);
        if (!$teamId) {
            throw new Exception("Failed to create team", 500);
        }
        
        return $this->teamDao->getById($teamId);
    }
    
    /**
     * Update an existing team
     * 
     * @param int $id Team ID
     * @param array $data Updated team data
     * @return array Updated team data
     * @throws Exception If team not found, validation fails, or update fails
     */
    public function updateTeam($id, $data) {
        // Check if team exists
        $existingTeam = $this->teamDao->getById($id);
        if (!$existingTeam) {
            throw new Exception("Team not found", 404);
        }
        
        // Validation
        if (empty($data['team_name'])) {
            throw new Exception("Team name is required", 400);
        }
        
        $data['team_id'] = $id;
        $success = $this->teamDao->update($data);
        if (!$success) {
            throw new Exception("Failed to update team", 500);
        }
        
        return $this->teamDao->getById($id);
    }
    
    /**
     * Delete a team
     * 
     * @param int $id Team ID
     * @return array Success message
     * @throws Exception If team not found or deletion fails
     */
    public function deleteTeam($id) {
        // Check if team exists
        $existingTeam = $this->teamDao->getById($id);
        if (!$existingTeam) {
            throw new Exception("Team not found", 404);
        }
        
        // Check referential integrity
        // Should check if team has related players or matches
        
        $success = $this->teamDao->delete($id);
        if (!$success) {
            throw new Exception("Failed to delete team", 500);
        }
        
        return ["message" => "Team deleted successfully"];
    }
    
    /**
     * Get all players for a specific team
     * 
     * @param int $teamId Team ID
     * @return array List of players
     * @throws Exception If team not found
     */
    public function getTeamPlayers($teamId) {
        // Check if team exists
        $existingTeam = $this->teamDao->getById($teamId);
        if (!$existingTeam) {
            throw new Exception("Team not found", 404);
        }
        
        return $this->teamDao->getTeamPlayers($teamId);
    }
}