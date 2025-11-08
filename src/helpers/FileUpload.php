<?php

require_once __DIR__ . '/ErrorHandler.php';
require_once __DIR__ . '/../config/Config.php';

class FileUpload {
    
    /**
     * Upload product image with standardized naming: product_{id}_{timestamp}.{ext}
     * 
     * @param array $file $_FILES array element
     * @param int $productId Product ID for naming
     * @param string|null $oldImagePath Old image path to delete
     * @return array Result with success status, filename or error message
     */
    public static function uploadProductImage($file, $productId, $oldImagePath = null) {
        $config = [
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
            'max_size' => Config::MAX_FILE_SIZE,
            'upload_dir' => __DIR__ . '/../../public/uploads/products/',
            'prefix' => 'product_' . $productId . '_'
        ];
        
        $result = self::handleUpload($file, $config);
        
        // Delete old image if upload was successful and old image exists
        if ($result['success'] && $oldImagePath) {
            self::deleteOldImage($oldImagePath);
        }
        
        return $result;
    }
    
    /**
     * Upload user profile picture with standardized naming: user_{id}_{timestamp}.{ext}
     * 
     * @param array $file $_FILES array element
     * @param int $userId User ID for naming
     * @param string|null $oldImagePath Old image path to delete
     * @return array Result with success status, filename or error message
     */
    public static function uploadUserProfile($file, $userId, $oldImagePath = null) {
        $config = [
            'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
            'max_size' => Config::MAX_FILE_SIZE,
            'upload_dir' => __DIR__ . '/../../public/uploads/profiles/',
            'prefix' => 'user_' . $userId . '_'
        ];
        
        $result = self::handleUpload($file, $config);
        
        // Delete old image if upload was successful and old image exists
        if ($result['success'] && $oldImagePath) {
            self::deleteOldImage($oldImagePath);
        }
        
        return $result;
    }
    
    /**
     * Generic file upload handler
     * 
     * @param array $file $_FILES array element
     * @param array $config Upload configuration
     * @return array Result with success status, filename or error message
     */
    private static function handleUpload($file, $config) {
        try {
            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = self::getUploadError($file['error']);
                ErrorHandler::log("File upload error: {$error}", 'WARNING', ['file' => $file['name']]);
                return ['success' => false, 'error' => $error];
            }
            
            // Check if file was actually uploaded
            if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                return ['success' => false, 'error' => 'No file uploaded'];
            }
            
            // Check file size
            if ($file['size'] > $config['max_size']) {
                $maxMB = round($config['max_size'] / 1024 / 1024, 2);
                return ['success' => false, 'error' => "File size exceeds limit of {$maxMB}MB"];
            }
            
            // Check MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $config['allowed_types'])) {
                return ['success' => false, 'error' => 'Invalid file type. Only images (JPG, PNG, GIF, WEBP) are allowed.'];
            }
            
            // Get file extension
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Validate extension matches MIME type
            $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $validExtensions)) {
                $extension = self::getExtensionFromMime($mimeType);
            }
            
            // Generate unique filename with prefix
            $timestamp = microtime(true) * 10000; // Use microtime for better uniqueness
            $random = mt_rand(1000, 9999); // Add random component for extra uniqueness
            $filename = $config['prefix'] . $timestamp . '_' . $random . '.' . $extension;
            
            // Ensure upload directory exists
            if (!is_dir($config['upload_dir'])) {
                if (!mkdir($config['upload_dir'], 0755, true)) {
                    ErrorHandler::log('Failed to create upload directory', 'ERROR', ['dir' => $config['upload_dir']]);
                    return ['success' => false, 'error' => 'Failed to create upload directory'];
                }
            }
            
            // Move uploaded file
            $destination = $config['upload_dir'] . $filename;
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                ErrorHandler::log('Failed to move uploaded file', 'ERROR', ['destination' => $destination]);
                return ['success' => false, 'error' => 'Failed to save uploaded file'];
            }
            
            // Set proper permissions
            chmod($destination, 0644);
            
            return ['success' => true, 'filename' => $filename];
            
        } catch (Exception $e) {
            ErrorHandler::log('File upload exception: ' . $e->getMessage(), 'ERROR', ['file' => $file['name'] ?? 'unknown']);
            return ['success' => false, 'error' => 'An error occurred during file upload'];
        }
    }
    
    /**
     * Delete old image file
     * 
     * @param string $imagePath Image path relative to uploads directory
     * @return bool Success status
     */
    public static function deleteOldImage($imagePath) {
        if (empty($imagePath)) {
            return false;
        }
        
        // Handle both full paths and relative paths
        $basePath = __DIR__ . '/../../public/uploads/';
        
        // If path already contains 'uploads/', extract the relative part
        if (strpos($imagePath, 'uploads/') !== false) {
            $imagePath = substr($imagePath, strpos($imagePath, 'uploads/') + 8);
        }
        
        $fullPath = $basePath . $imagePath;
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            try {
                return unlink($fullPath);
            } catch (Exception $e) {
                ErrorHandler::log('Failed to delete old image: ' . $e->getMessage(), 'WARNING', ['path' => $fullPath]);
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Get upload error message
     * 
     * @param int $errorCode Upload error code
     * @return string Error message
     */
    private static function getUploadError($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive in HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Get file extension from MIME type
     * 
     * @param string $mimeType MIME type
     * @return string File extension
     */
    private static function getExtensionFromMime($mimeType) {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        
        return $mimeMap[$mimeType] ?? 'jpg';
    }
    
    /**
     * Validate image file
     * 
     * @param string $filePath Full path to image file
     * @return bool True if valid image
     */
    public static function isValidImage($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $imageInfo = @getimagesize($filePath);
        return $imageInfo !== false;
    }
}
