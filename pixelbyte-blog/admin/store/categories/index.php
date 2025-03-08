<?php
/**
 * PIXELBYTE Admin Store Categories Index
 * Manages store categories
 */

// Include the configuration and authentication files
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Process form submission for creating new category
if(isset($_POST['add_category'])) {
    $name = sanitize_input($_POST['name']);
    $slug = !empty($_POST['slug']) ? create_slug($_POST['slug']) : create_slug($name);
    $description = sanitize_input($_POST['description']);
    
    // Check if category name or slug already exists
    $check_sql = "SELECT COUNT(*) as count FROM store_categories WHERE name = ? OR slug = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $name, $slug);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $exists = $check_result->fetch_assoc()['count'] > 0;
    
    if($exists) {
        $error_message = "A category with this name or slug already exists.";
    } else {
        // Insert new category
        $insert_sql = "INSERT INTO store_categories (name, slug, description) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sss", $name, $slug, $description);
        
        if($insert_stmt->execute()) {
            $_SESSION['success_message'] = "Category created successfully.";
            redirect(get_admin_url('store/categories/index.php'));
            exit;
        } else {
            $error_message = "Error creating category: " . $conn->error;
        }
    }
}

// Process category deletion
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if any products use this category
    $check_sql = "SELECT COUNT(*) as count FROM store_products WHERE category = (SELECT name FROM store_categories WHERE id = ?)";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $products_count = $check_result->fetch_assoc()['count'];
    
    if($products_count > 0) {
        $_SESSION['error_message'] = "Cannot delete this category because it is used by {$products_count} products.";
    } else {
        // Delete the category
        $delete_sql = "DELETE FROM store_categories WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id);
        
        if($delete_stmt->execute()) {
            $_SESSION['success_message'] = "Category deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error deleting category: " . $conn->error;
        }
    }
    
    redirect(get_admin_url('store/categories/index.php'));
    exit;
}

// Get all categories with product count
$sql = "SELECT c.*, COUNT(p.id) as product_count 
        FROM store_categories c
        LEFT JOIN store_products p ON c.name = p.category
        GROUP BY c.id
        ORDER BY c.name";
$result = $conn->query($sql);

// Set page title
$page_title = "Store Categories";

// Include the header
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Store Categories</h1>
        <p class="page-subtitle">Manage your store categories</p>
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
            <label for="name" class="form-label">Category Name</label>
            <input type="text" id="name" name="name" class="form-control" data-slug-source="slug" required>
        </div>
        
        <div class="form-group">
            <label for="slug" class="form-label">Slug (leave empty to generate from name)</label>
            <input type="text" id="slug" name="slug" class="form-control">
            <div class="form-help">Only use lowercase letters, numbers, and hyphens. No spaces.</div>
        </div>
        
        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
        </div>
        
        <button type="submit" name="add_category" class="btn btn-primary">Create Category</button>
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
                <th>Description</th>
                <th>Products</th>
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
                        <td><?php echo htmlspecialchars($category['description']); ?></td>
                        <td><?php echo $category['product_count']; ?></td>
                        <td class="actions">
                            <a href="<?php echo get_admin_url('store/categories/edit.php?id=' . $category['id']); ?>" class="action-btn" data-tooltip="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if($category['product_count'] == 0): ?>
                                <a href="<?php echo get_admin_url('store/categories/index.php?delete=' . $category['id']); ?>" class="action-btn" data-tooltip="Delete" data-confirm="Are you sure you want to delete this category?">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <span class="action-btn disabled" data-tooltip="Cannot delete: Category has products">
                                    <i class="fas fa-trash"></i>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No categories found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>