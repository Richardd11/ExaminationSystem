<?php

namespace App\Config;

class App
{
    const APP_NAME = 'Examination System';
    const APP_VERSION = '2.0.0';
    const APP_ENV = 'development'; // development, production
    
    const BASE_URL = 'http://localhost';
    const BASE_PATH = '/';
    
    // Session configuration
    const SESSION_LIFETIME = 3600; // 1 hour in seconds
    const SESSION_NAME = 'exam_system_session';
    
    // Security settings
    const BCRYPT_COST = 12;
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOGIN_LOCKOUT_TIME = 900; // 15 minutes in seconds
    
    // Pagination
    const DEFAULT_PAGE_SIZE = 20;
    const MAX_PAGE_SIZE = 100;
    
    // File upload settings
    const MAX_FILE_SIZE = 5242880; // 5MB in bytes
    const ALLOWED_FILE_TYPES = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    
    // Exam settings
    const DEFAULT_EXAM_DURATION = 60; // minutes
    const MAX_EXAM_DURATION = 300; // 5 hours in minutes
    
    /**
     * Get configuration value
     */
    public static function get($key, $default = null)
    {
        return defined("self::$key") ? constant("self::$key") : $default;
    }
    
    /**
     * Check if app is in debug mode
     */
    public static function isDebug()
    {
        return self::APP_ENV === 'development';
    }
    
    /**
     * Get full URL
     */
    public static function url($path = '')
    {
        return rtrim(self::BASE_URL . self::BASE_PATH, '/') . '/' . ltrim($path, '/');
    }
}