<?php
require_once __DIR__ . '/../services/PlayerService.php';

class PlayerRoutes {
    private $playerService;

    public function __construct() {
        $this->playerService = new PlayerService();
    }

    public function registerRoutes($app) {
        // GET /api/players - Get all players (public)
        $app->route('GET /api/players', function() {
            try {
                $players = $this->playerService->getAllPlayers();
                
                Flight::json([
                    'success' => true,
                    'data' => $players
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        });

        // GET /api/players/@id - Get player by ID (public)
        $app->route('GET /api/players/@id', function($id) {
            try {
                $player = $this->playerService->getPlayerById($id);
                
                Flight::json([
                    'success' => true,
                    'data' => $player
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
        });

        // POST /api/players - Create new player (admin only)
        $app->route('POST /api/players', function() {
            try {
                // Check authentication
                session_start();
                if (!isset($_SESSION['user_id'])) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Authentication required'
                    ], 401);
                    return;
                }

                // Check admin role
                if ($_SESSION['role'] !== 'admin') {
                    Flight::json([
                        'success' => false,
                        'message' => 'Admin access required'
                    ], 403);
                    return;
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                
                $playerId = $this->playerService->createPlayer($data);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Player created successfully',
                    'data' => ['player_id' => $playerId]
                ], 201);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // PUT /api/players/@id - Update player (admin only)
        $app->route('PUT /api/players/@id', function($id) {
            try {
                // Check authentication
                session_start();
                if (!isset($_SESSION['user_id'])) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Authentication required'
                    ], 401);
                    return;
                }

                // Check admin role
                if ($_SESSION['role'] !== 'admin') {
                    Flight::json([
                        'success' => false,
                        'message' => 'Admin access required'
                    ], 403);
                    return;
                }
                
                $data = json_decode(file_get_contents('php://input'), true);
                $data['player_id'] = $id;
                
                $result = $this->playerService->updatePlayer($data);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Player updated successfully'
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // DELETE /api/players/@id - Delete player (admin only)
        $app->route('DELETE /api/players/@id', function($id) {
            try {
                // Check authentication
                session_start();
                if (!isset($_SESSION['user_id'])) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Authentication required'
                    ], 401);
                    return;
                }

                // Check admin role
                if ($_SESSION['role'] !== 'admin') {
                    Flight::json([
                        'success' => false,
                        'message' => 'Admin access required'
                    ], 403);
                    return;
                }
                
                $result = $this->playerService->deletePlayer($id);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Player deleted successfully'
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // GET /api/players/team/@teamId - Get players by team (public)
        $app->route('GET /api/players/team/@teamId', function($teamId) {
            try {
                $players = $this->playerService->getPlayersByTeam($teamId);
                
                Flight::json([
                    'success' => true,
                    'data' => $players
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
        });

        // GET /api/players/position/@position - Get players by position (public)
        $app->route('GET /api/players/position/@position', function($position) {
            try {
                $players = $this->playerService->getPlayersByPosition($position);
                
                Flight::json([
                    'success' => true,
                    'data' => $players
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // GET /api/players/@id/stats - Get player statistics (public)
        $app->route('GET /api/players/@id/stats', function($id) {
            try {
                $stats = $this->playerService->getPlayerStats($id);
                
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