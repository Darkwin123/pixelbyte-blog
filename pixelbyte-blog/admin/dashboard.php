<?php
/**
 * PIXELBYTE Admin Dashboard
 * Main dashboard displaying key statistics and recent activities
 */

// Include the configuration and authentication files
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Get statistics
// Blog stats
$blog_posts_count = $conn->query("SELECT COUNT(*) as count FROM blog_posts")->fetch_assoc()['count'];
$blog_categories_count = $conn->query("SELECT COUNT(DISTINCT category) as count FROM blog_posts")->fetch_assoc()['count'];
$blog_tags_count = $conn->query("SELECT COUNT(*) as count FROM blog_tags")->fetch_assoc()['count'];
$blog_comments_count = 0; // Adjust if you have a comments table

// Store stats
$store_products_count = $conn->query("SELECT COUNT(*) as count FROM store_products")->fetch_assoc()['count'];
$store_categories_count = $conn->query("SELECT COUNT(*) as count FROM store_categories")->fetch_assoc()['count'];
$store_orders_count = $conn->query("SELECT COUNT(*) as count FROM store_orders")->fetch_assoc()['count'];
$store_revenue = $conn->query("SELECT SUM(total_amount) as total FROM store_orders WHERE payment_status = 'completed'")->fetch_assoc()['total'] ?? 0;

// Get recent blog posts
$recent_posts_sql = "SELECT id, title, category, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 5";
$recent_posts_result = $conn->query($recent_posts_sql);

// Get recent store orders
$recent_orders_sql = "SELECT id, order_number, customer_name, total_amount, created_at, payment_status FROM store_orders ORDER BY created_at DESC LIMIT 5";
$recent_orders_result = $conn->query($recent_orders_sql);

// Include the header
include 'includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, <?php echo get_admin_user('display_name'); ?>!</p>
    </div>
    
    <div>
        <a href="<?php echo get_site_url(); ?>" class="btn btn-outline" target="_blank">
            <i class="fas fa-external-link-alt"></i> Visit Site
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <!-- Blog Stats -->
    <div class="stat-card">
        <div class="stat-card-title">
            <i class="fas fa-file-alt"></i> Blog Posts
        </div>
        <div class="stat-card-value"><?php echo $blog_posts_count; ?></div>
        <div class="stat-card-desc">
            <a href="<?php echo get_admin_url('blog/posts/index.php'); ?>">Manage posts →</a>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-title">
            <i class="fas fa-folder"></i> Blog Categories
        </div>
        <div class="stat-card-value"><?php echo $blog_categories_count; ?></div>
        <div class="stat-card-desc">
            <a href="<?php echo get_admin_url('blog/categories/index.php'); ?>">Manage categories →</a>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-title">
            <i class="fas fa-tags"></i> Blog Tags
        </div>
        <div class="stat-card-value"><?php echo $blog_tags_count; ?></div>
        <div class="stat-card-desc">
            <a href="<?php echo get_admin_url('blog/tags/index.php'); ?>">Manage tags →</a>
        </div>
    </div>
    
    <!-- Store Stats -->
    <div class="stat-card">
        <div class="stat-card-title">
            <i class="fas fa-box"></i> Store Products
        </div>
        <div class="stat-card-value"><?php echo $store_products_count; ?></div>
        <div class="stat-card-desc">
            <a href="<?php echo get_admin_url('store/products/index.php'); ?>">Manage products →</a>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-title">
            <i class="fas fa-shopping-bag"></i> Store Orders
        </div>
        <div class="stat-card-value"><?php echo $store_orders_count; ?></div>
        <div class="stat-card-desc">
            <a href="<?php echo get_admin_url('store/orders/index.php'); ?>">Manage orders →</a>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-title">
            <i class="fas fa-folder"></i> Store Categories
        </div>
        <div class="stat-card-value"><?php echo $store_categories_count; ?></div>
        <div class="stat-card-desc">
            <a href="<?php echo get_admin_url('store/categories/index.php'); ?>">Manage categories →</a>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-title">
            <i class="fas fa-dollar-sign"></i> Revenue
        </div>
        <div class="stat-card-value">$<?php echo number_format($store_revenue, 2); ?></div>
        <div class="stat-card-desc">
            <a href="<?php echo get_admin_url('store/orders/index.php'); ?>">View details →</a>
        </div>
    </div>
</div>

<!-- Recent Blog Posts -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">
            <i class="fas fa-file-alt"></i> Recent Blog Posts
        </h2>
        <a href="<?php echo get_admin_url('blog/posts/index.php'); ?>" class="btn btn-primary btn-sm">View All</a>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Category</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($recent_posts_result && $recent_posts_result->num_rows > 0): ?>
                <?php while($post = $recent_posts_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo htmlspecialchars($post['category']); ?></td>
                        <td><?php echo format_date($post['created_at']); ?></td>
                        <td class="actions">
                            <a href="<?php echo get_admin_url('blog/posts/edit.php?id=' . $post['id']); ?>" class="action-btn" data-tooltip="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?php echo get_site_url('blog/post.php?id=' . $post['id']); ?>" class="action-btn" target="_blank" data-tooltip="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No blog posts found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Recent Store Orders -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">
            <i class="fas fa-shopping-bag"></i> Recent Orders
        </h2>
        <a href="<?php echo get_admin_url('store/orders/index.php'); ?>" class="btn btn-primary btn-sm">View All</a>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($recent_orders_result && $recent_orders_result->num_rows > 0): ?>
                <?php while($order = $recent_orders_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo format_date($order['created_at']); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status status-<?php echo strtolower($order['payment_status']); ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="<?php echo get_admin_url('store/orders/view.php?id=' . $order['id']); ?>" class="action-btn" data-tooltip="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No orders found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include the footer
include 'includes/footer.php';
?>