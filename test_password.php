<?php
require_once __DIR__ . '/app/includes/config.php';

// Test password
$test_password = 'demo123';
$test_email = 'john@ngo.com';
$test_role = 'admin';

echo "<h2>Password Verification Debug</h2>";
echo "<hr>";

// Query the database
$query = "SELECT StaffID, Email, Role, Password FROM Staff WHERE Email = '$test_email' AND Role = '$test_role'";
echo "<p><strong>Query:</strong> " . htmlspecialchars($query) . "</p>";

$result = $conn->query($query);

if (!$result) {
    echo "<p style='color: red;'><strong>Database Error:</strong> " . $conn->error . "</p>";
    exit;
}

if ($result->num_rows === 0) {
    echo "<p style='color: red;'><strong>Error:</strong> User not found in database</p>";
    exit;
}

$user = $result->fetch_assoc();
echo "<p><strong>Found User:</strong> " . htmlspecialchars($user['Email']) . " (" . htmlspecialchars($user['Role']) . ")</p>";

// Show the stored password hash
$stored_hash = $user['Password'];
echo "<p><strong>Stored Hash:</strong> <code>" . htmlspecialchars($stored_hash) . "</code></p>";
echo "<p><strong>Hash Length:</strong> " . strlen($stored_hash) . " characters</p>";

// Test password_verify
$verify_result = password_verify($test_password, $stored_hash);
echo "<p><strong>Testing password_verify('{$test_password}', hash):</strong> ";
if ($verify_result) {
    echo "<span style='color: green; font-weight: bold;'>✓ SUCCESS - Password matches!</span>";
} else {
    echo "<span style='color: red; font-weight: bold;'>✗ FAILED - Password does not match</span>";
}
echo "</p>";

// Let's also test with a known hash
echo "<hr>";
echo "<h3>Testing with Known Hash</h3>";
$known_hash = '$2y$10$N9qo8uLOickgx2ZMRZoXyejMHVmHaJlJUlWZ0XMrp2H8qVYV1Z1Ei';
echo "<p><strong>Known Hash (for demo123):</strong> <code>" . htmlspecialchars($known_hash) . "</code></p>";
$known_verify = password_verify($test_password, $known_hash);
echo "<p><strong>Testing known hash:</strong> ";
if ($known_verify) {
    echo "<span style='color: green; font-weight: bold;'>✓ SUCCESS - Password matches!</span>";
} else {
    echo "<span style='color: red; font-weight: bold;'>✗ FAILED - Password does not match</span>";
}
echo "</p>";

// Compare hashes
echo "<hr>";
echo "<h3>Hash Comparison</h3>";
echo "<p><strong>Stored Hash Matches Known Hash:</strong> ";
if ($stored_hash === $known_hash) {
    echo "<span style='color: green;'>✓ YES - They are identical</span>";
} else {
    echo "<span style='color: red;'>✗ NO - They are different</span>";
    echo "<p style='background: #f0f0f0; padding: 10px; margin-top: 10px;'>";
    echo "Stored: <code>" . htmlspecialchars($stored_hash) . "</code><br>";
    echo "Known:  <code>" . htmlspecialchars($known_hash) . "</code>";
    echo "</p>";
}
echo "</p>";

$conn->close();
?>
