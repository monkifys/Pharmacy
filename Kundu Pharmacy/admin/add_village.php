<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
require_once '../config/db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete village
    if (isset($_POST['delete_village_id'])) {
        $del_id = intval($_POST['delete_village_id']);
        $stmt = $conn->prepare("DELETE FROM villages WHERE id = ?");
        $stmt->bind_param("i", $del_id);
        if ($stmt->execute()) {
            $success = 'Village removed successfully!';
        } else {
            $error = 'Failed to remove village.';
        }
        $stmt->close();
    }
    // Add village
    elseif (isset($_POST['village_name'])) {
        $village_name = trim($_POST['village_name'] ?? '');

        if (empty($village_name)) {
            $error = 'Village name is required.';
        } else {
            $stmt = $conn->prepare("INSERT INTO villages (village_name) VALUES (?)");
            $stmt->bind_param("s", $village_name);
            if ($stmt->execute()) {
                $success = 'Village "' . htmlspecialchars($village_name) . '" added successfully!';
            } else {
                $error = 'Failed to add village.';
            }
            $stmt->close();
        }
    }
}

// Fetch existing villages
$villages = $conn->query("SELECT * FROM villages ORDER BY village_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Village - Kundu Pharmacy Admin</title>
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

<div class="container-sm">
    <div class="card">
        <h2>🏘️ Add Village / Location</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Village Name</label>
                <input type="text" name="village_name" required placeholder="Enter village name">
            </div>
            <button type="submit" class="btn btn-primary">Add Village</button>
        </form>
    </div>

    <div class="card">
        <h3>Existing Villages</h3>
        <?php if ($villages->num_rows === 0): ?>
            <p style="color:#94a3b8;">No villages added yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Village Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($v = $villages->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $v['id']; ?></td>
                            <td><?php echo htmlspecialchars($v['village_name']); ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Remove this village?');">
                                    <input type="hidden" name="delete_village_id" value="<?php echo $v['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
