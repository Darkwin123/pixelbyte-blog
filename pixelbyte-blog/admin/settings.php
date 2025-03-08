<?php
/**
 * PIXELBYTE Admin Settings
 * Manage site-wide settings
 */

// Include the configuration and authentication files
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Process form submission
if(isset($_POST['update_settings'])) {
    $site_title = sanitize_input($_POST['site_title']);
    $site_description = sanitize_input($_POST['site_description']);
    $admin_email = sanitize_input($_POST['admin_email']);
    $posts_per_page = (int)$_POST['posts_per_page'];
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
    
    // For demonstration purposes, we'll just show a success message
    // In a real application, you would save these to a database or config file
    $_SESSION['success_message'] = "Settings updated successfully.";
    
    redirect(get_admin_url('settings.php'));
    exit;
}

// Set page title
$page_title = "Settings";

// Include the header
include 'includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Configure your site settings</p>
    </div>
</div>

<div class="form-container">
    <div class="table-header">
        <h2 class="table-title">
            <i class="fas fa-cog"></i> General Settings
        </h2>
    </div>
    
    <form method="post" class="needs-validation">
        <div class="form-group">
            <label for="site_title" class="form-label">Site Title</label>
            <input type="text" id="site_title" name="site_title" class="form-control" value="PIXELBYTE" required>
        </div>
        
        <div class="form-group">
            <label for="site_description" class="form-label">Site Description</label>
            <input type="text" id="site_description" name="site_description" class="form-control" value="Web Development Blog & Store">
        </div>
        
        <div class="form-group">
            <label for="admin_email" class="form-label">Admin Email</label>
            <input type="email" id="admin_email" name="admin_email" class="form-control" value="admin@pixelbyte.com" required>
        </div>
        
        <div class="form-group">
            <label for="posts_per_page" class="form-label">Posts Per Page</label>
            <input type="number" id="posts_per_page" name="posts_per_page" class="form-control" value="10" min="1" max="50" required>
        </div>
        
        <div class="form-group form-check">
            <input type="checkbox" id="maintenance_mode" name="maintenance_mode" class="form-check-input">
            <label for="maintenance_mode" class="form-check-label">Enable Maintenance Mode</label>
            <div class="form-help">When enabled, only administrators can access the site.</div>
        </div>
        
        <div class="form-group">
            <button type="submit" name="update_settings" class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>

<div class="form-container">
    <div class="table-header">
        <h2 class="table-title">
            <i class="fas fa-shield-alt"></i> Security Settings
        </h2>
    </div>
    
    <form method="post" class="needs-validation">
        <div class="form-group">
            <label for="current_password" class="form-label">Current Password</label>
            <input type="password" id="current_password" name="current_password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" id="new_password" name="new_password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <button type="submit" name="update_password" class="btn btn-primary">Change Password</button>
        </div>
    </form>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>