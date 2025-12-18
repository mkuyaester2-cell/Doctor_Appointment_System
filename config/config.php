<?php
// config/config.php

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'docbook');

// Application Settings
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Get the directory where config.php is located, then go up one level to get project root
$project_root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$doc_root = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
// Calculate the relative path from document root to project root
$base_dir = str_replace($doc_root, '', $project_root);
// Ensure it starts with a / and doesn't end with one
$base_dir = '/' . ltrim($base_dir, '/');
$base_dir = rtrim($base_dir, '/');

define('APP_NAME', 'DocBook');
define('APP_URL', $protocol . '://' . $host . $base_dir);

// Error Reporting 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set Timezone
date_default_timezone_set('Asia/Kolkata'); 

// Start Session if it is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

