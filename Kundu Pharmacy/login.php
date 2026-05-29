<?php
session_start();
require_once 'config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['customer_id']   = $row['id'];
                $_SESSION['customer_name'] = $row['name'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'No account found with that email.';
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
    <title>Login - Kundu Pharmacy</title>
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
        <h2>Customer Login</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
        </form>

        <p style="text-align:center; margin-top:15px; color:#64748b;">
            Don't have an account? <a href="register.php" style="color:#0d9488;">Register here</a>
        </p>
    </div>
</div>

</body>
</html>
