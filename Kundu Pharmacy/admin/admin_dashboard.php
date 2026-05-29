<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kundu Pharmacy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="admin_dashboard.php" class="brand">💊 Kundu Pharmacy — Admin</a>
    <ul class="nav-links">
        <li><a href="view_orders.php">Orders</a></li>
        <li><a href="add_village.php">Villages</a></li>
        <li><a href="add_medicine.php">Medicines</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <div class="card">
        <h2>Admin Dashboard</h2>
        <p style="color:#64748b; margin-bottom:20px;">Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>. Manage your pharmacy below.</p>
    </div>

    <div class="dash-grid">
        <a href="view_orders.php" class="dash-card">
            <div class="icon">📋</div>
            <h3>View Orders</h3>
            <p>See all customer orders and update statuses</p>
        </a>
        <a href="add_village.php" class="dash-card">
            <div class="icon">🏘️</div>
            <h3>Add Village</h3>
            <p>Add delivery locations</p>
        </a>
        <a href="add_medicine.php" class="dash-card">
            <div class="icon">💊</div>
            <h3>Add Medicine</h3>
            <p>Add medicines to the catalog</p>
        </a>
    </div>
</div>

</body>
</html>
