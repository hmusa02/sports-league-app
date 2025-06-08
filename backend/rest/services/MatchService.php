<?php
require_once __DIR__ . '/../dao/MatchDao.php';
require_once __DIR__ . '/../dao/TeamDao.php';

class MatchService {
    private $matchDao;
    private $teamDao;

    public function __construct() {
        $this->matchDao = new MatchDao();
        $this->teamDao = new TeamDao();
    }

    public function createMatch($data) {
        // Validation
        if (!isset($data['home_team_id']) || !isset($data['away_team_id'])) {
            throw new Exception('Home team and away team are required');
        }

        if (!is_numeric($data['home_team_id']) || !is_numeric($data['away_team_id'])) {
            throw new Exception('Invalid team IDs');
        }

        // Check if teams are different
        if ($data['home_team_id'] == $data['away_team_id']) {
            throw new Exception('Home team and away team must be different');
        }

        // Validate teams exist
        $homeTeam = $this->teamDao->getById($data['home_team_id']);
        if (!$homeTeam) {
            throw new Exception('Home team not found');
        }

        $awayTeam = $this->teamDao->getById($data['away_team_id']);
        if (!$awayTeam) {
            throw new Exception('Away team not found');
        }

        // Validate date if provided
        if (isset($data['date_played']) && !empty($data['date_played'])) {
            // Accept both date (Y-m-d) and datetime (Y-m-d H:i:s) formats
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $data['date_played']);
            if (!$date) {
                $date = DateTime::createFromFormat('Y-m-d', $data['date_played']);
                if (!$date) {
                    throw new Exception('Invalid date format. Use YYYY-MM-DD or YYYY-MM-DD HH:MM:SS');
                }
            }
        }

        // Validate scores if provided
        if (isset($data['score_home']) && !is_null($data['score_home'])) {
            if (!is_numeric($data['score_home']) || $data['score_home'] < 0) {
                throw new Exception('Invalid home score');
            }
        }

        if (isset($data['score_away']) && !is_null($data['score_away'])) {
            if (!is_numeric($data['score_away']) || $data['score_away'] < 0) {
                throw new Exception('Invalid away score');
            }
        }

        return $this->matchDao->create($data);
    }

    public function getMatchById($id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid match ID');
        }

        $match = $this->matchDao->getById($id);
        if (!$match) {
            throw new Exception('Match not found');
        }

        return $match;
    }

    public function getAllMatches() {
        return $this->matchDao->getAll();
    }

    public function getUpcomingMatches() {
        return $this->matchDao->getUpcoming();
    }

    public function getMatchesByTeam($teamId) {
        if (!is_numeric($teamId)) {
            throw new Exception('Invalid team ID');
        }

        $team = $this->teamDao->getById($teamId);
        if (!$team) {
            throw new Exception('Team not found');
        }

        return $this->matchDao->getByTeamId($teamId);
    }

    public function updateMatch($data) {
        if (!isset($data['match_id']) || !is_numeric($data['match_id'])) {
            throw new Exception('Valid match ID is required');
        }

        // Check if match exists
        $existingMatch = $this->matchDao->getById($data['match_id']);
        if (!$existingMatch) {
            throw new Exception('Match not found');
        }

        // Validation
        if (!isset($data['home_team_id']) || !isset($data['away_team_id'])) {
            throw new Exception('Home team and away team are required');
        }

        if (!is_numeric($data['home_team_id']) || !is_numeric($data['away_team_id'])) {
            throw new Exception('Invalid team IDs');
        }

        // Check if teams are different
        if ($data['home_team_id'] == $data['away_team_id']) {
            throw new Exception('Home team and away team must be different');
        }

        // Validate teams exist
        $homeTeam = $this->teamDao->getById($data['home_team_id']);
        if (!$homeTeam) {
            throw new Exception('Home team not found');
        }

        $awayTeam = $this->teamDao->getById($data['away_team_id']);
        if (!$awayTeam) {
            throw new Exception('Away team not found');
        }

        // Validate date if provided
        if (isset($data['date_played']) && !empty($data['date_played'])) {
            // Accept both date (Y-m-d) and datetime (Y-m-d H:i:s) formats
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $data['date_played']);
            if (!$date) {
                $date = DateTime::createFromFormat('Y-m-d', $data['date_played']);
                if (!$date) {
                    throw new Exception('Invalid date format. Use YYYY-MM-DD or YYYY-MM-DD HH:MM:SS');
                }
            }
        }

        // Validate scores if provided
        if (isset($data['score_home']) && !is_null($data['score_home'])) {
            if (!is_numeric($data['score_home']) || $data['score_home'] < 0) {
                throw new Exception('Invalid home score');
            }
        }

        if (isset($data['score_away']) && !is_null($data['score_away'])) {
            if (!is_numeric($data['score_away']) || $data['score_away'] < 0) {
                throw new Exception('Invalid away score');
            }
        }

        return $this->matchDao->update($data);
    }

    public function deleteMatch($id) {
        if (!is_numeric($id)) {
            throw new Exception('Invalid match ID');
        }

        $match = $this->matchDao->getById($id);
        if (!$match) {
            throw new Exception('Match not found');
        }

        return $this->matchDao->delete($id);
    }

    public function getMatchStats($matchId) {
        if (!is_numeric($matchId)) {
            throw new Exception('Invalid match ID');
        }

        $match = $this->matchDao->getById($matchId);
        if (!$match) {
            throw new Exception('Match not found');
        }

        return $this->matchDao->getMatchStats($matchId);
    }

    public function updateMatchScore($matchId, $homeScore, $awayScore) {
        if (!is_numeric($matchId)) {
            throw new Exception('Invalid match ID');
        }

        if (!is_numeric($homeScore) || !is_numeric($awayScore) || $homeScore < 0 || $awayScore < 0) {
            throw new Exception('Invalid scores');
        }

        $match = $this->matchDao->getById($matchId);
        if (!$match) {
            throw new Exception('Match not found');
        }

        $data = [
            'match_id' => $matchId,
            'home_team_id' => $match['home_team_id'],
            'away_team_id' => $match['away_team_id'],
            'date_played' => $match['date_played'],
            'score_home' => $homeScore,
            'score_away' => $awayScore
        ];

        return $this->matchDao->update($data);
    }
}