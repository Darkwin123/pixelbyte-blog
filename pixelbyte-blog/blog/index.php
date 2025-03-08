<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'pixelbyte_blog');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add at the top of each file
$site_root = '/pixelbyte-blog/'; // Root of the site
$blog_root = '/pixelbyte-blog/blog/'; // Root of the blog section

// Include tag functions
require_once 'includes/tag_functions.php';

// Get featured post
$sql_featured = "SELECT * FROM blog_posts WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 1";
$featured_result = $conn->query($sql_featured);
$featured_post = $featured_result->fetch_assoc();

// Pagination settings
$posts_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $posts_per_page;

// Get total post count
$sql_count = "SELECT COUNT(*) as total FROM blog_posts WHERE is_featured = 0";
$count_result = $conn->query($sql_count);
$total_posts = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// Get regular posts with pagination
$sql_regular = "SELECT * FROM blog_posts WHERE is_featured = 0 ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql_regular);
$stmt->bind_param("ii", $offset, $posts_per_page);
$stmt->execute();
$regular_result = $stmt->get_result();

// Get categories with count
$sql_categories = "SELECT category, COUNT(*) as count FROM blog_posts GROUP BY category";
$categories_result = $conn->query($sql_categories);

// Get popular posts (for sidebar)
$sql_popular = "SELECT id, title, slug, image_url, created_at FROM blog_posts ORDER BY view_count DESC LIMIT 3";
$popular_result = $conn->query($sql_popular);

// Get all tags for sidebar
$all_tags = get_all_tags($conn);

// Helper function to sanitize names for CSS classes
function sanitize_class_name($name) {
    return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PIXELBYTE Blog - Insights on web design, development, UX, and creative inspiration with a neo-brutalist aesthetic">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="PIXELBYTE Blog - Web Design & Development Insights">
    <meta property="og:description" content="Explore articles on web design, development, UX, and creative inspiration">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <title>PIXELBYTE | Blog</title>
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
            padding-top: 120px;
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

        /* Blog Header with Search */
        .blog-header {
            text-align: center;
            margin-bottom: 3rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .blog-title-section {
            margin-bottom: 2rem;
        }

        .search-section {
            margin: 2rem auto;
            max-width: 600px;
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

        /* Category Tag Pills */
        .category-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 2.5rem;
            justify-content: center;
        }

        .category-pill {
            padding: 0.6rem 1.2rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50px;
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .category-pill:hover {
            background: rgba(255, 255, 255, 0.8);
            transform: translateY(-2px);
        }

        .category-pill.active {
            background: var(--primary);
            color: white;
            border: 1px solid rgba(126, 87, 194, 0.3);
        }

        /* Featured Post Section */
        .featured-post-container {
            margin-bottom: 3rem;
        }

        .featured-post {
            display: grid;
            grid-template-columns: 1fr;
            border-radius: var(--border-radius);
            overflow: hidden;
            background: var(--glass-bg);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            transition: all 0.3s ease;
        }

        .featured-post:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        @media (min-width: 768px) {
            .featured-post {
                grid-template-columns: 1.3fr 1fr;
            }
        }

        .featured-post-img {
            width: 100%;
            height: 100%;
            min-height: 250px;
            object-fit: cover;
        }

        .featured-post-content {
            padding: 2rem;
            display: flex;
            flex-direction: column;
        }

        .featured-label {
            display: inline-block;
            background: linear-gradient(135deg, #7E57C2, #B39DDB);
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
            align-self: flex-start;
        }

        /* Blog Posts Grid */
        .blog-posts-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            transition: all 0.5s ease;
        }

        @media (min-width: 640px) {
            .blog-posts-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .blog-posts-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

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

        .blog-card.filtering-fade-out {
            opacity: 0;
            transform: translateY(10px);
        }

        .blog-card.filtering-fade-in {
            opacity: 1;
            transform: translateY(0);
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

        .blog-card-category:hover {
            background: rgba(126, 87, 194, 0.2);
        }

        .blog-card-date {
            color: rgba(18, 18, 18, 0.6);
            font-size: 0.8rem;
        }

/* Blog Filters */
.blog-filters {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.8rem;
    margin-bottom: 2.5rem;
}

.filter-btn {
    padding: 0.7rem 1.5rem;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50px;
    backdrop-filter: var(--blur-effect);
    -webkit-backdrop-filter: var(--blur-effect);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--dark);
}

.filter-btn:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    color: var(--primary);
}

.filter-btn.active {
    background: var(--primary);
    color: white;
    border: 1px solid rgba(126, 87, 194, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(126, 87, 194, 0.2);
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

        .blog-card-footer {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        /* Blog Card Tags */
        .blog-card-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .blog-card-tag {
            display: inline-block;
            background: rgba(38, 166, 154, 0.1);
            color: var(--secondary);
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-size: 0.75rem;
            border: 1px solid rgba(38, 166, 154, 0.2);
            transition: all 0.3s ease;
        }

        .blog-card-tag:hover {
            background: rgba(38, 166, 154, 0.2);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 3rem 0;
            flex-wrap: wrap;
        }

        .page-btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: var(--blur-effect);
            -webkit-backdrop-filter: var(--blur-effect);
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .page-btn:hover, .page-btn.active {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .page-btn.prev, .page-btn.next {
            width: auto;
            padding: 0 1.2rem;
            border-radius: 50px;
        }

        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: rgba(255, 255, 255, 0.3);
            pointer-events: none;
        }

        .page-dots {
            margin: 0 0.5rem;
            align-self: center;
        }

        /* Sidebar */
        .blog-sidebar {
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
    <!-- Fixed Header -->
    <header>
        <div class="container">
            <nav>
                <a href="index.php" class="logo">PIXELBYTE</a>
                <div class="nav-links">
                    <ul>
                        <li><a href="index.php" class="underline">Home</a></li>
                        <li><a href="#" class="underline">Blog</a></li>
                        <li><a href="#" class="underline">Categories</a></li>
                        <li><a href="#" class="underline">About</a></li>
                        <li><a href="#" class="underline">Contact</a></li>
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

    <!-- Main Content Container -->
    <div class="container main-container">
        <!-- Blog Header with Search -->
        <div class="blog-header">
            <div class="blog-title-section">
                <h1>PIXELBYTE BLOG</h1>
                <p>Insights on web design, development, and creative inspiration</p>
            </div>
            <div class="search-section">
                <form class="search-form" action="/blog/search.php" method="get">
                    <input type="text" placeholder="Search articles..." name="q" class="search-input" required>
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>
        </div>

        <!-- Blog Content Layout -->
        <div class="blog-container">
            <!-- Blog Main Content -->
            <main class="blog-content">
                <!-- Blog Filters - Categories -->
                <div class="blog-filters">
                    <button class="filter-btn active" data-filter="all">All</button>
                    
                    <!-- Category filters - dynamically populated from database -->
                    <?php 
                    // Reset categories result pointer
                    if ($categories_result) $categories_result->data_seek(0);
                    while($category = $categories_result->fetch_assoc()): 
                    ?>
                    <button class="filter-btn" data-filter="category-<?php echo sanitize_class_name($category['category']); ?>">
                        <?php echo htmlspecialchars($category['category']); ?>
                    </button>
                    <?php endwhile; ?>
                </div>
                
                <!-- Featured Post Section (if available) -->
                <?php if($featured_post): ?>
                <div class="featured-post-container">
                    <article class="featured-post" data-category="category-<?php echo sanitize_class_name($featured_post['category']); ?>">
                        <img src="<?php echo htmlspecialchars($featured_post['image_url']); ?>" alt="<?php echo htmlspecialchars($featured_post['title']); ?>" class="featured-post-img">
                        <div class="featured-post-content">
                            <span class="featured-label">Featured</span>
                            <div class="blog-card-meta">
                                <span class="blog-card-category"><?php echo htmlspecialchars($featured_post['category']); ?></span>
                                <span class="blog-card-date"><?php echo date('F j, Y', strtotime($featured_post['created_at'])); ?></span>
                            </div>
                            <h2 class="blog-card-title"><?php echo htmlspecialchars($featured_post['title']); ?></h2>
                            <p class="blog-card-excerpt"><?php echo htmlspecialchars($featured_post['excerpt']); ?></p>
                            <a href="post.php?slug=<?php echo $featured_post['slug']; ?>" class="btn">Read More</a>
                            
                            <!-- Display tags for featured post -->
                            <?php 
                            $featured_tags = get_post_tags($conn, $featured_post['id']);
                            if (!empty($featured_tags)): 
                            ?>
                            <div class="blog-card-tags">
                                <?php foreach($featured_tags as $tag): ?>
                                <a href="tag.php?slug=<?php echo $tag['slug']; ?>" class="blog-card-tag"><?php echo htmlspecialchars($tag['name']); ?></a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </article>
                </div>
                <?php endif; ?>
                
                <!-- Regular Posts Grid -->
                <div class="blog-posts-grid">
                    <?php 
                    // Reset regular posts result
                    $regular_result->data_seek(0);
                    while($post = $regular_result->fetch_assoc()): 
                        $post_tags = get_post_tags($conn, $post['id']);
                    ?>
                    <article class="blog-card" data-category="category-<?php echo sanitize_class_name($post['category']); ?>">
                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="blog-card-img">
                        <div class="blog-card-content">
                            <div class="blog-card-meta">
                                <span class="blog-card-category"><?php echo htmlspecialchars($post['category']); ?></span>
                                <span class="blog-card-date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                            </div>
                            <h3 class="blog-card-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p class="blog-card-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <a href="<?php echo htmlspecialchars($post['slug']); ?>" class="btn">Read More</a>
                            
                            <!-- Display tags for regular posts -->
                            <?php if (!empty($post_tags)): ?>
                            <div class="blog-card-tags">
                                <?php foreach($post_tags as $tag): ?>
                                <a href="tag/<?php echo $tag['slug']; ?>" class="blog-card-tag"><?php echo htmlspecialchars($tag['name']); ?></a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                   <?php if($current_page > 1): ?>
                        <a href="index.php?page=<?php echo $current_page - 1; ?>" class="page-btn prev">Prev</a>
                    <?php else: ?>
                        <span class="page-btn prev disabled">Prev</span>
                    <?php endif; ?>
                    
                    <?php
                    // Calculate range of page numbers to display
                    $range = 2; // Number of pages before and after current page
                    $start_page = max(1, $current_page - $range);
                    $end_page = min($total_pages, $current_page + $range);
                    
                    // Always show first page
                    if($start_page > 1) {
                        echo '<a href="index.php?page=1" class="page-btn">1</a>';
                        if($start_page > 2) {
                            echo '<span class="page-dots">...</span>';
                        }
                    }
                    
                    // Display page numbers
                    for($i = $start_page; $i <= $end_page; $i++) {
                        if($i == $current_page) {
                            echo '<a href="index.php?page=' . $i . '" class="page-btn active">' . $i . '</a>';
                        } else {
                            echo '<a href="index.php?page=' . $i . '" class="page-btn">' . $i . '</a>';
                        }
                    }
                    
                    // Always show last page
                    if($end_page < $total_pages) {
                        if($end_page < $total_pages - 1) {
                            echo '<span class="page-dots">...</span>';
                        }
                        echo '<a href="index.php?page=' . $total_pages . '" class="page-btn">' . $total_pages . '</a>';
                    }
                    ?>
                    
                    <?php if($current_page < $total_pages): ?>
                        <a href="index.php?page=<?php echo $current_page + 1; ?>" class="page-btn next">Next</a>
                    <?php else: ?>
                        <span class="page-btn next disabled">Next</span>
                    <?php endif; ?>
                </div>
            </main>

            <!-- Blog Sidebar -->
            <aside class="blog-sidebar">
                               
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
                    <form class="newsletter-form">
                        <input type="email" placeholder="Your email address" class="newsletter-input" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
                
                <!-- About Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-heading">About</h3>
                    <p>PIXELBYTE Blog shares insights on web design, development, and creative inspiration with a focus on bold, innovative approaches.</p>
                    <a href="#" class="btn btn-secondary">More About Us</a>
                </div>

                <!-- Archives Section -->
                <div class="sidebar-section">
                    <h3 class="sidebar-heading">Archives</h3>
                    <ul class="archives-list">
                        <li><a href="#">March 2025 <span class="archive-count">5</span></a></li>
                        <li><a href="#">February 2025 <span class="archive-count">12</span></a></li>
                        <li><a href="#">January 2025 <span class="archive-count">8</span></a></li>
                        <li><a href="#">December 2024 <span class="archive-count">10</span></a></li>
                    </ul>
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
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Categories</a></li>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-nav">
                    <h3>Categories</h3>
                    <ul>
                        <li><a href="#">Web Design</a></li>
                        <li><a href="#">CSS Techniques</a></li>
                        <li><a href="#">UX Design</a></li>
                        <li><a href="#">Frontend Development</a></li>
                        <li><a href="#">Design Resources</a></li>
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
            document.body.classList.toggle('no-scroll');
        });
        
        // Close mobile menu when clicking on a nav link
        const mobileLinks = document.querySelectorAll('.mobile-menu a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                document.body.classList.remove('no-scroll');
            });
        });
        
        // Category filtering with smooth transitions
        document.addEventListener('DOMContentLoaded', function() {
            // Filter buttons at the top
            const filterBtns = document.querySelectorAll('.filter-btn');
            const blogCards = document.querySelectorAll('.blog-card, .featured-post');
            
            // Function to handle smooth filtering
            function filterPosts(filter) {
                // Step 1: Fade out all cards
                blogCards.forEach(card => {
                    card.classList.add('filtering-fade-out');
                });
                
                // Step 2: After fade out completes, show/hide appropriate cards
                setTimeout(() => {
                    blogCards.forEach(card => {
                        if (filter === 'all') {
                            // Show all cards
                            card.style.display = 'block';
                            if (card.classList.contains('featured-post')) {
                                card.style.display = 'grid';
                            }
                        } else {
                            // Filter by category
                            if (card.getAttribute('data-category') === filter) {
                                card.style.display = card.classList.contains('featured-post') ? 'grid' : 'block';
                            } else {
                                card.style.display = 'none';
                            }
                        }
                        
                        // Remove fade out class from visible cards
                        if (card.style.display !== 'none') {
                            card.classList.remove('filtering-fade-out');
                        }
                    });
                    
                    // Step 3: Fade in visible cards
                    setTimeout(() => {
                        blogCards.forEach(card => {
                            if (card.style.display !== 'none') {
                                card.classList.add('filtering-fade-in');
                            }
                        });
                    }, 50);
                }, 300); // Match this to your CSS transition duration
            }
            
            // Add click handlers to filter buttons
            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Remove active class from all buttons
                    filterBtns.forEach(b => b.classList.remove('active'));
                    
                    // Add active class to clicked button
                    btn.classList.add('active');
                    
                    // Get filter value and apply filtering
                    const filter = btn.getAttribute('data-filter');
                    filterPosts(filter);
                });
            });
            
            // Category links in sidebar
            const categoryLinks = document.querySelectorAll('.category-filter-link');
            
            categoryLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const categoryName = this.getAttribute('data-category');
                    
                    // Find and click the corresponding filter button
                    const filterButton = document.querySelector(`.filter-btn[data-filter="category-${categoryName}"]`);
                    if (filterButton) {
                        filterButton.click();
                        
                        // Scroll back to top of posts section
                        document.querySelector('.blog-filters').scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Handle responsive featured post layout
            function adjustFeaturedPostLayout() {
                const featuredPost = document.querySelector('.featured-post');
                if (!featuredPost || getComputedStyle(featuredPost).display === 'none') return;
                
                const featuredImg = featuredPost.querySelector('.featured-post-img');
                
                if (window.innerWidth >= 768) {
                    // Desktop layout
                    featuredPost.style.gridTemplateColumns = '1fr 1fr';
                    featuredImg.style.borderBottom = 'none';
                    featuredImg.style.borderRight = '3px solid var(--dark)';
                } else {
                    // Mobile layout
                    featuredPost.style.gridTemplateColumns = '1fr';
                    featuredImg.style.borderBottom = '3px solid var(--dark)';
                    featuredImg.style.borderRight = 'none';
                }
            }
            
            // Add resize handler
            window.addEventListener('resize', adjustFeaturedPostLayout);
            
            // Initial setup
            blogCards.forEach(card => {
                card.style.display = card.classList.contains('featured-post') ? 'grid' : 'block';
                card.classList.add('filtering-fade-in');
            });
            
            // Initial layout adjustment
            adjustFeaturedPostLayout();
        });
    </script>
</body>
</html>
