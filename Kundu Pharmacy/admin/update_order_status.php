<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $status   = $_POST['status'] ?? '';

    $allowed_statuses = ['Confirmed', 'Out for Delivery', 'Rejected'];

    if ($order_id > 0 && in_array($status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: view_orders.php?updated=1");
exit;
?>
