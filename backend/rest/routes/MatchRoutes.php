<?php
require_once __DIR__ . '/../services/MatchService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class MatchRoutes {
    private $matchService;
    private $authMiddleware;

    public function __construct() {
        $this->matchService = new MatchService();
        $this->authMiddleware = new AuthMiddleware();
    }

    public function registerRoutes($app) {
        // GET /api/matches - Get all matches (public)
        $app->route('GET /api/matches', function() {
            try {
                $matches = $this->matchService->getAllMatches();
                
                Flight::json([
                    'success' => true,
                    'data' => $matches
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        });

        // GET /api/matches/@id - Get match by ID (public)
        $app->route('GET /api/matches/@id', function($id) {
            try {
                $match = $this->matchService->getMatchById($id);
                
                Flight::json([
                    'success' => true,
                    'data' => $match
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
        });

        // GET /api/matches/upcoming - Get upcoming matches (public)
        $app->route('GET /api/matches/upcoming', function() {
            try {
                $matches = $this->matchService->getUpcomingMatches();
                
                Flight::json([
                    'success' => true,
                    'data' => $matches
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        });

        // POST /api/matches - Create new match (admin only)
        $app->route('POST /api/matches', function() {
            try {
                // JWT Authentication
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRole('admin');
                
                $data = json_decode(file_get_contents('php://input'), true);
                
                $matchId = $this->matchService->createMatch($data);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Match created successfully',
                    'data' => ['match_id' => $matchId]
                ], 201);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // PUT /api/matches/@id - Update match (admin only)
        $app->route('PUT /api/matches/@id', function($id) {
            try {
                // JWT Authentication
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRole('admin');
                
                $data = json_decode(file_get_contents('php://input'), true);
                $data['match_id'] = $id;
                
                $result = $this->matchService->updateMatch($data);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Match updated successfully'
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // DELETE /api/matches/@id - Delete match (admin only)
        $app->route('DELETE /api/matches/@id', function($id) {
            try {
                // JWT Authentication
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRole('admin');
                
                $result = $this->matchService->deleteMatch($id);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Match deleted successfully'
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // PUT /api/matches/@id/score - Update match score (admin only)
        $app->route('PUT /api/matches/@id/score', function($id) {
            try {
                // JWT Authentication
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRole('admin');
                
                $data = json_decode(file_get_contents('php://input'), true);
                
                $result = $this->matchService->updateMatchScore($id, $data['score_home'], $data['score_away']);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Match score updated successfully'
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // GET /api/matches/team/@teamId - Get matches by team (public)
        $app->route('GET /api/matches/team/@teamId', function($teamId) {
            try {
                $matches = $this->matchService->getMatchesByTeam($teamId);
                
                Flight::json([
                    'success' => true,
                    'data' => $matches
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
        });

        // GET /api/matches/@id/stats - Get match statistics (public)
        $app->route('GET /api/matches/@id/stats', function($id) {
            try {
                $stats = $this->matchService->getMatchStats($id);
                
                Flight::json([
                    'success' => true,
                    'data' => $stats
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
        });
    }
}