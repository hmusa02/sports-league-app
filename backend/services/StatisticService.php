<?php
// services/StatisticService.php
require_once __DIR__ . '/../dao/StatisticDao.php';

class StatisticService {
    private $statisticDao;
    
    public function __construct() {
        $this->statisticDao = new StatisticDao();
    }
    
    /**
     * Get all statistics from the database
     * 
     * @return array List of statistics
     */
    public function getAllStatistics() {
        return $this->statisticDao->getAll();
    }
    
    /**
     * Get statistic by ID
     * 
     * @param int $id Statistic ID
     * @return array Statistic data
     * @throws Exception If statistic not found
     */
    public function getStatisticById($id) {
        $statistic = $this->statisticDao->getById($id);
        if (!$statistic) {
            throw new Exception("Statistic not found", 404);
        }
        return $statistic;
    }
    
    /**
     * Get statistics by match ID
     * 
     * @param int $matchId Match ID
     * @return array List of statistics for the specified match
     */
    public function getStatisticsByMatchId($matchId) {
        return $this->statisticDao->getByMatchId($matchId);
    }
    
    /**
     * Get statistics by player ID
     * 
     * @param int $playerId Player ID
     * @return array List of statistics for the specified player
     */
    public function getStatisticsByPlayerId($playerId) {
        return $this->statisticDao->getByPlayerId($playerId);
    }
    
    /**
     * Get top scorers
     * 
     * @param int $limit Number of top scorers to retrieve
     * @return array List of top scorers
     */
    public function getTopScorers($limit = 10) {
        return $this->statisticDao->getTopScorers($limit);
    }
    
    /**
     * Create a new statistic
     * 
     * @param array $data Statistic data
     * @return array Created statistic data
     * @throws Exception If validation fails or creation fails
     */
    public function createStatistic($data) {
        // Validation
        if (empty($data['match_id'])) {
            throw new Exception("Match ID is required", 400);
        }
        
        if (empty($data['player_id'])) {
            throw new Exception("Player ID is required", 400);
        }
        
        if (empty($data['event_type'])) {
            throw new Exception("Event type is required", 400);
        }
        
        if (!isset($data['minute']) || !is_numeric($data['minute'])) {
            throw new Exception("Minute must be a number", 400);
        }
        
        $statisticId = $this->statisticDao->create($data);
        if (!$statisticId) {
            throw new Exception("Failed to create statistic", 500);
        }
        
        return $this->statisticDao->getById($statisticId);
    }
    
    /**
     * Update an existing statistic
     * 
     * @param int $id Statistic ID
     * @param array $data Updated statistic data
     * @return array Updated statistic data
     * @throws Exception If statistic not found, validation fails, or update fails
     */
    public function updateStatistic($id, $data) {
        // Check if statistic exists
        $existingStatistic = $this->statisticDao->getById($id);
        if (!$existingStatistic) {
            throw new Exception("Statistic not found", 404);
        }
        
        // Validation
        if (empty($data['match_id'])) {
            throw new Exception("Match ID is required", 400);
        }
        
        if (empty($data['player_id'])) {
            throw new Exception("Player ID is required", 400);
        }
        
        if (empty($data['event_type'])) {
            throw new Exception("Event type is required", 400);
        }
        
        if (!isset($data['minute']) || !is_numeric($data['minute'])) {
            throw new Exception("Minute must be a number", 400);
        }
        
        $data['stat_id'] = $id;
        $success = $this->statisticDao->update($data);
        if (!$success) {
            throw new Exception("Failed to update statistic", 500);
        }
        
        return $this->statisticDao->getById($id);
    }
    
    /**
     * Delete a statistic
     * 
     * @param int $id Statistic ID
     * @return array Success message
     * @throws Exception If statistic not found or deletion fails
     */
    public function deleteStatistic($id) {
        // Check if statistic exists
        $existingStatistic = $this->statisticDao->getById($id);
        if (!$existingStatistic) {
            throw new Exception("Statistic not found", 404);
        }
        
        $success = $this->statisticDao->delete($id);
        if (!$success) {
            throw new Exception("Failed to delete statistic", 500);
        }
        
        return ["message" => "Statistic deleted successfully"];
    }
}