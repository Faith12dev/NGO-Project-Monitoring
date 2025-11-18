<?php
require_once __DIR__ . '/app/includes/config.php';

echo "<h2>🔍 Password Recovery Debug Tool</h2>";
echo "<hr>";

// 1. Check PasswordReset table exists
echo "<h3>1. Database Structure Check</h3>";
$tables_query = "SHOW TABLES LIKE 'PasswordReset'";
$result = $conn->query($tables_query);
if ($result && $result->num_rows > 0) {
    echo "<p style='color: green;'>✓ PasswordReset table exists</p>";
    
    // Show table structure
    $describe = $conn->query("DESCRIBE PasswordReset");
    echo "<table style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
    echo "<tr style='background: #f0f0f0;'><th style='border: 1px solid #ddd; padding: 8px;'>Field</th><th style='border: 1px solid #ddd; padding: 8px;'>Type</th><th style='border: 1px solid #ddd; padding: 8px;'>Null</th><th style='border: 1px solid #ddd; padding: 8px;'>Key</th></tr>";
    while ($row = $describe->fetch_assoc()) {
        echo "<tr><td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['Key']) . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ PasswordReset table NOT FOUND - You need to run database_setup.sql</p>";
}

// 2. Check recent reset requests
echo "<hr>";
echo "<h3>2. Recent Reset Requests</h3>";
$resets_query = "SELECT ResetID, StaffID, ResetCode, Email, ExpiresAt, IsUsed, CreatedAt FROM PasswordReset ORDER BY CreatedAt DESC LIMIT 10";
$resets = $conn->query($resets_query);

if ($resets && $resets->num_rows > 0) {
    echo "<p style='color: green;'>✓ Found " . $resets->num_rows . " reset requests</p>";
    echo "<table style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Email</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Reset Code</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Expires At</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Expired?</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Used?</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Created</th>";
    echo "</tr>";
    
    while ($row = $resets->fetch_assoc()) {
        $now = new DateTime();
        $expires = new DateTime($row['ExpiresAt']);
        $is_expired = $now > $expires;
        
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['Email']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px; font-family: monospace; font-size: 12px;'>" . htmlspecialchars(substr($row['ResetCode'], 0, 16) . "...") . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['ExpiresAt']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px; color: " . ($is_expired ? 'red' : 'green') . ";'>";
        echo ($is_expired ? "✗ YES (EXPIRED)" : "✓ NO (valid)");
        echo "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . ($row['IsUsed'] ? "✓ YES" : "✗ NO") . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['CreatedAt']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ No reset requests found in database</p>";
}

// 3. Check Staff table
echo "<hr>";
echo "<h3>3. Staff Table Check</h3>";
$staff_query = "SELECT StaffID, Email, Role, Password FROM Staff";
$staff = $conn->query($staff_query);

if ($staff && $staff->num_rows > 0) {
    echo "<p style='color: green;'>✓ Found " . $staff->num_rows . " staff members</p>";
    echo "<table style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Email</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Role</th>";
    echo "<th style='border: 1px solid #ddd; padding: 8px;'>Password Hash</th>";
    echo "</tr>";
    
    while ($row = $staff->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['Email']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['Role']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px; font-family: monospace; font-size: 11px;'>" . htmlspecialchars(substr($row['Password'], 0, 20) . "...") . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>✗ No staff members found in database</p>";
}

// 4. Test a full flow
echo "<hr>";
echo "<h3>4. Manual Test Flow</h3>";
echo "<p>Testing complete password reset flow:</p>";

// Generate test code
$test_code = bin2hex(random_bytes(32));
$test_email = 'john@ngo.com';
$test_role = 'admin';
$expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

echo "<p style='background: #fff3cd; padding: 10px; border-left: 4px solid #ff9800;'>";
echo "<strong>Generated Test Code:</strong> <code>" . htmlspecialchars($test_code) . "</code><br>";
echo "<strong>Email:</strong> " . htmlspecialchars($test_email) . "<br>";
echo "<strong>Expires:</strong> " . htmlspecialchars($expires) . "<br>";
echo "</p>";

// Find staff
$find_staff = $conn->query("SELECT StaffID FROM Staff WHERE Email = '$test_email' AND Role = '$test_role'");
if ($find_staff && $find_staff->num_rows > 0) {
    $staff_row = $find_staff->fetch_assoc();
    $staff_id = $staff_row['StaffID'];
    
    echo "<p style='color: green;'>✓ Staff found: StaffID = " . htmlspecialchars($staff_id) . "</p>";
    
    // Insert test code
    $insert = "INSERT INTO PasswordReset (StaffID, ResetCode, Email, ExpiresAt, IsUsed) 
               VALUES ($staff_id, '$test_code', '$test_email', '$expires', FALSE)";
    
    if ($conn->query($insert)) {
        echo "<p style='color: green;'>✓ Test code inserted successfully</p>";
        
        // Verify it was inserted
        $verify = $conn->query("SELECT ResetCode FROM PasswordReset WHERE ResetCode = '$test_code'");
        if ($verify && $verify->num_rows > 0) {
            echo "<p style='color: green;'>✓ Code verified in database</p>";
            
            // Test retrieval (same logic as reset_password.php)
            $test_retrieve = "SELECT pr.StaffID, pr.Email, pr.ExpiresAt, pr.IsUsed FROM PasswordReset pr 
                            WHERE pr.ResetCode = '$test_code' AND pr.IsUsed = FALSE 
                            AND pr.ExpiresAt > NOW()";
            
            $test_result = $conn->query($test_retrieve);
            if ($test_result && $test_result->num_rows > 0) {
                echo "<p style='color: green;'>✓ Code retrieval successful - Code is VALID</p>";
            } else {
                echo "<p style='color: red;'>✗ Code retrieval FAILED - Check would fail</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Code NOT found after insert</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Error inserting test code: " . htmlspecialchars($conn->error) . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Staff not found: " . htmlspecialchars($test_email) . " / " . htmlspecialchars($test_role) . "</p>";
}

// 5. PHP Version Check
echo "<hr>";
echo "<h3>5. PHP & Database Info</h3>";
echo "<p>PHP Version: " . htmlspecialchars(phpversion()) . "</p>";
echo "<p>PHP Has password_hash: " . (function_exists('password_hash') ? '✓ YES' : '✗ NO') . "</p>";
echo "<p>Database Version: " . htmlspecialchars($conn->server_info) . "</p>";

$conn->close();
?>
