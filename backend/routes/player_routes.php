<?php
// routes/player_routes.php

require_once __DIR__ . '/../services/PlayerService.php';

/**
 * @OA\Tag(
 *     name="Players",
 *     description="Player management endpoints"
 * )
 */

$app->group('/api/players', function() use ($app) {
    
    /**
     * @OA\Get(
     *     path="/api/players",
     *     summary="Get all players",
     *     tags={"Players"},
     *     @OA\Response(
     *         response=200,
     *         description="List of players",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Player")
     *         )
     *     )
     * )
     */
    $app->get('', function() {
        $playerService = new PlayerService();
        return $playerService->getAllPlayers();
    });
    
    /**
     * @OA\Get(
     *     path="/api/players/{id}",
     *     summary="Get player by ID",
     *     tags={"Players"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Player ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Player found",
     *         @OA\JsonContent(ref="#/components/schemas/Player")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Player not found"
     *     )
     * )
     */
    $app->get('/{id}', function($id) {
        $playerService = new PlayerService();
        return $playerService->getPlayerById($id);
    });
    
    /**
     * @OA\Post(
     *     path="/api/players",
     *     summary="Create a new player",
     *     tags={"Players"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "position", "team_id"},
     *             @OA\Property(property="first_name", type="string", example="Lionel"),
     *             @OA\Property(property="last_name", type="string", example="Messi"),
     *             @OA\Property(property="position", type="string", example="Forward"),
     *             @OA\Property(property="team_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Player created",
     *         @OA\JsonContent(ref="#/components/schemas/Player")
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
        
        $playerService = new PlayerService();
        return $playerService->createPlayer($data);
    });
    
    /**
     * @OA\Put(
     *     path="/api/players/{id}",
     *     summary="Update player",
     *     tags={"Players"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Player ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "position", "team_id"},
     *             @OA\Property(property="first_name", type="string", example="Lionel"),
     *             @OA\Property(property="last_name", type="string", example="Messi"),
     *             @OA\Property(property="position", type="string", example="Forward"),
     *             @OA\Property(property="team_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Player updated",
     *         @OA\JsonContent(ref="#/components/schemas/Player")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Player not found"
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
        
        $playerService = new PlayerService();
        return $playerService->updatePlayer($id, $data);
    });
    
    /**
     * @OA\Delete(
     *     path="/api/players/{id}",
     *     summary="Delete player",
     *     tags={"Players"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Player ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Player deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Player deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Player not found"
     *     )
     * )
     */
    $app->delete('/{id}', function($id) {
        $playerService = new PlayerService();
        return $playerService->deletePlayer($id);
    });
    
    /**
     * @OA\Get(
     *     path="/api/players/{id}/stats",
     *     summary="Get player statistics",
     *     tags={"Players"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Player ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Player statistics",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Statistic")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Player not found"
     *     )
     * )
     */
    $app->get('/{id}/stats', function($id) {
        $playerService = new PlayerService();
        return $playerService->getPlayerStats($id);
    });
});