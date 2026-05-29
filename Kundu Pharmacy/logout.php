<?php
session_start();
session_unset();
session_destroy();
header("Location: index.php");
exit;
?>
https://localhost:3000