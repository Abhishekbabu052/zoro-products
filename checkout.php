<?php
session_start();
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

$user_id = $_SESSION['user_id'];

// Get cart items
$query = "SELECT c.quantity as cart_quantity, p.* 
          FROM cart c 
          JOIN products p ON c.product_id = p.product_id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total
$total = 0;
$cart_items = [];
while($item = $result->fetch_assoc()) {
    $item_total = $item['price'] * $item['cart_quantity'];
    $total += $item_total;
    $cart_items[] = $item;
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($cart_items)) {
    // Process order for each item
    foreach($cart_items as $item) {
        $stmt = $conn->prepare("INSERT INTO orders (product_id, price, company_name, address, email, phone, quantity, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $payment_method = $_POST['payment_method'] == 'cod' ? 'Cash on Delivery' : 'Card Payment';
        $stmt->bind_param("idssssis", $item['product_id'], $item['price'], $_SESSION['company_name'], $_POST['address'], $_SESSION['email'], $_POST['phone'], $item['cart_quantity'], $payment_method);
        $stmt->execute();
        
        // Update stock
        $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
        $update_stmt->bind_param("ii", $item['cart_quantity'], $item['product_id']);
        $update_stmt->execute();
    }
    
    // Clear cart after successful order
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $_SESSION['order_message'] = "Order placed successfully!";
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", sans-serif; padding: 20px; background: #fff8e7; color: #333; }
        .container { max-width: 600px; margin: 0 auto; }
        .order-summary, .checkout-form { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #2e7d32; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #c8e6c9; border-radius: 4px; outline: none; }
        .submit-btn { background: #2e7d32; color: white; border: none; padding: 12px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
        .cart-item { padding: 10px 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color:#2e7d32; margin-bottom:20px;"><i class="fas fa-shopping-bag"></i> Checkout</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="order-summary" style="text-align:center;">
                <p>Your cart is empty!</p>
                <a href="index.php" style="display:inline-block; margin-top:15px; padding:10px 20px; background:#2e7d32; color:white; text-decoration:none; border-radius:4px;">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="order-summary">
                <h2 style="color:#2e7d32; margin-bottom:15px;">Order Summary</h2>
                <?php foreach($cart_items as $item): ?>
                    <div class="cart-item">
                        <p><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></p>
                        <p>Quantity: <?php echo $item['cart_quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?></p>
                        <p>Subtotal: ₹<?php echo number_format($item['price'] * $item['cart_quantity'], 2); ?></p>
                    </div>
                <?php endforeach; ?>
                <p style="margin-top: 15px; font-size: 18px; color:#2e7d32;"><strong>Total: ₹<?php echo number_format($total, 2); ?></strong></p>
            </div>
            
            <div class="checkout-form">
                <form method="POST">
                    <div class="form-group">
                        <label>Shipping Address</label>
                        <textarea name="address" required rows="3" placeholder="Enter your full shipping address..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" required placeholder="Enter contact number...">
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select name="payment_method" required>
                            <option value="cod">Cash on Delivery</option>
                            <option value="card">Card Payment</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Place Order</button>
                    <a href="cart.php" style="display:block; text-align:center; margin-top:15px; color:#666; text-decoration:none;"><i class="fas fa-arrow-left"></i> Back to Cart</a>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html><?php $conn->close(); ?>