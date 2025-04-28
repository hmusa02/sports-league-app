<?php
// services/MatchService.php
require_once __DIR__ . '/../dao/MatchDao.php';

class MatchService {
    private $matchDao;
    
    public function __construct() {
        $this->matchDao = new MatchDao();
    }
    
    /**
     * Get all matches
     * 
     * @return array List of matches
     */
    public function getAllMatches() {
        return $this->matchDao->getAll();
    }
    
    /**
     * Get match by ID
     * 
     * @param int $id Match ID
     * @return array Match data
     * @throws Exception If match not found
     */
    public function getMatchById($id) {
        $match = $this->matchDao->getById($id);
        if (!$match) {
            throw new Exception("Match not found", 404);
        }
        return $match;
    }
    
    /**
     * Get upcoming matches
     * 
     * @return array List of upcoming matches
     */
    public function getUpcomingMatches() {
        return $this->matchDao->getUpcoming();
    }
    
    /**
     * Get matches by team ID
     * 
     * @param int $teamId Team ID
     * @return array List of matches
     */
    public function getMatchesByTeam($teamId) {
        return $this->matchDao->getByTeamId($teamId);
    }
    
    /**
     * Create a new match
     * 
     * @param array $data Match data
     * @return array Created match data
     * @throws Exception If validation fails or creation fails
     */
    public function createMatch($data) {
        // Validation
        if (empty($data['home_team_id']) || empty($data['away_team_id'])) {
            throw new Exception("Home team and away team are required", 400);
        }
        
        if (empty($data['date_played'])) {
            throw new Exception("Match date is required", 400);
        }
        
        $matchId = $this->matchDao->create($data);
        if (!$matchId) {
            throw new Exception("Failed to create match", 500);
        }
        
        return $this->matchDao->getById($matchId);
    }
    
    /**
     * Update an existing match
     * 
     * @param int $id Match ID
     * @param array $data Updated match data
     * @return array Updated match data
     * @throws Exception If match not found, validation fails, or update fails
     */
    public function updateMatch($id, $data) {
        // Check if match exists
        $existingMatch = $this->matchDao->getById($id);
        if (!$existingMatch) {
            throw new Exception("Match not found", 404);
        }
        
        // Validation
        if (empty($data['home_team_id']) || empty($data['away_team_id'])) {
            throw new Exception("Home team and away team are required", 400);
        }
        
        if (empty($data['date_played'])) {
            throw new Exception("Match date is required", 400);
        }
        
        $data['match_id'] = $id;
        $success = $this->matchDao->update($data);
        if (!$success) {
            throw new Exception("Failed to update match", 500);
        }
        
        return $this->matchDao->getById($id);
    }
    
    /**
     * Delete a match
     * 
     * @param int $id Match ID
     * @return array Success message
     * @throws Exception If match not found or deletion fails
     */
    public function deleteMatch($id) {
        // Check if match exists
        $existingMatch = $this->matchDao->getById($id);
        if (!$existingMatch) {
            throw new Exception("Match not found", 404);
        }
        
        // Check referential integrity
        // Should check if match has related statistics
        
        $success = $this->matchDao->delete($id);
        if (!$success) {
            throw new Exception("Failed to delete match", 500);
        }
        
        return ["message" => "Match deleted successfully"];
    }
    
    /**
     * Get match statistics
     * 
     * @param int $matchId Match ID
     * @return array Match statistics
     * @throws Exception If match not found
     */
    public function getMatchStats($matchId) {
        // Check if match exists
        $existingMatch = $this->matchDao->getById($matchId);
        if (!$existingMatch) {
            throw new Exception("Match not found", 404);
        }
        
        return $this->matchDao->getMatchStats($matchId);
    }
}