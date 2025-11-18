<?php
require_once __DIR__ . '/app/includes/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test JS Loading</title>
</head>
<body>
    <h1>Testing JavaScript Loading</h1>
    <button onclick="if(confirmDelete('Test?')) alert('Confirmed!');"> Test Delete Button</button>
    
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <script>
        console.log('confirmDelete type:', typeof confirmDelete);
        console.log('confirmDelete:', confirmDelete);
        console.log('window.confirmDelete:', window.confirmDelete);
    </script>
</body>
</html>
