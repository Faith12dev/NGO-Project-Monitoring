<?php
// Generate bcrypt hashes for demo123

$password = 'demo123';

// Generate hash with default cost (10)
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "<h2>Bcrypt Hash Generator</h2>";
echo "<hr>";
echo "<p><strong>Password:</strong> demo123</p>";
echo "<p><strong>Generated Hash:</strong></p>";
echo "<code style='background: #f0f0f0; padding: 10px; display: block; word-break: break-all; font-size: 14px;'>";
echo htmlspecialchars($hash);
echo "</code>";
echo "<p style='margin-top: 20px;'><strong>Copy the hash above and use it in the UPDATE statement below:</strong></p>";
echo "<code style='background: #ffe0e0; padding: 10px; display: block; word-break: break-all; margin-top: 10px;'>";
echo "UPDATE Staff SET Password = '" . htmlspecialchars($hash) . "' WHERE StaffID > 0;";
echo "</code>";

// Test the hash
echo "<hr>";
echo "<h3>Verification Test</h3>";
$test = password_verify($password, $hash);
echo "<p><strong>Testing password_verify('demo123', generated_hash):</strong> ";
if ($test) {
    echo "<span style='color: green; font-weight: bold;'>✓ SUCCESS - Hash works!</span>";
} else {
    echo "<span style='color: red; font-weight: bold;'>✗ FAILED</span>";
}
echo "</p>";
?>
