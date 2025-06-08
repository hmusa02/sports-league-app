<?php
require_once __DIR__ . '/../services/StatisticService.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../utils/ValidationHelper.php';

class StatisticRoutes {
    public function registerRoutes($app) {
        $statisticService = new StatisticService();
        $authMiddleware = new AuthMiddleware();

        // GET /api/statistics/top-scorers - Get top scorers (public) - MUST BE BEFORE /api/statistics/@id
        Flight::route('GET /api/statistics/top-scorers', function() use ($statisticService) {
            try {
                error_log("Top scorers endpoint called");
                $limit = Flight::request()->query['limit'] ?? 10;
                $topScorers = $statisticService->getTopScorers($limit);
                
                Flight::json([
                    'success' => true,
                    'data' => $topScorers
                ]);
                
            } catch (Exception $e) {
                error_log("Top scorers error: " . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // GET /api/statistics/player/@playerId/summary - Get player stats summary (public) - MUST BE BEFORE other player routes
        Flight::route('GET /api/statistics/player/@playerId/summary', function($playerId) use ($statisticService) {
            try {
                $summary = $statisticService->getPlayerStatsSummary($playerId);
                
                Flight::json([
                    'success' => true,
                    'data' => $summary
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
        });

        // Get all statistics
        Flight::route('GET /api/statistics', function() use ($statisticService, $authMiddleware) {
            try {
                error_log("Statistics endpoint called");
                $user = Flight::get('user');
                error_log("Current user: " . json_encode($user));
                
                $authMiddleware->authorizeRoles(['admin', 'user']);
                
                $statistics = $statisticService->getAllStatistics();
                
                Flight::json([
                    'success' => true,
                    'data' => $statistics
                ]);
                
            } catch (Exception $e) {
                error_log("Statistics error: " . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        });

        // Create new statistic (admin only)
        Flight::route('POST /api/statistics', function() use ($statisticService, $authMiddleware) {
            try {
                $authMiddleware->authorizeRole('admin');
                
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
                $validation = ValidationHelper::validateStatisticData($data);
                
                if (!$validation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Validation errors: ' . implode(', ', $validation['errors'])
                    ], 400);
                    return;
                }
                
                $statId = $statisticService->createStatistic($validation['data']);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Statistic created successfully',
                    'data' => ['stat_id' => $statId]
                ], 201);
                
            } catch (Exception $e) {
                error_log('Statistic creation error: ' . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => 'Failed to create statistic'
                ], 400);
            }
        });

        // Get statistic by ID
        Flight::route('GET /api/statistics/@id', function($id) use ($statisticService, $authMiddleware) {
            try {
                $authMiddleware->authorizeRoles(['admin', 'user']);
                
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Statistic ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $statistic = $statisticService->getStatisticById($idValidation['value']);
                
                Flight::json([
                    'success' => true,
                    'data' => $statistic
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
        });

        // Update statistic (admin only)
        Flight::route('PUT /api/statistics/@id', function($id) use ($statisticService, $authMiddleware) {
            try {
                $authMiddleware->authorizeRole('admin');
                
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Statistic ID', 1);
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
                $validation = ValidationHelper::validateStatisticData($data);
                
                if (!$validation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => 'Validation errors: ' . implode(', ', $validation['errors'])
                    ], 400);
                    return;
                }
                
                $validation['data']['stat_id'] = $idValidation['value'];
                
                $result = $statisticService->updateStatistic($validation['data']);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Statistic updated successfully'
                ]);
                
            } catch (Exception $e) {
                error_log('Statistic update error: ' . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => 'Failed to update statistic'
                ], 400);
            }
        });

        // Delete statistic (admin only)
        Flight::route('DELETE /api/statistics/@id', function($id) use ($statisticService, $authMiddleware) {
            try {
                $authMiddleware->authorizeRole('admin');
                
                // Validate ID
                $idValidation = ValidationHelper::validateInteger($id, 'Statistic ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $result = $statisticService->deleteStatistic($idValidation['value']);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Statistic deleted successfully'
                ]);
                
            } catch (Exception $e) {
                error_log('Statistic deletion error: ' . $e->getMessage());
                Flight::json([
                    'success' => false,
                    'message' => 'Failed to delete statistic'
                ], 400);
            }
        });

        // Get statistics by match
        Flight::route('GET /api/statistics/match/@matchId', function($matchId) use ($statisticService, $authMiddleware) {
            try {
                $authMiddleware->authorizeRoles(['admin', 'user']);
                
                // Validate match ID
                $idValidation = ValidationHelper::validateInteger($matchId, 'Match ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $statistics = $statisticService->getStatisticsByMatch($idValidation['value']);
                
                Flight::json([
                    'success' => true,
                    'data' => $statistics
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
        });

        // Get statistics by player
        Flight::route('GET /api/statistics/player/@playerId', function($playerId) use ($statisticService, $authMiddleware) {
            try {
                $authMiddleware->authorizeRoles(['admin', 'user']);
                
                // Validate player ID
                $idValidation = ValidationHelper::validateInteger($playerId, 'Player ID', 1);
                if (!$idValidation['valid']) {
                    Flight::json([
                        'success' => false,
                        'message' => $idValidation['message']
                    ], 400);
                    return;
                }
                
                $statistics = $statisticService->getStatisticsByPlayer($idValidation['value']);
                
                Flight::json([
                    'success' => true,
                    'data' => $statistics
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