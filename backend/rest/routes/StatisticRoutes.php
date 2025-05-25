<?php
require_once __DIR__ . '/../services/StatisticService.php';

class StatisticRoutes {
    private $statisticService;

    public function __construct() {
        $this->statisticService = new StatisticService();
    }

    public function registerRoutes($app) {
        // GET /api/statistics - Get all statistics (public)
        $app->route('GET /api/statistics', function() {
            try {
                $statistics = $this->statisticService->getAllStatistics();
                
                Flight::json([
                    'success' => true,
                    'data' => $statistics
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        });

        // GET /api/statistics/@id - Get statistic by ID (public)
        $app->route('GET /api/statistics/@id', function($id) {
            try {
                $statistic = $this->statisticService->getStatisticById($id);
                
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

        // POST /api/statistics - Create new statistic (admin only)
        $app->route('POST /api/statistics', function() {
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
                
                $statisticId = $this->statisticService->createStatistic($data);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Statistic created successfully',
                    'data' => ['stat_id' => $statisticId]
                ], 201);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // PUT /api/statistics/@id - Update statistic (admin only)
        $app->route('PUT /api/statistics/@id', function($id) {
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
                $data['stat_id'] = $id;
                
                $result = $this->statisticService->updateStatistic($data);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Statistic updated successfully'
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // DELETE /api/statistics/@id - Delete statistic (admin only)
        $app->route('DELETE /api/statistics/@id', function($id) {
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
                
                $result = $this->statisticService->deleteStatistic($id);
                
                Flight::json([
                    'success' => true,
                    'message' => 'Statistic deleted successfully'
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // GET /api/statistics/match/@matchId - Get statistics by match (public)
        $app->route('GET /api/statistics/match/@matchId', function($matchId) {
            try {
                $statistics = $this->statisticService->getStatisticsByMatch($matchId);
                
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

        // GET /api/statistics/player/@playerId - Get statistics by player (public)
        $app->route('GET /api/statistics/player/@playerId', function($playerId) {
            try {
                $statistics = $this->statisticService->getStatisticsByPlayer($playerId);
                
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

        // GET /api/statistics/top-scorers - Get top scorers (public)
        $app->route('GET /api/statistics/top-scorers', function() {
            try {
                $limit = Flight::request()->query['limit'] ?? 10;
                $topScorers = $this->statisticService->getTopScorers($limit);
                
                Flight::json([
                    'success' => true,
                    'data' => $topScorers
                ]);
                
            } catch (Exception $e) {
                Flight::json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        });

        // GET /api/statistics/player/@playerId/summary - Get player stats summary (public)
        $app->route('GET /api/statistics/player/@playerId/summary', function($playerId) {
            try {
                $summary = $this->statisticService->getPlayerStatsSummary($playerId);
                
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
    }
}