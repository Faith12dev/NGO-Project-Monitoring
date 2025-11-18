<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Mosh');
define('DB_NAME', 'NGO');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Define roles
define('ROLES', [
    'admin' => 'Administrator',
    'project_manager' => 'Project Manager',
    'accountant' => 'Accountant',
    'donor_liaison' => 'Donor Liaison Officer',
    'supervisor' => 'Supervisor',
    'field_officer' => 'Field Officer'
]);

// Session configuration
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base URL
define('BASE_URL', 'http://localhost/Ngo%20project/');
?>
