<?php
header('Content-Type: application/json');

$swagger = [
    "openapi" => "3.0.0",
    "info" => [
        "title" => "Sports League API",
        "version" => "1.0.0",
        "description" => "Official API documentation for the Sports League web platform. This API enables full CRUD operations for managing users, teams, players, matches, and statistics.",
        "contact" => [
            "name" => "Sports League Team",
            "email" => "admin@sportsleague.com"
        ]
    ],
    "servers" => [
        [
            "url" => "http://localhost:8888/sports-league-app/backend",
            "description" => "Local API Server"
        ],
        [
            "url" => "https://add-production-server-after-deployment/backend",
            "description" => "Production API Server"
        ]
    ],
    "components" => [
        "securitySchemes" => [
            "SessionAuth" => [
                "type" => "apiKey",
                "in" => "cookie",
                "name" => "PHPSESSID"
            ]
        ]
    ],
    "paths" => [
        "/api/health" => [
            "get" => [
                "summary" => "Health Check",
                "description" => "Check if the API is running",
                "responses" => [
                    "200" => [
                        "description" => "API is running",
                        "content" => [
                            "application/json" => [
                                "schema" => [
                                    "type" => "object",
                                    "properties" => [
                                        "success" => ["type" => "boolean"],
                                        "message" => ["type" => "string"],
                                        "timestamp" => ["type" => "string"]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        "/api/users/register" => [
            "post" => [
                "summary" => "Register new user",
                "description" => "Create a new user account",
                "requestBody" => [
                    "required" => true,
                    "content" => [
                        "application/json" => [
                            "schema" => [
                                "type" => "object",
                                "required" => ["username", "password", "email"],
                                "properties" => [
                                    "username" => [
                                        "type" => "string",
                                        "example" => "admin"
                                    ],
                                    "password" => [
                                        "type" => "string",
                                        "example" => "123456"
                                    ],
                                    "email" => [
                                        "type" => "string",
                                        "example" => "admin@test.com"
                                    ],
                                    "role" => [
                                        "type" => "string",
                                        "enum" => ["user", "admin"],
                                        "example" => "user"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "responses" => [
                    "201" => [
                        "description" => "User created successfully",
                        "content" => [
                            "application/json" => [
                                "schema" => [
                                    "type" => "object",
                                    "properties" => [
                                        "success" => ["type" => "boolean"],
                                        "message" => ["type" => "string"],
                                        "data" => [
                                            "type" => "object",
                                            "properties" => [
                                                "user_id" => ["type" => "integer"]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "400" => [
                        "description" => "Bad request"
                    ]
                ]
            ]
        ],
        "/api/users" => [
            "get" => [
                "summary" => "Get all users",
                "description" => "Retrieve list of all users",
                "responses" => [
                    "200" => [
                        "description" => "List of users",
                        "content" => [
                            "application/json" => [
                                "schema" => [
                                    "type" => "object",
                                    "properties" => [
                                        "success" => ["type" => "boolean"],
                                        "data" => [
                                            "type" => "array",
                                            "items" => [
                                                "type" => "object",
                                                "properties" => [
                                                    "user_id" => ["type" => "integer"],
                                                    "username" => ["type" => "string"],
                                                    "email" => ["type" => "string"],
                                                    "role" => ["type" => "string"],
                                                    "created_at" => ["type" => "string"]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        "/api/teams" => [
            "get" => [
                "summary" => "Get all teams",
                "description" => "Retrieve list of all teams",
                "responses" => [
                    "200" => [
                        "description" => "List of teams",
                        "content" => [
                            "application/json" => [
                                "schema" => [
                                    "type" => "object",
                                    "properties" => [
                                        "success" => ["type" => "boolean"],
                                        "data" => [
                                            "type" => "array",
                                            "items" => [
                                                "type" => "object",
                                                "properties" => [
                                                    "team_id" => ["type" => "integer"],
                                                    "team_name" => ["type" => "string"],
                                                    "city" => ["type" => "string"],
                                                    "coach_id" => ["type" => "integer"],
                                                    "coach_name" => ["type" => "string"]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];

echo json_encode($swagger, JSON_PRETTY_PRINT);
?>