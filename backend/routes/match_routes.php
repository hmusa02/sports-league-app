<?php
// routes/match_routes.php

require_once __DIR__ . '/../services/MatchService.php';

/**
 * @OA\Tag(
 *     name="Matches",
 *     description="Match management endpoints"
 * )
 */

$app->group('/api/matches', function() use ($app) {
    
    /**
     * @OA\Get(
     *     path="/api/matches",
     *     summary="Get all matches",
     *     tags={"Matches"},
     *     @OA\Response(
     *         response=200,
     *         description="List of matches",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Match")
     *         )
     *     )
     * )
     */
    $app->get('', function() {
        $matchService = new MatchService();
        return $matchService->getAllMatches();
    });
    
    /**
     * @OA\Get(
     *     path="/api/matches/{id}",
     *     summary="Get match by ID",
     *     tags={"Matches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Match ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Match found",
     *         @OA\JsonContent(ref="#/components/schemas/Match")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Match not found"
     *     )
     * )
     */
    $app->get('/{id}', function($id) {
        $matchService = new MatchService();
        return $matchService->getMatchById($id);
    });
    
    /**
     * @OA\Get(
     *     path="/api/matches/upcoming",
     *     summary="Get upcoming matches",
     *     tags={"Matches"},
     *     @OA\Response(
     *         response=200,
     *         description="List of upcoming matches",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Match")
     *         )
     *     )
     * )
     */
    $app->get('/upcoming', function() {
        $matchService = new MatchService();
        return $matchService->getUpcomingMatches();
    });
    
    /**
     * @OA\Get(
     *     path="/api/matches/team/{teamId}",
     *     summary="Get matches by team ID",
     *     tags={"Matches"},
     *     @OA\Parameter(
     *         name="teamId",
     *         in="path",
     *         required=true,
     *         description="Team ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of matches for the specified team",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Match")
     *         )
     *     )
     * )
     */
    $app->get('/team/{teamId}', function($teamId) {
        $matchService = new MatchService();
        return $matchService->getMatchesByTeam($teamId);
      });
    
    /**
     * @OA\Post(
     *     path="/api/matches",
     *     summary="Create a new match",
     *     tags={"Matches"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"home_team_id", "away_team_id", "date_played"},
     *             @OA\Property(property="home_team_id", type="integer", example=1),
     *             @OA\Property(property="away_team_id", type="integer", example=2),
     *             @OA\Property(property="date_played", type="string", format="date-time", example="2025-05-15 15:00:00"),
     *             @OA\Property(property="score_home", type="integer", example=2),
     *             @OA\Property(property="score_away", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Match created",
     *         @OA\JsonContent(ref="#/components/schemas/Match")
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
        
        $matchService = new MatchService();
        return $matchService->createMatch($data);
    });
    
    /**
     * @OA\Put(
     *     path="/api/matches/{id}",
     *     summary="Update match",
     *     tags={"Matches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Match ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"home_team_id", "away_team_id", "date_played"},
     *             @OA\Property(property="home_team_id", type="integer", example=1),
     *             @OA\Property(property="away_team_id", type="integer", example=2),
     *             @OA\Property(property="date_played", type="string", format="date-time", example="2025-05-15 15:00:00"),
     *             @OA\Property(property="score_home", type="integer", example=2),
     *             @OA\Property(property="score_away", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Match updated",
     *         @OA\JsonContent(ref="#/components/schemas/Match")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Match not found"
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
        
        $matchService = new MatchService();
        return $matchService->updateMatch($id, $data);
    });
    
    /**
     * @OA\Delete(
     *     path="/api/matches/{id}",
     *     summary="Delete match",
     *     tags={"Matches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Match ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Match deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Match deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Match not found"
     *     )
     * )
     */
    $app->delete('/{id}', function($id) {
        $matchService = new MatchService();
        return $matchService->deleteMatch($id);
    });
    
    /**
     * @OA\Get(
     *     path="/api/matches/{id}/stats",
     *     summary="Get match statistics",
     *     tags={"Matches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Match ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Match statistics",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Statistic")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Match not found"
     *     )
     * )
     */
    $app->get('/{id}/stats', function($id) {
        $matchService = new MatchService();
        return $matchService->getMatchStats($id);
    });
});