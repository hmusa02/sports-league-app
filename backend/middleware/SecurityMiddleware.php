<?php

class SecurityMiddleware {
    
    // Rate limiting configuration
    private static $rateLimits = [
        'login' => ['max' => 5, 'window' => 300], // 5 attempts per 5 minutes
        'register' => ['max' => 3, 'window' => 3600], // 3 attempts per hour
        'api' => ['max' => 100, 'window' => 60] // 100 requests per minute
    ];
    
    // Check for security headers
    public static function setSecurityHeaders() {
        // Prevent XSS
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        
        // CORS headers for development
        header('Access-Control-Allow-Origin: http://localhost:8888');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: true');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    
    // Rate limiting based on IP and endpoint
    public static function checkRateLimit($endpoint = 'api') {
        $ip = self::getClientIp();
        $key = $endpoint . '_' . $ip;
        
        if (!isset(self::$rateLimits[$endpoint])) {
            $endpoint = 'api'; // fallback to default
        }
        
        $limit = self::$rateLimits[$endpoint];
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($key) . '.txt';
        
        $attempts = [];
        if (file_exists($cacheFile)) {
            $data = file_get_contents($cacheFile);
            if ($data) {
                $attempts = json_decode($data, true) ?: [];
            }
        }
        
        // Clean old attempts
        $now = time();
        $attempts = array_filter($attempts, function($timestamp) use ($now, $limit) {
            return ($now - $timestamp) < $limit['window'];
        });
        
        // Check if limit exceeded
        if (count($attempts) >= $limit['max']) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => 'Rate limit exceeded. Please try again later.'
            ]);
            exit();
        }
        
        // Record this attempt
        $attempts[] = $now;
        file_put_contents($cacheFile, json_encode($attempts));
    }
    
    // Get real client IP
    private static function getClientIp() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                   'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 
                   'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, 
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    // Validate request content type
    public static function validateContentType() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') === false) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid content type. Expected application/json.'
                ]);
                exit();
            }
        }
    }
    
    // Validate request size
    public static function validateRequestSize($maxSize = 1048576) { // 1MB default
        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
        
        if ($contentLength > $maxSize) {
            http_response_code(413);
            echo json_encode([
                'success' => false,
                'message' => 'Request entity too large.'
            ]);
            exit();
        }
    }
    
    // Sanitize request URI
    public static function sanitizeRequestUri() {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Check for common attack patterns
        $maliciousPatterns = [
            '/\.\./i',           // Directory traversal
            '/union.*select/i',  // SQL injection
            '/<script/i',        // XSS
            '/javascript:/i',    // XSS
            '/vbscript:/i',      // XSS
            '/onload/i',         // XSS
            '/onerror/i',        // XSS
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Malicious request detected.'
                ]);
                exit();
            }
        }
    }
    
    // Check for suspicious user agents
    public static function validateUserAgent() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Common attack user agents
        $suspiciousAgents = [
            'sqlmap',
            'nikto',
            'nessus',
            'burp',
            'w3af',
            'acunetix',
            'vega',
            'webkit2png',
            'wkhtmltopdf'
        ];
        
        foreach ($suspiciousAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Forbidden.'
                ]);
                exit();
            }
        }
    }
    
    // Log security events
    public static function logSecurityEvent($event, $details = []) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'event' => $event,
            'details' => $details
        ];
        
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    // Apply all security checks
    public static function applySecurityChecks($endpoint = 'api') {
        self::setSecurityHeaders();
        self::sanitizeRequestUri();
        self::validateUserAgent();
        self::validateRequestSize();
        self::validateContentType();
        self::checkRateLimit($endpoint);
    }
} 