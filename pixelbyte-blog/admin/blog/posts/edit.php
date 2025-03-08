<?php
/**
 * PIXELBYTE Admin Blog Posts Edit
 * Form to edit existing blog posts
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
    $_SESSION['error_message'] = "Invalid post ID.";
    redirect(get_admin_url('blog/posts/index.php'));
    exit;
}

$id = $_GET['id'];

// Get post data
$sql = "SELECT * FROM blog_posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error_message'] = "Post not found.";
    redirect(get_admin_url('blog/posts/index.php'));
    exit;
}

$post = $result->fetch_assoc();

// Process form submission
if(isset($_POST['submit'])) {
    $title = sanitize_input($_POST['title']);
    $content = $_POST['content']; // Don't use htmlspecialchars for content
    $excerpt = sanitize_input($_POST['excerpt']);
    $category = sanitize_input($_POST['category']);
    $image_url = sanitize_input($_POST['image_url']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Use prepared statements
    $stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ?, excerpt = ?, category = ?, image_url = ?, is_featured = ? WHERE id = ?");
    $stmt->bind_param("sssssii", $title, $content, $excerpt, $category, $image_url, $is_featured, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Post updated successfully.";
        redirect(get_admin_url('blog/posts/index.php'));
        exit;
    } else {
        $error = "Error: " . $stmt->error;
    }
}

// Set page title
$page_title = "Edit Blog Post";

// Include the header
include '../../includes/header.php';

// Add TinyMCE initialization
$inline_js = "
    tinymce.init({
        selector: '#content',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 500,
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
        <h1 class="page-title">Edit Blog Post</h1>
        <p class="page-subtitle">Modify an existing blog post</p>
    </div>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-container">
    <form method="post" class="needs-validation">
        <div class="form-group">
            <label for="title" class="form-label">Title</label>
            <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="content" class="form-label">Content</label>
            <textarea id="content" name="content" class="form-control tinymce-editor" required><?php echo $post['content']; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="category" class="form-label">Category</label>
            <select id="category" name="category" class="form-control" required>
                <option value="Web Design" <?php echo $post['category'] == 'Web Design' ? 'selected' : ''; ?>>Web Design</option>
                <option value="CSS" <?php echo $post['category'] == 'CSS' ? 'selected' : ''; ?>>CSS</option>
                <option value="JavaScript" <?php echo $post['category'] == 'JavaScript' ? 'selected' : ''; ?>>JavaScript</option>
                <option value="Accessibility" <?php echo $post['category'] == 'Accessibility' ? 'selected' : ''; ?>>Accessibility</option>
                <option value="UX/UI" <?php echo $post['category'] == 'UX/UI' ? 'selected' : ''; ?>>UX/UI</option>
                <option value="Typography" <?php echo $post['category'] == 'Typography' ? 'selected' : ''; ?>>Typography</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="excerpt" class="form-label">Excerpt (short summary)</label>
            <textarea id="excerpt" name="excerpt" class="form-control" rows="3" required><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="image_url" class="form-label">Image URL</label>
            <input type="text" id="image_url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($post['image_url']); ?>" required>
        </div>
        
        <div class="form-group form-check">
            <input type="checkbox" id="is_featured" name="is_featured" class="form-check-input" <?php echo $post['is_featured'] ? 'checked' : ''; ?>>
            <label for="is_featured" class="form-check-label">Feature this post on homepage</label>
        </div>
        
        <div class="form-group">
            <button type="submit" name="submit" class="btn btn-primary">Update Post</button>
            <a href="<?php echo get_admin_url('blog/posts/index.php'); ?>" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>