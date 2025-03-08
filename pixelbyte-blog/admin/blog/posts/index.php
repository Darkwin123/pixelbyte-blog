<?php
/**
 * PIXELBYTE Admin Blog Posts Index
 * Displays list of all blog posts with management options
 */

// Include the configuration and authentication files
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Process post deletion if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $post_id = $_GET['delete'];
    
    // Delete the post
    $delete_sql = "DELETE FROM blog_posts WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $post_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = "Post deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting post: " . $conn->error;
    }
    
    // Redirect to refresh the page
    redirect(get_admin_url('blog/posts/index.php'));
    exit;
}

// Get all blog posts
$posts_sql = "SELECT id, title, category, created_at, is_featured FROM blog_posts ORDER BY created_at DESC";
$posts_result = $conn->query($posts_sql);

// Set page title
$page_title = "Blog Posts";

// Include the header
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Blog Posts</h1>
        <p class="page-subtitle">Manage your blog posts</p>
    </div>
    
    <div>
        <a href="<?php echo get_admin_url('blog/posts/create.php'); ?>" class="btn btn-success">
            <i class="fas fa-plus"></i> Create New Post
        </a>
    </div>
</div>

<!-- Posts Table -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Date</th>
                <th>Featured</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($posts_result && $posts_result->num_rows > 0): ?>
                <?php while($post = $posts_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $post['id']; ?></td>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo htmlspecialchars($post['category']); ?></td>
                        <td><?php echo format_date($post['created_at']); ?></td>
                        <td><?php echo $post['is_featured'] ? '<span class="status status-published">Yes</span>' : '<span class="status status-draft">No</span>'; ?></td>
                        <td class="actions">
                            <a href="<?php echo get_admin_url('blog/posts/edit.php?id=' . $post['id']); ?>" class="action-btn" data-tooltip="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?php echo get_admin_url('blog/posts/index.php?delete=' . $post['id']); ?>" class="action-btn" data-tooltip="Delete" data-confirm="Are you sure you want to delete this post?">
                                <i class="fas fa-trash"></i>
                            </a>
                            <a href="<?php echo get_site_url('blog/post.php?id=' . $post['id']); ?>" class="action-btn" target="_blank" data-tooltip="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No posts found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>