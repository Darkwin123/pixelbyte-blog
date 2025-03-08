<?php
/**
 * PIXELBYTE Admin Blog Tags Index
 * Manages blog tags
 */

// Include the configuration and authentication files
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Process form submission for new tag
if(isset($_POST['create_tag'])) {
    $tag_name = sanitize_input($_POST['tag_name']);
    
    if(!empty($tag_name)) {
        // Create slug from name
        $slug = create_slug($tag_name);
        
        // Check if tag already exists
        $check_sql = "SELECT COUNT(*) as count FROM blog_tags WHERE name = ? OR slug = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $tag_name, $slug);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $exists = $check_result->fetch_assoc()['count'] > 0;
        
        if($exists) {
            $error_message = "A tag with this name or slug already exists.";
        } else {
            // Insert new tag
            $insert_sql = "INSERT INTO blog_tags (name, slug) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ss", $tag_name, $slug);
            
            if($insert_stmt->execute()) {
                $_SESSION['success_message'] = "Tag created successfully.";
                redirect(get_admin_url('blog/tags/index.php'));
                exit;
            } else {
                $error_message = "Error creating tag: " . $conn->error;
            }
        }
    } else {
        $error_message = "Tag name cannot be empty.";
    }
}

// Process tag deletion
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $tag_id = $_GET['delete'];
    
    // Check if tag is used by any posts
    $check_sql = "SELECT COUNT(*) as post_count FROM post_tags WHERE tag_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $tag_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $post_count = $check_result->fetch_assoc()['post_count'];
    
    if($post_count > 0) {
        $_SESSION['error_message'] = "Cannot delete tag: it is used by {$post_count} posts. Remove tag from posts first.";
    } else {
        // Delete tag
        $delete_sql = "DELETE FROM blog_tags WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $tag_id);
        
        if($delete_stmt->execute()) {
            $_SESSION['success_message'] = "Tag deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error deleting tag: " . $conn->error;
        }
    }
    
    redirect(get_admin_url('blog/tags/index.php'));
    exit;
}

// Get all tags with post count
$sql = "SELECT t.*, COUNT(pt.post_id) as post_count 
        FROM blog_tags t
        LEFT JOIN post_tags pt ON t.id = pt.tag_id
        GROUP BY t.id
        ORDER BY t.name";
$result = $conn->query($sql);

// Set page title
$page_title = "Blog Tags";

// Include the header
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Blog Tags</h1>
        <p class="page-subtitle">Manage your blog tags</p>
    </div>
</div>

<?php if(isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<!-- Create New Tag Form -->
<div class="form-container">
    <div class="table-header">
        <h2 class="table-title">
            <i class="fas fa-tag"></i> Add New Tag
        </h2>
    </div>
    
    <form method="post" class="needs-validation">
        <div class="form-group">
            <label for="tag_name" class="form-label">Tag Name</label>
            <input type="text" id="tag_name" name="tag_name" class="form-control" required>
            <div class="form-help">The name will be used to create a URL-friendly slug automatically.</div>
        </div>
        
        <button type="submit" name="create_tag" class="btn btn-primary">Create Tag</button>
    </form>
</div>

<!-- Tags Table -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">
            <i class="fas fa-tags"></i> All Tags
        </h2>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Posts</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($tag = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $tag['id']; ?></td>
                        <td><?php echo htmlspecialchars($tag['name']); ?></td>
                        <td><?php echo htmlspecialchars($tag['slug']); ?></td>
                        <td><?php echo $tag['post_count']; ?></td>
                        <td class="actions">
                            <a href="<?php echo get_admin_url('blog/tags/edit.php?id=' . $tag['id']); ?>" class="action-btn" data-tooltip="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if($tag['post_count'] == 0): ?>
                                <a href="<?php echo get_admin_url('blog/tags/index.php?delete=' . $tag['id']); ?>" class="action-btn" data-tooltip="Delete" data-confirm="Are you sure you want to delete this tag?">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo get_admin_url('blog/tags/tag_posts.php?id=' . $tag['id']); ?>" class="action-btn" data-tooltip="View Posts">
                                    <i class="fas fa-eye"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No tags found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>