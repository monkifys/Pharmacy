<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'config/db.php';

$customer_id = $_SESSION['customer_id'];
$error   = '';
$success = '';

// Fetch villages
$villages = [];
$result = $conn->query("SELECT * FROM villages ORDER BY village_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $villages[] = $row;
    }
}

// Fetch medicines
$medicines = [];
$result = $conn->query("SELECT * FROM medicines ORDER BY medicine_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $medicines[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $village          = trim($_POST['village'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $manual_medicine  = trim($_POST['manual_medicine'] ?? '');
    $selected_meds    = $_POST['medicines'] ?? [];

    if (empty($village) || empty($address) || empty($phone)) {
        $error = 'Village, address, and phone are required.';
    } else {
        // Handle prescription image upload
        $prescription_image = '';
        if (isset($_FILES['prescription']) && $_FILES['prescription']['error'] === UPLOAD_ERR_OK) {
            $file     = $_FILES['prescription'];
            $allowed  = ['image/jpeg', 'image/png', 'image/jpg'];
            $max_size = 2 * 1024 * 1024; // 2 MB

            if (!in_array($file['type'], $allowed)) {
                $error = 'Only JPEG and PNG images are allowed.';
            } elseif ($file['size'] > $max_size) {
                $error = 'Image must be smaller than 2 MB.';
            } else {
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'rx_' . time() . '_' . $customer_id . '.' . $ext;
                $dest     = 'uploads/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $prescription_image = $filename;
                } else {
                    $error = 'Failed to upload prescription image.';
                }
            }
        }

        if (empty($error)) {
            // Insert order
            $stmt = $conn->prepare("INSERT INTO orders (customer_id, village, address, phone, prescription_image, manual_medicine, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("isssss", $customer_id, $village, $address, $phone, $prescription_image, $manual_medicine);

            if ($stmt->execute()) {
                $order_id = $stmt->insert_id;
                $stmt->close();

                // Insert selected medicines
                if (!empty($selected_meds)) {
                    $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, medicine_id) VALUES (?, ?)");
                    foreach ($selected_meds as $med_id) {
                        $med_id = intval($med_id);
                        $stmt2->bind_param("ii", $order_id, $med_id);
                        $stmt2->execute();
                    }
                    $stmt2->close();
                }

                $success = 'Your order has been placed successfully.';
            } else {
                $error = 'Failed to place order. Please try again.';
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - Kundu Pharmacy</title>
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

<div class="container-sm">
    <div class="card">
        <h2>📦 Place Order</h2>
        <p style="color:#64748b; margin-bottom:20px;">Fill in the details below to order your medicines. Payment: <strong>Cash on Delivery</strong>.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="dashboard.php" style="color:#065f46;">View your orders</a></div>
        <?php else: ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <!-- 1. Village -->
            <div class="form-group">
                <label>Village / Location *</label>
                <select name="village" required>
                    <option value="">-- Select Village --</option>
                    <?php foreach ($villages as $v): ?>
                        <option value="<?php echo htmlspecialchars($v['village_name']); ?>"><?php echo htmlspecialchars($v['village_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 2. Prescription Image -->
            <div class="form-group">
                <label>Upload Prescription Image (JPEG/PNG, max 2 MB)</label>
                <input type="file" name="prescription" accept="image/jpeg,image/png">
            </div>

            <!-- 3. Select Medicines -->
            <div class="form-group">
                <label>Select Medicines</label>
                <?php if (count($medicines) > 0): ?>
                    <div class="checkbox-group">
                        <?php foreach ($medicines as $med): ?>
                            <label>
                                <input type="checkbox" name="medicines[]" value="<?php echo $med['id']; ?>">
                                <?php echo htmlspecialchars($med['medicine_name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color:#94a3b8; font-size:0.9rem;">No medicines available yet. Use the field below to write manually.</p>
                <?php endif; ?>
            </div>

            <!-- 4. Manual Medicine Names -->
            <div class="form-group">
                <label>Write Medicine Names (if not listed above)</label>
                <textarea name="manual_medicine" placeholder="e.g. Paracetamol 500mg, Cough Syrup"></textarea>
            </div>

            <!-- 5. Address -->
            <div class="form-group">
                <label>Delivery Address *</label>
                <textarea name="address" required placeholder="Enter your full delivery address"></textarea>
            </div>

            <!-- 6. Phone -->
            <div class="form-group">
                <label>Phone Number *</label>
                <input type="tel" name="phone" required placeholder="Enter your phone number">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">🛒 Place Order (Cash on Delivery)</button>
        </form>

        <?php endif; ?>
    </div>
</div>

</body>
</html>
