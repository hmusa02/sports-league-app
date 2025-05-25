<?php
/**
 * @OA\OpenApi(
 *   @OA\Info(
 *     title="Sports League API",
 *     version="1.0.0",
 *     description="Official API documentation for the Sports League web platform. This API enables full CRUD operations for managing users, teams, players, matches, and statistics.",
 *     @OA\Contact(
 *       name="Sports League Team",
 *       email="admin@sportsleague.com"
 *     )
 *   ),
 *   @OA\Server(
 *     url="http://localhost:8888/sports-league-app/backend/",
 *     description="Local API Server"
 *   ),
 *   @OA\Server(
 *     url="https://add-production-server-after-deployment/backend/",
 *     description="Production API Server"
 *   )
 * )
 * @OA\SecurityScheme(
 *     securityScheme="SessionAuth",
 *     type="apiKey",
 *     in="cookie",
 *     name="PHPSESSID"
 * )
 */

