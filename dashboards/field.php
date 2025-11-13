<?php session_start(); if (!isset($_SESSION['role'])) { header("Location: ../login.php"); exit(); } ?>
<!DOCTYPE html>
<html>
<head><title>Field Officer Dashboard</title></head>
<link rel="stylesheet" href="../styles.css">
<body>
<div class="dashboard">
<h2>Welcome, <?= $_SESSION['fullname']; ?> (Field Officer)</h2>
<a href="../logout.php" class="logout">Logout</a>

<ul>
    <li><a href="#">Submit Field Report</a></li>
    <li><a href="#">View Assigned Projects</a></li>
</ul>
</div>
</body>
</html>
