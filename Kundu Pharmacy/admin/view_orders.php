<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
require_once '../config/db.php';

// Fetch all orders with customer info
$sql = "SELECT o.*, c.name AS customer_name, c.email AS customer_email
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        ORDER BY o.created_at DESC";
$orders = $conn->query($sql);

$success = '';
if (isset($_GET['updated'])) {
    $success = 'Order status updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders - Kundu Pharmacy Admin</title>
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

<div class="container" style="max-width:1100px;">
    <div class="card">
        <h2>📋 All Orders</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($orders->num_rows === 0): ?>
            <div class="alert alert-info">No orders yet.</div>
        <?php else: ?>
            <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Village</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Medicines</th>
                        <th>Manual Medicine</th>
                        <th>Prescription</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()):
                        // Fetch selected medicines for this order
                        $oi_stmt = $conn->prepare("SELECT m.medicine_name FROM order_items oi JOIN medicines m ON oi.medicine_id = m.id WHERE oi.order_id = ?");
                        $oi_stmt->bind_param("i", $order['id']);
                        $oi_stmt->execute();
                        $oi_result = $oi_stmt->get_result();
                        $med_names = [];
                        while ($oi_row = $oi_result->fetch_assoc()) {
                            $med_names[] = $oi_row['medicine_name'];
                        }
                        $oi_stmt->close();
                    ?>
                        <tr>
                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?><br><small style="color:#94a3b8;"><?php echo htmlspecialchars($order['customer_email']); ?></small></td>
                            <td><?php echo htmlspecialchars($order['village']); ?></td>
                            <td><?php echo htmlspecialchars($order['address']); ?></td>
                            <td><?php echo htmlspecialchars($order['phone']); ?></td>
                            <td><?php echo !empty($med_names) ? htmlspecialchars(implode(', ', $med_names)) : '<span style="color:#94a3b8;">—</span>'; ?></td>
                            <td><?php echo !empty($order['manual_medicine']) ? htmlspecialchars($order['manual_medicine']) : '<span style="color:#94a3b8;">—</span>'; ?></td>
                            <td>
                                <?php if (!empty($order['prescription_image'])): ?>
                                    <a href="../uploads/<?php echo htmlspecialchars($order['prescription_image']); ?>" target="_blank">
                                        <img src="../uploads/<?php echo htmlspecialchars($order['prescription_image']); ?>" class="prescription-img" alt="Prescription" style="max-width:80px;">
                                    </a>
                                <?php else: ?>
                                    <span style="color:#94a3b8;">—</span>
                                <?php endif; ?>
                            </td>
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
                            <td><small><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></small></td>
                            <td>
                                <?php if ($status === 'Pending'): ?>
                                    <form method="POST" action="update_order_status.php" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="status" value="Confirmed">
                                        <button type="submit" class="btn btn-success btn-sm">Confirm</button>
                                    </form>
                                    <form method="POST" action="update_order_status.php" style="display:inline; margin-left:4px;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="status" value="Rejected">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this order?');">Reject</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($status === 'Confirmed'): ?>
                                    <form method="POST" action="update_order_status.php" style="display:inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="status" value="Out for Delivery">
                                        <button type="submit" class="btn btn-warning btn-sm">Out for Delivery</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($status === 'Out for Delivery'): ?>
                                    <span style="color:#94a3b8; font-size:0.85rem;">Done</span>
                                <?php endif; ?>
                                <?php if ($status === 'Rejected'): ?>
                                    <span style="color:#991b1b; font-size:0.85rem;">Rejected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
