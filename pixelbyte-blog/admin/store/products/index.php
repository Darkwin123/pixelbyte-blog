<?php
/**
 * PIXELBYTE Admin Store Products Index
 * Lists all store products with management options
 */

// Include the configuration and authentication files
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Process product deletion if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = $_GET['delete'];
    
    // Delete the product
    $delete_sql = "DELETE FROM store_products WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $product_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['success_message'] = "Product deleted successfully.";
    } else {
        $_SESSION['error_message'] = "Error deleting product: " . $conn->error;
    }
    
    // Redirect to refresh the page
    redirect(get_admin_url('store/products/index.php'));
    exit;
}

// Search functionality
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$where_clause = '';
if(!empty($search)) {
    $search_term = '%' . $search . '%';
    $where_clause = "WHERE title LIKE ? OR description LIKE ? OR category LIKE ?";
}

// Get all products
if(empty($where_clause)) {
    $products_sql = "SELECT * FROM store_products ORDER BY created_at DESC";
    $products_result = $conn->query($products_sql);
} else {
    $products_sql = "SELECT * FROM store_products $where_clause ORDER BY created_at DESC";
    $products_stmt = $conn->prepare($products_sql);
    $products_stmt->bind_param("sss", $search_term, $search_term, $search_term);
    $products_stmt->execute();
    $products_result = $products_stmt->get_result();
}

// Set page title
$page_title = "Store Products";

// Include the header
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Store Products</h1>
        <p class="page-subtitle">Manage your store products</p>
    </div>
    
    <div>
        <a href="<?php echo get_admin_url('store/products/create.php'); ?>" class="btn btn-success">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>
</div>

<!-- Search Form -->
<div class="form-container">
    <form method="get" action="<?php echo get_admin_url('store/products/index.php'); ?>" class="search-form">
        <div class="form-group" style="display: flex; gap: 10px;">
            <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if(!empty($search)): ?>
                <a href="<?php echo get_admin_url('store/products/index.php'); ?>" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Title</th>
                <th>Category</th>
                <th>Price</th>
                <th>Featured</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($products_result && $products_result->num_rows > 0): ?>
                <?php while($product = $products_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                        </td>
                        <td><?php echo htmlspecialchars($product['title']); ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['is_featured'] ? '<span class="status status-published">Yes</span>' : '<span class="status status-draft">No</span>'; ?></td>
                        <td><?php echo format_date($product['created_at']); ?></td>
                        <td class="actions">
                            <a href="<?php echo get_admin_url('store/products/edit.php?id=' . $product['id']); ?>" class="action-btn" data-tooltip="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?php echo get_admin_url('store/products/index.php?delete=' . $product['id']); ?>" class="action-btn" data-tooltip="Delete" data-confirm="Are you sure you want to delete this product?">
                                <i class="fas fa-trash"></i>
                            </a>
                            <a href="<?php echo get_site_url('store/product.php?id=' . $product['id']); ?>" class="action-btn" target="_blank" data-tooltip="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No products found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>