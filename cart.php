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

// Handle remove from cart
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['remove_item'])) {
        $cart_id = intval($_POST['cart_id']);
        $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        $_SESSION['cart_message'] = "Item removed from cart!";
        header("Location: cart.php");
        exit();
    }
    // Handle clear cart
    if (isset($_POST['clear_cart'])) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $_SESSION['cart_message'] = "Cart cleared successfully!";
        header("Location: cart.php");
        exit();
    }

    // Handle order all items
    if (isset($_POST['order_all'])) {
        // Get all cart items with product details
        $query = "SELECT c.cart_id, c.product_id, c.quantity as cart_quantity, 
                         p.product_name, p.price, p.quantity as stock
                  FROM cart c 
                  JOIN products p ON c.product_id = p.product_id 
                  WHERE c.user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $cart_items = [];
            $total_amount = 0;
            while($item = $result->fetch_assoc()) {
                $cart_items[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['cart_quantity'],
                    'stock' => $item['stock']
                ];
                $total_amount += $item['price'] * $item['cart_quantity'];
            }
            $_SESSION['cart_order_items'] = $cart_items;
            $_SESSION['cart_order_total'] = $total_amount;
            header("Location: order.php?source=cart");
            exit();
        } else {
            $_SESSION['cart_error'] = "Your cart is empty!";
            header("Location: cart.php");
            exit();
        }
    }
}

// Get cart items with product details for display
$query = "SELECT c.cart_id, c.quantity as cart_quantity, p.* 
          FROM cart c 
          JOIN products p ON c.product_id = p.product_id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Segoe UI", sans-serif; padding: 20px; background: #fff8e7; }
        .container { max-width: 900px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 3px solid #c8e6c9; }
        .header h1 { color: #2e7d32; }
        .cart-item { background: #ffffff; padding: 18px; margin-bottom: 15px; border-radius: 12px; display: flex; gap: 15px; align-items: center; box-shadow: 0 6px 15px rgba(0,0,0,0.08); }
        .cart-image { width: 110px; height: 110px; object-fit: cover; border-radius: 10px; border: 2px solid #c8e6c9; }
        .item-details h3 { color: #2e7d32; margin-bottom: 6px; }
        .item-row { display: flex; justify-content: space-between; margin-top: 5px; color: #555; }
        .actions { display: flex; flex-direction: column; gap: 8px; }
        .single-order-btn { background: #6fbf73; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; }
        .single-order-btn:hover { background: #5aa864; }
        .remove-btn { background: #e57373; color: white; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; }
        .cart-summary { background: #ffffff; padding: 25px; border-radius: 14px; margin-top: 25px; box-shadow: 0 6px 15px rgba(0,0,0,0.08); }
        .total-row { font-size: 20px; font-weight: bold; margin-top: 15px; padding-top: 15px; border-top: 2px dashed #c8e6c9; display: flex; justify-content: space-between; }
        .order-btn { background: #2e7d32; color: white; border: none; padding: 14px; border-radius: 8px; cursor: pointer; font-size: 16px; width: 100%; margin-top: 15px; }
        .order-btn:hover { background: #256428; }
        .clear-cart-btn { background: #fbc02d; color: #333; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-size: 15px; width: 100%; margin-top: 10px; }
        .notification { background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #c8e6c9; }
        .error-notification { background: #fdecea; color: #c62828; }
        .empty-cart { text-align: center; padding: 40px; background: #ffffff; border-radius: 15px; box-shadow: 0 6px 15px rgba(0,0,0,0.08); }
        .continue-btn { background: #6fbf73; color: white; border: none; padding: 10px 22px; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Shopping Cart</h1>
            <div>
                <a href="index.php" style="margin-left: 15px; text-decoration: none; color: #333;"><i class="fas fa-home"></i> Home</a>
            </div>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <form method="POST" onsubmit="return confirm('Are you sure you want to clear the entire cart?');">
                <button type="submit" name="clear_cart" class="clear-cart-btn">Clear Cart</button>
            </form>
        <?php endif; ?>

        <?php if (isset($_SESSION['cart_message'])): ?>
            <div class="notification">
                <?php echo $_SESSION['cart_message']; ?>
                <button onclick="this.parentElement.remove()" style="background:none; border:none; color:#155724; margin-left:10px; cursor:pointer;">×</button>
            </div>
            <?php unset($_SESSION['cart_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['cart_error'])): ?>
            <div class="notification error-notification">
                <?php echo $_SESSION['cart_error']; ?>
                <button onclick="this.parentElement.remove()" style="background:none; border:none; color:#721c24; margin-left:10px; cursor:pointer;">×</button>
            </div>
            <?php unset($_SESSION['cart_error']); ?>
        <?php endif; ?>
        
        <?php if ($result->num_rows > 0): ?>
            <?php 
            $subtotal = 0;
            $item_count = 0;
            while($item = $result->fetch_assoc()): 
                $item_total = $item['price'] * $item['cart_quantity'];
                $subtotal += $item_total;
                $item_count++;
            ?>
                <div class="cart-item">
                    <?php if (!empty($item['image_path']) && file_exists($item['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                             class="cart-image">
                    <?php else: ?>
                        <div style="width:110px; height:110px; background:#eee; display:flex; align-items:center; justify-content:center; border-radius:4px;">
                            No Image
                        </div>
                    <?php endif; ?>
                    
                    <div class="item-details" style="flex:1;">
                        <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                        <div class="item-row">
                            <span>Brand: <?php echo htmlspecialchars($item['brand']); ?></span>
                            <span>Price: ₹<?php echo number_format($item['price'], 2); ?></span>
                        </div>
                        <div class="item-row">
                            <span>Quantity: <?php echo $item['cart_quantity']; ?></span>
                            <span><strong>Subtotal: ₹<?php echo number_format($item_total, 2); ?></strong></span>
                        </div>
                    </div>
                    
                    <div class="actions">
                        <button class="single-order-btn" onclick="window.location.href='order.php?product_id=<?php echo $item['product_id']; ?>&quantity=<?php echo $item['cart_quantity']; ?>'">
                            Order This
                        </button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <button type="submit" name="remove_item" class="remove-btn" 
                                    onclick="return confirm('Remove this item from cart?')">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <div class="cart-summary">
                <h2>Order Summary</h2>
                <div class="item-row">
                    <span>Items: <?php echo $item_count; ?></span>
                    <span>₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Total Amount:</span>
                    <span>₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                
                <form method="POST">
                    <button type="submit" name="order_all" class="order-btn">
                        Order All Items (<?php echo $item_count; ?> items)
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <h3>Your cart is empty</h3>
                <p>Browse our products and add them to your cart!</p>
                <a href="index.php" class="continue-btn" style="margin-top:20px;">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>