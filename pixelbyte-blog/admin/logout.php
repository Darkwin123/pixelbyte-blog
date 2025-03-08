<?php
/**
 * PIXELBYTE Admin Logout
 * Handles user logout and session destruction
 */

// Include authentication file
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Log out the user
logout_user();

// Redirect to login page
redirect(get_admin_url());
exit;