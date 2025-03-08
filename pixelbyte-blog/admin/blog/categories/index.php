<?php
/**
 * PIXELBYTE Admin Blog Categories Index
 * Manages blog categories
 */

// Include the configuration and authentication files
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Process form submission for creating new category
if(isset($_POST['create_category'])) {
    $name = sanitize_input($_POST['category_name']);
    
    if(!empty($name)) {
        // Create slug from name
        $slug = create_slug($name);
        
        // Check if the category already exists
        $check_sql = "SELECT COUNT(*) as count FROM blog_categories WHERE name = ? OR slug = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $name, $slug);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $exists = $check_result->fetch_assoc()['count'] > 0;
        
        if($exists) {
            $error_message = "A category with this name or slug already exists.";
        } else {
            // Insert new category
            $insert_sql = "INSERT INTO blog_categories (name, slug) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ss", $name, $slug);
            
            if($insert_stmt->execute()) {
                $_SESSION['success_message'] = "Category created successfully.";
                redirect(get_admin_url('blog/categories/index.php'));
                exit;
            } else {
                $error_message = "Error creating category: " . $conn->error;
            }
        }
    } else {
        $error_message = "Category name cannot be empty.";
    }
}

// Process category deletion
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if any posts use this category
    $check_sql = "SELECT COUNT(*) as count FROM blog_posts WHERE category = (SELECT name FROM blog_categories WHERE id = ?)";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $posts_count = $check_result->fetch_assoc()['count'];
    
    if($posts_count > 0) {
        $_SESSION['error_message'] = "Cannot delete this category because it is used by {$posts_count} posts.";
    } else {
        // Delete the category
        $delete_sql = "DELETE FROM blog_categories WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id);
        
        if($delete_stmt->execute()) {
            $_SESSION['success_message'] = "Category deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error deleting category: " . $conn->error;
        }
    }
    
    redirect(get_admin_url('blog/categories/index.php'));
    exit;
}

// Get all categories with post count
$sql = "SELECT c.*, COUNT(p.id) as post_count 
        FROM blog_categories c
        LEFT JOIN blog_posts p ON c.name = p.category
        GROUP BY c.id
        ORDER BY c.name";
$result = $conn->query($sql);

// Set page title
$page_title = "Blog Categories";

// Include the header
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Blog Categories</h1>
        <p class="page-subtitle">Manage your blog categories</p>
    </div>
</div>

<?php if(isset($error_message)): ?>
    <div class="alert alert-danger"><?php echo $error_message; ?></div>
<?php endif; ?>

<!-- Create New Category Form -->
<div class="form-container">
    <div class="table-header">
        <h2 class="table-title">
            <i class="fas fa-folder-plus"></i> Add New Category
        </h2>
    </div>
    
    <form method="post" class="needs-validation">
        <div class="form-group">
            <label for="category_name" class="form-label">Category Name</label>
            <input type="text" id="category_name" name="category_name" class="form-control" required>
            <div class="form-help">The name will be used to create a URL-friendly slug automatically.</div>
        </div>
        
        <button type="submit" name="create_category" class="btn btn-primary">Create Category</button>
    </form>
</div>

<!-- Categories Table -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">
            <i class="fas fa-folder"></i> All Categories
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
                <?php while($category = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $category['id']; ?></td>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td><?php echo htmlspecialchars($category['slug']); ?></td>
                        <td><?php echo $category['post_count']; ?></td>
                        <td class="actions">
                            <a href="<?php echo get_admin_url('blog/categories/edit.php?id=' . $category['id']); ?>" class="action-btn" data-tooltip="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if($category['post_count'] == 0): ?>
                                <a href="<?php echo get_admin_url('blog/categories/index.php?delete=' . $category['id']); ?>" class="action-btn" data-tooltip="Delete" data-confirm="Are you sure you want to delete this category?">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="action-btn disabled" data-tooltip="Cannot delete: Category has posts">
                                    <i class="fas fa-trash"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No categories found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>