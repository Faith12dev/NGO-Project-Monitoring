<?php
// Run this script once to create the AuditLog table if it doesn't exist
require_once __DIR__ . '/app/includes/config.php';

echo "<h2>Database Audit Table Setup</h2>";
echo "<hr>";

$sql = "CREATE TABLE IF NOT EXISTS AuditLog (
    AuditID INT AUTO_INCREMENT PRIMARY KEY,
    StaffID INT NOT NULL,
    StaffEmail VARCHAR(100) NOT NULL,
    Action VARCHAR(50) NOT NULL,
    TableName VARCHAR(100) NOT NULL,
    RecordID INT NOT NULL,
    RecordName VARCHAR(255),
    OldValue LONGTEXT,
    NewValue LONGTEXT,
    IPAddress VARCHAR(50),
    UserAgent VARCHAR(255),
    ActionTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StaffID) REFERENCES Staff(StaffID) ON DELETE CASCADE,
    INDEX idx_staff (StaffID),
    INDEX idx_action (Action),
    INDEX idx_table (TableName),
    INDEX idx_time (ActionTime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql)) {
    echo "<p style='color: green; font-weight: bold;'>✓ AuditLog table created successfully!</p>";
} else {
    if (strpos($conn->error, 'already exists') !== false) {
        echo "<p style='color: blue; font-weight: bold;'>ℹ AuditLog table already exists.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ Error creating AuditLog table:</p>";
        echo "<p>" . htmlspecialchars($conn->error) . "</p>";
    }
}

echo "<hr>";
echo "<h3>✅ Audit System Ready!</h3>";
echo "<p>All changes to Donors, Locations, and Beneficiaries will now be logged automatically.</p>";
echo "<p>Admin users can view audit logs at: <strong>Audit Logs</strong> menu item.</p>";

$conn->close();
?>
