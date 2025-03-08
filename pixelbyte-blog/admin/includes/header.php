<?php
/**
 * PIXELBYTE Admin Header
 * Contains the HTML head, top navigation, and opening body tags
 */

// Include config and auth if not already included
if (!function_exists('get_admin_url')) {
    require_once 'config.php';
}
if (!function_exists('is_logged_in')) {
    require_once 'auth.php';
}

// Check if user is logged in for all pages except login
if (basename($_SERVER['SCRIPT_NAME']) !== 'index.php' || is_logged_in()) {
    require_login();
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
$page_title = 'Dashboard'; // Default title

// Determine page title
foreach (ADMIN_SECTIONS as $section_key => $section) {
    if ($current_page === $section_key) {
        $page_title = $section['title'];
        break;
    }
    
    if (isset($section['items'])) {
        foreach ($section['items'] as $item_key => $item) {
            if ($current_page === $item_key) {
                $page_title = $item['title'];
                break 2;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PIXELBYTE Admin</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Darker+Grotesque:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Admin Styles -->
    <link rel="stylesheet" href="<?php echo get_admin_url('assets/css/admin.css'); ?>">
    
    <!-- Page-specific styles may be added here -->
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css_file): ?>
            <link rel="stylesheet" href="<?php echo $css_file; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="admin-container">
        <?php 
        // Include sidebar for authenticated users
        if (is_logged_in()): 
            include 'sidebar.php';
        endif;
        ?>
        
        <?php if (is_logged_in()): ?>
        <!-- Top Header -->
        <div class="top-header">
            <div class="top-header-left">
                <button class="menu-toggle" aria-label="Toggle Sidebar">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <h1 class="header-title"><?php echo $page_title; ?></h1>
            </div>
            
            <div class="top-header-right">
                <div class="user-dropdown">
                    <div class="user-info">
                        <img src="<?php echo get_admin_url('assets/images/avatar-placeholder.png'); ?>" alt="User Avatar">
                        <span class="name"><?php echo get_admin_user('display_name'); ?></span>
                        <i class="fas fa-chevron-down caret"></i>
                    </div>
                    
                    <div class="user-dropdown-content">
                        <a href="<?php echo get_admin_url('settings.php'); ?>">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <a href="<?php echo get_site_url(); ?>" target="_blank">
                            <i class="fas fa-external-link-alt"></i> View Site
                        </a>
                        <hr>
                        <a href="<?php echo get_admin_url('logout.php'); ?>">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <main class="main-content">
            <?php
            // Display session messages if they exist
            if (isset($_SESSION['success_message'])): 
            ?>
                <div class="alert alert-success alert-dismissible fade-in">
                    <?php echo $_SESSION['success_message']; ?>
                </div>
            <?php
                unset($_SESSION['success_message']);
            endif;
            
            if (isset($_SESSION['error_message'])): 
            ?>
                <div class="alert alert-danger alert-dismissible fade-in">
                    <?php echo $_SESSION['error_message']; ?>
                </div>
            <?php
                unset($_SESSION['error_message']);
            endif;
            ?>