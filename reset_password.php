<?php
require_once __DIR__ . '/app/includes/config.php';

$error = '';
$success = '';
$show_form = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reset_code = trim($_POST['reset_code'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($reset_code) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } else if ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else if (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Validate reset code
        $reset_code = $conn->real_escape_string($reset_code);
        $code_query = "SELECT pr.StaffID, pr.Email, pr.ExpiresAt, pr.IsUsed FROM PasswordReset pr 
                       WHERE pr.ResetCode = '$reset_code' AND pr.IsUsed = 0 
                       AND pr.ExpiresAt > NOW()";
        $code_result = $conn->query($code_query);

        if ($code_result && $code_result->num_rows > 0) {
            $code_data = $code_result->fetch_assoc();
            $staff_id = $code_data['StaffID'];
            
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            // Update staff password
            $update_query = "UPDATE Staff SET Password = '$hashed_password' WHERE StaffID = $staff_id";
            
            if ($conn->query($update_query)) {
                // Mark reset code as used
                $mark_used = "UPDATE PasswordReset SET IsUsed = TRUE WHERE ResetCode = '$reset_code'";
                $conn->query($mark_used);
                
                $success = 'Password reset successfully! You can now log in with your new password.';
                $show_form = false;
            } else {
                $error = 'Error updating password. Please try again.';
            }
        } else {
            $error = 'Invalid or expired reset code. Please request a new one.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - NGO System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .reset-password-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
            max-width: 450px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .reset-header h1 {
            font-size: 1.8em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .reset-header p {
            font-size: 0.95em;
            opacity: 0.9;
        }

        .reset-body {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .btn-reset {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 1em;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .alert {
            border: none;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background-color: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert-success {
            background-color: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #764ba2;
        }

        .password-requirements {
            background: #f9f9f9;
            border-left: 4px solid #667eea;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }

        .password-requirements li {
            margin: 3px 0;
        }

        .success-message {
            text-align: center;
        }

        .success-icon {
            font-size: 3em;
            color: #28a745;
            margin-bottom: 15px;
        }

        .show-password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #667eea;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
            border: none;
            font-size: 1em;
            transition: color 0.2s ease;
        }

        .show-password-toggle:hover {
            color: #764ba2;
        }

        .password-input-wrapper {
            position: relative;
        }

        .password-input-wrapper .form-control {
            padding-right: 40px;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="reset-header">
            <h1><i class="fas fa-lock me-2"></i>Reset Password</h1>
            <p>Create a new secure password</p>
        </div>

        <div class="reset-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success && !$show_form): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>

                <div class="success-message">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <p>Your password has been successfully reset.</p>
                    <p>Redirecting to login page in 3 seconds...</p>
                </div>

                <script>
                    setTimeout(function() {
                        window.location.href = 'index.php';
                    }, 3000);
                </script>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="reset_code" class="form-label">Reset Code *</label>
                        <input type="text" class="form-control" id="reset_code" name="reset_code" required placeholder="Enter your reset code" autocomplete="off">
                        <small class="text-muted">Paste the code you received from the forgot password page</small>
                    </div>

                    <div class="password-requirements">
                        <strong style="color: #667eea;">Password Requirements:</strong>
                        <ul style="margin-bottom: 0; padding-left: 20px;">
                            <li>At least 8 characters</li>
                            <li>Mix of uppercase and lowercase letters</li>
                            <li>Include numbers and special characters</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password *</label>
                        <div class="password-input-wrapper">
                            <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="Enter new password" autocomplete="new-password">
                            <button type="button" class="show-password-toggle" onclick="togglePassword('new_password')" tabindex="-1">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <div class="password-input-wrapper">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Confirm password" autocomplete="new-password">
                            <button type="button" class="show-password-toggle" onclick="togglePassword('confirm_password')" tabindex="-1">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-reset">
                        <i class="fas fa-lock me-2"></i>Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="back-link">
                <a href="index.php"><i class="fas fa-arrow-left me-2"></i>Back to Login</a>
                <?php if ($show_form): ?>
                    | <a href="forgot_password.php"><i class="fas fa-key me-2"></i>Request New Code</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = event.currentTarget;
            const icon = button.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>
