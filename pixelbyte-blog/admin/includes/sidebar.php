<?php
/**
 * PIXELBYTE Admin Sidebar
 * Contains the sidebar navigation menu
 */

// Get current page/section
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
$current_dir = basename(dirname($_SERVER['SCRIPT_NAME']));

// Function to check if page/section is active
function is_active($key) {
    global $current_page, $current_dir;
    return $current_page === $key || $current_dir === $key;
}

?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">PIXELBYTE</div>
        <div class="sidebar-subtitle">Admin Panel</div>
    </div>
    
    <nav>
        <ul class="sidebar-nav">
            <!-- Dashboard -->
            <li class="sidebar-nav-item">
                <a href="<?php echo get_admin_url('dashboard.php'); ?>" class="sidebar-nav-link <?php echo is_active('dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- Blog Section -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Blog</div>
                
                <li class="sidebar-nav-item">
                    <a href="<?php echo get_admin_url('blog/posts/index.php'); ?>" class="sidebar-nav-link <?php echo is_active('posts') ? 'active' : ''; ?>">
                        <i class="fas fa-file-alt"></i>
                        <span>Posts</span>
                    </a>
                </li>
                
                <li class="sidebar-nav-item">
                    <a href="<?php echo get_admin_url('blog/categories/index.php'); ?>" class="sidebar-nav-link <?php echo is_active('categories') && $current_dir === 'blog' ? 'active' : ''; ?>">
                        <i class="fas fa-folder"></i>
                        <span>Categories</span>
                    </a>
                </li>
                
                <li class="sidebar-nav-item">
                    <a href="<?php echo get_admin_url('blog/tags/index.php'); ?>" class="sidebar-nav-link <?php echo is_active('tags') ? 'active' : ''; ?>">
                        <i class="fas fa-tags"></i>
                        <span>Tags</span>
                    </a>
                </li>
            </div>
            
            <!-- Store Section -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">Store</div>
                
                <li class="sidebar-nav-item">
                    <a href="<?php echo get_admin_url('store/products/index.php'); ?>" class="sidebar-nav-link <?php echo is_active('products') ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                </li>
                
                <li class="sidebar-nav-item">
                    <a href="<?php echo get_admin_url('store/categories/index.php'); ?>" class="sidebar-nav-link <?php echo is_active('categories') && $current_dir === 'store' ? 'active' : ''; ?>">
                        <i class="fas fa-folder"></i>
                        <span>Categories</span>
                    </a>
                </li>
                
                <li class="sidebar-nav-item">
                    <a href="<?php echo get_admin_url('store/orders/index.php'); ?>" class="sidebar-nav-link <?php echo is_active('orders') ? 'active' : ''; ?>">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Orders</span>
                    </a>
                </li>
            </div>
            
            <!-- Settings Section -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">System</div>
                
                <li class="sidebar-nav-item">
                    <a href="<?php echo get_admin_url('settings.php'); ?>" class="sidebar-nav-link <?php echo is_active('settings') ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </div>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <ul class="sidebar-nav">
            <li class="sidebar-nav-item">
                <a href="<?php echo get_site_url(); ?>" class="sidebar-nav-link" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Visit Site</span>
                </a>
            </li>
            
            <li class="sidebar-nav-item">
                <a href="<?php echo get_admin_url('logout.php'); ?>" class="sidebar-nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</aside>