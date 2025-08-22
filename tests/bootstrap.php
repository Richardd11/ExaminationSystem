<?php

// Bootstrap file for PHPUnit tests
// This helps resolve session and output buffering issues

// Include the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Start output buffering to prevent "headers already sent" errors
if (ob_get_level() === 0) {
    ob_start();
}

// Set up test environment
$_ENV['APP_ENV'] = 'testing';

// Configure session settings for testing (but don't start sessions automatically)
ini_set('session.use_cookies', '0');
ini_set('session.use_only_cookies', '0');
ini_set('session.cache_limiter', '');

// Reset superglobals for clean test state
$_SESSION = [];
$_GET = [];
$_POST = [];
$_REQUEST = [];
$_COOKIE = [];
$_FILES = [];
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/index.php';