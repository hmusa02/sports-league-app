<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));

class Config
{
    public static function DB_NAME() {
        return $_ENV['DB_NAME'] ?? getenv('MYSQLDATABASE') ?? getenv('DB_NAME') ?? 'sportsleague';
    }
    
    public static function DB_PORT() {
        return $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 3306;  
    }
    
    public static function DB_USER() {
        return $_ENV['DB_USER'] ?? getenv('MYSQLUSER') ?? getenv('DB_USER') ?? 'root';
    }
    
    public static function DB_PASSWORD() {
        return $_ENV['DB_PASSWORD'] ?? getenv('MYSQLPASSWORD') ?? getenv('DB_PASSWORD') ?? 'bvbreus11';
    }
    
    public static function DB_HOST() {
        return $_ENV['DB_HOST'] ?? getenv('MYSQLHOST') ?? getenv('DB_HOST') ?? '127.0.0.1';
    }

    // JWT Secret Key 
    public static function JWT_SECRET() {
        return $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?? '3016ba4cf697b81194c57520c644edba054b0295a38dcc9ab662f716a882240c';
    }
    
    // Environment helpers
    public static function isProduction() {
        return ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'development') === 'production';
    }
    
    public static function getCorsOrigin() {
        return $_ENV['CORS_ORIGIN'] ?? getenv('CORS_ORIGIN') ?? 'http://localhost:8888';
    }
}

class Database {
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . Config::DB_HOST() . ";port=" . Config::DB_PORT() . ";dbname=" . Config::DB_NAME();
            $this->conn = new PDO($dsn, Config::DB_USER(), Config::DB_PASSWORD());
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
            
            if (Config::isProduction()) {
                // Production database settings
                $this->conn->setAttribute(PDO::ATTR_PERSISTENT, false);
                $this->conn->setAttribute(PDO::ATTR_TIMEOUT, 30);
            }
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            
            if (Config::isProduction()) {
                // Don't expose sensitive info in production
                throw new Exception("Database connection failed");
            } else {
                echo "Connection error: " . $exception->getMessage();
            }
        }

        return $this->conn;
    }
}