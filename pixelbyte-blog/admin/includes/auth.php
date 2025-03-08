<?php
/**
 * PIXELBYTE Admin Authentication
 * Handles user authentication and authorization
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration if not already included
if (!function_exists('get_admin_url')) {
    require_once 'config.php';
}

/**
 * Check if user is logged in
 * 
 * @return boolean True if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require login to access a page
 * Redirects to login page if not logged in
 * 
 * @return void
 */
function require_login() {
    if (!is_logged_in()) {
        // Store the current page URL for redirect after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect(get_admin_url('index.php'));
        exit;
    }
}

/**
 * Authenticate user with username and password
 * 
 * @param string $username Username to authenticate
 * @param string $password Password to authenticate
 * @return boolean True if authentication successful
 */
function authenticate_user($username, $password) {
    global $conn;
    
    // For demo purposes, we'll use a simple authentication
    // In production, you should use a secure authentication system with proper password hashing
    if ($username === 'admin' && $password === 'password123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_display_name'] = 'Administrator';
        $_SESSION['admin_last_login'] = date('Y-m-d H:i:s');
        
        return true;
    }
    
    return false;
}

/**
 * Log out the current user
 * 
 * @return void
 */
function logout_user() {
    // Unset all session variables
    $_SESSION = array();
    
    // If it's desired to kill the session, also delete the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Finally, destroy the session
    session_destroy();
}

/**
 * Get current admin user information
 * 
 * @param string $field Specific field to retrieve (optional)
 * @return mixed User information or specific field value
 */
function get_admin_user($field = null) {
    if (!is_logged_in()) {
        return null;
    }
    
    $user = [
        'username' => $_SESSION['admin_username'] ?? 'admin',
        'display_name' => $_SESSION['admin_display_name'] ?? 'Administrator',
        'last_login' => $_SESSION['admin_last_login'] ?? date('Y-m-d H:i:s'),
    ];
    
    if ($field !== null) {
        return $user[$field] ?? null;
    }
    
    return $user;
}

/**
 * Check if current user has permission for specific action
 * 
 * @param string $permission Permission to check
 * @return boolean True if user has permission
 */
function user_has_permission($permission) {
    // For demo purposes, admin has all permissions
    // In production, implement proper permission system
    if (is_logged_in() && $_SESSION['admin_username'] === 'admin') {
        return true;
    }
    
    return false;
}