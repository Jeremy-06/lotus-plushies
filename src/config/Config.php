<?php

class Config {
    // Database configuration
    const DB_HOST = 'localhost';
    const DB_NAME = 'im_final_project';
    const DB_USER = 'root';
    const DB_PASS = '';
    
    // Application configuration
    const APP_NAME = 'Lotus Plushies';
    const BASE_URL = 'http://localhost/lotus-plushies/public';
    
    // Timezone configuration (adjust to your timezone)
    const TIMEZONE = 'Asia/Manila'; // Change this to your timezone
    
    // Upload configuration
    const UPLOAD_DIR = __DIR__ . '/../../public/uploads/';
    const MAX_FILE_SIZE = 5242880; // 5MB
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Pagination
    const ITEMS_PER_PAGE = 12;
    
    // Session configuration
    const SESSION_LIFETIME = 3600; // 1 hour
}