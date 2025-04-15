<?php
// Security settings for session (must be before session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

// Start secure session after setting all configuration
session_start();

// Security headers (must be after session_start but before any output)
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Content-Security-Policy: default-src 'self'");

// Define admin credentials (in production, use a database)
$admin_username = "admin";
$admin_password_hash = password_hash("admin123", PASSWORD_DEFAULT); // Replace with your password

// Function to generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Invalid CSRF token, redirect with error
        header("Location: ../login.php?error=Invalid request. Please try again.");
        exit;
    }
    
    // Sanitize input data
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = $_POST['password']; // Don't sanitize password, will be used with password_verify
    
    // Validate credentials
    if ($username === $admin_username && password_verify($password, $admin_password_hash)) {
        // Authentication successful
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = 'admin';
        $_SESSION['last_activity'] = time();
        
        // Redirect to admin dashboard
        header("Location: ../admin.php");
        exit;
    } else {
        // Authentication failed
        header("Location: ../login.php?error=Invalid username or password");
        exit;
    }
} else {
    // Not a POST request, redirect to login page
    header("Location: ../login.php");
    exit;
}
?> 