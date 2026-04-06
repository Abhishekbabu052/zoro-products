<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Poppins', sans-serif;
    background: #fff8e7;
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 220px;
    background: #ffffff;
    color: #2e7d32;
    padding-top: 2rem;
    position: fixed;
    height: 100%;
    border-right: 2px solid #c8e6c9;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 2rem;
    color: #2e7d32;
}

.sidebar a {
    color: #2e7d32;
    padding: 1rem 2rem;
    display: block;
    text-decoration: none;
    border-radius: 8px;
    margin: 6px 10px;
    transition: background 0.25s;
}

.sidebar a.active,
.sidebar a:hover {
    background: #c8e6c9;
}

/* Main */
.main {
    margin-left: 220px;
    padding: 2rem;
    width: 100%;
    background: #fff8e7;
}

/* Sections */
.section { display: none; animation: fadeIn 0.3s ease; }
.section.active { display: block; }

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

h2 { color: #2e7d32; margin-bottom: 1.5rem; }
h3 { color: #388e3c; margin-bottom: 1rem; }

/* Forms */
form {
    background: #ffffff;
    padding: 25px;
    border-radius: 14px;
    margin-bottom: 30px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

label {
    margin-bottom: 8px;
    display: block;
    font-weight: 600;
    color: #2e7d32;
}

input, textarea, select {
    width: 100%;
    padding: 12px;
    border: 1px solid #c8e6c9;
    border-radius: 8px;
    font-size: 14px;
}

input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: #2e7d32;
}

textarea { resize: vertical; height: 120px; }

/* Buttons */
.btn, input[type="submit"] {
    background: #6fbf73;
    color: #ffffff;
    border: none;
    padding: 12px 22px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.btn:hover,
input[type="submit"]:hover {
    background: #5aa864;
}

/* Cards */
.card,
.products-table-container,
.stat-card {
    background: #ffffff;
    padding: 22px;
    border-radius: 14px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

/* Messages */
.message {
    padding: 14px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 600;
    text-align: center;
}

.success {
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
}

.error {
    background: #fdecea;
    color: #c62828;
    border: 1px solid #f5c6cb;
}

/* Tables */
table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #c8e6c9;
    color: #2e7d32;
    padding: 14px;
}

td {
    padding: 14px;
    border-bottom: 1px solid #eee;
}

tr:hover { background: #f1f8e9; }

.product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #c8e6c9;
}

/* Action Buttons */
.btn-edit { background: #c8e6c9; color: #2e7d32; }
.btn-delete { background: #ef9a9a; color: #b71c1c; }
.btn-view { background: #bbdefb; color: #0d47a1; }

.btn-edit:hover { background: #b2dfdb; }
.btn-delete:hover { background: #e57373; }
.btn-view:hover { background: #90caf9; }

/* Stats */
.stat-number {
    font-size: 28px;
    font-weight: bold;
    color: #2e7d32;
}

.stat-label {
    font-size: 14px;
    color: #6b8e6b;
}

/* Badges */
.payment-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.payment-cod {
    background: #c8e6c9;
    color: #2e7d32;
}

.payment-card {
    background: #6fbf73;
    color: white;
}

.status-badge {
    background: #fff3e0;
    color: #ef6c00;
}
</style>



</head>

<body>

<?php
session_start();

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

// Define categories and brands
$categories = [
    'Rice Varieties', 'Rice Powder', 'Wheat Products', 'Pulses', 'Pickles',
    'Spices', 'Readymade Curries/Gravies', 'Condiments', 'Snacks and Confectionary',
    'Frozen Food', 'Kitchen Utensils', 'Haircare and Beauty Products'
];

$brands = [
    'Ahaa', 'Anns', 'Anna', 'Butterfly', 'Chakson', 'Chandrika', 'Daawat',
    'Double Horse', 'Eastern', 'Godrej', 'Green Valley', 'Haldiram', 'Hamam',
    'Heer', 'Instant Delight', 'Kozhikodens', 'Kurkure', 'Lays', 'Lifebuoy',
    'Liril', 'Maggi', 'Medimix', 'Mother\'s Choice', 'Natco', 'Neptune',
    'Nilamels', 'Nirapara', 'Parachute', 'Parle', 'Pavizham', 'Preethi',
    'Prestige', 'Rexona', 'Sanam', 'Saras', 'SmartShop', 'StyleHub', 'Sujata',
    'Tasty Nibbles', 'Veetee', 'Village Farmer'
];

$message = '';
$error = '';
$editing_product = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add/Update Product
    if (isset($_POST['add_product']) || isset($_POST['update_product'])) {
        $product_name = trim($_POST['product_name']);
        $product_category = $_POST['product_category'];
        $brand = $_POST['brand'];
        $price = trim($_POST['price']);
        $quantity = trim($_POST['quantity']);
        $description = trim($_POST['description']);
        $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;
        
        // Validation
        if (empty($product_name) || empty($price) || empty($quantity)) {
            $error = "Product name, price and quantity are required!";
        } elseif (!is_numeric($price) || $price <= 0) {
            $error = "Price must be a positive number!";
        } elseif (!is_numeric($quantity) || $quantity < 0) {
            $error = "Quantity must be a non-negative number!";
        } else {
            // Handle image upload
            $image_path = '';
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = 'product_images/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_type = $_FILES['product_image']['type'];
                
                if (in_array($file_type, $allowed_types)) {
                    $file_ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $product_name) . '.' . $file_ext;
                    $target_file = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                        $image_path = $target_file;
                    } else {
                        $error = "Failed to upload image.";
                    }
                } else {
                    $error = "Only JPG, PNG, GIF, and WebP images are allowed.";
                }
            }
            
            if (empty($error)) {
                if (isset($_POST['add_product'])) {
                    // Add new product
                    $stmt = $conn->prepare("INSERT INTO products (product_name, product_category, brand, price, quantity, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssdiss", $product_name, $product_category, $brand, $price, $quantity, $description, $image_path);
                    
                    if ($stmt->execute()) {
                        $message = "Product added successfully!";
                    } else {
                        $error = "Failed to add product: " . $stmt->error;
                    }
                } elseif (isset($_POST['update_product'])) {
                    // Update existing product
                    if (!empty($image_path)) {
                        // Remove old image if exists
                        $stmt = $conn->prepare("SELECT image_path FROM products WHERE product_id = ?");
                        $stmt->bind_param("i", $product_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            if (!empty($row['image_path']) && file_exists($row['image_path'])) {
                                unlink($row['image_path']);
                            }
                        }
                        $stmt->close();
                        
                        $stmt = $conn->prepare("UPDATE products SET product_name = ?, product_category = ?, brand = ?, price = ?, quantity = ?, description = ?, image_path = ? WHERE product_id = ?");
                        $stmt->bind_param("sssdissi", $product_name, $product_category, $brand, $price, $quantity, $description, $image_path, $product_id);
                    } else {
                        $stmt = $conn->prepare("UPDATE products SET product_name = ?, product_category = ?, brand = ?, price = ?, quantity = ?, description = ? WHERE product_id = ?");
                        $stmt->bind_param("sssdisi", $product_name, $product_category, $brand, $price, $quantity, $description, $product_id);
                    }
                    
                    if ($stmt->execute()) {
                        $message = "Product updated successfully!";
                    } else {
                        $error = "Failed to update product: " . $stmt->error;
                    }
                }
                $stmt->close();
            }
        }
    }
    
    // Delete Product
    if (isset($_POST['delete_product'])) {
        $product_id = $_POST['product_id'];
        
        // Delete image file if exists
        $stmt = $conn->prepare("SELECT image_path FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (!empty($row['image_path']) && file_exists($row['image_path'])) {
                unlink($row['image_path']);
            }
        }
        $stmt->close();
        
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $message = "Product deleted successfully!";
        } else {
            $error = "Failed to delete product: " . $stmt->error;
        }
        $stmt->close();
    }
    
    // Edit Product
    if (isset($_POST['edit_product'])) {
        $product_id = $_POST['product_id'];
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $editing_product = $result->fetch_assoc();
        $stmt->close();
    }
    
    // Handle delete order
    if (isset($_POST['delete_order'])) {
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
            } else {
                $error = "Failed to delete order: " . $conn->error;
            }
            $delete_stmt->close();
        }
        $stmt->close();
    }
}

// Get search parameter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch all products with optional search
if (!empty($search)) {
    $search_term = "%$search%";
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_name LIKE ? OR product_category LIKE ? OR brand LIKE ? OR description LIKE ? ORDER BY product_id DESC");
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
} else {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id DESC");
}
$stmt->execute();
$products_result = $stmt->get_result();
$stmt->close();
?>

  <div class="sidebar">
    <h2>ADMIN</h2>
    <a href="#" class="active" onclick="showSection('products')">Products</a>
    <a href="#" onclick="showSection('orders')">Orders</a>
    <a href="#" onclick="showSection('profile')">Profile</a>
  </div>

  <div class="main">

    <!-- Products Section -->
    <div id="products" class="section active">
      <h2><?php echo $editing_product ? 'Edit Product' : 'Add New Product'; ?></h2>
      
      <?php if ($message): ?>
        <div class="message success"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>
      
      <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      
      <form method="POST" action="" enctype="multipart/form-data">
        <?php if ($editing_product): ?>
          <input type="hidden" name="product_id" value="<?php echo $editing_product['product_id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
          <label for="product_name">Product Name *</label>
          <input type="text" id="product_name" name="product_name" 
                 value="<?php echo $editing_product ? htmlspecialchars($editing_product['product_name']) : ''; ?>" 
                 required>
        </div>
        
        <div class="form-group">
          <label for="product_category">Category *</label>
          <select id="product_category" name="product_category" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo htmlspecialchars($category); ?>" 
                <?php echo ($editing_product && $editing_product['product_category'] == $category) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($category); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group">
          <label for="brand">Brand *</label>
          <select id="brand" name="brand" required>
            <option value="">Select Brand</option>
            <?php foreach ($brands as $brand_item): ?>
              <option value="<?php echo htmlspecialchars($brand_item); ?>" 
                <?php echo ($editing_product && $editing_product['brand'] == $brand_item) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($brand_item); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="form-group">
          <label for="price">Price (₹) *</label>
          <input type="number" id="price" name="price" step="0.01" min="0"
                 value="<?php echo $editing_product ? htmlspecialchars($editing_product['price']) : ''; ?>" 
                 required>
        </div>
        
        <div class="form-group">
          <label for="quantity">Quantity *</label>
          <input type="number" id="quantity" name="quantity" min="0"
                 value="<?php echo $editing_product ? htmlspecialchars($editing_product['quantity']) : ''; ?>" 
                 required>
        </div>
        
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description"><?php echo $editing_product ? htmlspecialchars($editing_product['description']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
          <label for="product_image">Product Image</label>
          <input type="file" id="product_image" name="product_image" accept="image/*">
          <?php if ($editing_product && !empty($editing_product['image_path'])): ?>
            <p style="margin-top: 5px; color: #666; font-size: 14px;">
              Current image: <?php echo htmlspecialchars(basename($editing_product['image_path'])); ?>
            </p>
          <?php endif; ?>
        </div>
        
        <div class="form-actions">
          <?php if ($editing_product): ?>
            <button type="submit" name="update_product" class="btn btn-update">Update Product</button>
            <button type="button" onclick="cancelEdit()" class="btn btn-cancel">Cancel</button>
          <?php else: ?>
            <button type="submit" name="add_product" class="btn">Add Product</button>
          <?php endif; ?>
        </div>
      </form>

      <div class="products-table-container">
        <div class="search-box">
          <input type="text" id="searchInput" placeholder="Search products..." 
                 value="<?php echo htmlspecialchars($search); ?>" onkeyup="filterProducts()">
          <button type="button" onclick="clearSearch()">Clear</button>
        </div>
        
        <h3 id="productCount">Product List (<?php echo $products_result->num_rows; ?>)</h3>
        
        <?php if ($products_result->num_rows > 0): ?>
          <div style="overflow-x: auto;">
            <table>
              <thead>
                <tr>
                  <th>Image</th>
                  <th>Name</th>
                  <th>Category</th>
                  <th>Brand</th>
                  <th>Price</th>
                  <th>Qty</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                  <tr data-name="<?php echo htmlspecialchars(strtolower($product['product_name'])); ?>" 
                      data-category="<?php echo htmlspecialchars(strtolower($product['product_category'])); ?>" 
                      data-brand="<?php echo htmlspecialchars(strtolower($product['brand'])); ?>" 
                      data-description="<?php echo htmlspecialchars(strtolower($product['description'])); ?>">
                    <td>
                      <?php if (!empty($product['image_path']) && file_exists($product['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             class="product-image">
                      <?php else: ?>
                        <span class="no-image">No image</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($product['product_category']); ?></td>
                    <td><?php echo htmlspecialchars($product['brand']); ?></td>
                    <td>₹<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                    <td>
                      <div class="action-buttons">
                        <form method="POST" action="" style="display: inline;">
                          <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                          <button type="submit" name="edit_product" class="btn-edit">Edit</button>
                        </form>
                        <form method="POST" action="" style="display: inline;" 
                              onsubmit="return confirm('Are you sure you want to delete this product?');">
                          <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                          <button type="submit" name="delete_product" class="btn-delete">Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p style="text-align: center; color: #666; padding: 20px;">No products found.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Orders Section -->
    <div id="orders" class="section">
      <h2>All Orders</h2>
      
      <?php
      // Get all orders from database
      $orders_query = "SELECT o.*, p.product_name, p.brand, p.price as unit_price 
                       FROM orders o 
                       LEFT JOIN products p ON o.product_id = p.product_id 
                       ORDER BY o.order_datetime DESC";
      
      $orders_result = $conn->query($orders_query);
      $total_orders = $orders_result->num_rows;
      
      // Calculate total revenue
      $total_revenue = 0;
      if ($orders_result->num_rows > 0) {
          $orders_result->data_seek(0);
          while($order = $orders_result->fetch_assoc()) {
              if (isset($order['price'])) {
                  $total_revenue += $order['price'] * $order['quantity'];
              } else {
                  $total_revenue += $order['unit_price'] * $order['quantity'];
              }
          }
          $orders_result->data_seek(0);
      }
      ?>
      
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number"><?php echo $total_orders; ?></div>
          <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">₹<?php echo number_format($total_revenue, 2); ?></div>
          <div class="stat-label">Total Revenue</div>
        </div>
        <div class="stat-card">
          <div class="stat-number"><?php echo date('M Y'); ?></div>
          <div class="stat-label">Current Month</div>
        </div>
      </div>

      <div class="quick-actions" style="margin-bottom: 20px;">
        <button onclick="exportOrders()" class="btn" style="background: #27ae60;">
          <i class="fas fa-download"></i> Export CSV
        </button>
        <a href="view_orders.php" target="_blank" class="btn" style="background: #3498db; text-decoration: none;">
          <i class="fas fa-external-link-alt"></i> Full View
        </a>
      </div>

      <div class="products-table-container">
        <div class="search-box">
          <input type="text" id="orderSearch" placeholder="Search orders by customer or product..." onkeyup="filterOrders()">
          <select onchange="filterOrderStatus()" id="orderStatusFilter">
            <option value="">All Payment Methods</option>
            <option value="Cash on Delivery">Cash on Delivery</option>
            <option value="Card Payment">Card Payment</option>
          </select>
          <button type="button" onclick="clearOrderSearch()">Clear</button>
        </div>
        
        <h3>Recent Orders (<?php echo $total_orders; ?>)</h3>
        
        <?php if ($orders_result->num_rows > 0): ?>
          <div style="overflow-x: auto;">
            <table>
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Date</th>
                  <th>Product</th>
                  <th>Customer</th>
                  <th>Qty</th>
                  <th>Total</th>
                  <th>Payment</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php while($order = $orders_result->fetch_assoc()): 
                  $order_date = date('d M Y, h:i A', strtotime($order['order_datetime']));
                  
                  // Calculate order total
                  if (isset($order['price'])) {
                      $order_total = $order['price'] * $order['quantity'];
                  } else {
                      $order_total = $order['unit_price'] * $order['quantity'];
                  }
                ?>
                  <tr class="order-row" 
                      data-payment="<?php echo $order['payment_method']; ?>"
                      data-date="<?php echo $order['order_datetime']; ?>"
                      data-customer="<?php echo strtolower($order['company_name'] . ' ' . $order['email']); ?>"
                      data-product="<?php echo strtolower($order['product_name']); ?>">
                    <td><strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                    <td><?php echo $order_date; ?></td>
                    <td>
                      <div style="font-weight: 600;"><?php echo htmlspecialchars($order['product_name']); ?></div>
                      <div style="font-size: 12px; color: #7f8c8d;"><?php echo htmlspecialchars($order['brand']); ?></div>
                    </td>
                    <td>
                      <div style="font-weight: 600;"><?php echo htmlspecialchars($order['company_name']); ?></div>
                      <div style="font-size: 12px; color: #7f8c8d;"><?php echo htmlspecialchars($order['email']); ?></div>
                    </td>
                    <td><?php echo $order['quantity']; ?></td>
                    <td><strong>₹<?php echo number_format($order_total, 2); ?></strong></td>
                    <td>
                      <span class="payment-badge <?php echo $order['payment_method'] == 'Cash on Delivery' ? 'payment-cod' : 'payment-card'; ?>">
                        <?php echo $order['payment_method']; ?>
                      </span>
                    </td>
                    <td>
                      <span class="status-badge">Pending</span>
                    </td>
                    <td>
                      <div class="action-buttons">
                        <button onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)" class="btn-view">
                          View
                        </button>
                        <form method="POST" action="" style="display: inline;" 
                              onsubmit="return confirm('Delete this order? This will restore product quantity.');">
                          <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                          <button type="submit" name="delete_order" class="btn-delete">
                            Delete
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-shopping-cart" style="font-size: 48px; color: #ddd; margin-bottom: 20px;"></i>
            <h4>No Orders Yet</h4>
            <p>No orders have been placed yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Profile Section -->
    <div id="profile" class="section">
      <h2>Admin Profile</h2>
      <div class="card">
        <h3>Welcome Admin</h3>
        <p><strong>System Status:</strong> <span style="color: #27ae60;">All systems operational</span></p>
        <p><strong>Total Products:</strong> <?php echo $products_result->num_rows; ?></p>
        <p><strong>Total Orders:</strong> <?php echo $total_orders; ?></p>
        <p><strong>Total Revenue:</strong> ₹<?php echo number_format($total_revenue, 2); ?></p>
        <p><strong>Last Login:</strong> <?php echo date('d M Y, h:i A'); ?></p>
        <br>
        <a href="logout.php" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>

  </div>

  <script>
    function showSection(id) {
      // Hide all sections
      document.querySelectorAll('.section').forEach(s => {
        s.classList.remove('active');
      });
      
      // Remove active class from all sidebar links
      document.querySelectorAll('.sidebar a').forEach(a => {
        a.classList.remove('active');
      });
      
      // Show the selected section
      document.getElementById(id).classList.add('active');
      
      // Add active class to clicked sidebar link
      event.target.classList.add('active');
      
      // If switching to orders section, refresh orders
      if (id === 'orders') {
        // You could add AJAX refresh here if needed
      }
    }
    
    function cancelEdit() {
      window.location.href = window.location.pathname;
    }
    
    function filterProducts() {
      const searchTerm = document.getElementById('searchInput').value.toLowerCase();
      const rows = document.querySelectorAll('#products table tbody tr');
      let visibleCount = 0;
      
      rows.forEach(row => {
        const name = row.dataset.name || '';
        const category = row.dataset.category || '';
        const brand = row.dataset.brand || '';
        const description = row.dataset.description || '';
        
        const matchesSearch = name.includes(searchTerm) || 
                            category.includes(searchTerm) || 
                            brand.includes(searchTerm) || 
                            description.includes(searchTerm);
        
        if (matchesSearch) {
          row.style.display = '';
          visibleCount++;
        } else {
          row.style.display = 'none';
        }
      });
      
      // Update the product count
      document.getElementById('productCount').textContent = `Product List (${visibleCount})`;
    }
    
    function clearSearch() {
      document.getElementById('searchInput').value = '';
      filterProducts();
    }
    
    function filterOrders() {
      const search = document.getElementById('orderSearch').value.toLowerCase();
      const rows = document.querySelectorAll('#orders .order-row');
      
      rows.forEach(row => {
        const customer = row.getAttribute('data-customer');
        const product = row.getAttribute('data-product');
        
        if (customer.includes(search) || product.includes(search)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }
    
    function filterOrderStatus() {
      const filter = document.getElementById('orderStatusFilter').value;
      const rows = document.querySelectorAll('#orders .order-row');
      
      rows.forEach(row => {
        const paymentMethod = row.getAttribute('data-payment');
        if (filter === '' || paymentMethod === filter) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }
    
    function clearOrderSearch() {
      document.getElementById('orderSearch').value = '';
      document.getElementById('orderStatusFilter').value = '';
      filterOrders();
    }
    
    function viewOrderDetails(orderId) {
      alert('Order Details for Order #' + orderId + '\n\nThis would show more details in a real implementation.\n\nYou can implement a modal or separate page to show:\n- Full customer details\n- Shipping address\n- Order timeline\n- Payment status');
    }
    
    function exportOrders() {
      // Create CSV data
      let csv = 'Order ID,Date,Product,Customer,Quantity,Total,Payment Method,Status\n';
      
      document.querySelectorAll('#orders .order-row').forEach(row => {
        if (row.style.display !== 'none') {
          const cells = row.querySelectorAll('td');
          const orderId = cells[0].textContent.trim();
          const date = cells[1].textContent.trim();
          const product = cells[2].querySelector('div:first-child').textContent.trim();
          const customer = cells[3].querySelector('div:first-child').textContent.trim();
          const quantity = cells[4].textContent.trim();
          const total = cells[5].textContent.trim();
          const payment = cells[6].textContent.trim();
          const status = cells[7].textContent.trim();
          
          csv += `"${orderId}","${date}","${product}","${customer}","${quantity}","${total}","${payment}","${status}"\n`;
        }
      });
      
      // Create download link
      const blob = new Blob([csv], { type: 'text/csv' });
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `orders_${new Date().toISOString().slice(0,10)}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);
    }
    
    // Clear messages after 5 seconds
    setTimeout(() => {
      const messages = document.querySelectorAll('.message');
      messages.forEach(msg => {
        msg.style.display = 'none';
      });
    }, 5000);
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      filterProducts();
    });
  </script>
</body>
</html>

<?php 
$conn->close();
?>