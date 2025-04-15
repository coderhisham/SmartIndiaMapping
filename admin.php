<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the session management functions
require_once 'php/session.php';

// Start a secure session
session_start_secure();

// Check if user is logged in
if (!is_logged_in()) {
    // Not logged in, redirect to login page
    header("Location: login.php");
    exit;
}

// Define secure access constant
define('SECURE_ACCESS', true);

// User is authenticated, include the admin HTML content
include('admin-content.html');
?> 