<?php
// Test file to verify the system is working
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== NGO System - Connection Test ===\n\n";

// Test 1: Config file
echo "Test 1: Loading config.php...\n";
if (file_exists(__DIR__ . '/app/includes/config.php')) {
    require_once __DIR__ . '/app/includes/config.php';
    echo "✅ Config.php loaded successfully\n";
    
    // Test database connection
    if ($conn->connect_error) {
        echo "❌ Database connection failed: " . $conn->connect_error . "\n";
    } else {
        echo "✅ Database connected successfully\n";
        
        // Test tables
        $result = $conn->query("SHOW TABLES");
        echo "✅ Database tables: " . $result->num_rows . " tables found\n";
        while ($row = $result->fetch_row()) {
            echo "   - " . $row[0] . "\n";
        }
    }
} else {
    echo "❌ Config.php not found\n";
}

echo "\n";
echo "Test 2: Checking auth functions...\n";
if (file_exists(__DIR__ . '/app/includes/auth.php')) {
    require_once __DIR__ . '/app/includes/auth.php';
    if (function_exists('isLoggedIn')) {
        echo "✅ Auth functions loaded\n";
    } else {
        echo "❌ Auth functions not found in auth.php\n";
    }
} else {
    echo "❌ auth.php file not found\n";
}

echo "\n";
echo "Test 3: Session test...\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Session started\n";
} else {
    echo "⚠️  Session not started\n";
}

echo "\n";
echo "=== All Tests Complete ===\n";
?>
