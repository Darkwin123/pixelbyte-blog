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

// Get category slug
if(!isset($_GET['slug'])) {
    header('Location: ' . $store_root);
    exit;
}

$slug = $_GET['slug'];

// Get category info
$cat_sql = "SELECT * FROM store_categories WHERE slug = ?";
$cat_stmt = $conn->prepare($cat_sql);
$cat_stmt->bind_param("s", $slug);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();

if($cat_result->num_rows == 0) {
    header('Location: ' . $store_root);
    exit;
}

$category = $cat_result->fetch_assoc();

// Pagination settings
$products_per_page = 8;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $products_per_page;

// Get total product count for this category
$count_sql = "SELECT COUNT(*) as total FROM store_products WHERE category = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("s", $category['name']);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $products_per_page);

// Get products in this category with pagination
$products_sql = "SELECT * FROM store_products WHERE category = ? ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($products_sql);
$stmt->bind_param("sii", $category['name'], $offset, $products_per_page);
$stmt->execute();
$products_result = $stmt->get_result();

// Get all categories for sidebar
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
    <meta name="description" content="<?php echo htmlspecialchars($category['description']); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($category['name']); ?> - PIXELBYTE Store">
    <meta property="og:description" content="<?php echo htmlspecialchars($category['description']); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <title><?php echo htmlspecialchars($category['name']); ?> - PIXELBYTE Store</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Darker+Grotesque:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Include the same styles as index.php -->
    <style>
        /* Include all CSS from index.php file */
        
        /* Additional styles for category page */
        .category-header {
            text-align: center;
            padding: 8rem 0 4rem;
            background-image: linear-gradient(135deg, rgba(179, 157, 219, 0.1) 0%, rgba(38, 166, 154, 0.1) 100%);
        }
        
        .category-title {
            margin-bottom: 1rem;
        }
        
        .category-description {
            max-width: 800px;
            margin: 0 auto 2rem;
        }
        
        .category-stats {
            font-size: 1.1rem;
            color: var(--primary);
        }
        
        .sidebar {
            margin-top: 2rem;
            position: sticky;
            top: 100px;
        }
        
        .sidebar-section {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .sidebar-title {
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .category-list {
            list-style: none;
        }
        
        .category-list li {
            margin-bottom: 0.8rem;
        }
        
        .category-list a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .category-list a:hover {
            background: rgba(126, 87, 194, 0.05);
            transform: translateX(5px);
        }
        
        .category-list a.active {
            background: rgba(126, 87, 194, 0.1);
            color: var(--primary);
            font-weight: 600;
        }
        
        .category-count {
            background: rgba(126, 87, 194, 0.1);
            color: var(--primary);
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-inner">
                <a href="<?php echo $site_root; ?>" class="logo">PIXELBYTE</a>
                <div class="nav-links">
                    <ul>
                        <li><a href="<?php echo $site_root; ?>">Home</a></li>
                        <li><a href="<?php echo $store_root; ?>">Store</a></li>
                        <li><a href="<?php echo $site_root; ?>blog/">Blog</a></li>
                        <li><a href="<?php echo $site_root; ?>#features">Features</a></li>
                        <li><a href="<?php echo $site_root; ?>#contact">Contact</a></li>
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
            <li><a href="<?php echo $site_root; ?>blog/">Blog</a></li>
            <li><a href="<?php echo $site_root; ?>#features">Features</a></li>
            <li><a href="<?php echo $site_root; ?>#contact">Contact</a></li>
        </ul>
    </div>

    <!-- Category Header -->
    <section class="category-header">
        <div class="container">
            <h1 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h1>
            <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
            <p class="category-stats"><?php echo $total_products; ?> products found</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="section">
        <div class="container">
            <div class="row">
                <!-- Products Grid -->
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
                        <p>No products found in this category.</p>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                    <div class="pagination">
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?php echo $store_root; ?>category/<?php echo $slug; ?>?page=<?php echo $i; ?>" class="page-link <?php echo ($current_page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
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
                            $categories_result->data_seek(0);
                            while($cat = $categories_result->fetch_assoc()): 
                        ?>
                            <li>
                                <a href="<?php echo $store_root; ?>category/<?php echo $cat['slug']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
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

    <!-- JavaScript (same as index.php) -->
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const mobileMenu = document.querySelector('.mobile-menu');
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
            document.body.classList.toggle('no-scroll');
            
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
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            });
        });
    </script>
</body>
</html>