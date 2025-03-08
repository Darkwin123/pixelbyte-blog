<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'pixelbyte_blog');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Site root paths
$site_root = '/pixelbyte-blog/'; // Root of the site
$store_root = '/pixelbyte-blog/store/'; // Root of the store section

// Pagination settings
$products_per_page = 8;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $products_per_page;

// Get total product count
$count_sql = "SELECT COUNT(*) as total FROM store_products";
$count_result = $conn->query($count_sql);
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $products_per_page);

// Get featured products
$featured_sql = "SELECT * FROM store_products WHERE is_featured = 1 LIMIT 4";
$featured_result = $conn->query($featured_sql);

// Get regular products with pagination
$products_sql = "SELECT * FROM store_products ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($products_sql);
$stmt->bind_param("ii", $offset, $products_per_page);
$stmt->execute();
$products_result = $stmt->get_result();

// Get categories for sidebar
$categories_sql = "SELECT c.name, c.slug, COUNT(p.id) as product_count 
                  FROM store_categories c
                  LEFT JOIN store_products p ON p.category = c.name
                  GROUP BY c.name
                  ORDER BY c.name";
$categories_result = $conn->query($categories_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PIXELBYTE Store - Premium web design templates and themes for your next project">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="PIXELBYTE Store - Premium Web Design Templates">
    <meta property="og:description" content="Get high-quality web design templates and themes for your next project">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <title>PIXELBYTE | Store</title>
    
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
            --accent1: #FF5722;
            --accent2: #FFC107;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            --card-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
            --font-main: 'Inter', sans-serif;
            --font-heading: 'Plus Jakarta Sans', sans-serif;
            --font-alt: 'Inter', sans-serif;
            --easing: cubic-bezier(0.76, 0, 0.24, 1);
        }

        /* Base styles */
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 80px;
        }

        body {
            font-family: var(--font-main);
            background-color: #f8f9fc;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
	    padding-top: 80px;
        }

    .main-container {
        padding-top: 2rem;
    }

        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: clamp(3rem, 8vw, 6rem);
            letter-spacing: -2px;
        }

        h2 {
            font-size: clamp(2rem, 5vw, 4rem);
            letter-spacing: -1px;
        }

        h3 {
            font-size: clamp(1.5rem, 3vw, 2.5rem);
            letter-spacing: -0.5px;
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
            transition: all 0.5s var(--easing);
        }

        a:hover {
            color: var(--secondary);
        }

        /* Container and Layout */
       .container {
        width: 90%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 1rem;
    }

        .section {
            padding: 6rem 0;
            position: relative;
        }
   header {
        position: fixed;
        width: 100%;
        top: 0;
        left: 0;
        z-index: 100;
        padding: 1rem 0;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
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
    
    /* Nav links and mobile adjustments */
    .nav-links {
        display: none;
    }
    
    @media (min-width: 768px) {
        .nav-links {
            display: block;
        }
        .mobile-menu-btn {
            display: none;
        }
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

/* Add CSS Variables for blur effect if not already present */
:root {
    --blur-effect: blur(10px);
}

/* Underline animation for nav links */
a.underline {
    position: relative;
}

a.underline::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -3px;
    left: 0;
    background-color: var(--primary);
    transition: width 0.3s ease;
}

a.underline:hover::after {
    width: 100%;
}

        /* Store Hero Section */
        .store-hero {
            padding: 8rem 0 6rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            background-image: linear-gradient(135deg, rgba(179, 157, 219, 0.1) 0%, rgba(38, 166, 154, 0.1) 100%);
        }

        .store-hero-title {
            margin-bottom: 1.5rem;
        }

        .store-hero-subtitle {
            max-width: 800px;
            margin: 0 auto 2.5rem;
            font-size: 1.3rem;
        }

        /* Product Grid */
        .store-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-top: 3rem;
        }

        @media (min-width: 640px) {
            .store-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .store-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Product Card */
        .product-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .product-card-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .product-card-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-card-category {
            display: inline-block;
            background: rgba(126, 87, 194, 0.1);
            color: var(--primary);
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .product-card-title {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .product-card-description {
            margin-bottom: 1.5rem;
            color: rgba(18, 18, 18, 0.7);
        }

        .product-card-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .product-card-footer {
            margin-top: auto;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 2rem;
            background: var(--primary);
            color: white;
            font-weight: 600;
            border-radius: 2rem;
            border: none;
            cursor: pointer;
            transition: all 0.5s var(--easing);
            position: relative;
            overflow: hidden;
            z-index: 1;
            font-family: var(--font-main);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: var(--secondary);
            transition: all 0.5s var(--easing);
            z-index: -1;
        }

        .btn:hover {
            transform: scale(1.05);
            color: white;
        }

        .btn:hover::before {
            width: 100%;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 3rem 0;
        }

        .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .page-link.active,
        .page-link:hover {
            background: var(--primary);
            color: white;
        }

        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 5rem 0 2rem;
            position: relative;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
        }

        @media (min-width: 768px) {
            .footer-content {
                grid-template-columns: 2fr 1fr 1fr;
            }
        }

        .footer-logo {
            font-family: var(--font-heading);
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary-light), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        .footer-nav h3 {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .footer-nav h3::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            bottom: 0;
            left: 0;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .footer-nav ul {
            list-style: none;
        }

        .footer-nav li {
            margin-bottom: 1rem;
        }

        .footer-nav a {
            color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
        }

        .footer-nav a:hover {
            color: white;
            transform: translateX(5px);
            display: inline-block;
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
            border-radius: 50%;
            color: white;
            transition: all 0.3s ease;
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
            <a href="<?php echo $site_root; ?>" class="logo">PIXELBYTE</a>
            <div class="nav-links">
                <ul>
                    <li><a href="<?php echo $site_root; ?>">Home</a></li>
                    <li><a href="<?php echo $store_root; ?>">Store</a></li>
                    <li><a href="<?php echo $site_root; ?>blog/">Blog</a></li>
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
        <li><a href="<?php echo $site_root; ?>">Home</a></li>
        <li><a href="<?php echo $store_root; ?>">Store</a></li>
        <li><a href="<?php echo $site_root; ?>blog/">Blog</a></li>
        <li><a href="#">About</a></li>
        <li><a href="#">Contact</a></li>
    </ul>
</div>
    <!-- Store Hero Section -->
    <section class="store-hero">
        <div class="container">
            <h1 class="store-hero-title">Premium Web Templates</h1>
            <p class="store-hero-subtitle">Explore our collection of beautifully crafted, responsive web templates to elevate your digital presence.</p>
            <a href="#featured" class="btn">Explore Templates</a>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="section" id="featured">
        <div class="container">
            <h2>Featured Templates</h2>
            <div class="store-grid">
                <?php if($featured_result->num_rows > 0): ?>
                    <?php while($product = $featured_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="product-card-img">
                            <div class="product-card-content">
                                <span class="product-card-category"><?php echo htmlspecialchars($product['category']); ?></span>
                                <h3 class="product-card-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                                <p class="product-card-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                <div class="product-card-price">$<?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-card-footer">
                                    <a href="<?php echo $store_root; ?>product/<?php echo $product['slug']; ?>" class="btn">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No featured products found.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- All Products Section -->
    <section class="section">
        <div class="container">
            <h2>All Templates</h2>
            <div class="store-grid">
                <?php if($products_result->num_rows > 0): ?>
                    <?php while($product = $products_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="product-card-img">
                            <div class="product-card-content">
                                <span class="product-card-category"><?php echo htmlspecialchars($product['category']); ?></span>
                                <h3 class="product-card-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                                <p class="product-card-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                                <div class="product-card-price">$<?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-card-footer">
                                    <a href="<?php echo $store_root; ?>product/<?php echo $product['slug']; ?>" class="btn">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No products found.</p>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="<?php echo $store_root; ?>page/<?php echo $i; ?>" class="page-link <?php echo ($current_page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">PIXELBYTE</div>
                    <p>Creating unique digital experiences that break the conventional mold with modern templates and themes.</p>
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
                        <li><a href="<?php echo $store_root; ?>">Store</a></li>
                        <li><a href="<?php echo $site_root; ?>blog/">Blog</a></li>
                        <li><a href="#">About</a></li>
                        <li><a href="<?php echo $site_root; ?>#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-nav">
                    <h3>Categories</h3>
                    <ul>
                        <?php 
                        if($categories_result && $categories_result->num_rows > 0):
                            while($category = $categories_result->fetch_assoc()): 
                        ?>
                            <li>
                                <a href="<?php echo $store_root; ?>category/<?php echo $category['slug']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php 
                            endwhile;
                        endif;
                        ?>
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
document.addEventListener("DOMContentLoaded", function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    mobileMenuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('active');
        
        // Transform hamburger to X
        const spans = mobileMenuBtn.querySelectorAll('span');
        
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