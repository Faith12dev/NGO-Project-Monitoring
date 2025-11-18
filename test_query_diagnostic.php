<?php
require_once __DIR__ . '/app/includes/config.php';

echo "<h2>🔍 Detailed Query Diagnostic</h2>";
echo "<hr>";

// Get the most recent code
$get_latest = "SELECT ResetCode, StaffID, Email, ExpiresAt, IsUsed FROM PasswordReset ORDER BY CreatedAt DESC LIMIT 1";
$latest_result = $conn->query($get_latest);

if ($latest_result && $latest_result->num_rows > 0) {
    $latest = $latest_result->fetch_assoc();
    $test_code = $latest['ResetCode'];
    
    echo "<h3>Latest Reset Code in Database:</h3>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
    echo "ResetCode: " . htmlspecialchars($test_code) . "\n";
    echo "StaffID: " . htmlspecialchars($latest['StaffID']) . "\n";
    echo "Email: " . htmlspecialchars($latest['Email']) . "\n";
    echo "ExpiresAt: " . htmlspecialchars($latest['ExpiresAt']) . "\n";
    echo "IsUsed: " . htmlspecialchars($latest['IsUsed']) . " (type: " . gettype($latest['IsUsed']) . ")\n";
    echo "</pre>";
    
    // Test 1: Simple query without WHERE
    echo "<hr>";
    echo "<h3>Test 1: SELECT all from PasswordReset</h3>";
    $test1 = "SELECT * FROM PasswordReset ORDER BY CreatedAt DESC LIMIT 1";
    $result1 = $conn->query($test1);
    if ($result1) {
        echo "<p style='color: green;'>✓ Query executed successfully</p>";
        echo "<p>Rows returned: " . $result1->num_rows . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Query failed: " . htmlspecialchars($conn->error) . "</p>";
    }
    
    // Test 2: Query with ResetCode condition
    echo "<hr>";
    echo "<h3>Test 2: WHERE ResetCode = 'code'</h3>";
    $escaped_code = $conn->real_escape_string($test_code);
    $test2 = "SELECT * FROM PasswordReset WHERE ResetCode = '$escaped_code'";
    echo "<p><strong>Query:</strong> <code>" . htmlspecialchars($test2) . "</code></p>";
    $result2 = $conn->query($test2);
    if ($result2) {
        echo "<p style='color: green;'>✓ Query executed</p>";
        echo "<p>Rows returned: " . $result2->num_rows . "</p>";
        if ($result2->num_rows > 0) {
            $row = $result2->fetch_assoc();
            echo "<p style='color: green;'>✓ Code FOUND with this query</p>";
        } else {
            echo "<p style='color: red;'>✗ Code NOT found with this query</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Query failed: " . htmlspecialchars($conn->error) . "</p>";
    }
    
    // Test 3: Query with IsUsed = 0
    echo "<hr>";
    echo "<h3>Test 3: WHERE ResetCode = 'code' AND IsUsed = 0</h3>";
    $test3 = "SELECT * FROM PasswordReset WHERE ResetCode = '$escaped_code' AND IsUsed = 0";
    echo "<p><strong>Query:</strong> <code>" . htmlspecialchars($test3) . "</code></p>";
    $result3 = $conn->query($test3);
    if ($result3) {
        echo "<p style='color: green;'>✓ Query executed</p>";
        echo "<p>Rows returned: " . $result3->num_rows . "</p>";
        if ($result3->num_rows > 0) {
            echo "<p style='color: green;'>✓ Code FOUND with IsUsed = 0</p>";
        } else {
            echo "<p style='color: red;'>✗ Code NOT found - IsUsed condition blocking it</p>";
            // Check what IsUsed value is
            $check_isused = "SELECT IsUsed, IsUsed = 0 as test FROM PasswordReset WHERE ResetCode = '$escaped_code'";
            $check_result = $conn->query($check_isused);
            if ($check_result && $check_result->num_rows > 0) {
                $check = $check_result->fetch_assoc();
                echo "<p><strong>IsUsed value:</strong> " . var_export($check['IsUsed'], true) . "</p>";
                echo "<p><strong>IsUsed = 0 comparison:</strong> " . var_export($check['test'], true) . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ Query failed: " . htmlspecialchars($conn->error) . "</p>";
    }
    
    // Test 4: Query with expiration check
    echo "<hr>";
    echo "<h3>Test 4: WITH ExpiresAt > NOW()</h3>";
    $test4 = "SELECT * FROM PasswordReset WHERE ResetCode = '$escaped_code' AND IsUsed = 0 AND ExpiresAt > NOW()";
    echo "<p><strong>Query:</strong> <code>" . htmlspecialchars($test4) . "</code></p>";
    $result4 = $conn->query($test4);
    if ($result4) {
        echo "<p style='color: green;'>✓ Query executed</p>";
        echo "<p>Rows returned: " . $result4->num_rows . "</p>";
        if ($result4->num_rows > 0) {
            echo "<p style='color: green;'>✓✓✓ THIS QUERY WORKS! Code FOUND</p>";
            echo "<p style='background: #ccffcc; padding: 10px; border-radius: 5px;'>";
            echo "<strong>SUCCESS!</strong> The retrieval query should now work in reset_password.php";
            echo "</p>";
        } else {
            echo "<p style='color: red;'>✗ Code NOT found - Expiration blocking it</p>";
            // Check expiration
            $check_exp = "SELECT ExpiresAt, NOW() as now, ExpiresAt > NOW() as is_valid FROM PasswordReset WHERE ResetCode = '$escaped_code'";
            $check_exp_result = $conn->query($check_exp);
            if ($check_exp_result && $check_exp_result->num_rows > 0) {
                $check_exp_row = $check_exp_result->fetch_assoc();
                echo "<p><strong>ExpiresAt:</strong> " . htmlspecialchars($check_exp_row['ExpiresAt']) . "</p>";
                echo "<p><strong>NOW():</strong> " . htmlspecialchars($check_exp_row['now']) . "</p>";
                echo "<p><strong>ExpiresAt > NOW():</strong> " . var_export($check_exp_row['is_valid'], true) . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ Query failed: " . htmlspecialchars($conn->error) . "</p>";
    }
    
    // Test 5: Full query with joins (like in reset_password.php)
    echo "<hr>";
    echo "<h3>Test 5: Full Query with Alias (reset_password.php style)</h3>";
    $test5 = "SELECT pr.StaffID, pr.Email, pr.ExpiresAt, pr.IsUsed FROM PasswordReset pr 
              WHERE pr.ResetCode = '$escaped_code' AND pr.IsUsed = 0 
              AND pr.ExpiresAt > NOW()";
    echo "<p><strong>Query:</strong> <code>" . htmlspecialchars($test5) . "</code></p>";
    $result5 = $conn->query($test5);
    if ($result5) {
        echo "<p style='color: green;'>✓ Query executed</p>";
        echo "<p>Rows returned: " . $result5->num_rows . "</p>";
        if ($result5->num_rows > 0) {
            echo "<p style='color: green;'>✓ FULL QUERY WORKS!</p>";
            $data = $result5->fetch_assoc();
            echo "<pre style='background: #ccffcc; padding: 10px; border-radius: 5px;'>";
            echo "StaffID: " . htmlspecialchars($data['StaffID']) . "\n";
            echo "Email: " . htmlspecialchars($data['Email']) . "\n";
            echo "ExpiresAt: " . htmlspecialchars($data['ExpiresAt']) . "\n";
            echo "IsUsed: " . htmlspecialchars($data['IsUsed']) . "\n";
            echo "</pre>";
        } else {
            echo "<p style='color: red;'>✗ Full query still fails</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Query failed: " . htmlspecialchars($conn->error) . "</p>";
    }
    
    // Summary
    echo "<hr>";
    echo "<h3>📋 Summary</h3>";
    echo "<p>If Test 5 shows ✓, then the reset_password.php query should work.</p>";
    echo "<p>Try going to <a href='reset_password.php' target='_blank'>reset_password.php</a> and pasting this code:</p>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 10px;'>";
    echo "<code style='font-size: 14px; word-break: break-all;'>" . htmlspecialchars($test_code) . "</code>";
    echo "</div>";
    
} else {
    echo "<p style='color: red;'>✗ No reset codes found in database</p>";
}

$conn->close();
?>
