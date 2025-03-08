<?php
require_once 'config.php';

// Calculate cart items count (if session exists)
$cart_items = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_items += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - PIXELBYTE Store' : 'PIXELBYTE Store'; ?></title>
    
    <!-- Meta description -->
    <meta name="description" content="<?php echo isset($meta_description) ? $meta_description : 'Premium web design templates and themes for your next project'; ?>">
    
    <!-- Open Graph meta tags -->
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - PIXELBYTE Store' : 'PIXELBYTE Store'; ?>">
    <meta property="og:description" content="<?php echo isset($meta_description) ? $meta_description : 'Premium web design templates and themes for your next project'; ?>">
    <meta property="og:url" content="<?php echo getBaseUrl() . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="<?php echo isset($og_type) ? $og_type : 'website'; ?>">
    <?php if(isset($og_image)): ?>
    <meta property="og:image" content="<?php echo $og_image; ?>">
    <?php endif; ?>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Darker+Grotesque:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <style>
        /* Include your CSS here (the same styles from other pages) */
    </style>
    
    <?php if(isset($extra_head)): ?>
        <?php echo $extra_head; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-inner">
                <a href="<?php echo SITE_ROOT; ?>" class="logo">PIXELBYTE</a>
                <div class="nav-links">
                    <ul>
                        <li><a href="<?php echo SITE_ROOT; ?>">Home</a></li>
                        <li><a href="<?php echo STORE_ROOT; ?>">Store</a></li>
                        <li><a href="<?php echo BLOG_ROOT; ?>">Blog</a></li>
                        <li><a href="<?php echo SITE_ROOT; ?>#features">Features</a></li>
                        <li><a href="<?php echo SITE_ROOT; ?>#contact">Contact</a></li>
                        <li><a href="<?php echo STORE_ROOT; ?>cart.php">Cart (<?php echo $cart_items; ?>)</a></li>
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
            <li><a href="<?php echo SITE_ROOT; ?>">Home</a></li>
            <li><a href="<?php echo STORE_ROOT; ?>">Store</a></li>
            <li><a href="<?php echo BLOG_ROOT; ?>">Blog</a></li>
            <li><a href="<?php echo SITE_ROOT; ?>#features">Features</a></li>
            <li><a href="<?php echo SITE_ROOT; ?>#contact">Contact</a></li>
            <li><a href="<?php echo STORE_ROOT; ?>cart.php">Cart (<?php echo $cart_items; ?>)</a></li>
        </ul>
    </div>