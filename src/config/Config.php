<?php

class Config {
    // Database configuration
    const DB_HOST = 'localhost';
    const DB_NAME = 'im_final_project';
    const DB_USER = 'root';
    const DB_PASS = '';
    
    // Application configuration
    const APP_NAME = 'Online Shop';
    const BASE_URL = 'http://localhost/IM-final-project/public';
    
    // Upload configuration
    const UPLOAD_DIR = __DIR__ . '/../../public/uploads/';
    const MAX_FILE_SIZE = 5242880; // 5MB
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Pagination
    const ITEMS_PER_PAGE = 12;
    
    // Session configuration
    const SESSION_LIFETIME = 3600; // 1 hour
}