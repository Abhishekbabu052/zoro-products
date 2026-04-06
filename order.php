<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database configuration
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
$is_cart_order = isset($_GET['source']) && $_GET['source'] == 'cart';
$single_product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

$cart_items = [];
$total_price = 0;
$error = '';
$success = '';
$product = null;

if ($is_cart_order) {
    if (isset($_SESSION['cart_order_items']) && !empty($_SESSION['cart_order_items'])) {
        $cart_items = $_SESSION['cart_order_items'];
        $total_price = $_SESSION['cart_order_total'] ?? 0;
    } else {
        $error = "No items in cart to order!";
    }
} elseif ($single_product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $single_product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("Product not found");
    }

    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product['quantity'] <= 0) {
        die("This product is out of stock");
    }
    
    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quantity'])) {
        $quantity = intval($_POST['quantity']);
    }
    
    $cart_items[] = [
        'product_id' => $single_product_id,
        'product_name' => $product['product_name'],
        'price' => $product['price'],
        'quantity' => $quantity
    ];
    $total_price = $product['price'] * $quantity;
} else {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = trim($_POST['company_name']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $payment_method = $_POST['payment_method'];
    
    if (!$is_cart_order && isset($_POST['quantity'])) {
        $quantity = intval($_POST['quantity']);
        $cart_items[0]['quantity'] = $quantity;
        $total_price = $product['price'] * $quantity;
    }
    
    if (empty($company_name) || empty($address) || empty($email) || empty($phone)) {
        $error = "All fields are required!";
    } elseif (!$is_cart_order && ($quantity <= 0 || $quantity > $product['quantity'])) {
        $error = "Quantity must be between 1 and " . $product['quantity'] . "!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address!";
    } else {
        $all_success = true;
        foreach ($cart_items as $item) {
            $check_stmt = $conn->prepare("SELECT quantity FROM products WHERE product_id = ?");
            $check_stmt->bind_param("i", $item['product_id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $stock = $check_result->fetch_assoc()['quantity'];
                if ($item['quantity'] > $stock) {
                    $error = "Only " . $stock . " items available for " . $item['product_name'] . "!";
                    $all_success = false;
                    break;
                }
            } else {
                $error = "Product not found: " . $item['product_name'];
                $all_success = false;
                break;
            }
            $check_stmt->close();
        }
        
        if ($all_success) {
            foreach ($cart_items as $item) {
                $item_quantity = $item['quantity'];
                $item_price = $item['price'];
                $stmt = $conn->prepare("INSERT INTO orders (product_id, price, company_name, address, email, phone, quantity, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("idssssis", $item['product_id'], $item_price, $company_name, $address, $email, $phone, $item_quantity, $payment_method);
                
                if ($stmt->execute()) {
                    $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
                    $update_stmt->bind_param("ii", $item_quantity, $item['product_id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    if ($is_cart_order) {
                        $delete_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                        $delete_stmt->bind_param("ii", $user_id, $item['product_id']);
                        $delete_stmt->execute();
                        $delete_stmt->close();
                    }
                } else {
                    $all_success = false;
                    $error = "Failed to place order: " . $conn->error;
                    break;
                }
                $stmt->close();
            }
            if ($all_success) {
                $success = "Order placed successfully! Total Amount: ₹" . number_format($total_price, 2);
                if ($is_cart_order) {
                    unset($_SESSION['cart_order_items']);
                    unset($_SESSION['cart_order_total']);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", sans-serif; padding: 20px; background: #fff8e1; }
        .container { max-width: 650px; margin: auto; }
        .header { margin-bottom: 25px; padding-bottom: 15px; border-bottom: 3px solid #c8e6c9; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { color: #2e7d32; }
        .nav a { text-decoration: none; color: #388e3c; font-weight: 500; margin-left: 15px; }
        .order-summary, .order-form { background: #ffffff; padding: 22px; border-radius: 16px; margin-bottom: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .order-summary h3 { margin-bottom: 15px; color: #2e7d32; }
        .order-item { padding: 12px 0; border-bottom: 1px dashed #c8e6c9; }
        .order-item:last-child { border-bottom: none; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; color: #2e7d32; }
        input, select, textarea { width: 100%; padding: 10px; border-radius: 10px; border: 1px solid #c8e6c9; outline: none; font-size: 14px; }
        .quantity-group { display: flex; align-items: center; gap: 10px; }
        .quantity-input { width: 90px; text-align: center; }
        .quantity-btn { background: #e8f5e9; border: 1px solid #81c784; color: #2e7d32; border-radius: 8px; padding: 8px 14px; cursor: pointer; }
        .price-info { background: #e8f5e9; padding: 14px; border-radius: 12px; margin-top: 15px; font-size: 18px; text-align: center; color: #2e7d32; }
        .btn-primary { width: 100%; padding: 14px; border-radius: 12px; border: none; background: linear-gradient(135deg, #81c784, #2e7d32); color: white; cursor: pointer; font-size: 16px; font-weight: bold; }
        .message { padding: 14px; border-radius: 12px; margin-bottom: 18px; text-align: center; font-weight: 500; }
        .success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .error { background: #fdecea; color: #c62828; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo $is_cart_order ? 'Cart Order' : 'Place Order'; ?></h1>
            <div class="nav">
                <a href="<?php echo $is_cart_order ? 'cart.php' : 'index.php'; ?>"><i class="fas fa-arrow-left"></i> Back</a>
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="message success"><?php echo $success; ?></div>
            <div style="text-align:center; margin-top:20px;">
                <a href="index.php" class="btn-primary" style="text-decoration:none; display:inline-block; width:auto; padding:10px 30px;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="order-summary">
                <h3>Order Summary</h3>
                <?php foreach($cart_items as $item): ?>
                    <div class="order-item">
                        <p><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></p>
                        <p>Quantity: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?></p>
                        <p>Subtotal: ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                    </div>
                <?php endforeach; ?>
                <div class="price-info" id="priceDisplay">
                    <strong>Total Amount: ₹<?php echo number_format($total_price, 2); ?></strong>
                </div>
            </div>
            
            <form method="POST" class="order-form">
                <?php if (!$is_cart_order): ?>
                    <div class="form-group">
                        <label>Quantity *</label>
                        <div class="quantity-group">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="number" name="quantity" id="quantity" value="<?php echo $quantity; ?>" min="1" max="<?php echo $product['quantity']; ?>" required readonly>
                            <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
                            <span>Max: <?php echo $product['quantity']; ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="company_name">Company Name *</label>
                    <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($_SESSION['company_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Delivery Address *</label>
                    <textarea id="address" name="address" required></textarea>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Payment Method *</label>
                    <select name="payment_method" required>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                        <option value="Card Payment">Card Payment</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Place Order</button>
            </form>
        <?php endif; ?>
    </div>
    
    <script>
        <?php if (!$is_cart_order): ?>
        const pricePerUnit = <?php echo $product['price']; ?>;
        const maxQuantity = <?php echo $product['quantity']; ?>;
        const quantityInput = document.getElementById('quantity');
        const priceDisplay = document.getElementById('priceDisplay');
        
        function changeQuantity(change) {
            let newQuantity = parseInt(quantityInput.value) + change;
            if (newQuantity >= 1 && newQuantity <= maxQuantity) {
                quantityInput.value = newQuantity;
                const total = pricePerUnit * newQuantity;
                priceDisplay.innerHTML = `<strong>Total Amount: ₹${total.toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong>`;
            }
        }
        <?php endif; ?>
    </script>
</body>
</html><?php $conn->close(); ?>