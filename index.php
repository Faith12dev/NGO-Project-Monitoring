<?php
require_once __DIR__ . '/app/includes/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Don't sanitize password
    $role = sanitize($_POST['role'] ?? '');

    // Validate input
    if (empty($email) || empty($password) || empty($role)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check if user exists in database with matching email and role
        $query = "SELECT * FROM Staff WHERE Email = '$email' AND Role = '$role'";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password using bcrypt
            if (password_verify($password, $user['Password'])) {
                // Valid credentials - login successful
                $_SESSION['user_id'] = $user['StaffID'];
                $_SESSION['email'] = $user['Email'];
                $_SESSION['full_name'] = $user['FullName'];
                $_SESSION['role'] = $user['Role'];
                
                header('Location: app/dashboard.php');
                exit;
            } else {
                $error = 'Invalid password.';
            }
        } else {
            $error = 'Invalid email or role. User not found in system.';
        }
    }
}

// Helper function for sanitization on this page
function sanitize($input) {
    return htmlspecialchars(trim($input));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGO Community Project Monitoring System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideIn 0.5s ease-out;
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

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 2em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-header p {
            font-size: 0.95em;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px;
            max-width: 450px;
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

        .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-login {
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
            margin-top: 10px;
        }

        .btn-login:hover {
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

        .demo-credentials {
            background: #f0f4ff;
            border: 2px solid #e0e0ff;
            border-radius: 8px;
            padding: 15px;
            margin-top: 25px;
            font-size: 0.9em;
        }

        .demo-credentials h6 {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 10px;
        }

        .demo-credentials p {
            margin: 5px 0;
            color: #555;
        }

        .role-info {
            margin-top: 10px;
            padding: 8px 12px;
            background: #f9f9f9;
            border-left: 3px solid #667eea;
            font-size: 0.85em;
            color: #666;
        }

        .role-icon {
            font-size: 2em;
            margin-bottom: 15px;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="role-icon">
                <i class="fas fa-leaf"></i>
            </div>
            <h1>NGO System</h1>
            <p>Community Project Monitoring System</p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div style="position: relative;">
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password" style="padding-right: 40px;">
                        <button type="button" class="btn" onclick="togglePasswordVisibility()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: none; background: none; color: #667eea; cursor: pointer; padding: 5px;">
                            <i class="fas fa-eye-slash" id="passwordToggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">Select Role</label>
                    <select class="form-select" id="role" name="role" required onchange="updateRoleInfo()">
                        <option value="">-- Select Your Role --</option>
                        <option value="admin">Administrator</option>
                        <option value="project_manager">Project Manager</option>
                        <option value="accountant">Accountant</option>
                        <option value="donor_liaison">Donor Liaison Officer</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="field_officer">Field Officer</option>
                    </select>
                    <div id="roleInfo" class="role-info" style="display: none;"></div>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>

            <div style="text-align: center; margin-top: 15px;">
                <a href="forgot_password.php" style="color: #667eea; text-decoration: none; font-size: 0.9em; font-weight: 500;">
                    <i class="fas fa-key me-1"></i>Forgot Password?
                    
                </a>
            </div>

            <div class="demo-credentials">
                <h6><i class="fas fa-info-circle me-2"></i>Demo Credentials</h6>
                <p style="font-size: 0.85em; margin-top: 10px;"><strong>Test Accounts:</strong></p>
                <ul style="margin: 5px 0; padding-left: 20px; font-size: 0.85em;">
                    <li><code>john@ngo.com</code> / <code>Mosh@gu.ac.gu</code> - Administrator</li>
                    <li><code>jane@ngo.com</code> / <code>ManagerJane</code> - Project Manager</li>
                    <li><code>peter@ngo.com</code> / <code>Peter123</code> - Field Officer</li>
                    <li><code>mary@ngo.com</code> / <code>Mary1234</code> - Donor Liaison</li>
                    <li><code>david@ngo.com</code> / <code>David123</code> - Accountant</li>
                    <li><code>sarah@ngo.com</code> / <code>Sarah123</code> - Supervisor</li>
                </ul>
                <p style="font-size: 0.8em; margin-top: 10px; color: #999;">
                    <i class="fas fa-lock me-1"></i>Each user has a unique secure password
                </p>
            </div>

        </div>
    </div>

    <script>
        const roleDescriptions = {
            'admin': 'Full access to all system features and user management',
            'project_manager': 'Create/edit projects, assign staff, view outcomes, track progress',
            'accountant': 'Add expenditures and generate financial summaries',
            'donor_liaison': 'View project progress reports, donor details, and send updates',
            'supervisor': 'View project implementation status, assign field officers, comment on reports',
            'field_officer': 'Submit field updates, upload photos/reports, view assigned project details'
        };

        function updateRoleInfo() {
            const roleSelect = document.getElementById('role');
            const roleInfo = document.getElementById('roleInfo');
            
            if (roleSelect.value) {
                roleInfo.textContent = roleDescriptions[roleSelect.value];
                roleInfo.style.display = 'block';
            } else {
                roleInfo.style.display = 'none';
            }
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>
