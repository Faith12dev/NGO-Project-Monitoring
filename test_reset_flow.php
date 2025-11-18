<?php
require_once __DIR__ . '/app/includes/config.php';

echo "<h2>🔧 Password Reset - Manual Test & Fix</h2>";
echo "<hr>";

// Step 1: Clean up old codes
echo "<h3>Step 1: Cleaning up old reset codes</h3>";
$delete_old = "DELETE FROM PasswordReset WHERE ExpiresAt < NOW() OR IsUsed = TRUE";
$conn->query($delete_old);
echo "<p>✓ Deleted expired/used codes</p>";

// Step 2: Get admin user
echo "<hr>";
echo "<h3>Step 2: Finding admin user</h3>";
$admin_query = "SELECT StaffID, Email, Role FROM Staff WHERE Email = 'mushabedavid2002@gmail.com' AND Role = 'admin'";
$admin_result = $conn->query($admin_query);

if ($admin_result && $admin_result->num_rows > 0) {
    $admin = $admin_result->fetch_assoc();
    $staff_id = $admin['StaffID'];
    echo "<p style='color: green;'>✓ Admin found: " . htmlspecialchars($admin['Email']) . " (StaffID: " . htmlspecialchars($staff_id) . ")</p>";
    
    // Step 3: Generate new reset code
    echo "<hr>";
    echo "<h3>Step 3: Generating fresh reset code</h3>";
    $reset_code = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
    $created_at = date('Y-m-d H:i:s');
    
    echo "<p><strong>Reset Code:</strong> <code>" . htmlspecialchars($reset_code) . "</code></p>";
    echo "<p><strong>Created:</strong> " . htmlspecialchars($created_at) . "</p>";
    echo "<p><strong>Expires:</strong> " . htmlspecialchars($expires_at) . "</p>";
    
    // Step 4: Insert into database
    echo "<hr>";
    echo "<h3>Step 4: Inserting into database</h3>";
    $insert_query = "INSERT INTO PasswordReset (StaffID, ResetCode, Email, ExpiresAt, IsUsed) 
                     VALUES ($staff_id, '$reset_code', 'mushabedavid2002@gmail.com', '$expires_at', FALSE)";
    
    if ($conn->query($insert_query)) {
        echo "<p style='color: green;'>✓ Reset code inserted successfully</p>";
        
        // Step 5: Verify it's in database and retrievable
        echo "<hr>";
        echo "<h3>Step 5: Verifying code in database</h3>";
        
        $verify_query = "SELECT pr.StaffID, pr.Email, pr.ExpiresAt, pr.IsUsed FROM PasswordReset pr 
                        WHERE pr.ResetCode = '$reset_code' AND pr.IsUsed = FALSE 
                        AND pr.ExpiresAt > NOW()";
        
        $verify_result = $conn->query($verify_query);
        
        if ($verify_result && $verify_result->num_rows > 0) {
            $verified = $verify_result->fetch_assoc();
            echo "<p style='color: green;'>✓ Code verified and retrieval works!</p>";
            echo "<p><strong>Retrieved Data:</strong></p>";
            echo "<ul>";
            echo "<li>StaffID: " . htmlspecialchars($verified['StaffID']) . "</li>";
            echo "<li>Email: " . htmlspecialchars($verified['Email']) . "</li>";
            echo "<li>ExpiresAt: " . htmlspecialchars($verified['ExpiresAt']) . "</li>";
            echo "<li>IsUsed: " . htmlspecialchars($verified['IsUsed']) . "</li>";
            echo "</ul>";
            
            // Step 6: Instructions for user
            echo "<hr>";
            echo "<h3>Step 6: Use Your Reset Code</h3>";
            echo "<div style='background: #e7f3ff; border: 2px solid #0066cc; padding: 20px; border-radius: 8px;'>";
            echo "<p><strong>✅ Your reset code is ready to use!</strong></p>";
            echo "<p style='background: #fff; padding: 10px; border-radius: 5px; font-family: monospace; word-break: break-all;'>";
            echo "<strong>Code:</strong> " . htmlspecialchars($reset_code);
            echo "</p>";
            echo "<p><strong>Steps to reset password:</strong></p>";
            echo "<ol>";
            echo "<li>Go to: <a href='reset_password.php' target='_blank'>http://localhost/Ngo project/reset_password.php</a></li>";
            echo "<li>Paste this code: <code>" . htmlspecialchars(substr($reset_code, 0, 16) . "...") . "</code></li>";
            echo "<li>Enter new password (8+ characters, mixed case, numbers, special chars)</li>";
            echo "<li>Confirm password</li>";
            echo "<li>Click 'Reset Password'</li>";
            echo "<li>You'll be redirected to login page</li>";
            echo "<li>Login with your new password</li>";
            echo "</ol>";
            echo "</div>";
            
        } else {
            echo "<p style='color: red;'>✗ Code retrieval FAILED</p>";
            echo "<p>This is the problem! The code is in the database but the retrieval query is not finding it.</p>";
            
            // Debug the retrieval
            echo "<p><strong>Checking why retrieval failed:</strong></p>";
            
            $check_exists = "SELECT * FROM PasswordReset WHERE ResetCode = '$reset_code'";
            $exists_result = $conn->query($check_exists);
            if ($exists_result && $exists_result->num_rows > 0) {
                echo "<p style='color: green;'>✓ Code exists in database</p>";
                $row = $exists_result->fetch_assoc();
                echo "<pre style='background: #f0f0f0; padding: 10px;'>";
                echo "IsUsed: " . var_export($row['IsUsed'], true) . "\n";
                echo "ExpiresAt: " . $row['ExpiresAt'] . "\n";
                echo "NOW(): " . date('Y-m-d H:i:s') . "\n";
                echo "ExpiresAt > NOW(): " . (strtotime($row['ExpiresAt']) > time() ? 'YES' : 'NO') . "\n";
                echo "</pre>";
            } else {
                echo "<p style='color: red;'>✗ Code does NOT exist in database!</p>";
            }
        }
        
    } else {
        echo "<p style='color: red;'>✗ Error inserting reset code: " . htmlspecialchars($conn->error) . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Admin user not found in database</p>";
    echo "<p>Looking for: mushabedavid2002@gmail.com with role: admin</p>";
}

$conn->close();
?>
