<?php
// Generate unique passwords and bcrypt hashes for each staff member

$staff_credentials = [
    [
        'name' => 'John Doe',
        'email' => 'john@ngo.com',
        'role' => 'admin',
        'password' => 'Admin@2024!secure'
    ],
    [
        'name' => 'Jane Smith',
        'email' => 'jane@ngo.com',
        'role' => 'project_manager',
        'password' => 'ProjMgr@2024!secure'
    ],
    [
        'name' => 'Peter Johnson',
        'email' => 'peter@ngo.com',
        'role' => 'field_officer',
        'password' => 'FieldOff@2024!secure'
    ],
    [
        'name' => 'Mary Wilson',
        'email' => 'mary@ngo.com',
        'role' => 'donor_liaison',
        'password' => 'DonorLia@2024!secure'
    ],
    [
        'name' => 'David Brown',
        'email' => 'david@ngo.com',
        'role' => 'accountant',
        'password' => 'Acct@2024!secure'
    ],
    [
        'name' => 'Sarah Davis',
        'email' => 'sarah@ngo.com',
        'role' => 'supervisor',
        'password' => 'Supervisor@2024!secure'
    ]
];

echo "<h2>Staff Password Generation</h2>";
echo "<hr>";
echo "<table style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
echo "<tr style='background: #f0f0f0;'>";
echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Name</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Email</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Role</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Password</th>";
echo "<th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Bcrypt Hash</th>";
echo "</tr>";

$update_statements = [];

foreach ($staff_credentials as $staff) {
    $hash = password_hash($staff['password'], PASSWORD_BCRYPT);
    $update_statements[] = "UPDATE Staff SET Password = '{$hash}' WHERE Email = '{$staff['email']}' AND Role = '{$staff['role']}';";
    
    echo "<tr>";
    echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($staff['name']) . "</td>";
    echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($staff['email']) . "</td>";
    echo "<td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($staff['role']) . "</td>";
    echo "<td style='border: 1px solid #ddd; padding: 10px; background: #fff3cd;'><strong>" . htmlspecialchars($staff['password']) . "</strong></td>";
    echo "<td style='border: 1px solid #ddd; padding: 10px; font-size: 11px; word-break: break-all; background: #f9f9f9;'><code>" . htmlspecialchars($hash) . "</code></td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>MySQL Update Statements</h3>";
echo "<p>Run these commands in MySQL Workbench to update all staff passwords:</p>";
echo "<div style='background: #ffe0e0; padding: 15px; border-radius: 5px; margin-top: 10px;'>";
echo "<code style='display: block; word-break: break-all; line-height: 1.8;'>";
echo "USE NGO;<br>";
foreach ($update_statements as $stmt) {
    echo htmlspecialchars($stmt) . "<br>";
}
echo "</code>";
echo "</div>";

echo "<hr>";
echo "<h3>Login Credentials Reference</h3>";
echo "<p style='background: #e7f3ff; padding: 10px; border-left: 4px solid #0066cc;'>";
echo "<strong>Save these credentials securely for testing:</strong><br>";
foreach ($staff_credentials as $staff) {
    echo "Email: <code>" . htmlspecialchars($staff['email']) . "</code> | Password: <code>" . htmlspecialchars($staff['password']) . "</code> | Role: <code>" . htmlspecialchars($staff['role']) . "</code><br>";
}
echo "</p>";

// Test verification
echo "<hr>";
echo "<h3>Password Verification Test</h3>";
$test_email = 'john@ngo.com';
$test_password = 'Admin@2024!secure';
$test_hash = password_hash($test_password, PASSWORD_BCRYPT);

if (password_verify($test_password, $test_hash)) {
    echo "<p style='color: green;'><strong>✓ Password verification working correctly</strong></p>";
} else {
    echo "<p style='color: red;'><strong>✗ Password verification failed</strong></p>";
}

?>
