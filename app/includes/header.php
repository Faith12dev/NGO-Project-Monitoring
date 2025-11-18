<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

requireLogin();

$current_user = getCurrentUserName();
$current_role = getCurrentRole();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - NGO System' : 'NGO Community Project Monitoring System'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h4><i class="fas fa-leaf me-2"></i>NGO System</h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>app/dashboard.php">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>

                <!-- Projects: Admin, Project Manager, Supervisor -->
                <?php if (hasAnyRole(['admin', 'project_manager', 'supervisor'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>app/pages/projects.php">
                            <i class="fas fa-project-diagram"></i> Projects
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Outcomes: Admin, Project Manager, Supervisor -->
                <?php if (hasAnyRole(['admin', 'project_manager', 'supervisor'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>app/pages/outcomes.php">
                            <i class="fas fa-trophy"></i> Outcomes
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Expenditures: Admin, Accountant -->
                <?php if (hasAnyRole(['admin', 'accountant'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>app/pages/expenditures.php">
                            <i class="fas fa-money-bill-wave"></i> Expenditures
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Donors: Admin, Donor Liaison Officer -->
                <?php if (hasAnyRole(['admin', 'donor_liaison'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>app/pages/donors.php">
                            <i class="fas fa-handshake"></i> Donors
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Staff: Admin only -->
                <?php if (hasRole('admin')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>app/pages/staff.php">
                            <i class="fas fa-user-tie"></i> Staff
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Field Officer Reports: Admin, Field Officer -->
                <?php if (hasAnyRole(['admin', 'field_officer'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>app/pages/field-reports.php">
                            <i class="fas fa-file-alt"></i> Field Reports
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Audit Logs: All Staff -->
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>app/pages/audit-logs.php">
                        <i class="fas fa-history"></i> Audit Logs
                    </a>
                </li>

                <hr class="my-3">

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo BASE_URL; ?>app/profile.php">
                        <i class="fas fa-user-circle"></i> Profile
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>app/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary me-3" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="navbar-brand mb-0 h1 ms-auto">
                        Welcome, <strong><?php echo htmlspecialchars($current_user); ?></strong>
                        <span class="badge bg-primary ms-2"><?php echo htmlspecialchars(ROLES[$current_role]); ?></span>
                    </span>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="content-wrapper">
