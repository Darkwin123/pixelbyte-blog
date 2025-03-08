<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pixelbyte_blog');

// Site paths
define('SITE_ROOT', '/pixelbyte-blog/');
define('STORE_ROOT', '/pixelbyte-blog/store/');
define('BLOG_ROOT', '/pixelbyte-blog/blog/');

// Store settings
define('PRODUCTS_PER_PAGE', 8);
define('ENABLE_DOWNLOADS', true);

// Connect to database
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Format currency
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Get base URL
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    return $protocol . $_SERVER['HTTP_HOST'];
}