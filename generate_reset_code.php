<?php
require_once __DIR__ . '/app/includes/config.php';

echo "<h2>✅ Generate Fresh Reset Code (24-hour validity for testing)</h2>";
echo "<hr>";

// Step 1: Clean up old codes
echo "<h3>Step 1: Cleaning up expired codes</h3>";
$delete_query = "DELETE FROM PasswordReset WHERE ExpiresAt < NOW()";
$conn->query($delete_query);
echo "<p>✓ Old codes deleted</p>";

// Step 2: Get admin user
echo "<hr>";
echo "<h3>Step 2: Finding supervisor user</h3>";
$supervisor_query = "SELECT StaffID FROM Staff WHERE Email = 'sarah@ngo.com' AND Role = 'supervisor'";
$supervisor_result = $conn->query($supervisor_query);

if ($supervisor_result && $supervisor_result->num_rows > 0) {
    $supervisor = $supervisor_result->fetch_assoc();
    $staff_id = $supervisor['StaffID'];
    echo "<p style='color: green;'>✓ Supervisor found (StaffID: " . htmlspecialchars($staff_id) . ")</p>";
    
    // Step 3: Generate new code with 24-hour validity
    echo "<hr>";
    echo "<h3>Step 3: Generating new reset code (24-hour validity)</h3>";
    $reset_code = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours')); // 24 hours instead of 1
    $created_at = date('Y-m-d H:i:s');
    
    echo "<p><strong>Reset Code:</strong></p>";
    echo "<div style='background: #fff; border: 2px solid #0066cc; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<code style='font-size: 16px; word-break: break-all; font-family: monospace;'>" . htmlspecialchars($reset_code) . "</code>";
    echo "</div>";
    
    echo "<p><strong>Created:</strong> " . htmlspecialchars($created_at) . "</p>";
    echo "<p><strong>Expires:</strong> " . htmlspecialchars($expires_at) . "</p>";
    echo "<p style='background: #e7f3ff; padding: 10px; border-left: 4px solid #0066cc;'>";
    echo "✓ This code is valid for <strong>24 hours</strong> (until " . htmlspecialchars($expires_at) . ")";
    echo "</p>";
    
    // Step 4: Insert into database
    echo "<hr>";
    echo "<h3>Step 4: Inserting into database</h3>";
    $insert_query = "INSERT INTO PasswordReset (StaffID, ResetCode, Email, ExpiresAt, IsUsed) 
                     VALUES ($staff_id, '$reset_code', 'sarah@ngo.com', '$expires_at', 0)";
    
    if ($conn->query($insert_query)) {
        echo "<p style='color: green;'>✓ Reset code inserted successfully</p>";
        
        // Step 5: Verify it works
        echo "<hr>";
        echo "<h3>Step 5: Verifying code retrieval</h3>";
        $verify_query = "SELECT pr.StaffID, pr.Email, pr.ExpiresAt, pr.IsUsed FROM PasswordReset pr 
                        WHERE pr.ResetCode = '$reset_code' AND pr.IsUsed = 0 
                        AND pr.ExpiresAt > NOW()";
        
        $verify_result = $conn->query($verify_query);
        
        if ($verify_result && $verify_result->num_rows > 0) {
            echo "<p style='color: green;'>✓✓✓ Code verified and READY TO USE!</p>";
            
            // Step 6: Instructions
            echo "<hr>";
            echo "<h3>Step 6: How to Use This Code</h3>";
            echo "<div style='background: #e7f3ff; border: 2px solid #0066cc; padding: 20px; border-radius: 8px;'>";
            echo "<h4 style='color: #0066cc;'>✅ Your Reset Code is Ready!</h4>";
            
            echo "<p><strong>Step 1:</strong> Go to Reset Password page</p>";
            echo "<p style='margin-left: 20px;'>";
            echo "<a href='reset_password.php' target='_blank' style='background: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>";
            echo "Click here to go to Reset Password →";
            echo "</a>";
            echo "</p>";
            
            echo "<p style='margin-top: 20px;'><strong>Step 2:</strong> Copy and paste this reset code:</p>";
            echo "<div style='background: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd;'>";
            echo "<input type='text' value='" . htmlspecialchars($reset_code) . "' readonly style='width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: monospace; font-size: 12px;'>";
            echo "<button onclick=\"navigator.clipboard.writeText('" . htmlspecialchars($reset_code) . "'); alert('Code copied to clipboard!');\" style='margin-top: 10px; background: #0066cc; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
            echo "📋 Copy Code";
            echo "</button>";
            echo "</div>";
            
            echo "<p style='margin-top: 20px;'><strong>Step 3:</strong> Paste in the 'Reset Code' field on the Reset Password page</p>";
            
            echo "<p style='margin-top: 20px;'><strong>Step 4:</strong> Enter a new password:</p>";
            echo "<div style='background: #fff3cd; padding: 10px; border-left: 4px solid #ff9800; margin-left: 20px;'>";
            echo "<strong>Example:</strong> MyNewPassword@2024!<br>";
            echo "<small>Must be: 8+ chars, uppercase, lowercase, numbers, special chars</small>";
            echo "</div>";
            
            echo "<p style='margin-top: 20px;'><strong>Step 5:</strong> Confirm password (same as above)</p>";
            
            echo "<p style='margin-top: 20px;'><strong>Step 6:</strong> Click 'Reset Password'</p>";
            
            echo "<p style='margin-top: 20px;'><strong>Step 7:</strong> You'll be redirected to login page</p>";
            
            echo "<p style='margin-top: 20px;'><strong>Step 8:</strong> Login with:</p>";
            echo "<div style='background: #fff3cd; padding: 10px; border-left: 4px solid #ff9800; margin-left: 20px;'>";
            echo "Email: sarah@ngo<br>";
            echo "Password: Your new password<br>";
            echo "Role: Supervisor";
            echo "</div>";
            
            echo "<p style='margin-top: 30px; padding: 15px; background: #ccffcc; border-radius: 5px; text-align: center;'>";
            echo "<strong>✅ All set! Your password reset code is ready.</strong>";
            echo "</p>";
            echo "</div>";
            
        } else {
            echo "<p style='color: red;'>✗ Verification FAILED - Something went wrong</p>";
            // Debug
            $check = $conn->query("SELECT * FROM PasswordReset WHERE ResetCode = '$reset_code'");
            if ($check && $check->num_rows > 0) {
                $row = $check->fetch_assoc();
                echo "<pre>";
                print_r($row);
                echo "</pre>";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ Error inserting reset code: " . htmlspecialchars($conn->error) . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Supervisor user not found</p>";
}

$conn->close();
?>
