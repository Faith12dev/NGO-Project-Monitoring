<?php
require_once __DIR__ . '/app/includes/auth.php';
require_once __DIR__ . '/app/includes/functions.php';
require_once __DIR__ . '/app/includes/config.php';

requireLogin();

// Simulate a donor ID for testing
$testDonorID = 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Button Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div style="padding: 20px;">
    <h1>Delete Button Test</h1>
    
    <h2>Raw Button HTML:</h2>
    <pre><?php 
$buttonHTML = '<button class="btn btn-sm btn-danger" onclick="if(confirmDelete(\'Delete this donor?\')) window.location=\'donors.php?delete=' . $testDonorID . '\';">
    <i class="fas fa-trash"></i>
</button>';
echo htmlspecialchars($buttonHTML);
    ?></pre>

    <h2>Rendered Button:</h2>
    <button class="btn btn-sm btn-danger" onclick="if(confirmDelete('Delete this donor?')) window.location='donors.php?delete=<?php echo $testDonorID; ?>';">
        <i class="fas fa-trash"></i> Delete
    </button>
    
    <h2>Test confirmDelete directly:</h2>
    <button class="btn btn-primary" onclick="testDelete();">Test confirmDelete Function</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/main.js?v=<?php echo time(); ?>"></script>
<script>
    function testDelete() {
        console.log('Testing confirmDelete...');
        console.log('typeof confirmDelete:', typeof confirmDelete);
        console.log('confirmDelete function:', confirmDelete);
        
        if (typeof confirmDelete === 'function') {
            alert('✓ confirmDelete function exists!');
            if (confirmDelete('Test delete?')) {
                alert('User confirmed delete');
            } else {
                alert('User cancelled delete');
            }
        } else {
            alert('✗ ERROR: confirmDelete function not found!');
            console.error('confirmDelete is not a function');
            console.log('Available functions:', Object.keys(window).filter(k => typeof window[k] === 'function').slice(0, 20));
        }
    }
    
    // Log on page load
    window.addEventListener('load', function() {
        console.log('Page loaded');
        console.log('confirmDelete available:', typeof confirmDelete !== 'undefined');
    });
</script>
</body>
</html>
