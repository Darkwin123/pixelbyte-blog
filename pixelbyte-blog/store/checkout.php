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

// Redirect to cart if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: ' . $store_root . 'cart.php');
    exit;
}

// Calculate cart totals
$cart_total = 0;
$cart_items = 0;

foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
    $cart_items += $item['quantity'];
}

// Process checkout form
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    // If no errors, process the order (this will be connected to Stripe later)
    if (empty($errors)) {
        // Generate a unique order number
        $order_number = 'ORD-' . strtoupper(substr(uniqid(), 0, 8));
        
        // Insert order into database
        $sql = "INSERT INTO store_orders (order_number, customer_email, customer_name, total_amount, payment_status) 
                VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssd", $order_number, $email, $name, $cart_total);
        
        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            
            // Insert order items
            $item_sql = "INSERT INTO store_order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_sql);
            
            foreach ($_SESSION['cart'] as $item) {
                $item_stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }
            
            // Clear the cart
            $_SESSION['cart'] = [];
            
            // Redirect to thank you page
            header('Location: ' . $store_root . 'thank-you.php?order=' . $order_number);
            exit;
        } else {
            $errors['system'] = 'Error processing your order. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Complete your purchase of premium web templates from PIXELBYTE">
    
    <title>Checkout - PIXELBYTE Store</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Darker+Grotesque:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS styles (use the same as other pages) -->
    <style>
        /* Include base styles from index.php */
        
        /* Additional styles for checkout page */
        .checkout-header {
            text-align: center;
            padding: 8rem 0 4rem;
            background-image: linear-gradient(135deg, rgba(179, 157, 219, 0.1) 0%, rgba(38, 166, 154, 0.1) 100%);
        }
        
        .checkout-title {
            margin-bottom: 1rem;
        }
        
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }
        
        @media (min-width: 768px) {
            .checkout-container {
                grid-template-columns: 1.5fr 1fr;
            }
        }
        
        .checkout-form {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            font-family: var(--font-main);
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(126, 87, 194, 0.1);
        }
        
        .form-input.error {
            border-color: #ff3e00;
            box-shadow: 0 0 0 3px rgba(255, 62, 0, 0.1);
        }
        
        .error-message {
            color: #ff3e00;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .checkout-summary {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            padding: 2rem;
            height: fit-content;
        }
        
        .checkout-summary-title {
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .checkout-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .checkout-summary-label {
            font-weight: 600;
        }
        
        .checkout-divider {
            height: 1px;
            background: rgba(0, 0, 0, 0.1);
            margin: 1rem 0;
        }
        
        .checkout-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.2rem;
            font-weight: 700;
            margin: 1rem 0;
        }
        
        .checkout-total-value {
            color: var(--primary);
        }
        
        .checkout-products {
            margin-top: 2rem;
        }
        
        .checkout-product {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .checkout-product:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .checkout-product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .checkout-product-info {
            flex: 1;
        }
        
        .checkout-product-title {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        
        .checkout-product-price {
            display: flex;
            justify-content: space-between;
            color: var(--primary);
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

    <!-- Checkout Header -->
    <section class="checkout-header">
        <div class="container">
            <h1 class="checkout-title">Checkout</h1>
            <p class="checkout-summary">Complete your purchase to download your templates</p>
        </div>
    </section>

    <!-- Checkout Content -->
    <section class="section">
        <div class="container">
            <?php if (!empty($errors['system'])): ?>
                <div class="error-message" style="text-align: center; margin-bottom: 2rem;">
                    <?php echo $errors['system']; ?>
                </div>
            <?php endif; ?>
            
            <div class="checkout-container">
                <!-- Checkout Form -->
                <div class="checkout-form">
                    <h2>Billing Details</h2>
                    <form action="<?php echo $store_root; ?>checkout.php" method="post">
                        <div class="form-group">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" id="name" name="name" class="form-input <?php echo isset($errors['name']) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="error-message"><?php echo $errors['name']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" class="form-input <?php echo isset($errors['email']) ? 'error' : ''; ?>" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="error-message"><?php echo $errors['email']; ?></div>
                            <?php endif; ?>
                            <p class="help-text" style="font-size: 0.9rem; margin-top: 0.5rem; opacity: 0.7;">We'll send your download links to this email address.</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment" class="form-label">Payment Method</label>
                            <div style="background: rgba(126, 87, 194, 0.05); padding: 1rem; border-radius: 8px;">
                                <p>Stripe payment integration will be implemented here.</p>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn" style="width: 100%;">Complete Purchase</button>
                    </form>
                </div>
                
                <!-- Order Summary -->
                <div class="checkout-summary">
                    <h2 class="checkout-summary-title">Order Summary</h2>
                    
                    <!-- Product List -->
                    <div class="checkout-products">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="checkout-product">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="checkout-product-img">
                                <div class="checkout-product-info">
                                    <h3 class="checkout-product-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <div class="checkout-product-price">
                                        <span><?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?></span>
                                        <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="checkout-divider"></div>
                    
                    <!-- Totals -->
                    <div class="checkout-summary-item">
                        <span class="checkout-summary-label">Subtotal:</span>
                        <span>$<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    
                    <div class="checkout-total">
                        <span>Total:</span>
                        <span class="checkout-total-value">$<?php echo number_format($cart_total, 2); ?></span>
                    </div>
                    
                    <div class="checkout-divider"></div>
                    
                    <p style="font-size: 0.9rem; opacity: 0.7;">By completing your purchase, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</p>
                </div>
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

    <!-- JavaScript (same as index.php plus form validation) -->
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
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            let valid = true;
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            
            if (name.value.trim() === '') {
                document.querySelector('.error-message[for="name"]').textContent = 'Name is required';
                name.classList.add('error');
                valid = false;
            }
            
            if (email.value.trim() === '') {
                document.querySelector('.error-message[for="email"]').textContent = 'Email is required';
                email.classList.add('error');
                valid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                document.querySelector('.error-message[for="email"]').textContent = 'Please enter a valid email address';
                email.classList.add('error');
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>