<?php
// routes/statistic_routes.php

require_once __DIR__ . '/../services/StatisticService.php';

/**
 * @OA\Tag(
 *     name="Statistics",
 *     description="Match statistics management endpoints"
 * )
 */

$app->group('/api/statistics', function() use ($app) {
    
    /**
     * @OA\Get(
     *     path="/api/statistics",
     *     summary="Get all statistics",
     *     tags={"Statistics"},
     *     @OA\Response(
     *         response=200,
     *         description="List of statistics",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Statistic")
     *         )
     *     )
     * )
     */
    $app->get('', function() {
        $statisticService = new StatisticService();
        return $statisticService->getAllStatistics();
    });
    
    /**
     * @OA\Get(
     *     path="/api/statistics/{id}",
     *     summary="Get statistic by ID",
     *     tags={"Statistics"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Statistic ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistic found",
     *         @OA\JsonContent(ref="#/components/schemas/Statistic")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Statistic not found"
     *     )
     * )
     */
    $app->get('/{id}', function($id) {
        $statisticService = new StatisticService();
        return $statisticService->getStatisticById($id);
    });
    
    /**
     * @OA\Get(
     *     path="/api/statistics/match/{matchId}",
     *     summary="Get statistics by match ID",
     *     tags={"Statistics"},
     *     @OA\Parameter(
     *         name="matchId",
     *         in="path",
     *         required=true,
     *         description="Match ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of statistics for the specified match",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Statistic")
     *         )
     *     )
     * )
     */
    $app->get('/match/{matchId}', function($matchId) {
        $statisticService = new StatisticService();
        return $statisticService->getStatisticsByMatchId($matchId);
    });
    
    /**
     * @OA\Get(
     *     path="/api/statistics/player/{playerId}",
     *     summary="Get statistics by player ID",
     *     tags={"Statistics"},
     *     @OA\Parameter(
     *         name="playerId",
     *         in="path",
     *         required=true,
     *         description="Player ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of statistics for the specified player",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Statistic")
     *         )
     *     )
     * )
     */
    $app->get('/player/{playerId}', function($playerId) {
        $statisticService = new StatisticService();
        return $statisticService->getStatisticsByPlayerId($playerId);
    });
    
    /**
     * @OA\Get(
     *     path="/api/statistics/top-scorers",
     *     summary="Get top scorers",
     *     tags={"Statistics"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of top scorers to retrieve",
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of top scorers",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="player_id", type="integer", example=1),
     *                 @OA\Property(property="first_name", type="string", example="Lionel"),
     *                 @OA\Property(property="last_name", type="string", example="Messi"),
     *                 @OA\Property(property="team_id", type="integer", example=1),
     *                 @OA\Property(property="team_name", type="string", example="FC Barcelona"),
     *                 @OA\Property(property="goals", type="integer", example=25)
     *             )
     *         )
     *     )
     * )
     */
    $app->get('/top-scorers', function() {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $statisticService = new StatisticService();
        return $statisticService->getTopScorers($limit);
    });
    
    /**
     * @OA\Post(
     *     path="/api/statistics",
     *     summary="Create a new statistic",
     *     tags={"Statistics"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"match_id", "player_id", "event_type", "minute"},
     *             @OA\Property(property="match_id", type="integer", example=1),
     *             @OA\Property(property="player_id", type="integer", example=1),
     *             @OA\Property(property="event_type", type="string", example="goal"),
     *             @OA\Property(property="minute", type="integer", example=75)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Statistic created",
     *         @OA\JsonContent(ref="#/components/schemas/Statistic")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid data"
     *     )
     * )
     */
    $app->post('', function() {
        $request = Flight::request();
        $data = $request->data->getData();
        
        $statisticService = new StatisticService();
        return $statisticService->createStatistic($data);
    });
    
    /**
     * @OA\Put(
     *     path="/api/statistics/{id}",
     *     summary="Update statistic",
     *     tags={"Statistics"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Statistic ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"match_id", "player_id", "event_type", "minute"},
     *             @OA\Property(property="match_id", type="integer", example=1),
     *             @OA\Property(property="player_id", type="integer", example=1),
     *             @OA\Property(property="event_type", type="string", example="goal"),
     *             @OA\Property(property="minute", type="integer", example=75)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistic updated",
     *         @OA\JsonContent(ref="#/components/schemas/Statistic")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Statistic not found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid data"
     *     )
     * )
     */
    $app->put('/{id}', function($id) {
        $request = Flight::request();
        $data = $request->data->getData();
        
        $statisticService = new StatisticService();
        return $statisticService->updateStatistic($id, $data);
    });
    
    /**
     * @OA\Delete(
     *     path="/api/statistics/{id}",
     *     summary="Delete statistic",
     *     tags={"Statistics"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Statistic ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Statistic deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Statistic deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Statistic not found"
     *     )
     * )
     */
    $app->delete('/{id}', function($id) {
        $statisticService = new StatisticService();
        return $statisticService->deleteStatistic($id);
    });
});