<?php
/**
 * PIXELBYTE Admin Configuration
 * Contains database connection, paths and other configuration settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pixelbyte_blog');

// Site Paths (adjust based on your installation directory)
define('SITE_ROOT', '/pixelbyte-blog/');
define('ADMIN_ROOT', '/pixelbyte-blog/admin/');
define('BLOG_ROOT', '/pixelbyte-blog/blog/');
define('STORE_ROOT', '/pixelbyte-blog/store/');

// Absolute paths (for file operations)
define('ABSPATH', dirname(dirname(__FILE__)) . '/');
define('UPLOADS_PATH', ABSPATH . 'uploads/');

// Admin sections
define('ADMIN_SECTIONS', [
    'dashboard' => [
        'icon' => 'fas fa-tachometer-alt',
        'title' => 'Dashboard',
        'description' => 'Overview of your site',
    ],
    'blog' => [
        'icon' => 'fas fa-pencil-alt',
        'title' => 'Blog',
        'description' => 'Manage your blog content',
        'items' => [
            'posts' => [
                'icon' => 'fas fa-file-alt',
                'title' => 'Posts',
                'description' => 'Manage blog posts',
            ],
            'categories' => [
                'icon' => 'fas fa-folder',
                'title' => 'Categories',
                'description' => 'Manage blog categories',
            ],
            'tags' => [
                'icon' => 'fas fa-tags',
                'title' => 'Tags',
                'description' => 'Manage blog tags',
            ],
        ],
    ],
    'store' => [
        'icon' => 'fas fa-shopping-cart',
        'title' => 'Store',
        'description' => 'Manage your online store',
        'items' => [
            'products' => [
                'icon' => 'fas fa-box',
                'title' => 'Products',
                'description' => 'Manage store products',
            ],
            'categories' => [
                'icon' => 'fas fa-folder',
                'title' => 'Categories',
                'description' => 'Manage store categories',
            ],
            'orders' => [
                'icon' => 'fas fa-shopping-bag',
                'title' => 'Orders',
                'description' => 'View and manage orders',
            ],
        ],
    ],
    'settings' => [
        'icon' => 'fas fa-cog',
        'title' => 'Settings',
        'description' => 'Configure your site',
    ],
]);

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set common site functions
/**
 * Get the site URL with specified path
 * 
 * @param string $path Path to append to site URL
 * @return string Full URL
 */
function get_site_url($path = '') {
    return SITE_ROOT . ltrim($path, '/');
}

/**
 * Get the admin URL with specified path
 * 
 * @param string $path Path to append to admin URL
 * @return string Full admin URL
 */
function get_admin_url($path = '') {
    return ADMIN_ROOT . ltrim($path, '/');
}

/**
 * Check if current page is the active page
 *
 * @param string $page Page to check
 * @return boolean True if it's the active page
 */
function is_active_page($page) {
    $current_file = basename($_SERVER['SCRIPT_NAME'], '.php');
    return $current_file === $page;
}

/**
 * Create a slug from string
 *
 * @param string $string String to convert to slug
 * @return string Slug version of the string
 */
function create_slug($string) {
    return strtolower(trim(preg_replace('/[^a-zA-Z0-9-]+/', '-', $string), '-'));
}

/**
 * Format date string
 *
 * @param string $date_string Date string to format
 * @param string $format Format string (default is 'M j, Y')
 * @return string Formatted date
 */
function format_date($date_string, $format = 'M j, Y') {
    $date = new DateTime($date_string);
    return $date->format($format);
}

/**
 * Redirect to a specific URL
 *
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Sanitize input to prevent XSS
 *
 * @param string $input Input to sanitize
 * @return string Sanitized input
 */
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Get error message HTML
 *
 * @param string $message Error message
 * @return string HTML for error message
 */
function get_error_message($message) {
    return '<div class="alert alert-danger">' . $message . '</div>';
}

/**
 * Get success message HTML
 *
 * @param string $message Success message
 * @return string HTML for success message
 */
function get_success_message($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}