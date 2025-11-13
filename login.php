<?php
session_start();
include('db_connect.php'); // make sure this file connects to your Workbench database

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Secure query
    $stmt = $conn->prepare("SELECT * FROM Staff WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Compare plain password for now (later we use password_hash)
        if ($password === $row['Password']) {
            $_SESSION['role'] = $row['Role'];
            $_SESSION['fullname'] = $row['FullName'];

            // Redirect based on role
            switch ($row['Role']) {
                case 'Project Manager':
                    header("Location: project_manager_dashboard.php");
                    break;
                case 'Accountant':
                    header("Location: accountant_dashboard.php");
                    break;
                case 'Donor Liaison Officer':
                    header("Location: donor_dashboard.php");
                    break;
                case 'Supervisor':
                    header("Location: supervisor_dashboard.php");
                    break;
                case 'Field Officer':
                    header("Location: field_dashboard.php");
                    break;
                default:
                    header("Location: dashboard.php");
                    break;
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>NGO Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-box">
        <h2>NGO Project Monitoring System</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>
