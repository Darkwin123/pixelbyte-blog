<?php
// Near the top of your file, add:
$base_url = 'https://' . $_SERVER['HTTP_HOST'];

// Check if slug is provided
if(!isset($_GET['slug'])) {
    header('Location: index.php');
    exit;
}

$slug = $_GET['slug'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'pixelbyte_blog');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Include tag functions
require_once 'includes/tag_functions.php';

// Add at the top of each file
$site_root = '/pixelbyte-blog/'; // Root of the site
$blog_root = '/pixelbyte-blog/blog/'; // Root of the blog section

// Check if slug is provided via GET param or path info
if(isset($_GET['slug'])) {
    $slug = $_GET['slug'];
} else {
    // If using clean URLs and no GET parameter
    $request_uri = $_SERVER['REQUEST_URI'];
    $path_parts = explode('/', trim($request_uri, '/'));
    $slug = end($path_parts);
}

// Sanitize the slug before using it
$slug = htmlspecialchars($slug);

// Get post data by slug
$sql = "SELECT * FROM blog_posts WHERE slug = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$post = $result->fetch_assoc();

// Get post tags
$post_tags = get_post_tags($conn, $post['id']);

// Get categories with count (for sidebar)
$sql_categories = "SELECT category, COUNT(*) as count FROM blog_posts GROUP BY category";
$categories_result = $conn->query($sql_categories);

// Get popular posts (for sidebar)
$sql_popular = "SELECT id, title, slug, image_url, created_at FROM blog_posts ORDER BY view_count DESC LIMIT 3";
$popular_result = $conn->query($sql_popular);

// Get all tags for sidebar
$all_tags = get_all_tags($conn);

// Get related posts (same category, excluding current post)
$sql_related = "SELECT id, title, slug, image_url, created_at, excerpt FROM blog_posts 
                WHERE category = ? AND id != ? 
                ORDER BY created_at DESC LIMIT 3";
$related_stmt = $conn->prepare($sql_related);
$related_stmt->bind_param("si", $post['category'], $post['id']);
$related_stmt->execute();
$related_result = $related_stmt->get_result();

// Get previous post
$prev_sql = "SELECT title, slug FROM blog_posts WHERE created_at < ? ORDER BY created_at DESC LIMIT 1";
$prev_stmt = $conn->prepare($prev_sql);
$prev_stmt->bind_param("s", $post['created_at']);
$prev_stmt->execute();
$prev_result = $prev_stmt->get_result();
$prev_post = $prev_result->fetch_assoc();

// Get next post
$next_sql = "SELECT title, slug FROM blog_posts WHERE created_at > ? ORDER BY created_at ASC LIMIT 1";
$next_stmt = $conn->prepare($next_sql);
$next_stmt->bind_param("s", $post['created_at']);
$next_stmt->execute();
$next_result = $next_stmt->get_result();
$next_post = $next_result->fetch_assoc();

// Increment view count
$update_views = "UPDATE blog_posts SET view_count = view_count + 1 WHERE id = ?";
$view_stmt = $conn->prepare($update_views);
$view_stmt->bind_param("i", $post['id']);
$view_stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($post['excerpt']); ?>">
    
    <!-- Open Graph Meta Tags for Social Sharing -->
    <meta property="og:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($post['excerpt']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($post['image_url']); ?>">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="article">
    
    <!-- Twitter Card data -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($post['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($post['excerpt']); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($post['image_url']); ?>">
    
    <title><?php echo htmlspecialchars($post['title']); ?> - PIXELBYTE Blog</title>

<!-- Canonical URL for SEO -->
<link rel="canonical" href="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/pixelbyte-blog/blog/' . htmlspecialchars($post['slug']); ?>" />
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* CSS Reset */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* CSS Variables */
        :root {
            --primary: #7E57C2;
            --primary-light: #B39DDB;
            --secondary: #26A69A;
            --dark: #121212;
            --light: #F8F9FA;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --card-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
            --font-main: 'Inter', sans-serif;
            --font-heading: 'Plus Jakarta Sans', sans-serif;
            --border-radius: 16px;
            --blur-effect: blur(10px);
        }

        /* Base styles */
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-main);
            background-color: #f8f9fc;
            background-image: 
                radial-gradient(at 80% 0%, rgba(126, 87, 194, 0.1) 0px, transparent 50%),
                radial-gradient(at 0% 50%, rgba(38, 166, 154, 0.1) 0px, transparent 50%),
                radial-gradient(at 90% 90%, rgba(126, 87, 194, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
            padding-top: 80px; /* For the fixed header */
        }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        h2 {
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            letter-spacing: -0.5px;
        }

        h3 {
            font-size: clamp(1.4rem, 3vw, 1.8rem);
        }

        p {
            margin-bottom: 1.5rem;
            font-size: clamp(1rem, 1.2vw, 1.1rem);
            color: rgba(18, 18, 18, 0.8);
        }

        a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        a:hover {
            color: var(--secondary);
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: rgba(126, 87, 194, 0.8);
            color: white;
            font-weight: 600;
            border-radius: 50px;
            backdrop-filter: var(--blur-effect);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn:hover {
            background: rgba(126, 87, 194, 1);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
            color: white;
        }

        .btn-secondary {
            background: rgba(38, 166, 154, 0.8);
        }

        .btn-secondary:hover {
            background: rgba(38, 166, 154, 1);
        }

        /* Layout */
        .container {
            width: 90%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .main-container {
            padding-top: 2rem;
            padding-bottom: 60px;
            position: relative;
            z-index: 1;
        }

        /* Glassmorphic Elements */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border-radius: var(--border-radius);
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        /* Header & Navigation */
        header {
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 100;
            padding: 1rem 0;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            position: relative;
            padding: 0.5rem 0;
        }

        .nav-links {
            display: none;
        }

        .nav-links ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            font-weight: 600;
            position: relative;
            padding: 0.5rem 0;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .mobile-menu-btn {
            display: block;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 200;
        }

        .mobile-menu-btn span {
            display: block;
            width: 30px;
            height: 2px;
            margin: 7px;
            background-color: var(--primary);
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            height: 100vh;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: all 0.5s cubic-bezier(0.77, 0, 0.175, 1);
            z-index: 99;
        }

        .mobile-menu.active {
            right: 0;
        }

        .mobile-menu ul {
            list-style: none;
            text-align: center;
        }

        .mobile-menu li {
            margin: 2rem 0;
        }

        .mobile-menu a {
            font-size: 1.8rem;
            font-weight: 700;
            position: relative;
            padding: 0.5rem 0;
        }

        .mobile-menu a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .mobile-menu a:hover::after {
            width: 100%;
        }

        @media (min-width: 768px) {
            .nav-links {
                display: block;
            }
            .mobile-menu-btn {
                display: none;
            }
        }

        /* Page Title */
        .page-title-section {
            margin-bottom: 2rem;
            padding: 3rem 0 1rem;
            text-align: center;
            position: relative;
        }

        .page-title-meta {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            align-items: center;
        }

        .page-category-badge {
            display: inline-block;
            background: rgba(126, 87, 194, 0.1);
            color: var(--primary);
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid rgba(126, 87, 194, 0.2);
            transition: all 0.3s ease;
        }

        .page-category-badge:hover {
            background: rgba(126, 87, 194, 0.2);
            transform: translateY(-2px);
        }

        .page-post-date,
        .page-view-count {
            color: rgba(18, 18, 18, 0.6);
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.9rem;
        }

        .view-icon {
            font-size: 0.9rem;
        }

        /* Blog Content Layout */
        .blog-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 1024px) {
            .blog-container {
                grid-template-columns: 2.2fr 1fr;
                gap: 3rem;
            }
        }

        /* Blog Post Styles */
        .blog-post {
            background: var(--glass-bg);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border-radius: var(--border-radius);
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .blog-post:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .blog-featured-img {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .blog-post-body {
            margin-bottom: 2rem;
        }

        .blog-post-body h2 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .blog-post-body h2::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            bottom: 0;
            left: 0;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .blog-post-body img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin: 1.5rem 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .blog-post-body pre {
            background: rgba(18, 18, 18, 0.05);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border-radius: 10px;
            padding: 1.2rem;
            overflow-x: auto;
            margin: 1.5rem 0;
            border-left: 3px solid var(--primary);
        }

        .blog-post-body blockquote {
            background: rgba(126, 87, 194, 0.05);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-left: 3px solid var(--primary);
            font-style: italic;
        }

        .blog-post-body a {
            color: var(--primary);
            border-bottom: 1px dashed rgba(126, 87, 194, 0.3);
            padding-bottom: 0.1rem;
        }

        .blog-post-body a:hover {
            color: var(--secondary);
            border-bottom: 1px solid var(--secondary);
        }

        .blog-post-body ul,
        .blog-post-body ol {
            margin-left: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .blog-post-body li {
            margin-bottom: 0.5rem;
        }

        /* Post tags */
        .blog-post-tags {
            margin: 2rem 0;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .blog-post-tag {
            display: inline-block;
            background: rgba(38, 166, 154, 0.1);
            color: var(--secondary);
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.85rem;
            border: 1px solid rgba(38, 166, 154, 0.2);
            transition: all 0.3s ease;
        }

        .blog-post-tag:hover {
            background: rgba(38, 166, 154, 0.2);
            transform: translateY(-2px);
        }

        /* Social sharing buttons */
        .social-sharing {
            display: flex;
            gap: 1rem;
            margin: 2rem 0;
        }

        .social-sharing a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            color: var(--dark);
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            font-size: 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .social-sharing a:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .twitter {
            color: #1da1f2 !important;
        }

        .facebook {
            color: #1877f2 !important;
        }

        .linkedin {
            color: #0077b5 !important;
        }

        .pinterest {
            color: #e60023 !important;
        }

        /* Author section */
        .blog-post-author {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border-radius: var(--border-radius);
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            margin: 2rem 0;
        }

        .author-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-right: 1.5rem;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .author-info h4 {
            margin-bottom: 0.5rem;
        }

        /* Post navigation */
        .post-navigation {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .post-navigation a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border-radius: var(--border-radius);
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            max-width: 45%;
            font-weight: 600;
        }

        .post-navigation a:hover {
            background: rgba(126, 87, 194, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .post-navigation .prev:before {
            content: '←';
            margin-right: 0.5rem;
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .post-navigation .next:after {
            content: '→';
            margin-left: 0.5rem;
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .post-navigation-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Related posts section */
        .related-posts {
            margin: 3rem 0;
        }

        .related-posts-title {
            position: relative;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            font-size: 1.5rem;
        }

        .related-posts-title::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            bottom: 0;
            left: 0;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .related-posts-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 768px) {
            .related-posts-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Blog card for related posts */
        .blog-card {
            background: var(--glass-bg);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border-radius: var(--border-radius);
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .blog-card:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .blog-card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .blog-card-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
        }

        .blog-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            align-items: center;
        }

        .blog-card-category {
            display: inline-block;
            background: rgba(126, 87, 194, 0.1);
            color: var(--primary);
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid rgba(126, 87, 194, 0.2);
            transition: all 0.3s ease;
        }

        .blog-card-date {
            color: rgba(18, 18, 18, 0.6);
            font-size: 0.8rem;
        }

        .blog-card-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            line-height: 1.4;
            font-weight: 700;
        }

        .blog-card-excerpt {
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            color: rgba(18, 18, 18, 0.7);
        }

        /* Sidebar */
        .blog-sidebar {
            margin-top: 0;
            position: sticky;
            top: 100px;
            height: max-content;
        }

        .sidebar-section {
            background: var(--glass-bg);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border-radius: var(--border-radius);
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            padding: 1.8rem;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .sidebar-section:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .sidebar-section:last-child {
            margin-bottom: 0;
        }

        .sidebar-heading {
            position: relative;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .sidebar-heading::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            bottom: 0;
            left: 0;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .search-form {
            display: flex;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50px;
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .search-form:focus-within {
            box-shadow: 0 8px 25px rgba(126, 87, 194, 0.2);
            border: 1px solid rgba(126, 87, 194, 0.3);
            transform: translateY(-2px);
        }

        .search-input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: none;
            background: transparent;
            font-size: 1rem;
            color: var(--dark);
            font-family: var(--font-main);
        }

        .search-input:focus {
            outline: none;
        }

.search-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            font-weight: 600;
            border-radius: 0 50px 50px 0;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: var(--secondary);
        }

        .categories-list, 
        .popular-posts-list,
        .archives-list,
        .tags-list {
            list-style: none;
        }

        .categories-list li,
        .archives-list li {
            margin-bottom: 0.8rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .categories-list li:last-child,
        .archives-list li:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .categories-list a,
        .archives-list a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .categories-list a:hover,
        .archives-list a:hover {
            transform: translateX(5px);
        }

        .category-count,
        .archive-count {
            background: rgba(126, 87, 194, 0.1);
            color: var(--primary);
            padding: 0.1rem 0.5rem;
            border-radius: 50px;
            font-size: 0.8rem;
            border: 1px solid rgba(126, 87, 194, 0.2);
            font-weight: 600;
        }

        .popular-post {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .popular-post:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .popular-post-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .popular-post-content {
            flex: 1;
        }

        .popular-post-title {
            font-size: 1rem;
            margin-bottom: 0.3rem;
            font-weight: 600;
            line-height: 1.4;
        }

        .popular-post-date {
            font-size: 0.8rem;
            color: rgba(18, 18, 18, 0.6);
        }

        .tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
        }

        .tags-list a {
            display: inline-block;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50px;
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.3rem 0.8rem;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .tags-list a:hover {
            background: rgba(126, 87, 194, 0.1);
            border: 1px solid rgba(126, 87, 194, 0.2);
            transform: translateY(-2px);
        }

        .tag-count {
            display: inline-block;
            background: rgba(126, 87, 194, 0.2);
            color: var(--primary);
            font-size: 0.7rem;
            padding: 0.1rem 0.4rem;
            border-radius: 50px;
            margin-left: 0.3rem;
            font-weight: 600;
        }

        .newsletter-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .newsletter-input {
            padding: 1rem;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-family: var(--font-main);
            transition: all 0.3s ease;
        }

        .newsletter-input:focus {
            outline: none;
            box-shadow: 0 4px 15px rgba(126, 87, 194, 0.1);
            border: 1px solid rgba(126, 87, 194, 0.3);
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }
        
        .delay-2 {
            animation-delay: 0.3s;
        }
        
        .delay-3 {
            animation-delay: 0.5s;
        }

        /* Footer */
        footer {
            background: rgba(18, 18, 18, 0.8);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            color: var(--light);
            padding: 4rem 0 2rem;
            margin-top: 5rem;
            position: relative;
            z-index: 1;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
        }

        @media (min-width: 768px) {
            .footer-content {
                grid-template-columns: 1.5fr 1fr 1fr;
            }
        }

        .footer-logo {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -1px;
            color: var(--light);
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-nav ul {
            list-style: none;
        }

        .footer-nav li {
            margin-bottom: 0.8rem;
        }

        .footer-nav a {
            color: var(--light);
            opacity: 0.8;
            transition: all 0.3s ease;
        }

        .footer-nav a:hover {
            color: var(--primary-light);
            opacity: 1;
            transform: translateX(5px);
            display: inline-block;
        }

        .footer-nav h3 {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            color: white;
            font-size: 1.3rem;
        }

        .footer-nav h3::after {
            content: '';
            position: absolute;
            width: 40px;
            height: 3px;
            bottom: 0;
            left: 0;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border-radius: 50%;
            color: var(--light);
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-5px);
        }

        .copyright {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>
      <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">PIXELBYTE</a>
                <div class="nav-links">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Categories</a></li>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <button class="mobile-menu-btn" aria-label="Toggle Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Categories</a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
    </div>

    <div class="container">
        <div class="page-title-section">
            <div class="page-title-meta">
                <a href="category.php?name=<?php echo urlencode($post['category']); ?>" class="page-category-badge"><?php echo htmlspecialchars($post['category']); ?></a>
                <span class="page-post-date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
            </div>
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        </div>
    </div>

    <div class="container">
        <div class="blog-container">
            <!-- Blog Post Content -->
            <main class="blog-content">
                <article class="blog-post">
                    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-featured-img">
                    
                    <!-- Social Sharing Buttons -->
                    <div class="social-sharing">
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" class="twitter" aria-label="Share on Twitter">T</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="facebook" aria-label="Share on Facebook">F</a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&title=<?php echo urlencode($post['title']); ?>" target="_blank" class="linkedin" aria-label="Share on LinkedIn">L</a>
                        <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&media=<?php echo urlencode($post['image_url']); ?>&description=<?php echo urlencode($post['title']); ?>" target="_blank" class="pinterest" aria-label="Pin on Pinterest">P</a>
                    </div>
                    
                    <div class="blog-post-body">
                        <?php echo $post['content']; ?>
                    </div>
                    
                    <!-- Display post tags -->
                    <?php if (!empty($post_tags)): ?>
                    <div class="blog-post-tags">
                        <strong>Tags:</strong>
                        <?php foreach($post_tags as $tag): ?>
                        <a href="tag.php?slug=<?php echo $tag['slug']; ?>" class="blog-post-tag"><?php echo htmlspecialchars($tag['name']); ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Author Section -->
                    <div class="blog-post-author">
                        <img src="https://via.placeholder.com/200" alt="Author Avatar" class="author-avatar">
                        <div class="author-info">
                            <h4>John Doe</h4>
                            <p>Web designer and developer with a passion for creating unique digital experiences that break the conventional mold.</p>
                        </div>
                    </div>
                    
                    <!-- Post Navigation -->
                    <div class="post-navigation">
                        <?php if($prev_post): ?>
                        <a href="<?php echo $prev_post['slug']; ?>" class="prev">
                            <span class="post-navigation-text"><?php echo htmlspecialchars($prev_post['title']); ?></span>
                        </a>
                        <?php else: ?>
                        <span></span>
                        <?php endif; ?>
                        
                        <?php if($next_post): ?>
                        <a href="post.php?slug=<?php echo $next_post['slug']; ?>" class="next">
                            <span class="post-navigation-text"><?php echo htmlspecialchars($next_post['title']); ?></span>
                        </a>
                        <?php else: ?>
                        <span></span>
                        <?php endif; ?>
                    </div>
                </article>
                
                <!-- Related Posts Section -->
                <?php if($related_result->num_rows > 0): ?>
                <section class="related-posts">
                    <h2 class="related-posts-title">Related Posts</h2>
                    <div class="related-posts-grid">
                        <?php while($related = $related_result->fetch_assoc()): ?>
                        <article class="blog-card fade-in-up">
                            <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="blog-card-img">
                            <div class="blog-card-content">
                                <div class="blog-card-meta">
                                    <span class="blog-card-category"><?php echo htmlspecialchars($post['category']); ?></span>
                                    <span class="blog-card-date"><?php echo date('F j, Y', strtotime($related['created_at'])); ?></span>
                                </div>
                                <h3 class="blog-card-title"><?php echo htmlspecialchars($related['title']); ?></h3>
                                <p class="blog-card-excerpt"><?php echo htmlspecialchars(substr($related['excerpt'], 0, 100)); ?>...</p>
                                <a href="post.php?slug=<?php echo $related['slug']; ?>" class="btn">Read More</a>
                            </div>
                        </article>
                        <?php endwhile; ?>
                    </div>
                </section>
                <?php endif; ?>
            </main>

            <!-- Blog Sidebar -->
            <aside class="blog-sidebar">
                <!-- Search Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-heading">Search</h3>
                    <form class="search-form" action="/blog/search.php" method="get">
                        <input type="text" placeholder="Search articles..." name="q" class="search-input" required>
                        <button type="submit" class="search-btn">Go</button>
                    </form>
                </div>

                <!-- Categories Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-heading">Categories</h3>
                    <ul class="categories-list">
                        <?php while($category = $categories_result->fetch_assoc()): ?>
                        <li>
                            <a href="<?php echo $blog_root; ?>category.php?name=<?php echo urlencode($category['category']); ?>">
                                <?php echo htmlspecialchars($category['category']); ?>
                                <span class="category-count"><?php echo $category['count']; ?></span>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <!-- Popular Posts Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-heading">Popular Posts</h3>
                    <div class="popular-posts-list">
                        <?php while($popular = $popular_result->fetch_assoc()): ?>
                        <div class="popular-post">
                            <img src="<?php echo htmlspecialchars($popular['image_url']); ?>" alt="<?php echo htmlspecialchars($popular['title']); ?>" class="popular-post-img">
                            <div class="popular-post-content">
                                <h4 class="popular-post-title">
                                    <a href="post.php?slug=<?php echo $popular['slug']; ?>"><?php echo htmlspecialchars($popular['title']); ?></a>
                                </h4>
                                <p class="popular-post-date"><?php echo date('F j, Y', strtotime($popular['created_at'])); ?></p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <!-- Tags Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-heading">Tags</h3>
                    <div class="tags-list">
                        <?php foreach ($all_tags as $tag): ?>
                            <a href="tag.php?slug=<?php echo $tag['slug']; ?>">
                                <?php echo htmlspecialchars($tag['name']); ?>
                                <?php if($tag['post_count'] > 0): ?> 
                                    <span class="tag-count"><?php echo $tag['post_count']; ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Newsletter Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-heading">Newsletter</h3>
                    <p>Subscribe to get the latest articles, design tips, and resources straight to your inbox.</p>
                    <form class="newsletter-form" method="post" action="subscribe.php">
                        <input type="email" placeholder="Your email address" name="email" class="newsletter-input" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </aside>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">PIXELBYTE</div>
                    <p>Creating unique digital experiences<br>that break the conventional mold.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Twitter">T</a>
                        <a href="#" aria-label="Instagram">I</a>
                        <a href="#" aria-label="Dribbble">D</a>
                        <a href="#" aria-label="GitHub">G</a>
                        <a href="#" aria-label="LinkedIn">L</a>
                    </div>
                </div>
                <div class="footer-nav">
                    <h3>Navigation</h3>
                    <ul>
                        <li><a href="<?php echo $site_root; ?>">Home</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Categories</a></li>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-nav">
                    <h3>Categories</h3>
                    <ul>
                        <?php
                        // Reset categories result pointer
                        $categories_result->data_seek(0);
                        $count = 0;
                        while($category = $categories_result->fetch_assoc() && $count < 5): 
                            $count++;
                        ?>
                            <li>
                                <a href="category.php?name=<?php echo urlencode($category['category']); ?>">
                                    <?php echo htmlspecialchars($category['category']); ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> PIXELBYTE. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const mobileMenu = document.querySelector('.mobile-menu');
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
            
            // Transform hamburger to X
            const spans = mobileMenuBtn.querySelectorAll('span');
            spans.forEach(span => span.classList.toggle('active'));
            
            if (mobileMenu.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(8px, -8px)';
            } else {
                document.body.style.overflow = '';
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
        
        // Close mobile menu when clicking on a nav link
        const mobileLinks = document.querySelectorAll('.mobile-menu a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
                
                // Reset hamburger icon
                const spans = mobileMenuBtn.querySelectorAll('span');
                spans.forEach(span => {
                    span.classList.remove('active');
                    span.style.transform = 'none';
                    span.style.opacity = '1';
                });
            });
        });
        
        // Fade in animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add fade-in classes with delays to relevant elements
            const relatedCards = document.querySelectorAll('.related-posts .blog-card');
            
            relatedCards.forEach((card, index) => {
                card.classList.add(`delay-${index + 1}`);
                setTimeout(() => {
                    card.style.opacity = '1';
                }, 100 * (index + 1));
            });
            
            // Header scroll effect
            const header = document.querySelector('header');
            
            window.addEventListener('scroll', () => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > 50) {
                    header.style.background = 'rgba(255, 255, 255, 0.9)';
                    header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
                } else {
                    header.style.background = 'rgba(255, 255, 255, 0.8)';
                    header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.05)';
                }
            });
        });
    </script>
</body>
</html>