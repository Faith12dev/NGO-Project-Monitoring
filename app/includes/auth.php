<?php
require_once __DIR__ . '/config.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user has specific role
function hasRole($role) {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Check if user has any of the specified roles
function hasAnyRole($roles) {
    if (!isLoggedIn()) return false;
    return in_array($_SESSION['role'], $roles);
}

// Get current user ID
function getCurrentUserID() {
    return isLoggedIn() ? $_SESSION['user_id'] : null;
}

// Get current user role
function getCurrentRole() {
    return isLoggedIn() ? $_SESSION['role'] : null;
}

// Get current user name
function getCurrentUserName() {
    return isLoggedIn() ? $_SESSION['full_name'] : null;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Redirect if user doesn't have required role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ' . BASE_URL . 'app/unauthorized.php');
        exit;
    }
}

// Redirect if user doesn't have any of the required roles
function requireAnyRole($roles) {
    requireLogin();
    if (!hasAnyRole($roles)) {
        header('Location: ' . BASE_URL . 'app/unauthorized.php');
        exit;
    }
}

// Logout user
function logout() {
    session_destroy();
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}
?>
