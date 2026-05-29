<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
require_once '../config/db.php';

$error   = '';
$success = '';
$preview_data = [];

// Handle file upload and preview
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'File upload error.';
    } else {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['csv', 'xls', 'xlsx'])) {
            $error = 'Only CSV files are supported. Please upload a .csv file.';
        } else {
            // Read CSV file
            $handle = fopen($file['tmp_name'], 'r');
            $row_count = 0;
            $headers = null;
            
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $row_count++;
                
                // First row is headers
                if ($row_count === 1) {
                    $headers = $data;
                    continue;
                }
                
                // Limit preview to 10 rows
                if ($row_count <= 11) {
                    $preview_data[] = array_combine($headers, array_pad($data, count($headers), ''));
                }
            }
            fclose($handle);
            
            if (empty($headers)) {
                $error = 'Invalid CSV file format.';
            } else {
                // Check required columns
                $required = ['medicine_name', 'company', 'strength', 'form', 'price'];
                $missing = array_diff($required, $headers);
                
                if (!empty($missing)) {
                    $error = 'Missing required columns: ' . implode(', ', $missing) . 
                             '. Required columns: medicine_name, company, strength, form, price';
                } else {
                    // Store file for actual import
                    $temp_filename = 'temp_' . time() . '.csv';
                    move_uploaded_file($file['tmp_name'], '../uploads/' . $temp_filename);
                }
            }
        }
    }
}

// Handle actual import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_import']) && isset($_POST['temp_file'])) {
    $temp_file = $_POST['temp_file'];
    $file_path = '../uploads/' . basename($temp_file);
    
    if (file_exists($file_path)) {
        $handle = fopen($file_path, 'r');
        $row_count = 0;
        $imported = 0;
        $failed = 0;
        $headers = null;
        
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $row_count++;
            
            if ($row_count === 1) {
                $headers = $data;
                continue;
            }
            
            $row = array_combine($headers, array_pad($data, count($headers), ''));
            
            // Validate required fields
            $medicine_name = trim($row['medicine_name'] ?? '');
            $company = trim($row['company'] ?? '');
            $strength = trim($row['strength'] ?? '');
            $form = trim($row['form'] ?? '');
            $price = trim($row['price'] ?? '');
            $description = trim($row['description'] ?? '');
            $stock = intval($row['stock'] ?? 100);
            
            if (!empty($medicine_name) && !empty($company) && !empty($strength) && !empty($form) && !empty($price)) {
                $stmt = $conn->prepare("INSERT INTO medicines (medicine_name, company, strength, form, price, description, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssdsi", $medicine_name, $company, $strength, $form, $price, $description, $stock);
                
                if ($stmt->execute()) {
                    $imported++;
                } else {
                    $failed++;
                }
                $stmt->close();
            } else {
                $failed++;
            }
        }
        fclose($handle);
        
        // Delete temp file
        unlink($file_path);
        
        $success = "Import completed! Imported: $imported medicines, Failed: $failed.";
    } else {
        $error = 'Temporary file not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Import Medicines - Kundu Pharmacy Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .preview-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.85rem;
        }
        .preview-table th, .preview-table td {
            border: 1px solid #cbd5e1;
            padding: 0.5rem;
            text-align: left;
        }
        .preview-table th {
            background-color: #f1f5f9;
        }
        .import-steps {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .step {
            margin-bottom: 0.75rem;
        }
        .csv-template {
            background: #eff6ff;
            border-left: 4px solid #0284c7;
            padding: 0.75rem;
            margin: 1rem 0;
            border-radius: 0.375rem;
        }
        .button-group {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="admin_dashboard.php" class="brand">💊 Kundu Pharmacy — Admin</a>
    <ul class="nav-links">
        <li><a href="view_orders.php">Orders</a></li>
        <li><a href="add_village.php">Villages</a></li>
        <li><a href="add_medicine.php">Medicines</a></li>
        <li><a href="bulk_import_medicines.php" style="font-weight:bold; color:#0284c7;">Bulk Import</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container-sm">
    <div class="card">
        <h2>📥 Bulk Import Medicines</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="import-steps">
            <h4>📋 CSV Format Instructions:</h4>
            <div class="step"><strong>1. Required Columns:</strong> medicine_name, company, strength, form, price</div>
            <div class="step"><strong>2. Optional Columns:</strong> description, stock</div>
            <div class="step"><strong>3. Example CSV content:</strong></div>
            <div class="csv-template">
                medicine_name,company,strength,form,price,description,stock<br>
                Aspirin,Bayer,500mg,Tablet,10,Pain reliever,500<br>
                Amoxicillin,Generic,250mg,Capsule,45,Antibiotic,300
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Select CSV File</label>
                <input type="file" name="csv_file" accept=".csv" required>
                <small style="color:#64748b;">Maximum 10 MB file size</small>
            </div>
            <button type="submit" class="btn btn-primary">Upload & Preview</button>
        </form>

        <?php if (!empty($preview_data)): ?>
            <div style="margin-top: 2rem;">
                <h3>📊 Preview (First 10 rows)</h3>
                <table class="preview-table">
                    <thead>
                        <tr>
                            <?php foreach ($preview_data[0] as $key => $value): ?>
                                <th><?php echo htmlspecialchars($key); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preview_data as $index => $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <form method="POST" style="margin-top: 1rem;">
                    <input type="hidden" name="temp_file" value="<?php echo htmlspecialchars($temp_filename ?? ''); ?>">
                    <div class="button-group">
                        <button type="submit" name="confirm_import" class="btn btn-primary">✓ Confirm Import</button>
                        <button type="reset" class="btn btn-secondary">↻ Cancel</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>💡 Quick Links</h3>
        <ul>
            <li><a href="add_medicine.php">→ Manage Individual Medicines</a></li>
            <li><a href="#">→ Download Sample Template (CSV)</a></li>
            <li><a href="medicines_list.php">→ View All Medicines</a></li>
        </ul>
    </div>
</div>

</body>
</html>
