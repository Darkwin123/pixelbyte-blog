<?php
/**
 * PIXELBYTE Admin Store Order View
 * Display detailed information about a specific order
 */

// Include the configuration and authentication files
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Check if ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid order ID.";
    redirect(get_admin_url('store/orders/index.php'));
    exit;
}

$id = $_GET['id'];

// Get order data
$order_sql = "SELECT * FROM store_orders WHERE id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows == 0) {
    $_SESSION['error_message'] = "Order not found.";
    redirect(get_admin_url('store/orders/index.php'));
    exit;
}

$order = $order_result->fetch_assoc();

// Get order items
$items_sql = "SELECT i.*, p.title, p.slug, p.image_url FROM store_order_items i 
              LEFT JOIN store_products p ON i.product_id = p.id
              WHERE i.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Process status update if requested
if(isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    $update_sql = "UPDATE store_orders SET payment_status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $id);
    
    if($update_stmt->execute()) {
        $_SESSION['success_message'] = "Order status updated successfully.";
        
        // Refresh the order data
        $order_stmt->execute();
        $order_result = $order_stmt->get_result();
        $order = $order_result->fetch_assoc();
    } else {
        $error = "Error updating order status: " . $conn->error;
    }
}

// Set page title
$page_title = "Order Details";

// Include the header
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Order Details: <?php echo htmlspecialchars($order['order_number']); ?></h1>
        <p class="page-subtitle">View and manage order information</p>
    </div>
    
    <div>
        <a href="<?php echo get_admin_url('store/orders/index.php'); ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="stats-grid">
    <!-- Order Information -->
    <div class="stat-card">
        <div class="stat-card-title">
            <i class="fas fa-info-circle"></i> Order Information
        </div>
        <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
        <p><strong>Date:</strong> <?php echo format_date($order['created_at'], 'F j, Y, g:i a'); ?></p>
        <p>
            <strong>Status:</strong> 
            <span class="status status-<?php echo strtolower($order['payment_status']); ?>">
                <?php echo ucfirst($order['payment_status']); ?>
            </span>
        </p>
    </div>
    
    <!-- Customer Information -->
    <div class="stat-card">
        <div class="stat-card-title">
            <i class="fas fa-user"></i> Customer Information
        </div>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
    </div>
    
    <!-- Order Total -->
    <div class="stat-card">
        <div class="stat-card-title">
            <i class="fas fa-dollar-sign"></i> Order Total
        </div>
        <div class="stat-card-value">$<?php echo number_format($order['total_amount'], 2); ?></div>
    </div>
    
    <!-- Update Status -->
    <div class="stat-card">
        <div class="stat-card-title">
            <i class="fas fa-edit"></i> Update Status
        </div>
        <form method="post" class="needs-validation">
            <div class="form-group">
                <select name="status" class="form-control">
                    <option value="pending" <?php echo $order['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="completed" <?php echo $order['payment_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $order['payment_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update Status</button>
        </form>
    </div>
</div>

<!-- Order Items -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">
            <i class="fas fa-shopping-basket"></i> Order Items
        </h2>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal = 0;
            if($items_result && $items_result->num_rows > 0): 
                while($item = $items_result->fetch_assoc()): 
                    $item_total = $item['quantity'] * $item['price'];
                    $subtotal += $item_total;
            ?>
                <tr>
                    <td>
                        <?php if(!empty($item['title'])): ?>
                            <a href="<?php echo get_site_url('store/product.php?id=' . $item['product_id']); ?>" target="_blank">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                        <?php else: ?>
                            [Product No Longer Available]
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!empty($item['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <?php else: ?>
                            [No Image]
                        <?php endif; ?>
                    </td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item_total, 2); ?></td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="5">No items found for this order.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right;"><strong>Subtotal:</strong></td>
                <td>$<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php
// Include the footer
include '../../includes/footer.php';
?>