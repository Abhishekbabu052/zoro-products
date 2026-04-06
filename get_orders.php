<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

$user_email = $_SESSION['email'];

// Get orders for the logged-in user (by email)
$query = "SELECT o.*, p.product_name, p.brand, p.image_path 
          FROM orders o 
          LEFT JOIN products p ON o.product_id = p.product_id 
          WHERE o.email = ? 
          ORDER BY o.order_datetime DESC";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();

$total_orders = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", sans-serif; padding: 20px; background: #f5f5f5; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #ddd; }
        .nav a { margin-left: 15px; text-decoration: none; color: #2e7d32; font-weight: 500; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: white; padding: 15px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-number { font-size: 24px; font-weight: bold; color: #2e7d32; }
        .order-card { background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .order-details { display: flex; gap: 15px; }
        .order-image { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .order-info { flex: 1; }
        .product-name { font-weight: bold; }
        .payment-badge { padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; background: #e8f5e9; color: #2e7d32; }
        .empty-orders { text-align: center; padding: 40px; background: white; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>My Orders</h1>
            <div class="nav">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div id="ordersList">
                <?php while($order = $result->fetch_assoc()): 
                    $order_total = ($order['price'] ?? 0) * $order['quantity'];
                ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <span style="font-weight:bold;">Order #<?php echo $order['order_id']; ?></span>
                                <span class="payment-badge"><?php echo $order['payment_method']; ?></span>
                            </div>
                            <span style="font-size:12px; color:#666;"><?php echo date('d M Y, h:i A', strtotime($order['order_datetime'])); ?></span>
                        </div>
                        <div class="order-details">
                            <?php if (!empty($order['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($order['image_path']); ?>" class="order-image">
                            <?php endif; ?>
                            <div class="order-info">
                                <div class="product-name"><?php echo htmlspecialchars($order['product_name']); ?></div>
                                <div style="font-size:12px; color:#666;"><?php echo htmlspecialchars($order['brand']); ?></div>
                                <div style="margin-top:10px; font-weight:bold; color:#2e7d32;">₹<?php echo number_format($order_total, 2); ?></div>
                                <div style="font-size:12px; margin-top:5px;">Qty: <?php echo $order['quantity']; ?></div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-orders">
                <h3>No Orders Yet</h3>
                <p>Start shopping to see your orders here!</p>
                <a href="index.php" style="display:inline-block; margin-top:15px; padding:10px 20px; background:#2e7d32; color:white; text-decoration:none; border-radius:4px;">Shop Now</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html><?php $conn->close(); ?>