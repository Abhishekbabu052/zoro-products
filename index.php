<?php
session_start();

// Database configuration
$hostname = "sql100.infinityfree.com";
$username = "if0_41576406";
$password = "Irl4WePkLEH6jq";
$dbname = "if0_41576406_products";

// Create connection
$conn = new mysqli($hostname, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Get cart count if logged in
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $cart_count = $row['total'] ?? 0;
    $stmt->close();
    
    $username = $_SESSION['contact_person_name'] ?? 'User';
    $company = $_SESSION['company_name'] ?? 'Store';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GECO International - Home</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:'Segoe UI',sans-serif;
    background:#fff8e7;
    color:#333;
}
.container{
    max-width:1200px;
    margin:auto;
    padding:0 16px;
}
/* Top Bar */
.top-header{
    background:#2e7d32;
    color:#fff;
    font-size:14px;
}
.top-header .container{
    padding:10px 16px;
}
/* Header */
.main-header{
    background:#ffffff;
    border-bottom:2px solid #c8e6c9;
}
.header-content{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:16px 0;
    gap:16px;
}
.logo{
    font-size:22px;
    font-weight:700;
    color:#2e7d32;
    display:flex;
    gap:8px;
    align-items:center;
}
.header-actions{
    display:flex;
    gap:20px;
    align-items:center;
}
.cart-icon{
    position:relative;
    color:#2e7d32;
    cursor:pointer;
}
.cart-count{
    position:absolute;
    top:-6px;
    right:-10px;
    background:#6fbf73;
    color:#fff;
    font-size:12px;
    width:18px;
    height:18px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
}
.user-menu{
    display:flex;
    gap:10px;
    align-items:center;
    cursor:pointer;
    text-decoration: none;
    color: inherit;
}
.user-avatar{
    width:36px;
    height:36px;
    border-radius:50%;
    background:#c8e6c9;
    color:#2e7d32;
    font-weight:700;
    display:flex;
    align-items:center;
    justify-content:center;
}
.user-info small{
    color:#666;
}
.login-btn {
    padding: 8px 16px;
    background: #6fbf73;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}
/* Nav */
.main-nav{
    background:#ffffff;
    border-bottom:1px solid #c8e6c9;
}
.nav-container{
    display:flex;
    justify-content:space-between;
    padding:12px 0;
}
.nav-links{
    list-style:none;
    display:flex;
    gap:20px;
}
.nav-links a{
    text-decoration:none;
    color:#2e7d32;
    font-weight:600;
}
.nav-links a:hover{
    color:#1b5e20;
}
/* Hero */
.hero-section{
    padding:60px 16px;
    text-align:center;
}
.hero-section h1{
    font-size:36px;
    color:#2e7d32;
    margin-bottom:10px;
}
.hero-section p{
    max-width:700px;
    margin:auto;
    font-size:16px;
    color:#555;
}
/* Categories */
.categories-section{
    padding:40px 0;
}
.categories-section h2{
    text-align:center;
    margin-bottom:30px;
    color:#2e7d32;
}
.categories-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:24px;
}
.category-card{
    background:#ffffff;
    padding:24px;
    border-radius:14px;
    box-shadow:0 6px 16px rgba(0,0,0,0.08);
    text-align:center;
    transition:transform .2s, box-shadow .2s;
}
.category-card:hover{
    transform:translateY(-4px);
    box-shadow:0 10px 22px rgba(0,0,0,0.12);
}
.category-card h3{
    color:#2e7d32;
    margin-bottom:10px;
}
.category-card p{
    font-size:14px;
    color:#666;
    margin-bottom:16px;
}
.category-link{
    display:inline-block;
    padding:10px 20px;
    background:#6fbf73;
    color:#fff;
    border-radius:8px;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
}
.category-link:hover{
    background:#5aa864;
}
@media(max-width:900px){
    .categories-grid{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:600px){
    .categories-grid{grid-template-columns:1fr;}
    .header-content{flex-direction:column;align-items:flex-start;}
}
</style>
</head>
<body>
<div class="top-header">
    <div class="container">
        100% Secure delivery by ZORO International
    </div>
</div>
<header class="main-header">
    <div class="container header-content">
        <div class="logo">
            <i class="fas fa-store"></i> ZORO INTERNATIONAL
        </div>
        <div class="header-actions">
            <a href="cart.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count"><?php echo $cart_count; ?></span>
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-menu">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($username,0,1)); ?>
                    </div>
                    <div class="user-info">
                        <div>Welcome, <?php echo htmlspecialchars($username); ?></div>
                        <small><?php echo htmlspecialchars($company); ?></small>
                    </div>
                    <a href="logout.php" style="margin-left:10px; color:#2e7d32;"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login / Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<nav class="main-nav">
    <div class="container nav-container">
        <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="#"><i class="fas fa-info-circle"></i> About Us</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="view_orders.php"><i class="fas fa-list"></i> My Orders</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
<section class="hero-section">
    <div class="container">
        <h1>Welcome to ZORO International</h1>
        <p>Your one-stop destination for premium food products, kitchen essentials and beauty products at wholesale prices.</p>
    </div>
</section>
<section class="categories-section">
    <div class="container">
        <h2>Shop By Categories</h2>
        <div class="categories-grid">
            <div class="category-card">
                <h3>Rice Varieties</h3>
                <p>Premium quality rice from around the world</p>
                <a href="products/rice_varieties.php" class="category-link">Shop Now</a>
            </div>
            <div class="category-card">
                <h3>Rice Powder</h3>
                <p>Finely ground rice powder for cooking</p>
                <a href="products/riceproducts.php" class="category-link">Shop Now</a>
            </div>
            <div class="category-card">
                <h3>Wheat Products</h3>
                <p>Fresh wheat flour and related products</p>
                <a href="products/wheat.php" class="category-link">Shop Now</a>
            </div>
            <div class="category-card">
                <h3>Pulses</h3>
                <p>Protein-rich lentils and beans</p>
                <a href="products/pulses.php" class="category-link">Shop Now</a>
            </div>
        </div>
    </div>
</section>
</body>
</html>