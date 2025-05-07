<?php
/**
 * Session management and security functions
 */

// Set secure session parameters
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 3600); // 1 hour

/**
 * Start a secure session
 */
function session_start_secure() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Generate a CSRF token
 * @return string The generated token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify the CSRF token
 * @param string $token The token to verify
 * @return bool Whether the token is valid
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 * @return bool Whether the user is logged in
 */
function is_logged_in() {
    if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
        return false;
    }
    
    // Check session expiration (30 minutes)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        // Session expired, destroy it
        logout();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Check if user has admin role
 * @return bool Whether the user is an admin
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Log out the user
 */
function logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Delete the session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Get CSRF token for forms
 * @return string HTML input with CSRF token
 */
function csrf_input() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Redirect to login page if not logged in
 * @param string $redirect_url URL to redirect to after login
 */
function require_login($redirect_url = '') {
    if (!is_logged_in()) {
        // Build the URL with the redirect parameter
        $login_url = '../login.php';
        if (!empty($redirect_url)) {
            $login_url .= '?redirect=' . urlencode($redirect_url);
        }
        
        // Perform the redirect
        header('Location: ' . $login_url);
        exit;
    }
}

/**
 * Require admin privileges
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        // Not an admin, redirect to unauthorized page
        header('Location: ../unauthorized.html');
        exit;
    }
} 