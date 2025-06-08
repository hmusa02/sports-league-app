<?php
// Railway Deployment Router
// This file handles routing for both frontend and backend

$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remove query string
$uri = strtok($request_uri, '?');

// API Routes - redirect to backend
if (strpos($uri, '/api/') === 0) {
    // Set the document root to backend for API calls
    $_SERVER['SCRIPT_NAME'] = '/backend/index.php';
    $_SERVER['REQUEST_URI'] = $uri;
    
    require_once __DIR__ . '/backend/index.php';
    exit();
}

// Frontend Routes - serve static files
if ($uri === '/' || $uri === '/index.html') {
    require_once __DIR__ . '/frontend/index.html';
    exit();
}

// CSS Files
if (strpos($uri, '/css/') === 0) {
    $file = __DIR__ . '/frontend' . $uri;
    if (file_exists($file)) {
        header('Content-Type: text/css');
        readfile($file);
        exit();
    }
}

// JavaScript Files
if (strpos($uri, '/js/') === 0) {
    $file = __DIR__ . '/frontend' . $uri;
    if (file_exists($file)) {
        header('Content-Type: application/javascript');
        readfile($file);
        exit();
    }
}

// HTML Views
if (strpos($uri, '/views/') === 0) {
    $file = __DIR__ . '/frontend' . $uri;
    if (file_exists($file)) {
        header('Content-Type: text/html');
        readfile($file);
        exit();
    }
}

// Static assets (images, etc.)
if (preg_match('/\.(jpg|jpeg|png|gif|ico|svg)$/', $uri)) {
    $file = __DIR__ . '/frontend' . $uri;
    if (file_exists($file)) {
        $mime_type = mime_content_type($file);
        header('Content-Type: ' . $mime_type);
        readfile($file);
        exit();
    }
}

// Default - serve index.html for SPA routing
require_once __DIR__ . '/frontend/index.html'; 