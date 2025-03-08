<?php
/**
 * PIXELBYTE Admin Store Orders Index
 * Lists all store orders with management options
 */

// Include the configuration and authentication files
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Require user to be logged in
require_login();

// Database connection
global $conn;

// Pagination settings
$orders_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $orders_per_page;

// Count total orders
$count_sql = "SELECT COUNT(*) as total FROM store_orders";
$count_result = $conn->query($count_sql);
$total_orders = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $orders_per_page);

// Get orders with pagination
$sql = "SELECT * FROM store_orders ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $orders_per_page);
$stmt->execute();
$orders_result = $stmt->get_result();

// Process status update if requested
if(isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $update_sql = "UPDATE store_orders SET payment_status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $new_status, $order_id);
    
    if($update_stmt->execute()) {
        $_SESSION['success_message'] = "Order status updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating order status: " . $conn->error;
    }
    
    redirect(get_admin_url('store/orders/index.php'));
    exit;
}

// Set page title
$page_title = "Store Orders";

// Include the header
include '../../includes/header.php';
?>

<div class="dashboard-header">
    <div>
        <h1 class="page-title">Store Orders</h1>
        <p class="page-subtitle">Manage your store orders</p>
    </div>
</div>

<!-- Orders Table -->
<div class="table-container">
    <div class="table-header">
        <h2 class="table-title">
            <i class="fas fa-shopping-bag"></i> All Orders
        </h2>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($orders_result && $orders_result->num_rows > 0): ?>
                <?php while($order = $orders_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><?php echo format_date($order['created_at']); ?></td>
                        <td>
                            <span class="status status-<?php echo strtolower($order['payment_status']); ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="<?php echo get_admin_url('store/orders/view.php?id=' . $order['id']); ?>" class="action-btn" data-tooltip="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="action-btn status-change-btn" data-order-id="<?php echo $order['id']; ?>" data-tooltip="Change Status">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No orders found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Pagination -->
    <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php if($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>" class="page-link">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="page-link <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>" class="page-link">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Status Change Modal -->
<div id="statusModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 400px;">
        <span class="close" style="float: right; cursor: pointer; font-size: 20px;">&times;</span>
        <h2>Update Order Status</h2>
        <form method="post" action="<?php echo get_admin_url('store/orders/index.php'); ?>">
            <input type="hidden" id="order_id_input" name="order_id" value="">
            <div class="form-group">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<?php
// Add custom JS for the status change modal
$inline_js = "
    // Status change modal functionality
    const statusModal = document.getElementById('statusModal');
    const statusChangeBtns = document.querySelectorAll('.status-change-btn');
    const orderIdInput = document.getElementById('order_id_input');
    const closeBtn = document.querySelector('.close');
    
    statusChangeBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const orderId = this.getAttribute('data-order-id');
            orderIdInput.value = orderId;
            statusModal.style.display = 'flex';
        });
    });
    
    closeBtn.addEventListener('click', function() {
        statusModal.style.display = 'none';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === statusModal) {
           statusModal.style.display = 'none';
        }
    });
";

// Include the footer
include '../../includes/footer.php';
?>