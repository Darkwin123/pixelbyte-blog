<?php
// Start session to manage cart data
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

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Add product to cart
    if ($action == 'add' && isset($_GET['id'])) {
        $product_id = (int)$_GET['id'];
        
        // Check if product exists
        $sql = "SELECT * FROM store_products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            
            // Check if product is already in cart
            $found = false;
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['id'] == $product_id) {
                    $_SESSION['cart'][$key]['quantity']++;
                    $found = true;
                    break;
                }
            }
            
            // If product not in cart, add it
            if (!$found) {
                $_SESSION['cart'][] = [
                    'id' => $product_id,
                    'title' => $product['title'],
                    'price' => $product['price'],
                    'image_url' => $product['image_url'],
                    'quantity' => 1
                ];
            }
            
            // Redirect back to previous page or to cart
            if (isset($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            } else {
                header('Location: ' . $store_root . 'cart.php');
            }
            exit;
        }
    }
    
    // Remove product from cart
    if ($action == 'remove' && isset($_GET['key'])) {
        $key = (int)$_GET['key'];
        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
            // Re-index the array
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
        header('Location: ' . $store_root . 'cart.php');
        exit;
    }
    
    // Update quantity
    if ($action == 'update' && isset($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $key => $quantity) {
            if (isset($_SESSION['cart'][$key])) {
                $_SESSION['cart'][$key]['quantity'] = max(1, (int)$quantity);
            }
        }
        header('Location: ' . $store_root . 'cart.php');
        exit;
    }
    
    // Clear cart
    if ($action == 'clear') {
        $_SESSION['cart'] = [];
        header('Location: ' . $store_root . 'cart.php');
        exit;
    }
}

// Calculate cart totals
$cart_total = 0;
$cart_items = 0;

foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
    $cart_items += $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your shopping cart at PIXELBYTE - Review your items and proceed to checkout">
    
    <title>Shopping Cart - PIXELBYTE Store</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Darker+Grotesque:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS styles (use the same as other pages) -->
    <style>
        /* Include base styles from index.php */
        
        /* Additional styles for cart page */
        .cart-header {
            text-align: center;
            padding: 8rem 0 4rem;
            background-image: linear-gradient(135deg, rgba(179, 157, 219, 0.1) 0%, rgba(38, 166, 154, 0.1) 100%);
        }
        
        .cart-title {
            margin-bottom: 1rem;
        }
        
        .cart-summary {
            font-size: 1.1rem;
            color: var(--primary);
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
        }
        
        .cart-table th,
        .cart-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .cart-table th {
            background: rgba(126, 87, 194, 0.1);
            font-weight: 600;
        }
        
        .cart-product {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .cart-product-img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .cart-quantity {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            text-align: center;
        }
        
        .cart-remove {
            color: #ff3e00;
            font-weight: 600;
        }
        
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
        }
        
        .cart-totals {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            padding: 2rem;
            margin-top: 2rem;
            max-width: 400px;
            margin-left: auto;
        }
        
        .cart-total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .cart-total-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .cart-total-label {
            font-weight: 600;
        }
        
        .cart-total-value {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary);
        }
        
        .cart-empty {
            text-align: center;
            padding: 3rem;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            margin: 2rem 0;
        }
        
        .cart-empty-title {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
        
        .cart-empty-message {
            margin-bottom: 2rem;
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

    <!-- Cart Header -->
    <section class="cart-header">
        <div class="container">
            <h1 class="cart-title">Your Shopping Cart</h1>
            <p class="cart-summary"><?php echo $cart_items; ?> items in your cart</p>
        </div>
    </section>

    <!-- Cart Content -->
    <section class="section">
        <div class="container">
            <?php if (!empty($_SESSION['cart'])): ?>
                <form action="<?php echo $store_root; ?>cart.php?action=update" method="post">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $key => $item): ?>
                                <tr>
                                    <td>
                                        <div class="cart-product">
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="cart-product-img">
                                            <div>
                                                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                            </div>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <input type="number" name="quantity[<?php echo $key; ?>]" value="<?php echo $item['quantity']; ?>" min="1" class="cart-quantity">
                                    </td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    <td>
                                        <a href="<?php echo $store_root; ?>cart.php?action=remove&key=<?php echo $key; ?>" class="cart-remove">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="cart-actions">
                        <div>
                            <a href="<?php echo $store_root; ?>" class="btn btn-outline">Continue Shopping</a>
                            <a href="<?php echo $store_root; ?>cart.php?action=clear" class="btn btn-outline">Clear Cart</a>
                        </div>
                        <button type="submit" class="btn">Update Cart</button>
                    </div>
                </form>
                
                <div class="cart-totals">
                    <h2>Cart Totals</h2>
                    <div class="cart-total-row">
                        <span class="cart-total-label">Subtotal:</span>
                        <span class="cart-total-value">$<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    <div class="cart-total-row">
                        <span class="cart-total-label">Total:</span>
                        <span class="cart-total-value">$<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    <a href="<?php echo $store_root; ?>checkout.php" class="btn" style="width: 100%; margin-top: 1rem;">Proceed to Checkout</a>
                </div>
            <?php else: ?>
                <div class="cart-empty">
                    <h2 class="cart-empty-title">Your cart is empty</h2>
                    <p class="cart-empty-message">Looks like you haven't added any products to your cart yet.</p>
                    <a href="<?php echo $store_root; ?>" class="btn">Continue Shopping</a>
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

 <script>
// JavaScript for cart functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle (same as index.php)
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
            
            // Quantity input validation
            const quantityInputs = document.querySelectorAll('.cart-quantity');
            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value < 1) {
                        this.value = 1;
                    }
                });
            });
        });
    </script>
</body>
</html>