<?php
// cart_functions.php

function addToCart($conn, $user_id, $product_id) {
    if (empty($user_id)) {
        return "login_required";
    }
    
    // Check if product exists and has stock
    $stmt = $conn->prepare("SELECT quantity, product_name, price FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        return "Product not found";
    }
    
    $product = $result->fetch_assoc();
    
    // Check available stock
    $check_cart = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $check_cart->bind_param("ii", $user_id, $product_id);
    $check_cart->execute();
    $cart_result = $check_cart->get_result();
    $cart_quantity = 0;
    
    if ($cart_result->num_rows > 0) {
        $cart_item = $cart_result->fetch_assoc();
        $cart_quantity = $cart_item['quantity'];
    }
    
    // Check if adding one more exceeds stock
    if (($cart_quantity + 1) > $product['quantity']) {
        return "Only " . ($product['quantity'] - $cart_quantity) . " items available in stock!";
    }
    
    // Add or update cart (add 1 item)
    if ($cart_quantity > 0) {
        // Update existing cart item
        $new_quantity = $cart_quantity + 1;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $new_quantity, $user_id, $product_id);
    } else {
        // Add new cart item with quantity 1
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $user_id, $product_id);
    }
    
    if ($stmt->execute()) {
        return "success";
    } else {
        return "Failed to add to cart";
    }
}

function getCartCount($conn, $user_id) {
    if (empty($user_id)) return 0;

    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}
?>