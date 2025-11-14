<?php
session_start();
if (!isset($_SESSION['role'])) { header("Location: ../login.php"); exit(); }
include("db_connect.php");

$message = "";

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ProjectName = $_POST['ProjectName'];
    $Description = $_POST['Description'];
    $StartDate   = $_POST['StartDate'];
    $EndDate     = $_POST['EndDate'];
    $Budget      = $_POST['Budget'];
    $DonorID     = $_POST['DonorID'];
    $LocationID  = $_POST['LocationID'];

    $stmt = $conn->prepare("INSERT INTO projects 
        (ProjectName, Description, StartDate, EndDate, Budget, DonorID, LocationID) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssdis",
        $ProjectName, $Description, $StartDate, $EndDate, $Budget, $DonorID, $LocationID
    );

    if ($stmt->execute()) {
        $message = "Project added successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Project</title>
<style>
body {
    background: #f2f2f2;
    font-family: Arial, sans-serif;
}
.container {
    width: 45%;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
h2 { text-align: center; color: #333; }
label { font-weight: bold; }
input, textarea, select {
    width: 100%; padding: 10px;
    margin-top: 5px; margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
button {
    width: 100%; padding: 12px;
    background: green; border: none;
    color: white; font-size: 16px;
    border-radius: 5px; cursor: pointer;
}
button:hover { background: #0a6d0a; }
.msg {
    padding: 10px; text-align: center;
    background: #dff0d8; color: #3c763d;
    margin-bottom: 20px; border-radius: 5px;
}
</style>
</head>

<body>
<div class="container">

<h2>Add New Project</h2>

<?php if ($message != ""): ?>
    <div class="msg"><?= $message ?></div>
<?php endif; ?>

<form action="" method="POST">

    <label>Project Name</label>
    <input type="text" name="ProjectName" required>

    <label>Description</label>
    <textarea name="Description"></textarea>

    <label>Start Date</label>
    <input type="date" name="StartDate">

    <label>End Date</label>
    <input type="date" name="EndDate">

    <label>Budget</label>
    <input type="number" name="Budget" step="0.01">

    <label>Donor</label>
    <select name="DonorID" required>
        <option value="">Select Donor</option>
        <?php
        $d = $conn->query("SELECT DonorID, DonorName FROM donor");
        while ($row = $d->fetch_assoc()) {
            echo "<option value='{$row['DonorID']}'>{$row['DonorName']}</option>";
        }
        ?>
    </select>

    <label>Location</label>
    <select name="LocationID" required>
        <option value="">Select Location</option>
        <?php
        $l = $conn->query("SELECT LocationID, District, Region FROM location");
        while ($row = $l->fetch_assoc()) {
            echo "<option value='{$row['LocationID']}'>{$row['District']} - {$row['Region']}</option>";
        }
        ?>
    </select>

    <button type="submit">Save Project</button>
</form>

</div>
</body>
</html>
