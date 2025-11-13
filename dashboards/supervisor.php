<?php session_start(); if (!isset($_SESSION['role'])) { header("Location: ../login.php"); exit(); } ?>
<!DOCTYPE html>
<html>
<head><title>Supervisor Dashboard</title></head>
<link rel="stylesheet" href="../styles.css">
<body>
<div class="dashboard">
<h2>Welcome, <?= $_SESSION['fullname']; ?> (Supervisor)</h2>
<a href="../logout.php" class="logout">Logout</a>

<ul>
    <li><a href="#">View Assigned Projects</a></li>
    <li><a href="#">Monitor Field Reports</a></li>
</ul>
</div>
</body>
</html>
