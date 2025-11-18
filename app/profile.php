<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$page_title = 'Profile';
require_once __DIR__ . '/includes/header.php';

$user_id = getCurrentUserID();
$user = getStaffByID($user_id);
?>

<div class="page-header">
    <h1><i class="fas fa-user-circle me-2"></i>My Profile</h1>
    <p>View and manage your profile information</p>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user me-2"></i>Profile Information
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div style="font-size: 4em; color: #667eea;">
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>

                <p class="mb-3">
                    <strong>Full Name:</strong><br>
                    <?php echo htmlspecialchars(getCurrentUserName()); ?>
                </p>

                <p class="mb-3">
                    <strong>Email:</strong><br>
                    <?php echo htmlspecialchars($_SESSION['email'] ?? 'N/A'); ?>
                </p>

                <p class="mb-3">
                    <strong>Role:</strong><br>
                    <span class="badge bg-primary" style="font-size: 0.9em; padding: 8px 12px;">
                        <?php echo htmlspecialchars(ROLES[getCurrentRole()]); ?>
                    </span>
                </p>

                <?php if ($user): ?>
                    <p class="mb-3">
                        <strong>Phone:</strong><br>
                        <?php echo htmlspecialchars($user['Phone'] ?? 'Not set'); ?>
                    </p>

                    <p class="mb-3">
                        <strong>Gender:</strong><br>
                        <?php echo htmlspecialchars($user['Gender'] ?? 'Not set'); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-lock me-2"></i>Security
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Password management and security settings can be configured by the administrator.
                </div>

                <div class="mt-4">
                    <h6>Session Information</h6>
                    <p class="text-muted">
                        <small>
                            <strong>Last Login:</strong><br>
                            <?php echo date('d M Y, H:i A'); ?>
                        </small>
                    </p>
                </div>

                <hr>

                <div class="mt-4">
                    <h6>Quick Actions</h6>
                    <a href="<?php echo BASE_URL; ?>app/dashboard.php" class="btn btn-primary btn-sm mb-2">
                        <i class="fas fa-home me-2"></i>Go to Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>app/logout.php" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
