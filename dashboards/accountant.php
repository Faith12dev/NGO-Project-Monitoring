<?php session_start(); if (!isset($_SESSION['role'])) { header("Location: ../login.php"); exit(); } ?>
<!DOCTYPE html>
<html>
<head><title>Accountant Dashboard</title></head>
<link rel="stylesheet" href="../styles.css">
<body>
<div class="dashboard">
<h2>Welcome, <?= $_SESSION['fullname']; ?> (Accountant)</h2>
<a href="../logout.php" class="logout">Logout</a>

<ul>
    <li><a href="#">Record Expenditure</a></li>
    <li><a href="#">Generate Financial Reports</a></li>
</ul>
</div>
</body>
</html>
