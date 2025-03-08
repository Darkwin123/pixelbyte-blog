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

// Get product slug
if(!isset($_GET['slug'])) {
    header('Location: ' . $store_root);
    exit;
}

$slug = $_GET['slug'];

// Get product details
$sql = "SELECT * FROM store_products WHERE slug = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    header('Location: ' . $store_root);
    exit;
}

$product = $result->fetch_assoc();

// Get related products in the same category
$related_sql = "SELECT * FROM store_products WHERE category = ? AND id != ? LIMIT 4";
$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("si", $product['category'], $product['id']);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($product['description']); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($product['title']); ?> - PIXELBYTE Store">
    <meta property="og:description" content="<?php echo htmlspecialchars($product['description']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($product['image_url']); ?>">
    <meta property="og:type" content="product">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <title><?php echo htmlspecialchars($product['title']); ?> - PIXELBYTE Store</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Darker+Grotesque:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Include the same CSS styles from index.php here -->
    <style>
        /* Add the same CSS from index.php */
        /* Plus additional styles for product details page */
        
        .product-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            margin: 2rem 0;
        }
        
        @media (min-width: 768px) {
            .product-details {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .product-image {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--glass-shadow);
        }
        
        .product-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .product-info {
            display: flex;
            flex-direction: column;
        }
        
        .product-category {
            display: inline-block;
            background: rgba(126, 87, 194, 0.1);
            color: var(--primary);
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .product-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .product-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .product-description {
            margin-bottom: 2rem;
        }
        
        .cta-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .related-products {
            margin-top: 5rem;
        }
        
        .related-products h3 {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <!-- Header - Same as index.php -->
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

    <!-- Mobile Menu - Same as index.php -->
    <div class="mobile-menu">
        <ul>
            <li><a href="<?php echo $site_root; ?>">Home</a></li>
            <li><a href="<?php echo $store_root; ?>">Store</a></li>
            <li><a href="<?php echo $site_root; ?>blog/">Blog</a></li>
            <li><a href="<?php echo $site_root; ?>#features">Features</a></li>
            <li><a href="<?php echo $site_root; ?>#contact">Contact</a></li>
        </ul>
    </div>

    <!-- Product Details -->
    <section class="section">
        <div class="container">
            <div class="product-details">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                </div>
                <div class="product-info">
                    <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                    <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
                    <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                    <div class="product-description">
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                    <div class="cta-buttons">
                        <a href="#" class="btn" id="add-to-cart" data-product-id="<?php echo $product['id']; ?>">Add to Cart</a>
                        <a href="#" class="btn btn-outline">Preview</a>
                    </div>
                </div>
            </div>
            
            <!-- Related Products -->
            <?php if($related_result->num_rows > 0): ?>
            <div class="related-products">
                <h3>Related Templates</h3>
                <div class="store-grid">
                    <?php while($related = $related_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="product-card-img">
                            <div class="product-card-content">
                                <span class="product-card-category"><?php echo htmlspecialchars($related['category']); ?></span>
                                <h3 class="product-card-title"><?php echo htmlspecialchars($related['title']); ?></h3>
                                <p class="product-card-description"><?php echo htmlspecialchars(substr($related['description'], 0, 100)); ?>...</p>
                                <div class="product-card-price">$<?php echo number_format($related['price'], 2); ?></div>
                                <div class="product-card-footer">
                                    <a href="<?php echo $store_root; ?>product/<?php echo $related['slug']; ?>" class="btn">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer - Same as index.php -->
    <!-- JavaScript - Same as index.php -->
    <!-- Add additional JavaScript for the product page functionality -->
    <script>
        // Add to Cart functionality (to be implemented with Stripe later)
        document.getElementById('add-to-cart').addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            alert('Product added to cart! (To be implemented with Stripe)');
            // We'll implement actual cart functionality later
        });
    </script>
</body>
</html>