<?php
session_start();
include('db_connect.php');

// (Optional) Only allow certain roles to access registration
// if ($_SESSION['role'] !== 'Project Manager') { header("Location: login.php"); exit(); }

if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $gender = $_POST['gender'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "❌ Passwords do not match!";
    } else {
        // Hash the password before saving
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $checkEmail = "SELECT * FROM Staff WHERE Email='$email'";
        $res = $conn->query($checkEmail);
        if ($res->num_rows > 0) {
            $message = "⚠️ Email already exists!";
        } else {
            $query = "INSERT INTO Staff (FullName, Email, Password, Phone, Role, Gender)
                      VALUES ('$fullname', '$email', '$hashedPassword', '$phone', '$role', '$gender')";
            if ($conn->query($query)) {
                $message = "✅ Staff registered successfully!";
            } else {
                $message = "❌ Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Staff</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="login-box">
    <h2>Register New Staff</h2>
    <?php if (!empty($message)) echo "<p class='error'>$message</p>"; ?>
    <form method="post">
        <input type="text" name="fullname" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="text" name="phone" placeholder="Phone"><br>
        <select name="role" required>
            <option value="">Select Role</option>
            <option>Project Manager</option>
            <option>Accountant</option>
            <option>Donor Liaison Officer</option>
            <option>Supervisor</option>
            <option>Field Officer</option>
        </select><br>
        <select name="gender" required>
            <option value="">Select Gender</option>
            <option>Male</option>
            <option>Female</option>
        </select><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="password" name="confirm" placeholder="Confirm Password" required><br>
        <button type="submit" name="register">Register</button>
        <p><a href="login.php">Back to Login</a></p>
    </form>
</div>
</body>
</html>
