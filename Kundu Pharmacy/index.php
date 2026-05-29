<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kundu Pharmacy - Medicine Delivery</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="brand">💊 Kundu Pharmacy</a>
    <ul class="nav-links">
        <li><a href="login.php">Customer Login</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="admin/admin_login.php">Admin</a></li>
    </ul>
</nav>

<div class="container">
    <div class="hero">
        <h1>💊 Kundu Pharmacy</h1>
        <p>Your trusted neighbourhood pharmacy — delivering medicines right to your doorstep.<br>Cash on Delivery only.</p>
        <div class="hero-actions">
            <a href="register.php" class="btn btn-primary">Register Now</a>
            <a href="login.php" class="btn btn-success">Customer Login</a>
            <a href="admin/admin_login.php" class="btn btn-warning">Admin Login</a>
        </div>
    </div>
</div>

<div class="footer">
    &copy; <?php echo date('Y'); ?> Kundu Pharmacy. All rights reserved.
</div>

</body>
</html>
