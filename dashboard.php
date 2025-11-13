<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = strtolower($_SESSION['role']);
switch ($role) {
    case "project manager":
        header("Location: dashboards/manager.php");
        break;
    case "accountant":
        header("Location: dashboards/accountant.php");
        break;
    case "donor liaison officer":
        header("Location: dashboards/liaison.php");
        break;
    case "supervisor":
        header("Location: dashboards/supervisor.php");
        break;
    case "field officer":
        header("Location: dashboards/field.php");
        break;
    default:
        echo "Invalid role!";
}
?>
