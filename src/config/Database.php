<?php

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/../helpers/ErrorHandler.php';

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            // Attempt to connect
            $this->conn = mysqli_connect(
                Config::DB_HOST,
                Config::DB_USER,
                Config::DB_PASS,
                Config::DB_NAME
            );
            
            if (!$this->conn) {
                $error = mysqli_connect_error();
                ErrorHandler::log("Database connection failed: {$error}", 'CRITICAL');
                // For localhost: show the actual error to help with debugging
                die("Database Connection Failed: " . $error . "<br>Check your Config.php settings.");
            }
            
            // Set charset with error handling
            if (!mysqli_set_charset($this->conn, "utf8mb4")) {
                ErrorHandler::log("Failed to set charset: " . mysqli_error($this->conn), 'WARNING');
            }
            
            // Enable exception mode for better error handling
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
        } catch (Exception $e) {
            ErrorHandler::log("Database initialization error: " . $e->getMessage(), 'CRITICAL');
            die("Database Error: " . $e->getMessage());
        }
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
        try {
            $result = mysqli_query($this->conn, $sql);
            if ($result === false) {
                ErrorHandler::log("Query failed: " . mysqli_error($this->conn), 'ERROR', ['sql' => $sql]);
                return false;
            }
            return $result;
        } catch (Exception $e) {
            ErrorHandler::log("Query exception: " . $e->getMessage(), 'ERROR', ['sql' => $sql]);
            return false;
        }
    }
    
    public function prepare($sql) {
        try {
            $stmt = mysqli_prepare($this->conn, $sql);
            if ($stmt === false) {
                ErrorHandler::log("Prepare failed: " . mysqli_error($this->conn), 'ERROR', ['sql' => $sql]);
                return false;
            }
            return $stmt;
        } catch (Exception $e) {
            ErrorHandler::log("Prepare exception: " . $e->getMessage(), 'ERROR', ['sql' => $sql]);
            return false;
        }
    }
    
    public function escape($value) {
        return mysqli_real_escape_string($this->conn, $value);
    }
    
    public function getLastId() {
        return mysqli_insert_id($this->conn);
    }
    
    public function getError() {
        return mysqli_error($this->conn);
    }
    
    public function beginTransaction() {
        return mysqli_begin_transaction($this->conn);
    }
    
    public function commit() {
        return mysqli_commit($this->conn);
    }
    
    public function rollback() {
        return mysqli_rollback($this->conn);
    }
}