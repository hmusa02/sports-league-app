<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));

class Config
{
    public static function DB_NAME() {
        return 'sportsleague';
    }
    
    public static function DB_PORT() {
        return 3306;  
    }
    
    public static function DB_USER() {
        return 'root';
    }
    
    public static function DB_PASSWORD() {
        return 'bvbreus11';
    }
    
    public static function DB_HOST() {
        return '127.0.0.1';
    }

    // JWT Secret Key 
    public static function JWT_SECRET() {
        return 'sports_league_secret_key_2025_very_secure_string';
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
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}