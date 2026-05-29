<?php
/**
 * Migration Script: Update medicines table schema
 * Run this once to add new fields to existing medicines table
 * Access via: http://localhost/Kundu%20Pharmacy/admin/migrate_medicines_schema.php
 */

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    // Check if columns already exist
    $result = $conn->query("SHOW COLUMNS FROM medicines");
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    $migrations = [];
    
    // Add company column if doesn't exist
    if (!in_array('company', $existing_columns)) {
        $migrations[] = "ALTER TABLE medicines ADD COLUMN company VARCHAR(150) DEFAULT 'Generic'";
    }
    
    // Add strength column if doesn't exist
    if (!in_array('strength', $existing_columns)) {
        $migrations[] = "ALTER TABLE medicines ADD COLUMN strength VARCHAR(100)";
    }
    
    // Add form column if doesn't exist
    if (!in_array('form', $existing_columns)) {
        $migrations[] = "ALTER TABLE medicines ADD COLUMN form VARCHAR(50)";
    }
    
    // Add price column if doesn't exist
    if (!in_array('price', $existing_columns)) {
        $migrations[] = "ALTER TABLE medicines ADD COLUMN price DECIMAL(10, 2) DEFAULT 0";
    }
    
    // Add description column if doesn't exist
    if (!in_array('description', $existing_columns)) {
        $migrations[] = "ALTER TABLE medicines ADD COLUMN description TEXT";
    }
    
    // Add stock column if doesn't exist
    if (!in_array('stock', $existing_columns)) {
        $migrations[] = "ALTER TABLE medicines ADD COLUMN stock INT DEFAULT 100";
    }
    
    // Add created_at column if doesn't exist
    if (!in_array('created_at', $existing_columns)) {
        $migrations[] = "ALTER TABLE medicines ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    }
    
    if (empty($migrations)) {
        $success = '✓ Database schema is already up to date!';
    } else {
        // Run migrations
        $failed = 0;
        foreach ($migrations as $sql) {
            if (!$conn->query($sql)) {
                $error .= "Error: " . $conn->error . "<br>";
                $failed++;
            }
        }
        
        if ($failed === 0) {
            $success = '✓ Database schema updated successfully! Applied ' . count($migrations) . ' migrations.';
        } else {
            $error = "Migration completed with " . $failed . " errors.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migrate Database - Kundu Pharmacy Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="admin_dashboard.php" class="brand">💊 Kundu Pharmacy — Admin</a>
    <ul class="nav-links">
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container-sm">
    <div class="card">
        <h2>🔧 Database Migration</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <p style="margin-bottom: 1.5rem;">This will update the medicines table schema to support more fields like company, strength, form, price, etc.</p>

        <form method="POST">
            <button type="submit" name="run_migration" class="btn btn-primary">Run Migration</button>
        </form>

        <div style="margin-top: 2rem; padding: 1rem; background:#f1f5f9; border-radius:0.375rem;">
            <h4>📋 Changes:</h4>
            <ul style="margin:0; padding-left:1.5rem;">
                <li>Add 'company' field (VARCHAR)</li>
                <li>Add 'strength' field (VARCHAR)</li>
                <li>Add 'form' field (VARCHAR)</li>
                <li>Add 'price' field (DECIMAL)</li>
                <li>Add 'description' field (TEXT)</li>
                <li>Add 'stock' field (INT)</li>
                <li>Add 'created_at' timestamp</li>
            </ul>
        </div>
    </div>
</div>

</body>
</html>
