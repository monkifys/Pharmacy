<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';  // Default XAMPP password is empty
$dbname = 'kundu_pharmacy';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
