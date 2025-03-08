<?php
/**
 * PIXELBYTE Admin Blog Categories Edit
 * Edit blog categories
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
    $_SESSION['error_message'] = "Invalid category ID.";
    redirect(get_admin_url('blog/categories/index.php'));
    exit;
}

$id = $_GET['id'];

// Get category data
$sql = "SELECT * FROM blog_categories WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_message'] = "Category not found.";
    redirect(get_admin_url('blog/categories/index.php'));
    exit;
}

$category = $result->fetch_assoc();

// Process form submission
if(isset($_POST['update_category'])) {
    $name = sanitize_input($_POST['name']);
    
    if(!empty($name)) {
        // Create slug from name
        $slug = create_slug($name);
        
        // Check if another category with this name or slug exists
        $check_sql = "SELECT COUNT(*) as count FROM blog_categories WHERE (name = ? OR slug = ?) AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ssi", $name, $slug, $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $exists = $check_result->fetch_assoc()['count'] > 0;
        
        if($exists) {
            $error = "Another category with this name or slug already exists.";
        } else {
            // Check if posts use the old category name
            $old_name = $category['name'];
            if($old_name != $name) {
                // Update posts with the old category name
                $update_posts_sql = "UPDATE blog_posts SET category = ? WHERE category = ?";
                $update_posts_stmt = $conn->prepare($update_posts_sql);
                $update_posts_stmt->bind_param("ss", $name, $old_name);
                $update_posts_stmt->execute();
            }
            
            // Update category
            $update_sql = "UPDATE blog_categories SET name = ?, slug = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $name, $slug, $id);
            
            if($update_stmt->execute()) {
                $_SESSION['success_message'] = "Category updated successfully.";
                redirect(get_admin_url('blog/categories/index.php'));
                exit;
            } else {
                $error = "Error updating category: " . $conn->error;
            }
        }
    } else {
        $error = "Category name cannot be empty.";
    }
}

// Set page title
$page_title = "Edit Category";

// Include the header
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Edit Category</h1>
        <p class="page-subtitle">Modify an existing blog category</p>
    </div>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-container">
    <form method="post" class="needs-validation">
        <div class="form-group">
            <label for="name" class="form-label">Category Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($category['name']); ?>" required>
            <div class="form-help">The name will be used to create a URL-friendly slug automatically.</div>
        </div>
        
        <div class="form-group">
            <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
            <a href="<?php echo get_admin_url('blog/categories/index.php'); ?>" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>