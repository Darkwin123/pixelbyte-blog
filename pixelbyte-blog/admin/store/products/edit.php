<?php
/**
 * PIXELBYTE Admin Store Products Edit
 * Form to edit existing store products
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
    $_SESSION['error_message'] = "Invalid product ID.";
    redirect(get_admin_url('store/products/index.php'));
    exit;
}

$id = $_GET['id'];

// Get product data
$sql = "SELECT * FROM store_products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_message'] = "Product not found.";
    redirect(get_admin_url('store/products/index.php'));
    exit;
}

$product = $result->fetch_assoc();

// Get categories for dropdown
$categories_sql = "SELECT name FROM store_categories ORDER BY name";
$categories_result = $conn->query($categories_sql);

// Process form submission
if(isset($_POST['submit'])) {
    $title = sanitize_input($_POST['title']);
    $slug = isset($_POST['slug']) && !empty($_POST['slug']) ? create_slug($_POST['slug']) : create_slug($title);
    $description = $_POST['description']; // HTML content from TinyMCE
    $price = floatval($_POST['price']);
    $category = sanitize_input($_POST['category']);
    $image_url = sanitize_input($_POST['image_url']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Check if slug already exists (except for this product)
    $check_slug = "SELECT COUNT(*) as count FROM store_products WHERE slug = ? AND id != ?";
    $stmt = $conn->prepare($check_slug);
    $stmt->bind_param("si", $slug, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $slug_count = $result->fetch_assoc()['count'];
    
    if($slug_count > 0) {
        $error = "Another product with this slug already exists. Please choose a different slug.";
    } else {
        // Update the product
        $sql = "UPDATE store_products SET 
                title = ?, 
                slug = ?, 
                description = ?, 
                price = ?, 
                category = ?, 
                image_url = ?, 
                is_featured = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssdssi", $title, $slug, $description, $price, $category, $image_url, $is_featured, $id);
        
        if($stmt->execute()) {
            $_SESSION['success_message'] = "Product updated successfully.";
            redirect(get_admin_url('store/products/index.php'));
            exit;
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}

// Set page title
$page_title = "Edit Product";

// Include the header
include '../../includes/header.php';

// Add TinyMCE initialization
$inline_js = "
    tinymce.init({
        selector: '#description',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 300,
        setup: function(editor) {
            editor.on('change', function () {
                tinymce.triggerSave();
            });
        }
    });
";
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Edit Product</h1>
        <p class="page-subtitle">Modify an existing product</p>
    </div>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-container">
    <form method="post" class="needs-validation">
        <div class="form-group">
            <label for="title" class="form-label">Title</label>
            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($product['title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" id="slug" name="slug" class="form-control" value="<?php echo htmlspecialchars($product['slug']); ?>">
            <div class="form-help">Only use lowercase letters, numbers, and hyphens. No spaces.</div>
        </div>
        
        <div class="form-group">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control tinymce-editor" required><?php echo $product['description']; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="price" class="form-label">Price ($)</label>
            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0.01" value="<?php echo $product['price']; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="category" class="form-label">Category</label>
            <select id="category" name="category" class="form-control" required>
                <option value="">Select a category</option>
                <?php 
                if ($categories_result) {
                    $categories_result->data_seek(0);
                    while($category = $categories_result->fetch_assoc()): 
                        $selected = ($category['name'] == $product['category']) ? 'selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars($category['name']); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endwhile; 
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="image_url" class="form-label">Image URL</label>
            <input type="text" id="image_url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($product['image_url']); ?>" required>
        </div>
        
        <div class="form-group form-check">
            <input type="checkbox" id="is_featured" name="is_featured" class="form-check-input" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
            <label for="is_featured" class="form-check-label">Feature this product on homepage</label>
        </div>
        
        <div class="form-group">
            <button type="submit" name="submit" class="btn btn-primary">Update Product</button>
            <a href="<?php echo get_admin_url('store/products/index.php'); ?>" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>