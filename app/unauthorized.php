<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized - NGO System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div style="background: white; border-radius: 10px; padding: 50px; text-align: center; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
            <div style="font-size: 4em; color: #f56565; margin-bottom: 20px;">
                <i class="fas fa-lock"></i>
            </div>
            <h1 style="color: #2d3748; margin-bottom: 10px;">Access Denied</h1>
            <p style="color: #718096; margin-bottom: 30px;">
                You do not have permission to access this page.
            </p>
            <a href="<?php echo BASE_URL; ?>app/dashboard.php" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Go to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
