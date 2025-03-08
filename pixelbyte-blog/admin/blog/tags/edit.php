<?php
/**
 * PIXELBYTE Admin Blog Tags Edit
 * Edit blog tags
 */

// Include the configuration and authentication files
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Check if ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid tag ID.";
    redirect(get_admin_url('blog/tags/index.php'));
    exit;
}

$id = $_GET['id'];

// Get tag data
$sql = "SELECT * FROM blog_tags WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_message'] = "Tag not found.";
    redirect(get_admin_url('blog/tags/index.php'));
    exit;
}

$tag = $result->fetch_assoc();

// Process form submission
if(isset($_POST['edit_tag'])) {
    $tag_name = sanitize_input($_POST['tag_name']);
    
    if(!empty($tag_name)) {
        // Create slug from name
        $slug = create_slug($tag_name);
        
        // Check if another tag with this name or slug exists
        $check_sql = "SELECT COUNT(*) as count FROM blog_tags WHERE (name = ? OR slug = ?) AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ssi", $tag_name, $slug, $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $exists = $check_result->fetch_assoc()['count'] > 0;
        
        if($exists) {
            $error = "Another tag with this name or slug already exists.";
        } else {
            // Update tag
            $update_sql = "UPDATE blog_tags SET name = ?, slug = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $tag_name, $slug, $id);
            
            if($update_stmt->execute()) {
                $_SESSION['success_message'] = "Tag updated successfully.";
                redirect(get_admin_url('blog/tags/index.php'));
                exit;
            } else {
                $error = "Error updating tag: " . $conn->error;
            }
        }
    } else {
        $error = "Tag name cannot be empty.";
    }
}

// Set page title
$page_title = "Edit Tag";

// Include the header
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Edit Tag</h1>
        <p class="page-subtitle">Modify an existing blog tag</p>
    </div>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-container">
    <form method="post" class="needs-validation">
        <div class="form-group">
            <label for="tag_name" class="form-label">Tag Name</label>
            <input type="text" id="tag_name" name="tag_name" class="form-control" value="<?php echo htmlspecialchars($tag['name']); ?>" required>
            <div class="form-help">The name will be used to create a URL-friendly slug automatically.</div>
        </div>
        
        <div class="form-group">
            <button type="submit" name="edit_tag" class="btn btn-primary">Update Tag</button>
            <a href="<?php echo get_admin_url('blog/tags/index.php'); ?>" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>