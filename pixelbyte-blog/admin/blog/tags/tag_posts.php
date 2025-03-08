<?php
/**
 * PIXELBYTE Admin Blog Tag Posts
 * View and manage posts associated with a tag
 */

// Include the configuration and authentication files
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Check if tag ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid tag ID.";
    redirect(get_admin_url('blog/tags/index.php'));
    exit;
}

$tag_id = $_GET['id'];

// Get tag info
$tag_sql = "SELECT * FROM blog_tags WHERE id = ?";
$tag_stmt = $conn->prepare($tag_sql);
$tag_stmt->bind_param("i", $tag_id);
$tag_stmt->execute();
$tag_result = $tag_stmt->get_result();

if ($tag_result->num_rows == 0) {
    $_SESSION['error_message'] = "Tag not found.";
    redirect(get_admin_url('blog/tags/index.php'));
    exit;
}

$tag = $tag_result->fetch_assoc();

// Get posts with this tag
$posts_sql = "SELECT p.* 
              FROM blog_posts p
              JOIN post_tags pt ON p.id = pt.post_id
              WHERE pt.tag_id = ?
              ORDER BY p.created_at DESC";
$posts_stmt = $conn->prepare($posts_sql);
$posts_stmt->bind_param("i", $tag_id);
$posts_stmt->execute();
$posts_result = $posts_stmt->get_result();

// Process tag removal from post
if(isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $post_id = $_GET['remove'];
    
    // Remove tag from post
    $remove_sql = "DELETE FROM post_tags WHERE post_id = ? AND tag_id = ?";
    $remove_stmt = $conn->prepare($remove_sql);
    $remove_stmt->bind_param("ii", $post_id, $tag_id);
    
    if($remove_stmt->execute()) {
        $_SESSION['success_message'] = "Tag removed from post successfully.";
    } else {
        $_SESSION['error_message'] = "Error removing tag from post: " . $conn->error;
    }
    
    redirect(get_admin_url('blog/tags/tag_posts.php?id=' . $tag_id));
    exit;
}

// Set page title
$page_title = "Posts with Tag: " . $tag['name'];

// Include the header
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Posts with Tag: "<?php echo htmlspecialchars($tag['name']); ?>"</h1>
        <p class="page-subtitle">View and manage posts with this tag</p>
    </div>
    
    <div>
        <a href="<?php echo get_admin_url('blog/tags/index.php'); ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Tags
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
                        <td class="actions">
                            <a href="<?php echo get_admin_url('blog/posts/edit.php?id=' . $post['id']); ?>" class="action-btn" data-tooltip="Edit Post">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?php echo get_admin_url('blog/tags/tag_posts.php?id=' . $tag_id . '&remove=' . $post['id']); ?>" class="action-btn" data-tooltip="Remove Tag" data-confirm="Are you sure you want to remove this tag from the post?">
                                <i class="fas fa-unlink"></i>
                            </a>
                            <a href="<?php echo get_site_url('blog/post.php?id=' . $post['id']); ?>" class="action-btn" target="_blank" data-tooltip="View Post">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No posts found with this tag</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>