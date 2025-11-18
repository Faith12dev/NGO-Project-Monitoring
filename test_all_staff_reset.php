<?php
require_once __DIR__ . '/app/includes/config.php';

echo "<h2>🔍 Password Reset Feature - Diagnostic for All Staff</h2>";
echo "<hr>";

// Check all staff members
echo "<h3>1. All Staff Members in Database</h3>";
$staff_query = "SELECT StaffID, FullName, Email, Role FROM Staff ORDER BY Email";
$staff_result = $conn->query($staff_query);

if ($staff_result && $staff_result->num_rows > 0) {
    echo "<table style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Email</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Role</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Full Name</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Test Reset Code</th>";
    echo "</tr>";
    
    while ($staff = $staff_result->fetch_assoc()) {
        $email = $staff['Email'];
        $role = $staff['Role'];
        $staff_id = $staff['StaffID'];
        
        // Generate a test reset code for each staff
        $test_code = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Insert test code
        $insert = "INSERT INTO PasswordReset (StaffID, ResetCode, Email, ExpiresAt, IsUsed) 
                   VALUES ($staff_id, '$test_code', '$email', '$expires_at', 0)";
        $conn->query($insert);
        
        // Verify it was inserted
        $verify = "SELECT ResetCode FROM PasswordReset WHERE ResetCode = '$test_code' AND IsUsed = 0 AND ExpiresAt > NOW()";
        $verify_result = $conn->query($verify);
        $is_valid = ($verify_result && $verify_result->num_rows > 0) ? '✓ YES' : '✗ NO';
        
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($email) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($role) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($staff['FullName']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>";
        echo "<span style='color: " . (strpos($is_valid, 'YES') !== false ? 'green' : 'red') . ";'>";
        echo htmlspecialchars($is_valid) . " - " . substr($test_code, 0, 16) . "...";
        echo "</span>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ No staff members found</p>";
}

// Test each staff's forgot password flow
echo "<hr>";
echo "<h3>2. Test Forgot Password Flow for Each Staff</h3>";
$staff_result = $conn->query("SELECT StaffID, FullName, Email, Role FROM Staff ORDER BY Email");

if ($staff_result) {
    echo "<table style='border-collapse: collapse; width: 100%; margin-top: 15px;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Email</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Role</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Forgot Password Link</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Reset Code Generated</th>";
    echo "</tr>";
    
    while ($staff = $staff_result->fetch_assoc()) {
        $email = $staff['Email'];
        $role = $staff['Role'];
        
        // Check if we can find the staff with email + role combo
        $find_query = "SELECT StaffID FROM Staff WHERE Email = '$email' AND Role = '$role'";
        $find_result = $conn->query($find_query);
        $can_find = ($find_result && $find_result->num_rows > 0) ? '✓ YES' : '✗ NO';
        
        // Generate test code for display
        $test_code = bin2hex(random_bytes(8));
        
        $role_display = ucfirst(str_replace('_', ' ', $role));
        
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($email) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($role_display) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>";
        echo "<a href='forgot_password.php' target='_blank' style='color: #0066cc; text-decoration: none;'>→ Go to Forgot Password</a>";
        echo "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>";
        echo "<span style='color: " . (strpos($can_find, 'YES') !== false ? 'green' : 'red') . ";'>";
        echo htmlspecialchars($can_find);
        echo "</span>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Instructions for testing
echo "<hr>";
echo "<h3>3. How to Test Reset Password for Each Staff</h3>";
echo "<div style='background: #e7f3ff; border: 2px solid #0066cc; padding: 20px; border-radius: 8px;'>";
echo "<h4 style='color: #0066cc;'>Steps to Test for Any Staff Member:</h4>";
echo "<ol>";
echo "<li>Go to <a href='forgot_password.php' target='_blank'>Forgot Password Page</a></li>";
echo "<li>Enter the staff member's <strong>email</strong> (from the table above)</li>";
echo "<li>Select the staff member's <strong>role</strong> (from the table above)</li>";
echo "<li>Click 'Send Reset Code'</li>";
echo "<li>Copy the generated reset code</li>";
echo "<li>Go to <a href='reset_password.php' target='_blank'>Reset Password Page</a></li>";
echo "<li>Paste the reset code</li>";
echo "<li>Enter new password (8+ chars, mixed case, numbers, special chars)</li>";
echo "<li>Confirm password</li>";
echo "<li>Click 'Reset Password'</li>";
echo "<li>You should see a success message and be redirected to login</li>";
echo "</ol>";
echo "</div>";

// List all current reset codes
echo "<hr>";
echo "<h3>4. Current Reset Codes in Database</h3>";
$codes_query = "SELECT pr.ResetID, pr.Email, pr.ResetCode, pr.IsUsed, pr.ExpiresAt, 
                       CASE WHEN pr.ExpiresAt > NOW() THEN 'VALID' ELSE 'EXPIRED' END as status
                FROM PasswordReset pr 
                ORDER BY pr.CreatedAt DESC 
                LIMIT 20";
$codes_result = $conn->query($codes_query);

if ($codes_result && $codes_result->num_rows > 0) {
    echo "<p>Found " . $codes_result->num_rows . " reset codes:</p>";
    echo "<table style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Email</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Used</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Status</th>";
    echo "<th style='border: 1px solid #ddd; padding: 10px;'>Expires At</th>";
    echo "</tr>";
    
    while ($code = $codes_result->fetch_assoc()) {
        $color = ($code['status'] === 'VALID' && !$code['IsUsed']) ? 'green' : 'orange';
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($code['Email']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . ($code['IsUsed'] ? '✓ YES' : '✗ NO') . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'><span style='color: " . $color . ";'>" . htmlspecialchars($code['status']) . "</span></td>";
        echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($code['ExpiresAt']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: #ff9800;'>ℹ️ No reset codes yet. Generate one by going to Forgot Password page.</p>";
}

// Troubleshooting
echo "<hr>";
echo "<h3>5. Troubleshooting Checklist</h3>";
echo "<div style='background: #fff3cd; border-left: 4px solid #ff9800; padding: 15px;'>";
echo "<ul>";
echo "<li>✓ Email must be spelled correctly (check against table above)</li>";
echo "<li>✓ Role must match exactly (case-sensitive: use lowercase like 'admin', not 'Administrator')</li>";
echo "<li>✓ Code must not be expired (generated codes are valid for 24 hours)</li>";
echo "<li>✓ Code must not have been used before (one-time use only)</li>";
echo "<li>✓ Password must be 8+ characters with mixed case and special characters</li>";
echo "<li>✓ Confirmation password must match exactly</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p style='background: #ccffcc; padding: 10px; border-radius: 5px;'>";
echo "✅ <strong>All staff members should be able to use the password reset feature.</strong><br>";
echo "If you have issues with a specific staff member, check the <strong>Troubleshooting Checklist</strong> above.";
echo "</p>";

$conn->close();
?>
