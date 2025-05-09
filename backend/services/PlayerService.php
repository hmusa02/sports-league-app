<?php
// services/PlayerService.php
require_once __DIR__ . '/../dao/PlayerDao.php';

class PlayerService {
    private $playerDao;
    
    public function __construct() {
        $this->playerDao = new PlayerDao();
    }
    
    /**
     * Get all players from the database
     * 
     * @return array List of players
     */
    public function getAllPlayers() {
        return $this->playerDao->getAll();
    }
    
    /**
     * Get player by ID
     * 
     * @param int $id Player ID
     * @return array Player data
     * @throws Exception If player not found
     */
    public function getPlayerById($id) {
        $player = $this->playerDao->getById($id);
        if (!$player) {
            throw new Exception("Player not found", 404);
        }
        return $player;
    }
    
    /**
     * Create a new player
     * 
     * @param array $data Player data
     * @return array Created player data
     * @throws Exception If validation fails or creation fails
     */
    public function createPlayer($data) {
        // Validation
        if (empty($data['first_name']) || empty($data['last_name'])) {
            throw new Exception("First name and last name are required", 400);
        }
        
        if (empty($data['team_id'])) {
            throw new Exception("Team ID is required", 400);
        }
        
        $playerId = $this->playerDao->create($data);
        if (!$playerId) {
            throw new Exception("Failed to create player", 500);
        }
        
        return $this->playerDao->getById($playerId);
    }
    
    /**
     * Update an existing player
     * 
     * @param int $id Player ID
     * @param array $data Updated player data
     * @return array Updated player data
     * @throws Exception If player not found, validation fails, or update fails
     */
    public function updatePlayer($id, $data) {
        // Check if player exists
        $existingPlayer = $this->playerDao->getById($id);
        if (!$existingPlayer) {
            throw new Exception("Player not found", 404);
        }
        
        // Validation
        if (empty($data['first_name']) || empty($data['last_name'])) {
            throw new Exception("First name and last name are required", 400);
        }
        
        if (empty($data['team_id'])) {
            throw new Exception("Team ID is required", 400);
        }
        
        $data['player_id'] = $id;
        $success = $this->playerDao->update($data);
        if (!$success) {
            throw new Exception("Failed to update player", 500);
        }
        
        return $this->playerDao->getById($id);
    }
    
    /**
     * Delete a player
     * 
     * @param int $id Player ID
     * @return array Success message
     * @throws Exception If player not found or deletion fails
     */
    public function deletePlayer($id) {
        // Check if player exists
        $existingPlayer = $this->playerDao->getById($id);
        if (!$existingPlayer) {
            throw new Exception("Player not found", 404);
        }
        
        // Check referential integrity
        // Should check if player has related statistics
        
        $success = $this->playerDao->delete($id);
        if (!$success) {
            throw new Exception("Failed to delete player", 500);
        }
        
        return ["message" => "Player deleted successfully"];
    }
    
    /**
     * Get player statistics
     * 
     * @param int $playerId Player ID
     * @return array Player statistics
     * @throws Exception If player not found
     */
    public function getPlayerStats($playerId) {
        // Check if player exists
        $existingPlayer = $this->playerDao->getById($playerId);
        if (!$existingPlayer) {
            throw new Exception("Player not found", 404);
        }
        
        return $this->playerDao->getPlayerStats($playerId);
    }
}