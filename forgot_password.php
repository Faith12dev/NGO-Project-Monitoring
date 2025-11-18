<?php
require_once __DIR__ . '/app/includes/config.php';

$error = '';
$success = '';
$reset_code = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $role = sanitize($_POST['role'] ?? '');

    if (empty($email) || empty($role)) {
        $error = 'Please enter both email and role.';
    } else {
        // Check if user exists
        $email = $conn->real_escape_string($email);
        $role = $conn->real_escape_string($role);
        $query = "SELECT StaffID, Email, Role FROM Staff WHERE Email = '$email' AND Role = '$role'";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate unique reset code
            $reset_code_generated = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+5 hour'));
            
            // Store reset code in database
            $insert_query = "INSERT INTO PasswordReset (StaffID, ResetCode, Email, ExpiresAt, IsUsed) 
                           VALUES ({$user['StaffID']}, '$reset_code_generated', '$email', '$expires_at', 0)";
            
            if ($conn->query($insert_query)) {
                $reset_code = $reset_code_generated;
                $success = 'Password reset code generated successfully! Your code is valid for 1 hour.';
            } else {
                $error = 'Error generating reset code. Please try again.';
            }
        } else {
            $error = 'No user found with that email and role combination.';
        }
    }
}

function sanitize($input) {
    return htmlspecialchars(trim($input));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - NGO System</title>
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

        .password-recover-container {
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

        .recover-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .recover-header h1 {
            font-size: 1.8em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .recover-header p {
            font-size: 0.95em;
            opacity: 0.9;
        }

        .recover-body {
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

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .btn-recover {
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

        .btn-recover:hover {
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

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-to-login a:hover {
            color: #764ba2;
        }

        .reset-code-box {
            background: #f0f4ff;
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }

        .reset-code-label {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }

        .reset-code-value {
            font-size: 1.1em;
            font-weight: bold;
            color: #667eea;
            word-break: break-all;
            font-family: monospace;
        }

        .copy-btn {
            margin-top: 10px;
            font-size: 0.9em;
        }

        .next-step {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 12px 15px;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 0.95em;
        }

        .next-step strong {
            color: #0066cc;
        }
    </style>
</head>
<body>
    <div class="password-recover-container">
        <div class="recover-header">
            <h1><i class="fas fa-key me-2"></i>Forgot Password</h1>
            <p>Recover your account access</p>
        </div>

        <div class="recover-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>

                <div class="reset-code-box">
                    <div class="reset-code-label">Your Password Reset Code:</div>
                    <div class="reset-code-value"><?php echo htmlspecialchars($reset_code); ?></div>
                    <button class="btn btn-sm btn-primary copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($reset_code); ?>')">
                        <i class="fas fa-copy me-2"></i>Copy Code
                    </button>
                </div>

                <div class="next-step">
                    <strong><i class="fas fa-arrow-right me-2"></i>Next Step:</strong><br>
                    Go to <a href="reset_password.php">Reset Password</a> page and enter this code to set your new password.
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Your Role *</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">-- Select Your Role --</option>
                            <option value="admin">Administrator</option>
                            <option value="project_manager">Project Manager</option>
                            <option value="accountant">Accountant</option>
                            <option value="donor_liaison">Donor Liaison Officer</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="field_officer">Field Officer</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-recover">
                        <i class="fas fa-envelope me-2"></i>Send Reset Code
                    </button>
                </form>
            <?php endif; ?>

            <div class="back-to-login">
                <a href="index.php"><i class="fas fa-arrow-left me-2"></i>Back to Login</a>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Reset code copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
    </script>
</body>
</html>
