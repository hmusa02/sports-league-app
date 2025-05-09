<?php
// routes/team_routes.php

require_once __DIR__ . '/../services/TeamService.php';

/**
 * @OA\Tag(
 *     name="Teams",
 *     description="Team management endpoints"
 * )
 */

$app->group('/api/teams', function() use ($app) {
    
    /**
     * @OA\Get(
     *     path="/api/teams",
     *     summary="Get all teams",
     *     tags={"Teams"},
     *     @OA\Response(
     *         response=200,
     *         description="List of teams",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Team")
     *         )
     *     )
     * )
     */
    $app->get('', function() {
        $teamService = new TeamService();
        return $teamService->getAllTeams();
    });
    
    /**
     * @OA\Get(
     *     path="/api/teams/{id}",
     *     summary="Get team by ID",
     *     tags={"Teams"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Team ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Team found",
     *         @OA\JsonContent(ref="#/components/schemas/Team")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Team not found"
     *     )
     * )
     */
    $app->get('/{id}', function($id) {
        $teamService = new TeamService();
        return $teamService->getTeamById($id);
    });
    
    /**
     * @OA\Post(
     *     path="/api/teams",
     *     summary="Create a new team",
     *     tags={"Teams"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"team_name", "city"},
     *             @OA\Property(property="team_name", type="string", example="FC Barcelona"),
     *             @OA\Property(property="city", type="string", example="Barcelona"),
     *             @OA\Property(property="coach_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Team created",
     *         @OA\JsonContent(ref="#/components/schemas/Team")
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
        
        $teamService = new TeamService();
        return $teamService->createTeam($data);
    });
    
    /**
     * @OA\Put(
     *     path="/api/teams/{id}",
     *     summary="Update team",
     *     tags={"Teams"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Team ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"team_name", "city"},
     *             @OA\Property(property="team_name", type="string", example="FC Barcelona"),
     *             @OA\Property(property="city", type="string", example="Barcelona"),
     *             @OA\Property(property="coach_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Team updated",
     *         @OA\JsonContent(ref="#/components/schemas/Team")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Team not found"
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
        
        $teamService = new TeamService();
        return $teamService->updateTeam($id, $data);
    });
    
    /**
     * @OA\Delete(
     *     path="/api/teams/{id}",
     *     summary="Delete team",
     *     tags={"Teams"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Team ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Team deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Team deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Team not found"
     *     )
     * )
     */
    $app->delete('/{id}', function($id) {
        $teamService = new TeamService();
        return $teamService->deleteTeam($id);
    });
    
    /**
     * @OA\Get(
     *     path="/api/teams/{id}/players",
     *     summary="Get team players",
     *     tags={"Teams"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Team ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of team players",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Player")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Team not found"
     *     )
     * )
     */
    $app->get('/{id}/players', function($id) {
        $teamService = new TeamService();
        return $teamService->getTeamPlayers($id);
    });
});