<?php
// Database connection
$hostname = "sql100.infinityfree.com";
$username = "if0_41576406";
$password = "Irl4WePkLEH6jq";
$dbname = "if0_41576406_products";

$conn = new mysqli($hostname, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Handle delete order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_order'])) {
    $order_id = intval($_POST['order_id']);
    
    // First get the order details to update product quantity
    $stmt = $conn->prepare("SELECT product_id, quantity FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Restore product quantity
        $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE product_id = ?");
        $update_stmt->bind_param("ii", $order['quantity'], $order['product_id']);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Delete the order
        $delete_stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $delete_stmt->bind_param("i", $order_id);
        
        if ($delete_stmt->execute()) {
            $message = "Order deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Failed to delete order: " . $conn->error;
            $message_type = "error";
        }
        $delete_stmt->close();
    }
    $stmt->close();
}

// Get all orders with product details
$query = "SELECT o.*, p.product_name, p.brand, p.image_path 
          FROM orders o 
          LEFT JOIN products p ON o.product_id = p.product_id 
          ORDER BY o.order_datetime DESC";
          
$result = $conn->query($query);
$total_orders = $result->num_rows;

// Calculate total revenue
$total_revenue = 0;
if ($result->num_rows > 0) {
    $result->data_seek(0);
    while($order = $result->fetch_assoc()) {
        if (isset($order['price'])) {
            $total_revenue += $order['price'] * $order['quantity'];
        } else {
            $price_stmt = $conn->prepare("SELECT price FROM products WHERE product_id = ?");
            $price_stmt->bind_param("i", $order['product_id']);
            $price_stmt->execute();
            $price_result = $price_stmt->get_result();
            if ($price_result->num_rows > 0) {
                $product = $price_result->fetch_assoc();
                $total_revenue += $product['price'] * $order['quantity'];
            }
            $price_stmt->close();
        }
    }
    $result->data_seek(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders - Admin View</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", sans-serif; padding: 20px; background: #fff8e1; color: #2e7d32; }
        .container { max-width: 1200px; margin: auto; }
        .header { margin-bottom: 25px; padding-bottom: 15px; border-bottom: 3px solid #c8e6c9; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { color: #2e7d32; }
        .nav a { text-decoration: none; color: #2e7d32; font-weight: 500; margin-right: 15px; }
        .export-btn { background: #2e7d32; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 25px; }
        .stat-card { background: #ffffff; padding: 18px; border-radius: 14px; text-align: center; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .stat-number { font-size: 26px; font-weight: bold; color: #2e7d32; }
        .stat-label { font-size: 14px; color: #689f38; }
        .filters { display: flex; gap: 12px; margin-bottom: 25px; padding: 18px; background: #ffffff; border-radius: 14px; box-shadow: 0 6px 16px rgba(0,0,0,0.08); }
        .filters input, .filters select { padding: 10px; border-radius: 10px; border: 1px solid #c8e6c9; outline: none; }
        .orders-table { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #e8f5e9; padding: 14px; text-align: left; color: #2e7d32; }
        td { padding: 14px; border-bottom: 1px solid #e0e0e0; }
        tr:hover { background: #f1f8e9; }
        .product-image { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .status-badge { padding: 5px 10px; border-radius: 14px; font-size: 11px; font-weight: bold; background: #fff3cd; color: #856404; }
        .action-btn { padding: 7px 14px; border: none; border-radius: 8px; cursor: pointer; background: #ef5350; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>All Orders - Admin View</h1>
            <div class="nav">
                <a href="admin.php"><i class="fas fa-tasks"></i> Products</a>
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <button class="export-btn" onclick="exportToCSV()">Export CSV</button>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">₹<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <div class="filters">
            <input type="text" placeholder="Search orders..." id="searchInput" onkeyup="searchOrders()">
            <select id="paymentFilter" onchange="filterOrders()">
                <option value="">All Payments</option>
                <option value="Cash on Delivery">Cash on Delivery</option>
                <option value="Card Payment">Card Payment</option>
            </select>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = $result->fetch_assoc()): 
                            $order_total = ($order['price'] ?? 0) * $order['quantity'];
                        ?>
                            <tr class="order-row" data-customer="<?php echo strtolower($order['company_name']); ?>" data-product="<?php echo strtolower($order['product_name']); ?>" data-payment="<?php echo $order['payment_method']; ?>">
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo date('d M Y', strtotime($order['order_datetime'])); ?></td>
                                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['company_name']); ?></td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td>₹<?php echo number_format($order_total, 2); ?></td>
                                <td><span class="status-badge"><?php echo $order['payment_method']; ?></span></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Delete this order?')">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <button type="submit" name="delete_order" class="action-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <script>
        function searchOrders() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.order-row').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(search) ? '' : 'none';
            });
        }
        function filterOrders() {
            const filter = document.getElementById('paymentFilter').value;
            document.querySelectorAll('.order-row').forEach(row => {
                const payment = row.getAttribute('data-payment');
                row.style.display = (filter === '' || payment === filter) ? '' : 'none';
            });
        }
    </script>
</body>
</html><?php $conn->close(); ?>