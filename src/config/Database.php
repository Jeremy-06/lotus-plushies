<?php

require_once __DIR__ . '/Config.php';

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $this->conn = mysqli_connect(
            Config::DB_HOST,
            Config::DB_USER,
            Config::DB_PASS,
            Config::DB_NAME
        );
        
        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        
        mysqli_set_charset($this->conn, "utf8mb4");
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql) {
        return mysqli_query($this->conn, $sql);
    }
    
    public function prepare($sql) {
        return mysqli_prepare($this->conn, $sql);
    }
    
    public function escape($value) {
        return mysqli_real_escape_string($this->conn, $value);
    }
    
    public function getLastId() {
        return mysqli_insert_id($this->conn);
    }
}