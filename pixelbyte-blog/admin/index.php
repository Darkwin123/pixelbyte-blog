<?php
/**
 * PIXELBYTE Admin Login
 * Handles user authentication and login
 */

// Include configuration and authentication files
require_once 'includes/config.php';
require_once 'includes/auth.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    redirect(get_admin_url('dashboard.php'));
    exit;
}

// Process login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (authenticate_user($username, $password)) {
            // Redirect to dashboard or previously requested page
            $redirect_url = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : get_admin_url('dashboard.php');
            unset($_SESSION['redirect_after_login']);
            
            redirect($redirect_url);
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Please enter both username and password';
    }
}

// Include header (without sidebar for login page)
include 'includes/header.php';
?>

<div class="login-container">
    <div class="login-box">
        <div class="login-header">
            <h1 class="login-title">PIXELBYTE</h1>
            <p class="login-subtitle">Admin Panel Login</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="needs-validation">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group form-check">
                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                <label for="remember" class="form-check-label">Remember me</label>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
        </form>
        
        <p style="text-align: center; margin-top: 1.5rem;">
            <a href="<?php echo get_site_url(); ?>">← Back to Website</a>
        </p>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>