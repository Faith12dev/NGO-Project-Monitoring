<?php
require_once __DIR__ . '/app/includes/config.php';

echo "<h2>🔍 Staff Database Verification & Fix</h2>";
echo "<hr>";

// Check all staff
echo "<h3>Current Staff in Database</h3>";
$staff_query = "SELECT StaffID, FullName, Email, Role FROM Staff ORDER BY Email";
$staff_result = $conn->query($staff_query);

echo "<table style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>StaffID</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>Full Name</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>Email</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>Role</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>Status</th>";
echo "</tr>";

$staff_data = [];
while ($staff = $staff_result->fetch_assoc()) {
    $staff_data[] = $staff;
    $status = "✓ Found";
    $color = "green";
    
    echo "<tr>";
    echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($staff['StaffID']) . "</td>";
    echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($staff['FullName']) . "</td>";
    echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($staff['Email']) . "</td>";
    echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($staff['Role']) . "</td>";
    echo "<td style='border: 1px solid #ddd; padding: 10px; color: " . $color . ";'>" . $status . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test password reset for each staff
echo "<hr>";
echo "<h3>Test Password Reset Request for Each Staff</h3>";

$expected_staff = [
    ['email' => 'mushabedavid2002@gmail.com', 'role' => 'admin', 'name' => 'Admin User'],
    ['email' => 'jane@ngo.com', 'role' => 'project_manager', 'name' => 'Jane Smith'],
    ['email' => 'peter@ngo.com', 'role' => 'field_officer', 'name' => 'Peter Johnson'],
    ['email' => 'mary@ngo.com', 'role' => 'donor_liaison', 'name' => 'Mary Williams'],
    ['email' => 'david@ngo.com', 'role' => 'accountant', 'name' => 'David Brown'],
    ['email' => 'sarah@ngo.com', 'role' => 'supervisor', 'name' => 'Sarah Wilson'],
];

echo "<table style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>Expected Email</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>Role</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>Exists?</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px;'>Can Reset?</th>";
echo "</tr>";

foreach ($expected_staff as $exp) {
    $email = $exp['email'];
    $role = $exp['role'];
    
    // Check if exists
    $check_query = "SELECT StaffID FROM Staff WHERE Email = '$email' AND Role = '$role'";
    $check_result = $conn->query($check_query);
    $exists = ($check_result && $check_result->num_rows > 0) ? '✓ YES' : '✗ NO';
    $exists_color = strpos($exists, 'YES') !== false ? 'green' : 'red';
    
    // Try to generate reset code
    if ($check_result && $check_result->num_rows > 0) {
        $staff = $check_result->fetch_assoc();
        $test_code = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $insert = "INSERT INTO PasswordReset (StaffID, ResetCode, Email, ExpiresAt, IsUsed) 
                   VALUES (" . $staff['StaffID'] . ", '$test_code', '$email', '$expires', 0)";
        
        $insert_result = $conn->query($insert);
        $can_reset = $insert_result ? '✓ YES' : '✗ NO';
        $reset_color = $insert_result ? 'green' : 'red';
    } else {
        $can_reset = '✗ NO';
        $reset_color = 'red';
    }
    
    echo "<tr>";
    echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($email) . "</td>";
    echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($role) . "</td>";
    echo "<td style='border: 1px solid #ddd; padding: 10px; color: " . $exists_color . ";'>" . $exists . "</td>";
    echo "<td style='border: 1px solid #ddd; padding: 10px; color: " . $reset_color . ";'>" . $can_reset . "</td>";
    echo "</tr>";
}
echo "</table>";

// If any are missing, show the fix
echo "<hr>";
echo "<h3>🔧 If Other Staff Can't Reset Password</h3>";

$missing = false;
foreach ($expected_staff as $exp) {
    $check = $conn->query("SELECT StaffID FROM Staff WHERE Email = '{$exp['email']}' AND Role = '{$exp['role']}'");
    if (!$check || $check->num_rows === 0) {
        $missing = true;
        break;
    }
}

if ($missing) {
    echo "<p style='color: red; font-weight: bold;'>⚠️ Some staff members are missing or have incorrect roles!</p>";
    echo "<p>Run this SQL in MySQL Workbench to fix:</p>";
    echo "<div style='background: #ffe0e0; padding: 15px; border-radius: 5px; margin-top: 10px;'>";
    echo "<code style='display: block; word-break: break-all; line-height: 1.8;'>";
    echo "USE NGO;<br>";
    echo "DELETE FROM PasswordReset;<br>";
    echo "DELETE FROM Staff;<br>";
    echo "<br>";
    echo "INSERT INTO Staff (FullName, Email, Phone, Role, Gender, Password) VALUES<br>";
    echo "('Admin User', 'mushabedavid2002@gmail.com', '+254712345678', 'admin', 'Male', '\$2y\$10\$8ZgjPwFq/hgrKHN8m0V7.evCUE0DJZFjLcnF7LWN7m3Q9C.2V2eim'),<br>";
    echo "('Jane Smith', 'jane@ngo.com', '+254787654321', 'project_manager', 'Female', '\$2y\$10\$v9DreoyWbe1RSKbfUSATz.oKPE4VpH3P7V2u8BG7HvQ4x8T4S0rPy'),<br>";
    echo "('Peter Johnson', 'peter@ngo.com', '+254712111111', 'field_officer', 'Male', '\$2y\$10\$SPQ5VMFqVzvgUV1pS2Z7BOwEZyP9RQMZvKQ9E8Mc.4Lm8X3X0jqAu'),<br>";
    echo "('Mary Williams', 'mary@ngo.com', '+254787222222', 'donor_liaison', 'Female', '\$2y\$10\$JZ07cHazS3GeoBrLKQfyR.QcJqFPRE5F0hJp8mPJ8DnnFLEjwSIFm'),<br>";
    echo "('David Brown', 'david@ngo.com', '+254712333333', 'accountant', 'Male', '\$2y\$10\$owgV8HXLYITco0.lq4pU2.gZ5D8bDnVPYEr3N5Oqj.3a1xKX1/y4W'),<br>";
    echo "('Sarah Wilson', 'sarah@ngo.com', '+254712444444', 'supervisor', 'Female', '\$2y\$10\$HcLIO5FjRiHuyV5Ld2DwC.7Q8W8dv3EJ9ZK4v9A2iLN0f6VPJvvKC');<br>";
    echo "</code>";
    echo "</div>";
} else {
    echo "<p style='color: green; font-weight: bold;'>✓ All staff members are correctly configured!</p>";
    echo "<p>They should all be able to use the password reset feature.</p>";
}

// Test links for each staff
echo "<hr>";
echo "<h3>✅ Test Reset Password Links</h3>";
echo "<p>Click each link to test the password reset for that staff member:</p>";
echo "<ul>";
foreach ($expected_staff as $exp) {
    $check = $conn->query("SELECT StaffID FROM Staff WHERE Email = '{$exp['email']}' AND Role = '{$exp['role']}'");
    if ($check && $check->num_rows > 0) {
        $role_display = ucfirst(str_replace('_', ' ', $exp['role']));
        echo "<li>";
        echo "<strong>" . htmlspecialchars($role_display) . ":</strong> " . htmlspecialchars($exp['email']) . " - ";
        echo "<a href='generate_reset_code.php' target='_blank' style='color: #0066cc; text-decoration: none;'>→ Generate Reset Code</a>";
        echo "</li>";
    }
}
echo "</ul>";

$conn->close();
?>
