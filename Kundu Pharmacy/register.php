<?php
session_start();
require_once 'config/db.php';

$error = '';
$success = '';

// Fetch villages for dropdown
$villages = [];
$result = $conn->query("SELECT * FROM villages ORDER BY village_name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $villages[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $village  = trim($_POST['village'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = 'All fields are required.';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Email already registered. Please login.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("INSERT INTO customers (name, email, password, village, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssss", $name, $email, $hashed, $village, $phone);

            if ($stmt2->execute()) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Kundu Pharmacy</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="brand">💊 Kundu Pharmacy</a>
    <ul class="nav-links">
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
    </ul>
</nav>

<div class="container-sm">
    <div class="card">
        <h2>Create Account</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="login.php">Login here</a></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($name ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Village / Location</label>
                <select name="village">
                    <option value="">-- Select Village --</option>
                    <?php foreach ($villages as $v): ?>
                        <option value="<?php echo htmlspecialchars($v['village_name']); ?>"><?php echo htmlspecialchars($v['village_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" required value="<?php echo htmlspecialchars($phone ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Register</button>
        </form>

        <p style="text-align:center; margin-top:15px; color:#64748b;">
            Already have an account? <a href="login.php" style="color:#0d9488;">Login here</a>
        </p>
    </div>
</div>

</body>
</html>
