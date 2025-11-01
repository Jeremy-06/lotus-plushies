<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/ErrorHandler.php';

class BaseModel {
    
    protected $db;
    protected $conn;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    public function findAll() {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $result = mysqli_query($this->conn, $sql);
            
            if ($result === false) {
                ErrorHandler::log("findAll failed for table {$this->table}: " . mysqli_error($this->conn), 'ERROR');
                return [];
            }
            
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        } catch (Exception $e) {
            ErrorHandler::log("findAll exception for table {$this->table}: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }
    
    public function findById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
            $stmt = mysqli_prepare($this->conn, $sql);
            
            if ($stmt === false) {
                ErrorHandler::log("findById prepare failed for table {$this->table}: " . mysqli_error($this->conn), 'ERROR');
                return null;
            }
            
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            return mysqli_fetch_assoc($result);
        } catch (Exception $e) {
            ErrorHandler::log("findById exception for table {$this->table}: " . $e->getMessage(), 'ERROR', ['id' => $id]);
            return null;
        }
    }
    
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $sql);
            
            if ($stmt === false) {
                ErrorHandler::log("delete prepare failed for table {$this->table}: " . mysqli_error($this->conn), 'ERROR');
                return false;
            }
            
            mysqli_stmt_bind_param($stmt, 'i', $id);
            $success = mysqli_stmt_execute($stmt);
            
            if (!$success) {
                ErrorHandler::log("delete failed for table {$this->table}: " . mysqli_error($this->conn), 'ERROR', ['id' => $id]);
            }
            
            return $success;
        } catch (Exception $e) {
            ErrorHandler::log("delete exception for table {$this->table}: " . $e->getMessage(), 'ERROR', ['id' => $id]);
            return false;
        }
    }
    
    protected function escape($value) {
        return mysqli_real_escape_string($this->conn, $value);
    }
    
    /**
     * Execute a query with error handling
     * 
     * @param string $sql SQL query
     * @param string $operation Operation name for logging
     * @return mixed Query result or false on failure
     */
    protected function executeQuery($sql, $operation = 'query') {
        try {
            $result = mysqli_query($this->conn, $sql);
            
            if ($result === false) {
                ErrorHandler::log("{$operation} failed: " . mysqli_error($this->conn), 'ERROR', ['sql' => $sql]);
                return false;
            }
            
            return $result;
        } catch (Exception $e) {
            ErrorHandler::log("{$operation} exception: " . $e->getMessage(), 'ERROR', ['sql' => $sql]);
            return false;
        }
    }
}