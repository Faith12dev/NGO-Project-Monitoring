<?php
session_start();
if (!isset($_SESSION['role'])) { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html>
<head><title>Project Manager Dashboard</title></head>
<link rel="stylesheet" href="../styles.css">
<body>
<div class="dashboard">
<h2>Welcome, <?= $_SESSION['fullname']; ?> (Project Manager)</h2>
<a href="../logout.php" class="logout">Logout</a>

<ul>
    <li><a href="#">Add New Project</a></li>
    <li><a href="#">Assign Staff</a></li>
    <li><a href="#">View Project Reports</a></li>
</ul>
</div>
</body>
</html>
