<?php
/**
 * Social Service Dream - Logout Handler
 * Safely clears user session variables and redirects to login
 * Target: C:\xampp\htdocs\ssdream\logout.php
 */

require_once __DIR__ . '/config/config.php';

// Unset user session variables
unset(
    $_SESSION['user_logged_in'],
    $_SESSION['user_id'],
    $_SESSION['user_username'],
    $_SESSION['user_fullname'],
    $_SESSION['user_email'],
    $_SESSION['current_level']
);

// Regenerate session ID to prevent fixation
if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
}

// Queue confirmation notification for the login screen
if (!isset($_SESSION['flash'])) {
    $_SESSION['flash'] = [];
}
$_SESSION['flash']['info'] = "You have been safely signed out. Please log in again to continue.";

header("Location: " . BASE_URL . "/login.php");
exit;
