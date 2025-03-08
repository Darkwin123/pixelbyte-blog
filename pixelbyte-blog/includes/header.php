<?php
/**
 * PIXELBYTE - Unified Header Include
 * This file contains the shared header for all frontend pages
 */

// Define site paths if not already defined
if (!isset($site_root)) {
    $site_root = '/pixelbyte-blog/'; // Root of the site
}
if (!isset($blog_root)) {
    $blog_root = '/pixelbyte-blog/blog/'; // Root of the blog section
}
if (!isset($store_root)) {
    $store_root = '/pixelbyte-blog/store/'; // Root of the store section
}

// Determine current section
$current_path = $_SERVER['REQUEST_URI'];
$is_home = ($current_path == $site_root || $current_path == $site_root . 'index.php');
$is_blog = (strpos($current_path, $blog_root) !== false);
$is_store = (strpos($current_path, $store_root) !== false);

// Set default page title and description if not already set
if (!isset($page_title)) {
    $page_title = 'PIXELBYTE - Avant-Garde Web Design Templates';
}
if (!isset($page_description)) {
    $page_description = 'Pushing the boundaries of web design with cutting-edge templates and themes. Avant-garde digital experiences.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $page_description; ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $page_description; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <?php if(isset($og_image)): ?>
    <meta property="og:image" content="<?php echo $og_image; ?>">
    <?php endif; ?>
    
    <title><?php echo $page_title; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Darker+Grotesque:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="<?php echo $site_root; ?>assets/css/main.css">
    
    <!-- Page-specific CSS if needed -->
    <?php if(isset($page_css)): ?>
    <link rel="stylesheet" href="<?php echo $page_css; ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Cursor (visible on desktop) -->
    <div class="cursor"></div>
    <div class="cursor-follower"></div>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-inner">
                <a href="<?php echo $site_root; ?>" class="logo">PIXELBYTE</a>
                <div class="nav-links">
                    <ul>
                        <li><a href="<?php echo $site_root; ?>" <?php if($is_home) echo 'class="active"'; ?>>Home</a></li>
                        <li><a href="<?php echo $store_root; ?>" <?php if($is_store) echo 'class="active"'; ?>>Store</a></li>
                        <li><a href="<?php echo $blog_root; ?>" <?php if($is_blog) echo 'class="active"'; ?>>Blog</a></li>
                        <li><a href="<?php echo $site_root; ?>#features">Features</a></li>
                        <li><a href="<?php echo $site_root; ?>#contact">Contact</a></li>
                        <?php if($is_store): ?>
                        <li>
                            <a href="<?php echo $store_root; ?>cart.php" class="cart-link">
                                Cart <span class="cart-count">0</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <button class="mobile-menu-btn" aria-label="Toggle Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <ul>
            <li><a href="<?php echo $site_root; ?>">Home</a></li>
            <li><a href="<?php echo $store_root; ?>">Store</a></li>
            <li><a href="<?php echo $blog_root; ?>">Blog</a></li>
            <li><a href="<?php echo $site_root; ?>#features">Features</a></li>
            <li><a href="<?php echo $site_root; ?>#contact">Contact</a></li>
            <?php if($is_store): ?>
            <li><a href="<?php echo $store_root; ?>cart.php">Cart</a></li>
            <?php endif; ?>
        </ul>
    </div>