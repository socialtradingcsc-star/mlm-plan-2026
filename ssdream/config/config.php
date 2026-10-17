<?php
/**
 * Social Service Dream - Main Application Configuration
 * Technology: PHP 8+, MySQL, PDO
 * Target: XAMPP / cPanel / Shared Hosting
 */

// Prevent multiple definitions
if (!defined('SS_DREAM_INIT')) {
    define('SS_DREAM_INIT', true);
}

// Application Environment Mode ('development' or 'production')
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

// Error reporting settings based on Environment
if (APP_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . '/logs/error.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Timezone configuration (Indian Standard Time)
date_default_timezone_set('Asia/Kolkata');

// Session configuration - secure session settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Detect protocol and host dynamically for flexible hosting (localhost/XAMPP or live domain)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Base application path calculation
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseFolder = trim($scriptName, '/');

// Extract root ssdream folder
if (preg_match('#^([^/]+)#', $baseFolder, $matches)) {
    $subDir = '/' . $matches[1];
} else {
    $subDir = '/ssdream';
}

define('BASE_URL', $protocol . $host . $subDir);
define('ROOT_PATH', dirname(__DIR__));

// Application metadata
define('APP_NAME', 'Social Service Dream');
define('APP_TAGLINE', 'Empowering Financial Independence');
define('APP_VERSION', '1.0.0');
define('CURRENCY_SYMBOL', '₹');
define('CURRENCY_CODE', 'INR');

// Official Business Plan Constants (Strictly preserved from SS Dream document)
define('JOINING_PACKAGE_AMOUNT', 1200);
define('TOTAL_LEVELS', 7);
define('MATRIX_STRUCTURE', 'Binary');

// Package levels and team sizes
define('PLAN_PACKAGES', [
    1 => ['name' => 'Level 1', 'price' => 1200,   'team_size' => 2],
    2 => ['name' => 'Level 2', 'price' => 2000,   'team_size' => 4],
    3 => ['name' => 'Level 3', 'price' => 3000,   'team_size' => 8],
    4 => ['name' => 'Level 4', 'price' => 8000,   'team_size' => 16],
    5 => ['name' => 'Level 5', 'price' => 40000,  'team_size' => 32],
    6 => ['name' => 'Level 6', 'price' => 200000, 'team_size' => 64],
    7 => ['name' => 'Level 7', 'price' => 500000, 'team_size' => 128],
]);

// Official Income Categories (Preserved strictly as documented)
define('INCOME_CATEGORIES', [
    'direct'  => ['title' => 'Direct Income',  'percentage' => '100%', 'desc' => '100% Direct Sponsor Referral Income'],
    'level'   => ['title' => 'Level Income',   'percentage' => '50%',  'desc' => '50% Level Income from Level 2 to Level 7'],
    'sponsor' => ['title' => 'Sponsor Income', 'percentage' => '50%',  'desc' => '50% Sponsor Income from Level 2 to Unlimited Level'],
]);

// Database Credentials (Default XAMPP credentials)
define('DB_HOST', 'localhost');
define('DB_NAME', 'ssdream');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');
