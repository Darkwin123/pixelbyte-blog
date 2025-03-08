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

// Search term validation
$search_term = '';
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_term = htmlspecialchars($_GET['q']);
} else {
    // Redirect to home if no search term
    header('Location: index.php');
    exit;
}

// Get search results
$sql = "SELECT * FROM blog_posts WHERE 
        title LIKE ? OR 
        excerpt LIKE ? OR 
        content LIKE ? 
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$search_param = "%" . $search_term . "%";
$stmt->bind_param("sss", $search_param, $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();

// Get categories with count
$sql_categories = "SELECT category, COUNT(*) as count FROM blog_posts GROUP BY category";
$categories_result = $conn->query($sql_categories);

// Get popular posts (for sidebar)
$sql_popular = "SELECT id, title, image_url, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 3";
$popular_result = $conn->query($sql_popular);

// Get all tags for sidebar
$all_tags = get_all_tags($conn);

// Helper function to sanitize names for CSS classes
function sanitize_class_name($name) {
    return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
}

// Add the highlight_search_term function right here
/**
 * Function to highlight search terms in text
 * 
 * @param string $text Text to highlight terms in
 * @param string $search_term Term to highlight
 * @return string Text with highlighted terms
 */
function highlight_search_term($text, $search_term) {
    if (empty($search_term)) return $text;
    
    // Escape the search term for regex
    $search_term = preg_quote($search_term, '/');
    
    // Replace the term with highlighted version, case-insensitive
    return preg_replace('/(' . $search_term . ')/i', '<span class="highlight">$1</span>', $text);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PIXELBYTE Blog - Search results for <?php echo $search_term; ?>">
    <title>Search Results: <?php echo $search_term; ?> - PIXELBYTE Blog</title>
    <style>
        /* CSS Reset */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* CSS Variables */
        :root {
            --primary: #ff3e00;
            --secondary: #0099ff;
            --dark: #111111;
            --light: #f5f5f5;
            --accent: #ffcc00;
            --font-main: 'Space Grotesk', sans-serif;
            --font-heading: 'Clash Display', sans-serif;
            --shadow: 4px 4px 0 rgba(0, 0, 0, 0.9);
            --border: 3px solid var(--dark);
        }

        /* Base styles */
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 100px;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

/* Full width container for search results (no sidebar) */
.blog-container.full-width {
    grid-template-columns: 1fr !important;
}

/* Search results header with back button */
.search-results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.back-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: clamp(2.5rem, 6vw, 4rem);
            text-transform: uppercase;
            letter-spacing: -1px;
        }

        h2 {
            font-size: clamp(2rem, 5vw, 3rem);
            letter-spacing: -0.5px;
        }

        h3 {
            font-size: clamp(1.5rem, 3vw, 2rem);
        }

        p {
            margin-bottom: 1.5rem;
            font-size: clamp(1rem, 1.5vw, 1.2rem);
        }

        a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        a:hover {
            color: var(--primary);
        }

        a.underline::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 3px;
            bottom: -3px;
            left: 0;
            background-color: var(--primary);
            transform: scaleX(0);
            transform-origin: bottom right;
            transition: transform 0.3s ease;
        }

        a.underline:hover::after {
            transform: scaleX(1);
            transform-origin: bottom left;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background-color: var(--dark);
            color: var(--light);
            border: var(--border);
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            position: relative;
            top: 0;
            left: 0;
        }

        .btn:hover {
            top: -5px;
            left: -5px;
            box-shadow: 9px 9px 0 rgba(0, 0, 0, 0.9);
            color: var(--light);
        }

        .btn-primary {
            background-color: var(--primary);
        }

        .btn-secondary {
            background-color: var(--secondary);
        }

        /* Layout */
        .container {
            width: 90%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .blog-container {
            padding-top: 2rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 1024px) {
            .blog-container {
                grid-template-columns: 2fr 1fr;
            }
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
            background-color: var(--light);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -1px;
            color: var(--dark);
            padding: 0.5rem 1rem;
            background-color: var(--accent);
            border: var(--border);
            box-shadow: var(--shadow);
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
            font-weight: 700;
            text-transform: uppercase;
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
            height: 3px;
            margin: 6px;
            background-color: var(--dark);
            transition: all 0.3s ease;
        }

        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            height: 100vh;
            background-color: var(--light);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: right 0.5s ease;
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
            text-transform: uppercase;
        }

        @media (min-width: 768px) {
            .nav-links {
                display: block;
            }
            .mobile-menu-btn {
                display: none;
            }
        }

        /* Blog Hero Section */
        .blog-hero {
            padding: 8rem 0 4rem;
            text-align: center;
            background-color: var(--accent);
            border-bottom: var(--border);
            position: relative;
            overflow: hidden;
        }

        .blog-hero-title {
            margin-bottom: 1rem;
        }

        .blog-hero-text {
            max-width: 700px;
            margin: 0 auto 2rem;
        }

        .blog-hero-shape {
            position: absolute;
            width: 150px;
            height: 150px;
            background-color: var(--primary);
            border: var(--border);
            z-index: 1;
        }

        .shape-1 {
            top: -30px;
            right: 10%;
            transform: rotate(15deg);
        }

        .shape-2 {
            bottom: -30px;
            left: 10%;
            transform: rotate(-15deg);
        }

        /* Blog Post Cards */
        .blog-posts-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
            margin-top: 3rem;
        }

        @media (min-width: 768px) {
            .blog-posts-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .blog-card {
            border: var(--border);
            box-shadow: var(--shadow);
            background-color: white;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            top: 0;
            left: 0;
        }

        .blog-card:hover {
            top: -10px;
            left: -10px;
            box-shadow: 14px 14px 0 rgba(0, 0, 0, 0.9);
        }

        .blog-card-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-bottom: var(--border);
        }

        .blog-card-content {
            padding: 1.5rem;
        }

        .blog-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .blog-card-category {
            display: inline-block;
            background-color: var(--accent);
            padding: 0.3rem 1rem;
            font-weight: 600;
            border: 2px solid var(--dark);
        }

        .blog-card-date {
            color: #666;
        }

        .blog-card-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .blog-card-excerpt {
            margin-bottom: 1.5rem;
        }

        .featured-post {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            .featured-post {
                grid-template-columns: 1fr 1fr;
            }
        }

        .featured-post .blog-card-img {
            height: 100%;
            border-bottom: var(--border);
        }

        @media (min-width: 768px) {
            .featured-post .blog-card-img {
                border-bottom: none;
                border-right: var(--border);
            }
        }

        .featured-label {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 0.3rem 1rem;
            font-weight: 600;
            border: 2px solid var(--dark);
            margin-bottom: 0.5rem;
        }

        /* Blog Filters */
        .blog-filters {
            margin: 2rem 0;
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
        }

        .filter-btn {
            padding: 0.5rem 1.2rem;
            background-color: white;
            border: 2px solid var(--dark);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            top: 0;
            box-shadow: 2px 2px 0 rgba(0, 0, 0, 0.3);
        }

        .filter-btn:hover {
            top: -2px;
            box-shadow: 4px 4px 0 rgba(0, 0, 0, 0.3);
        }

        .filter-btn.active {
            background-color: var(--dark);
            color: white;
            top: -2px;
            box-shadow: 4px 4px 0 rgba(0, 0, 0, 0.3);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 3rem 0;
        }

        .page-btn {
            display: inline-block;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: white;
            border: 2px solid var(--dark);
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .page-btn:hover, .page-btn.active {
            background-color: var(--dark);
            color: white;
        }

        .page-btn.prev, .page-btn.next {
            width: auto;
            padding: 0 1rem;
        }

        /* Sidebar */
        .blog-sidebar {
            margin-top: 2rem;
        }

        .sidebar-section {
            border: var(--border);
            box-shadow: var(--shadow);
            background-color: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .sidebar-section:last-child {
            margin-bottom: 0;
        }

        .sidebar-heading {
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid var(--dark);
        }

        .search-form {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .search-input {
            flex: 1;
            padding: 0.8rem;
            border: 2px solid var(--dark);
            font-family: var(--font-main);
        }

        .search-btn {
            background-color: var(--dark);
            color: var(--light);
            border: 2px solid var(--dark);
            padding: 0 1rem;
            cursor: pointer;
        }

        .search-btn:hover {
            background-color: var(--primary);
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
            border-bottom: 1px solid #eee;
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
        }

        .category-count,
        .archive-count {
            background-color: var(--accent);
            color: var(--dark);
            padding: 0.1rem 0.5rem;
            border-radius: 50px;
            font-size: 0.8rem;
            border: 1px solid var(--dark);
        }

        .popular-post {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .popular-post:last-child {
            margin-bottom: 0;
        }

        .popular-post-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 2px solid var(--dark);
        }

        .popular-post-content {
            flex: 1;
        }

        .popular-post-title {
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }

        .popular-post-date {
            font-size: 0.8rem;
            color: #666;
        }

        /* Tag Styles */
        .blog-card-tags {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .blog-card-tag {
            display: inline-block;
            background-color: #eee;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-size: 0.8rem;
            border: 1px solid var(--dark);
            transition: all 0.3s ease;
        }

        .blog-card-tag:hover {
            background-color: var(--accent);
        }

        .tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.7rem;
        }

        .tags-list a {
            display: inline-block;
            background-color: #eee;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.9rem;
            border: 1px solid var(--dark);
        }

        .tags-list a:hover {
            background-color: var(--accent);
        }

        .tag-count {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            font-size: 0.7rem;
            padding: 0.1rem 0.4rem;
            border-radius: 50px;
            margin-left: 0.3rem;
        }

        .newsletter-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .newsletter-input {
            padding: 0.8rem;
            border: 2px solid var(--dark);
            font-family: var(--font-main);
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: var(--light);
            padding: 3rem 0;
            margin-top: 5rem;
        }

        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        @media (min-width: 768px) {
            .footer-content {
                flex-direction: row;
                justify-content: space-between;
                text-align: left;
                align-items: flex-start;
            }
        }

        .footer-logo {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -1px;
            color: var(--light);
            margin-bottom: 1rem;
        }

        .footer-nav ul {
            list-style: none;
        }

        .footer-nav li {
            margin-bottom: 0.5rem;
        }

        .footer-nav a {
            color: var(--light);
        }

        .footer-nav a:hover {
            color: var(--accent);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: var(--light);
            color: var(--dark);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background-color: var(--primary);
            color: var(--light);
        }

        .copyright {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
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
        
        /* Additional styles for search results */
        .search-results-heading {
            margin-bottom: 2rem;
        }
        
        .highlight {
            background-color: var(--accent);
            padding: 0 3px;
        }
        
        .no-results-message {
            text-align: center;
            padding: 3rem 0;
        }
        
        .search-term {
            font-weight: bold;
            color: var(--primary);
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

   <!-- Search Hero Section -->
<section class="blog-hero">
    <div class="container">
        <h1 class="blog-hero-title">Search Results</h1>
        <p class="blog-hero-text">Looking for articles matching your interests?</p>
    </div>
    <div class="blog-hero-shape shape-1"></div>
    <div class="blog-hero-shape shape-2"></div>
</section>

   <div class="container">
    <div class="blog-container full-width">
        <!-- Search Results Content -->
        <main class="blog-content">
            <div class="search-results-header">
                <h2 class="search-results-heading">
                    <?php echo $result->num_rows; ?> result<?php echo $result->num_rows != 1 ? 's' : ''; ?> found for "<?php echo $search_term; ?>"
                </h2>
                <a href="index.php" class="btn btn-secondary back-button">← Back to Blog</a>
            </div>
            
            <!-- Blog Posts Grid -->
            <div class="blog-posts-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($post = $result->fetch_assoc()): 
                        $post_tags = get_post_tags($conn, $post['id']);
                    ?>
                    <article class="blog-card" data-category="category-<?php echo sanitize_class_name($post['category']); ?>">
                        <img src="<?php echo $post['image_url']; ?>" alt="<?php echo $post['title']; ?>" class="blog-card-img">
                        <div class="blog-card-content">
                            <div class="blog-card-meta">
                                <span class="blog-card-category"><?php echo $post['category']; ?></span>
                                <span class="blog-card-date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                            </div>
                            <h3 class="blog-card-title"><?php echo highlight_search_term($post['title'], $search_term); ?></h3>
                            <p class="blog-card-excerpt"><?php echo highlight_search_term($post['excerpt'], $search_term); ?></p>
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="btn">Read More</a>
                            
                            <!-- Display tags for post -->
                            <?php if (!empty($post_tags)): ?>
                            <div class="blog-card-tags">
                                <?php foreach($post_tags as $tag): ?>
                                <a href="tag.php?slug=<?php echo $tag['slug']; ?>" class="blog-card-tag"><?php echo $tag['name']; ?></a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </article>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-results-message">
                        <h2>No results found</h2>
                        <p>Sorry, no posts match your search term. Please try a different search.</p>
                        <a href="index.php" class="btn">Back to Blog</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
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
                <p>&copy; 2025 PIXELBYTE. All rights reserved.</p>
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
    </script>
</body>
</html>