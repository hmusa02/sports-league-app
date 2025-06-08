<?php
require_once __DIR__ . '/../services/PlayerService.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../utils/ValidationHelper.php';

class PlayerRoutes {
    private $playerService;
    private $authMiddleware;

    public function __construct() {
        $this->playerService = new PlayerService();
        $this->authMiddleware = new AuthMiddleware();
    }

    public function registerRoutes($app) {
        // Get all players
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

        // Create new player (admin only)
        $app->route('POST /api/players', function() {
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
                $validation = ValidationHelper::validatePlayerData($data);
                
                if (!$validation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Validation errors: ' . implode(', ', $validation['errors'])
                    ], 400);
                    return;
                }
                
                $playerId = $this->playerService->createPlayer($validation['data']);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Player created successfully',
                    'data' => ['player_id' => $playerId]
                ], 201);
                
            } catch (Exception $e) {
                error_log('Player creation error: ' . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => 'Failed to create player'
                ], 400);
            }
        });

        // Get player by ID
        $app->route('GET /api/players/@id', function($id) {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRoles(['admin', 'user']);
                
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Player ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $player = $this->playerService->getPlayerById($idValidation['value']);
                
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

        // Update player (admin only)
        $app->route('PUT /api/players/@id', function($id) {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRole('admin');
                
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Player ID', 1);
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
                $validation = ValidationHelper::validatePlayerData($data);
                
                if (!$validation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Validation errors: ' . implode(', ', $validation['errors'])
                    ], 400);
                    return;
                }
                
                $validation['data']['player_id'] = $idValidation['value'];
                
                $result = $this->playerService->updatePlayer($validation['data']);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Player updated successfully'
                ]);
                
            } catch (Exception $e) {
                error_log('Player update error: ' . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => 'Failed to update player'
                ], 400);
            }
        });

        // Delete player (admin only)
        $app->route('DELETE /api/players/@id', function($id) {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRole('admin');
                
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Player ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $result = $this->playerService->deletePlayer($idValidation['value']);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Player deleted successfully'
                ]);
                
            } catch (Exception $e) {
                error_log('Player deletion error: ' . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => 'Failed to delete player'
                ], 400);
            }
        });

        // Get player statistics
        $app->route('GET /api/players/@id/stats', function($id) {
            try {
                $token = $this->authMiddleware->checkAuthHeader();
                $this->authMiddleware->verifyToken($token);
                $this->authMiddleware->authorizeRoles(['admin', 'user']);
                
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Player ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $stats = $this->playerService->getPlayerStats($idValidation['value']);
                
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