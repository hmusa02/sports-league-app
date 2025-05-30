<?php
require_once __DIR__ . '/../dao/StatisticDao.php';
require_once __DIR__ . '/../dao/MatchDao.php';
require_once __DIR__ . '/../dao/PlayerDao.php';

class StatisticService {
    private $statisticDao;
    private $matchDao;
    private $playerDao;

    public function __construct() {
        $this->statisticDao = new StatisticDao();
        $this->matchDao = new MatchDao();
        $this->playerDao = new PlayerDao();
    }

    public function createStatistic($data) {
        // Validation
        if (!isset($data['match_id']) || !isset($data['player_id']) || !isset($data['event_type'])) {
            throw new Exception('Match ID, Player ID and Event type are required');
        }

        if (!is_numeric($data['match_id']) || !is_numeric($data['player_id'])) {
            throw new Exception('Invalid match ID or player ID');
        }

        // Validate match exists
        $match = $this->matchDao->getById($data['match_id']);
        if (!$match) {
            throw new Exception('Match not found');
        }

        // Validate player exists
        $player = $this->playerDao->getById($data['player_id']);
        if (!$player) {
            throw new Exception('Player not found');
        }

        // Validate event type
        $validEvents = ['goal', 'yellow_card', 'red_card', 'substitution', 'assist', 'save'];
        if (!in_array($data['event_type'], $validEvents)) {
            throw new Exception('Invalid event type. Must be: ' . implode(', ', $validEvents));
        }

        // Validate minute if provided
        if (isset($data['minute']) && !empty($data['minute'])) {
            if (!is_numeric($data['minute']) || $data['minute'] < 0 || $data['minute'] > 120) {
                throw new Exception('Invalid minute. Must be between 0 and 120');
            }
        }

        // Validate that player belongs to one of the teams in the match
        if ($player['team_id'] != $match['home_team_id'] && $player['team_id'] != $match['away_team_id']) {
            throw new Exception('Player does not belong to either team in this match');
        }

        return $this->statisticDao->create($data);
    }

    public function getStatisticById($id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid statistic ID');
        }

        $statistic = $this->statisticDao->getById($id);
        if (!$statistic) {
            throw new Exception('Statistic not found');
        }

        return $statistic;
    }

    public function getAllStatistics() {
        return $this->statisticDao->getAll();
    }

    public function getStatisticsByMatch($matchId) {
        if (!is_numeric($matchId)) {
            throw new Exception('Invalid match ID');
        }

        $match = $this->matchDao->getById($matchId);
        if (!$match) {
            throw new Exception('Match not found');
        }

        return $this->statisticDao->getByMatchId($matchId);
    }

    public function getStatisticsByPlayer($playerId) {
        if (!is_numeric($playerId)) {
            throw new Exception('Invalid player ID');
        }

        $player = $this->playerDao->getById($playerId);
        if (!$player) {
            throw new Exception('Player not found');
        }

        return $this->statisticDao->getByPlayerId($playerId);
    }

    public function updateStatistic($data) {
        if (!isset($data['stat_id']) || !is_numeric($data['stat_id'])) {
            throw new Exception('Valid statistic ID is required');
        }

        // Check if statistic exists
        $existingStatistic = $this->statisticDao->getById($data['stat_id']);
        if (!$existingStatistic) {
            throw new Exception('Statistic not found');
        }

        // Validation
        if (!isset($data['match_id']) || !isset($data['player_id']) || !isset($data['event_type'])) {
            throw new Exception('Match ID, Player ID and Event type are required');
        }

        if (!is_numeric($data['match_id']) || !is_numeric($data['player_id'])) {
            throw new Exception('Invalid match ID or player ID');
        }

        // Validate match exists
        $match = $this->matchDao->getById($data['match_id']);
        if (!$match) {
            throw new Exception('Match not found');
        }

        // Validate player exists
        $player = $this->playerDao->getById($data['player_id']);
        if (!$player) {
            throw new Exception('Player not found');
        }

        // Validate event type
        $validEvents = ['goal', 'yellow_card', 'red_card', 'substitution', 'assist', 'save'];
        if (!in_array($data['event_type'], $validEvents)) {
            throw new Exception('Invalid event type. Must be: ' . implode(', ', $validEvents));
        }

        // Validate minute if provided
        if (isset($data['minute']) && !empty($data['minute'])) {
            if (!is_numeric($data['minute']) || $data['minute'] < 0 || $data['minute'] > 120) {
                throw new Exception('Invalid minute. Must be between 0 and 120');
            }
        }

        // Validate that player belongs to one of the teams in the match
        if ($player['team_id'] != $match['home_team_id'] && $player['team_id'] != $match['away_team_id']) {
            throw new Exception('Player does not belong to either team in this match');
        }

        return $this->statisticDao->update($data);
    }

    public function deleteStatistic($id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid statistic ID');
        }

        $statistic = $this->statisticDao->getById($id);
        if (!$statistic) {
            throw new Exception('Statistic not found');
        }

        return $this->statisticDao->delete($id);
    }

    public function getTopScorers($limit = 10) {
        if (!is_numeric($limit) || $limit < 1) {
            throw new Exception('Invalid limit');
        }

        return $this->statisticDao->getTopScorers($limit);
    }

    public function getPlayerStatsSummary($playerId) {
        if (!is_numeric($playerId)) {
            throw new Exception('Invalid player ID');
        }

        $player = $this->playerDao->getById($playerId);
        if (!$player) {
            throw new Exception('Player not found');
        }

        $stats = $this->statisticDao->getByPlayerId($playerId);

        $summary = [
            'player' => $player,
            'total_matches' => count(array_unique(array_column($stats, 'match_id'))),
            'goals' => count(array_filter($stats, function($s) { return $s['event_type'] === 'goal'; })),
            'yellow_cards' => count(array_filter($stats, function($s) { return $s['event_type'] === 'yellow_card'; })),
            'red_cards' => count(array_filter($stats, function($s) { return $s['event_type'] === 'red_card'; })),
            'assists' => count(array_filter($stats, function($s) { return $s['event_type'] === 'assist'; }))
        ];

        return $summary;
    }
}