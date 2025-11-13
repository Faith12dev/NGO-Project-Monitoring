<?php session_start(); if (!isset($_SESSION['role'])) { header("Location: ../login.php"); exit(); } ?>
<!DOCTYPE html>
<html>
<head><title>Donor Liaison Dashboard</title></head>
<link rel="stylesheet" href="../styles.css">
<body>
<div class="dashboard">
<h2>Welcome, <?= $_SESSION['fullname']; ?> (Donor Liaison Officer)</h2>
<a href="../logout.php" class="logout">Logout</a>

<ul>
    <li><a href="#">View Donor Information</a></li>
    <li><a href="#">Send Donor Updates</a></li>
</ul>
</div>
</body>
</html>
