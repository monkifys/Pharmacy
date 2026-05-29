<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'config/db.php';

$customer_id   = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'];

// Fetch orders for this customer
$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kundu Pharmacy</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="brand">💊 Kundu Pharmacy</a>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="place_order.php">Place Order</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <div class="card">
        <h2>Welcome, <?php echo htmlspecialchars($customer_name); ?>! 👋</h2>
        <p style="color:#64748b; margin-bottom:20px;">Manage your medicine orders here.</p>

        <a href="place_order.php" class="btn btn-primary">📦 Place New Order</a>
    </div>

    <div class="card">
        <h3>Your Orders</h3>

        <?php if ($orders->num_rows === 0): ?>
            <div class="alert alert-info">You haven't placed any orders yet.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Village</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['village']); ?></td>
                            <td><?php echo htmlspecialchars($order['phone']); ?></td>
                            <td>
                                <?php
                                $status = $order['status'];
                                $badge = 'badge-pending';
                                if ($status === 'Confirmed') $badge = 'badge-confirmed';
                                if ($status === 'Out for Delivery') $badge = 'badge-delivery';
                                if ($status === 'Rejected') $badge = 'badge-rejected';
                                ?>
                                <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span>
                            </td>
                            <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
