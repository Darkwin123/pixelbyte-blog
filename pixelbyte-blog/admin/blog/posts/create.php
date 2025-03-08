<?php
/**
 * PIXELBYTE Admin Blog Posts Create
 * Form to create new blog posts
 */

// Include the configuration and authentication files
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Process form submission
if(isset($_POST['submit'])) {
    $title = sanitize_input($_POST['title']);
    $content = $_POST['content']; // Don't use htmlspecialchars for content - TinyMCE outputs HTML
    $excerpt = sanitize_input($_POST['excerpt']);
    $category = sanitize_input($_POST['category']);
    $image_url = sanitize_input($_POST['image_url']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Create a slug from the title
    $slug = create_slug($title);
    
    // Check if slug already exists
    $check_slug = $conn->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ?");
    $check_slug->bind_param("s", $slug);
    $check_slug->execute();
    $check_slug->bind_result($slug_count);
    $check_slug->fetch();
    $check_slug->close();
    
    if($slug_count > 0) {
        // Append a number to make the slug unique
        $slug = $slug . '-' . time();
    }
    
    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, category, image_url, created_at, is_featured) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("ssssssi", $title, $slug, $content, $excerpt, $category, $image_url, $is_featured);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Post created successfully.";
        redirect(get_admin_url('blog/posts/index.php'));
        exit;
    } else {
        $error = "Error: " . $stmt->error;
    }
}

// Set page title
$page_title = "Create Blog Post";

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
        <h1 class="page-title">Create New Blog Post</h1>
        <p class="page-subtitle">Add a new post to your blog</p>
    </div>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="form-container">
    <form method="post" class="needs-validation">
        <div class="form-group">
            <label for="title" class="form-label">Title</label>
            <input type="text" id="title" name="title" class="form-control" data-slug-source="slug" required>
        </div>
        
        <div class="form-group">
            <label for="content" class="form-label">Content</label>
            <textarea id="content" name="content" class="form-control tinymce-editor" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="category" class="form-label">Category</label>
            <select id="category" name="category" class="form-control" required>
                <option value="">Select a category</option>
                <option value="Web Design">Web Design</option>
                <option value="CSS">CSS</option>
                <option value="JavaScript">JavaScript</option>
                <option value="Accessibility">Accessibility</option>
                <option value="UX/UI">UX/UI</option>
                <option value="Typography">Typography</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="excerpt" class="form-label">Excerpt (short summary)</label>
            <textarea id="excerpt" name="excerpt" class="form-control" rows="3" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="image_url" class="form-label">Image URL</label>
            <input type="text" id="image_url" name="image_url" class="form-control" placeholder="/api/placeholder/600/400" required>
        </div>
        
        <div class="form-group form-check">
            <input type="checkbox" id="is_featured" name="is_featured" class="form-check-input">
            <label for="is_featured" class="form-check-label">Feature this post on homepage</label>
        </div>
        
        <div class="form-group">
            <button type="submit" name="submit" class="btn btn-primary">Create Post</button>
            <a href="<?php echo get_admin_url('blog/posts/index.php'); ?>" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>