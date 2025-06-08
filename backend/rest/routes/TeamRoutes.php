<?php
require_once __DIR__ . '/../services/TeamService.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../utils/ValidationHelper.php';

class TeamRoutes {
    private $teamService;
    private $authMiddleware;

    public function __construct() {
        $this->teamService = new TeamService();
        $this->authMiddleware = new AuthMiddleware();
    }

    public function registerRoutes($app) {
        // Get all teams
        $app->route('GET /api/teams', function() {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRoles(['admin', 'user']);
                
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

        // Create new team (admin only)
        $app->route('POST /api/teams', function() {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRole('admin');
                
                $rawData = file_get_contents('php://input');
                
                if (empty($rawData)) {
                    Flight::json([
                        'success' => false,
                        'message' => 'No data provided'
                    ], 400);
                    return;
                }
                
                $data = json_decode($rawData, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Invalid JSON data'
                    ], 400);
                    return;
                }
                
                // Validate and sanitize data
                $validation = ValidationHelper::validateTeamData($data);
                
                if (!$validation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Validation errors: ' . implode(', ', $validation['errors'])
                    ], 400);
                    return;
                }
                
                $teamId = $this->teamService->createTeam($validation['data']);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Team created successfully',
                    'data' => ['team_id' => $teamId]
                ], 201);
                
            } catch (Exception $e) {
                error_log('Team creation error: ' . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => 'Failed to create team'
                ], 400);
            }
        });

        // Get team by ID
        $app->route('GET /api/teams/@id', function($id) {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRoles(['admin', 'user']);
                
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Team ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $team = $this->teamService->getTeamById($idValidation['value']);
                
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

        // Update team (admin only)
        $app->route('PUT /api/teams/@id', function($id) {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRole('admin');
                
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Team ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $rawData = file_get_contents('php://input');
                
                if (empty($rawData)) {
                    Flight::json([
                        'success' => false,
                        'message' => 'No data provided'
                    ], 400);
                    return;
                }
                
                $data = json_decode($rawData, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Invalid JSON data'
                    ], 400);
                    return;
                }
                
                // Validate and sanitize data
                $validation = ValidationHelper::validateTeamData($data);
                
                if (!$validation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Validation errors: ' . implode(', ', $validation['errors'])
                    ], 400);
                    return;
                }
                
                $validation['data']['team_id'] = $idValidation['value'];
                
                $result = $this->teamService->updateTeam($validation['data']);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Team updated successfully'
                ]);
                
            } catch (Exception $e) {
                error_log('Team update error: ' . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => 'Failed to update team'
                ], 400);
            }
        });

        // Delete team (admin only)
        $app->route('DELETE /api/teams/@id', function($id) {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRole('admin');
                
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Team ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $result = $this->teamService->deleteTeam($idValidation['value']);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Team deleted successfully'
                ]);
                
            } catch (Exception $e) {
                error_log('Team deletion error: ' . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => 'Failed to delete team'
                ], 400);
            }
        });

        // Get team players
        $app->route('GET /api/teams/@id/players', function($id) {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRoles(['admin', 'user']);
                
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Team ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $players = $this->teamService->getTeamPlayers($idValidation['value']);
                
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
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Team ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $stats = $this->teamService->getTeamStats($idValidation['value']);
                
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