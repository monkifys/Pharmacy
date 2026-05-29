<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
require_once '../config/db.php';

$error   = '';
$success = '';

// Check if database schema is updated
$check_columns = $conn->query("SHOW COLUMNS FROM medicines");
$has_new_schema = false;
$columns = [];
while ($row = $check_columns->fetch_assoc()) {
    $columns[] = $row['Field'];
}
$has_new_schema = in_array('company', $columns);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete medicine
    if (isset($_POST['delete_medicine_id'])) {
        $del_id = intval($_POST['delete_medicine_id']);
        $stmt = $conn->prepare("DELETE FROM medicines WHERE id = ?");
        $stmt->bind_param("i", $del_id);
        if ($stmt->execute()) {
            $success = 'Medicine removed successfully!';
        } else {
            $error = 'Failed to remove medicine.';
        }
        $stmt->close();
    }
    // Add medicine (single)
    elseif (isset($_POST['medicine_name'])) {
        $medicine_name = trim($_POST['medicine_name'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $strength = trim($_POST['strength'] ?? '');
        $form = trim($_POST['form'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $stock = intval($_POST['stock'] ?? 100);

        if (empty($medicine_name)) {
            $error = 'Medicine name is required.';
        } elseif ($has_new_schema && (empty($company) || empty($strength) || empty($form))) {
            $error = 'Company, Strength, and Form are required.';
        } else {
            if ($has_new_schema) {
                $stmt = $conn->prepare("INSERT INTO medicines (medicine_name, company, strength, form, price, description, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssdsi", $medicine_name, $company, $strength, $form, $price, $description, $stock);
            } else {
                $stmt = $conn->prepare("INSERT INTO medicines (medicine_name) VALUES (?)");
                $stmt->bind_param("s", $medicine_name);
            }
            
            if ($stmt->execute()) {
                $success = 'Medicine "' . htmlspecialchars($medicine_name) . '" added successfully!';
            } else {
                $error = 'Failed to add medicine: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Search functionality
$search = $_GET['search'] ?? '';
$filter_company = $_GET['company'] ?? '';
$sort = $_GET['sort'] ?? 'name';

$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(medicine_name LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $types .= 's';
}

if (!empty($filter_company) && $has_new_schema) {
    $where[] = "(company = ?)";
    $params[] = $filter_company;
    $types .= 's';
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$order_by = 'medicine_name ASC';
if ($sort === 'company' && $has_new_schema) {
    $order_by = 'company ASC, medicine_name ASC';
} elseif ($sort === 'price' && $has_new_schema) {
    $order_by = 'price DESC';
}

$query = "SELECT * FROM medicines $where_clause ORDER BY $order_by LIMIT 1000";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$medicines = $stmt->get_result();
$total_medicines = $medicines->num_rows;
$stmt->close();

// Get unique companies for filter
$companies = [];
if ($has_new_schema) {
    $comp_result = $conn->query("SELECT DISTINCT company FROM medicines ORDER BY company");
    while ($row = $comp_result->fetch_assoc()) {
        $companies[] = $row['company'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Medicines - Kundu Pharmacy Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .medicine-toolbar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .toolbar-item {
            flex: 1;
            min-width: 200px;
        }
        .toolbar-item select,
        .toolbar-item input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
        }
        .stats {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .stat-item {
            flex: 1;
            min-width: 150px;
        }
        .stat-label {
            color: #64748b;
            font-size: 0.875rem;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #0284c7;
        }
        .medicine-table {
            font-size: 0.9rem;
            overflow-x: auto;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .alert-warning {
            background-color: #fef3c7;
            border: 1px solid #fcd34d;
            color: #92400e;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="admin_dashboard.php" class="brand">💊 Kundu Pharmacy — Admin</a>
    <ul class="nav-links">
        <li><a href="view_orders.php">Orders</a></li>
        <li><a href="add_village.php">Villages</a></li>
        <li><a href="add_medicine.php" style="font-weight:bold;color:#0284c7;">Medicines</a></li>
        <li><a href="bulk_import_medicines.php">Bulk Import</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <div class="card">
        <h2>💊 Medicine Management</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (!$has_new_schema): ?>
            <div class="alert alert-warning">
                ⚠️ <strong>Schema Update Required!</strong><br>
                Your database needs to be updated to support advanced features. 
                <a href="migrate_medicines_schema.php" style="color:#b45309; font-weight:bold;">Run Migration →</a>
            </div>
        <?php endif; ?>

        <h3>➕ Add New Medicine</h3>
        <form method="POST" action="" style="margin-bottom: 2rem;">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label>Medicine Name *</label>
                    <input type="text" name="medicine_name" required placeholder="e.g., Aspirin">
                </div>
                <?php if ($has_new_schema): ?>
                    <div class="form-group">
                        <label>Company *</label>
                        <input type="text" name="company" required placeholder="e.g., Cipla">
                    </div>
                    <div class="form-group">
                        <label>Strength *</label>
                        <input type="text" name="strength" required placeholder="e.g., 500mg">
                    </div>
                    <div class="form-group">
                        <label>Form *</label>
                        <select name="form" required>
                            <option value="">Select Form</option>
                            <option>Tablet</option>
                            <option>Capsule</option>
                            <option>Injection</option>
                            <option>Syrup</option>
                            <option>Suspension</option>
                            <option>Cream</option>
                            <option>Ointment</option>
                            <option>Gel</option>
                            <option>Oil</option>
                            <option>Powder</option>
                            <option>Solution</option>
                            <option>Drops</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input type="number" name="price" step="0.01" placeholder="0.00">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Description</label>
                        <textarea name="description" placeholder="Brief description" style="width:100%;padding:0.5rem;border:1px solid #cbd5e1;border-radius:0.375rem;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" value="100" min="0">
                    </div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Add Medicine</button>
        </form>

        <hr style="margin: 2rem 0; border: none; border-top: 1px solid #cbd5e1;">

        <h3>🔍 Find Medicines</h3>
        <form method="GET" action="" style="margin-bottom: 1rem;">
            <div class="medicine-toolbar">
                <div class="toolbar-item">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search medicine name...">
                </div>
                <?php if ($has_new_schema && !empty($companies)): ?>
                    <div class="toolbar-item">
                        <select name="company">
                            <option value="">All Companies</option>
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?php echo htmlspecialchars($comp); ?>" <?php echo $filter_company === $comp ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($comp); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="toolbar-item">
                        <select name="sort">
                            <option value="name">Sort by Name</option>
                            <option value="company" <?php echo $sort === 'company' ? 'selected' : ''; ?>>Sort by Company</option>
                            <option value="price" <?php echo $sort === 'price' ? 'selected' : ''; ?>>Sort by Price</option>
                        </select>
                    </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary" style="min-width:120px;">Search</button>
            </div>
        </form>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-label">Results Found</div>
                <div class="stat-value"><?php echo $total_medicines; ?></div>
            </div>
            <?php if ($has_new_schema): ?>
                <div class="stat-item">
                    <div class="stat-label">Unique Companies</div>
                    <div class="stat-value"><?php echo count($companies); ?></div>
                </div>
            <?php endif; ?>
        </div>

        <div class="medicine-table" style="overflow-x:auto;">
            <?php if ($total_medicines === 0): ?>
                <p style="color:#94a3b8;text-align:center;padding:2rem;">No medicines found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Medicine Name</th>
                            <?php if ($has_new_schema): ?>
                                <th>Company</th>
                                <th>Strength</th>
                                <th>Form</th>
                                <th>Price</th>
                                <th>Stock</th>
                            <?php endif; ?>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; while ($medicine = $medicines->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($medicine['medicine_name']); ?></td>
                                <?php if ($has_new_schema): ?>
                                    <td><?php echo htmlspecialchars($medicine['company'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($medicine['strength'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($medicine['form'] ?? ''); ?></td>
                                    <td>₹<?php echo number_format($medicine['price'] ?? 0, 2); ?></td>
                                    <td><?php echo $medicine['stock'] ?? 0; ?></td>
                                <?php endif; ?>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="delete_medicine_id" value="<?php echo $medicine['id']; ?>">
                                        <button type="submit" class="btn btn-sm" style="background:#ef4444;color:white;padding:0.25rem 0.5rem;border:none;border-radius:0.25rem;cursor:pointer;" onclick="return confirm('Delete this medicine?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h3>💡 Quick Actions</h3>
        <div style="display:grid;grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));gap:1rem;">
            <a href="bulk_import_medicines.php" class="btn btn-primary">📥 Bulk Import Medicines</a>
            <a href="generate_medicines_data.php" class="btn btn-primary">🤖 Generate CSV Data</a>
            <a href="add_village.php" class="btn btn-secondary">🏘️ Manage Villages</a>
            <a href="admin_dashboard.php" class="btn btn-secondary">📊 Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>
