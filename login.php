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

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        // Login process
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        if (!empty($email) && !empty($password)) {
            $stmt = $conn->prepare("SELECT user_id, company_name, contact_person_name, email, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // Verify password (plain text comparison - NOT secure for production)
                if ($password === $user['password']) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['company_name'] = $user['company_name'];
                    $_SESSION['contact_person_name'] = $user['contact_person_name'];
                    $_SESSION['email'] = $user['email'];
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Invalid email or password!";
                }
            } else {
                $error = "Invalid email or password!";
            }
            $stmt->close();
        } else {
            $error = "Please fill in all fields!";
        }
    }
    
    if (isset($_POST['signup'])) {
        // Signup process
        $company_name = trim($_POST['company_name']);
        $store_address = trim($_POST['store_address']);
        $contact_person_name = trim($_POST['contact_person_name']);
        $phone_number = trim($_POST['phone_number']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        // Basic validation
        if (empty($company_name) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = "Please fill in all required fields!";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match!";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email already registered!";
            } else {
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (company_name, store_address, contact_person_name, phone_number, email, password) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $company_name, $store_address, $contact_person_name, $phone_number, $email, $password);
                
                if ($stmt->execute()) {
                    $success = "Account created successfully! Please login.";
                } else {
                    $error = "Registration failed: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Segoe UI", sans-serif;
            background: #fff8e7;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.08);
        }
        h1 { text-align: center; margin-bottom: 25px; color: #2e7d32; }
        .tab-container { display: flex; margin-bottom: 25px; border-bottom: 2px solid #c8e6c9; }
        .tab {
            flex: 1; padding: 12px; text-align: center; cursor: pointer; border: none;
            background: none; font-size: 15px; font-weight: 600; color: #2e7d32; transition: 0.3s;
        }
        .tab.active { border-bottom: 3px solid #6fbf73; color: #1b5e20; }
        .form-container { display: none; }
        .form-container.active { display: block; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-size: 14px; font-weight: 600; color: #2e7d32; }
        input {
            width: 100%; padding: 10px 12px; border: 1px solid #c8e6c9; border-radius: 8px;
            font-size: 14px; background: #fbfdfb; transition: 0.3s;
        }
        input:focus { outline: none; border-color: #6fbf73; box-shadow: 0 0 0 2px rgba(111,191,115,0.15); }
        button[type="submit"] {
            width: 100%; padding: 11px; background: #6fbf73; color: white; border: none;
            border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: 0.3s;
        }
        button[type="submit"]:hover { background: #5aa864; }
        .message { padding: 12px; margin-bottom: 18px; border-radius: 8px; text-align: center; font-size: 14px; font-weight: 600; }
        .error { background: #fdecea; color: #c62828; border: 1px solid #f5c6cb; }
        .success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .back-home { text-align: center; margin-top: 20px; }
        .back-home a { color: #2e7d32; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>GECO International</h1>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="tab-container">
            <button class="tab <?php echo !isset($_POST['signup']) ? 'active' : ''; ?>" onclick="showForm('login')">Login</button>
            <button class="tab <?php echo isset($_POST['signup']) ? 'active' : ''; ?>" onclick="showForm('signup')">Signup</button>
        </div>
        
        <div id="login-form" class="form-container <?php echo !isset($_POST['signup']) ? 'active' : ''; ?>">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="login-email">Email:</label>
                    <input type="email" id="login-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Password:</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
        
        <div id="signup-form" class="form-container <?php echo isset($_POST['signup']) ? 'active' : ''; ?>">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="company_name">Company Name *</label>
                    <input type="text" id="company_name" name="company_name" required>
                </div>
                <div class="form-group">
                    <label for="store_address">Store Address</label>
                    <input type="text" id="store_address" name="store_address">
                </div>
                <div class="form-group">
                    <label for="contact_person_name">Contact Person Name</label>
                    <input type="text" id="contact_person_name" name="contact_person_name">
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number">
                </div>
                <div class="form-group">
                    <label for="signup-email">Email *</label>
                    <input type="email" id="signup-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="signup-password">Password *</label>
                    <input type="password" id="signup-password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="signup">Sign Up</button>
            </form>
        </div>
        
        <div class="back-home">
            <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>
    
    <script>
        function showForm(formType) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.form-container').forEach(form => form.classList.remove('active'));
            
            if (formType === 'login') {
                document.querySelector('.tab:nth-child(1)').classList.add('active');
                document.getElementById('login-form').classList.add('active');
            } else {
                document.querySelector('.tab:nth-child(2)').classList.add('active');
                document.getElementById('signup-form').classList.add('active');
            }
        }
    </script>
</body>
</html>
