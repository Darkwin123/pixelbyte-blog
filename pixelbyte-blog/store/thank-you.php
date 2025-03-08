<?php
// Start session to access cart data
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'pixelbyte_blog');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Site root paths
$site_root = '/pixelbyte-blog/'; // Root of the site
$store_root = '/pixelbyte-blog/store/'; // Root of the store section

// Get order information
$order_number = isset($_GET['order']) ? $_GET['order'] : '';
$order_info = null;

if (!empty($order_number)) {
    $sql = "SELECT * FROM store_orders WHERE order_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $order_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order_info = $result->fetch_assoc();
        
        // Get order items
        $items_sql = "SELECT oi.*, p.title, p.image_url 
                     FROM store_order_items oi
                     JOIN store_products p ON oi.product_id = p.id
                     WHERE oi.order_id = ?";
        $items_stmt = $conn->prepare($items_sql);
        $items_stmt->bind_param("i", $order_info['id']);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        $order_items = [];
        
        while ($item = $items_result->fetch_assoc()) {
            $order_items[] = $item;
        }
    }
}

// Redirect if order doesn't exist
if (!$order_info) {
    header('Location: ' . $store_root);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Thank you for your purchase from PIXELBYTE Store">
    
    <title>Thank You - PIXELBYTE Store</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Darker+Grotesque:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS styles (use the same as other pages) -->
    <style>
        /* Include base styles from index.php */
        
        /* Additional styles for thank you page */
        .thank-you-header {
            text-align: center;
            padding: 8rem 0 4rem;
            background-image: linear-gradient(135deg, rgba(179, 157, 219, 0.1) 0%, rgba(38, 166, 154, 0.1) 100%);
        }
        
        .thank-you-title {
            margin-bottom: 1rem;
        }
        
        .thank-you-message {
            max-width: 800px;
            margin: 0 auto 2rem;
            font-size: 1.2rem;
        }
        
        .order-info {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 800px;
        }
        
        .order-details {
            margin-bottom: 2rem;
        }
        
        .order-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .order-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .order-label {
            font-weight: 600;
        }
        
        .order-items {
            margin-top: 2rem;
        }
        
        .order-item {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .order-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .order-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .order-item-info {
            flex: 1;
        }
        
        .order-item-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .order-item-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: rgba(0, 0, 0, 0.7);
        }
        
        .download-btn {
            width: 100%;
            margin-top: 2rem;
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

    <!-- Thank You Header -->
    <section class="thank-you-header">
        <div class="container">
            <h1 class="thank-you-title">Thank You for Your Purchase!</h1>
            <p class="thank-you-message">Your order has been successfully placed. We've sent a confirmation email to <?php echo htmlspecialchars($order_info['customer_email']); ?> with your receipt and download links.</p>
        </div>
    </section>

    <!-- Order Details -->
    <section class="section">
        <div class="container">
            <div class="order-info">
                <h2>Order Details</h2>
                <div class="order-details">
                    <div class="order-row">
                        <span class="order-label">Order Number:</span>
                        <span><?php echo htmlspecialchars($order_info['order_number']); ?></span>
                    </div>
                    <div class="order-row">
                        <span class="order-label">Date:</span>
                        <span><?php echo date('F j, Y', strtotime($order_info['created_at'])); ?></span>
                    </div>
                    <div class="order-row">
                      <span class="order-label">Customer:</span>
                        <span><?php echo htmlspecialchars($order_info['customer_name']); ?></span>
                    </div>
                    <div class="order-row">
                        <span class="order-label">Email:</span>
                        <span><?php echo htmlspecialchars($order_info['customer_email']); ?></span>
                    </div>
                    <div class="order-row">
                        <span class="order-label">Payment Status:</span>
                        <span><?php echo ucfirst(htmlspecialchars($order_info['payment_status'])); ?></span>
                    </div>
                    <div class="order-row">
                        <span class="order-label">Total:</span>
                        <span>$<?php echo number_format($order_info['total_amount'], 2); ?></span>
                    </div>
                </div>
                
                <div class="order-items">
                    <h3>Items Purchased</h3>
                    
                    <?php if(!empty($order_items)): ?>
                        <?php foreach($order_items as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="order-item-img">
                                <div class="order-item-info">
                                    <h4 class="order-item-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                                    <div class="order-item-meta">
                                        <span>Quantity: <?php echo $item['quantity']; ?></span>
                                        <span>Price: $<?php echo number_format($item['price'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No items found for this order.</p>
                    <?php endif; ?>
                </div>
                
                <a href="#" class="btn download-btn">Download Your Templates</a>
                <p style="text-align: center; margin-top: 1rem; font-size: 0.9rem; opacity: 0.7;">Download links were also sent to your email</p>
            </div>
            
            <div style="text-align: center; margin-top: 3rem;">
                <a href="<?php echo $store_root; ?>" class="btn btn-outline">Continue Shopping</a>
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
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Refund Policy</a></li>
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