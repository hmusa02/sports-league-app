<?php
require_once __DIR__ . '/../services/TeamService.php';

class TeamRoutes {
    private $teamService;

    public function __construct() {
        $this->teamService = new TeamService();
    }

    public function registerRoutes($app) {
        // GET /api/teams - Get all teams (public)
        $app->route('GET /api/teams', function() {
            try {
                $teams = $this->teamService->getAllTeams();
                
                Flight::json([
                    'success' => true,
                    'data' => $teams
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        });

        // GET /api/teams/@id - Get team by ID (public)
        $app->route('GET /api/teams/@id', function($id) {
            try {
                $team = $this->teamService->getTeamById($id);
                
                Flight::json([
                    'success' => true,
                    'data' => $team
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
        });

        // POST /api/teams - Create new team (admin only)
        $app->route('POST /api/teams', function() {
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
                
                $teamId = $this->teamService->createTeam($data);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Team created successfully',
                    'data' => ['team_id' => $teamId]
                ], 201);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // PUT /api/teams/@id - Update team (admin only)
        $app->route('PUT /api/teams/@id', function($id) {
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
                $data['team_id'] = $id;
                
                $result = $this->teamService->updateTeam($data);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Team updated successfully'
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // DELETE /api/teams/@id - Delete team (admin only)
        $app->route('DELETE /api/teams/@id', function($id) {
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
                
                $result = $this->teamService->deleteTeam($id);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Team deleted successfully'
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // GET /api/teams/@id/players - Get team players (public)
        $app->route('GET /api/teams/@id/players', function($id) {
            try {
                $players = $this->teamService->getTeamPlayers($id);
                
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

        // GET /api/teams/@id/stats - Get team statistics (public)
        $app->route('GET /api/teams/@id/stats', function($id) {
            try {
                $stats = $this->teamService->getTeamStats($id);
                
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