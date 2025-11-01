<?php

/**
 * ErrorHandler - Centralized error logging and handling (simplified for localhost)
 */
class ErrorHandler {
    
    /**
     * Log an error - for localhost, just use PHP's error_log
     * 
     * @param string $message Error message
     * @param string $type Error type (ERROR, WARNING, INFO)
     * @param array $context Additional context
     */
    public static function log($message, $type = 'ERROR', $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] [{$type}] {$message}{$contextStr}";
        
        // For localhost: just use PHP's built-in error_log (goes to XAMPP error log)
        error_log($logEntry);
    }
    
    /**
     * Handle database errors gracefully
     * 
     * @param string $operation Operation that failed
     * @param mixed $error Error object or message
     * @return void
     */
    public static function handleDatabaseError($operation, $error) {
        $errorMsg = is_object($error) ? $error->getMessage() : $error;
        self::log("Database error during {$operation}: {$errorMsg}", 'ERROR');
    }
    
    /**
     * Handle file upload errors
     * 
     * @param int $errorCode PHP file upload error code
     * @return string User-friendly error message
     */
    public static function getFileUploadError($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File is too large';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Display error page and exit
     * 
     * @param string $title Error title
     * @param string $message Error message
     * @param int $code HTTP status code
     */
    public static function showErrorPage($title, $message, $code = 500) {
        http_response_code($code);
        
        if (file_exists(__DIR__ . '/../views/error.php')) {
            $error = ['title' => $title, 'message' => $message, 'code' => $code];
            include __DIR__ . '/../views/error.php';
        } else {
            echo "<h1>{$title}</h1><p>{$message}</p>";
        }
        exit();
    }
    
    /**
     * Check if operation succeeded and handle failure
     * 
     * @param mixed $result Operation result
     * @param string $operation Operation name
     * @param string $errorMessage User-friendly error message
     * @return bool
     */
    public static function assertSuccess($result, $operation, $errorMessage = null) {
        if (!$result) {
            $msg = $errorMessage ?? "Failed to {$operation}";
            self::log($msg, 'ERROR', ['operation' => $operation]);
            return false;
        }
        return true;
    }
}
