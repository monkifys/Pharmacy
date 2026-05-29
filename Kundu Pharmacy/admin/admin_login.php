<?php
session_start();
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_id']       = $row['id'];
                $_SESSION['admin_username'] = $row['username'];
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'Admin not found.';
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
    <title>Admin Login - Kundu Pharmacy</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="../index.php" class="brand">💊 Kundu Pharmacy</a>
    <ul class="nav-links">
        <li><a href="../index.php">Home</a></li>
    </ul>
</nav>

<div class="container-sm">
    <div class="card">
        <h2>🔐 Admin Login</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required value="<?php echo htmlspecialchars($username ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
        </form>
    </div>
</div>

</body>
</html>
