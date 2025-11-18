<?php
require_once __DIR__ . '/config.php';

// Sanitize input
function sanitize($input) {
    global $conn;
    return htmlspecialchars($conn->real_escape_string(trim($input)));
}

// Format date
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

// Format currency
function formatCurrency($amount) {
    return 'UGX ' . number_format($amount, 2);
}

// Get staff member by ID
function getStaffByID($id) {
    global $conn;
    $id = (int)$id;
    $result = $conn->query("SELECT * FROM Staff WHERE StaffID = $id");
    return $result->fetch_assoc();
}

// Get all donors
function getAllDonors() {
    global $conn;
    $result = $conn->query("SELECT * FROM Donor ORDER BY DonorName ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get donor by ID
function getDonorByID($id) {
    global $conn;
    $id = (int)$id;
    $result = $conn->query("SELECT * FROM Donor WHERE DonorID = $id");
    return $result->fetch_assoc();
}

// Get all locations
function getAllLocations() {
    global $conn;
    $result = $conn->query("SELECT * FROM Location ORDER BY District ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get location by ID
function getLocationByID($id) {
    global $conn;
    $id = (int)$id;
    $result = $conn->query("SELECT * FROM Location WHERE LocationID = $id");
    return $result->fetch_assoc();
}

// Get all projects
function getAllProjects() {
    global $conn;
    $result = $conn->query("
        SELECT p.*, d.DonorName, l.District, l.Region 
        FROM Projects p 
        LEFT JOIN Donor d ON p.DonorID = d.DonorID 
        LEFT JOIN Location l ON p.LocationID = l.LocationID 
        ORDER BY p.StartDate DESC
    ");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get project by ID
function getProjectByID($id) {
    global $conn;
    $id = (int)$id;
    $result = $conn->query("
        SELECT p.*, d.DonorName, l.District, l.Region, l.Country
        FROM Projects p 
        LEFT JOIN Donor d ON p.DonorID = d.DonorID 
        LEFT JOIN Location l ON p.LocationID = l.LocationID 
        WHERE p.ProjectID = $id
    ");
    return $result->fetch_assoc();
}

// Get all beneficiaries
function getAllBeneficiaries() {
    global $conn;
    $result = $conn->query("
        SELECT b.*, p.ProjectName 
        FROM Beneficiary b 
        LEFT JOIN Projects p ON b.ProjectID = p.ProjectID 
        ORDER BY b.BeneficiaryName ASC
    ");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get beneficiaries by project
function getBeneficiariesByProject($projectID) {
    global $conn;
    $projectID = (int)$projectID;
    $result = $conn->query("
        SELECT * FROM Beneficiary 
        WHERE ProjectID = $projectID 
        ORDER BY BeneficiaryName ASC
    ");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all expenditures
function getAllExpenditures() {
    global $conn;
    $result = $conn->query("
        SELECT e.*, p.ProjectName 
        FROM Expenditure e 
        LEFT JOIN Projects p ON e.ProjectID = p.ProjectID 
        ORDER BY e.Date DESC
    ");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get expenditures by project
function getExpendituresByProject($projectID) {
    global $conn;
    $projectID = (int)$projectID;
    $result = $conn->query("
        SELECT * FROM Expenditure 
        WHERE ProjectID = $projectID 
        ORDER BY Date DESC
    ");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all outcomes
function getAllOutcomes() {
    global $conn;
    $result = $conn->query("
        SELECT o.*, p.ProjectName 
        FROM Outcome o 
        LEFT JOIN Projects p ON o.ProjectID = p.ProjectID 
        ORDER BY o.ReportDate DESC
    ");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get outcomes by project
function getOutcomesByProject($projectID) {
    global $conn;
    $projectID = (int)$projectID;
    $result = $conn->query("
        SELECT * FROM Outcome 
        WHERE ProjectID = $projectID 
        ORDER BY ReportDate DESC
    ");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all staff
function getAllStaff() {
    global $conn;
    $result = $conn->query("SELECT * FROM Staff ORDER BY FullName ASC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get project statistics
function getProjectStats() {
    global $conn;
    
    $stats = [];
    
    // Total projects
    $result = $conn->query("SELECT COUNT(*) as count FROM Projects");
    $stats['total_projects'] = $result->fetch_assoc()['count'];
    
    // Total budget
    $result = $conn->query("SELECT SUM(Budget) as total FROM Projects");
    $row = $result->fetch_assoc();
    $stats['total_budget'] = $row['total'] ?: 0;
    
    // Total spent
    $result = $conn->query("SELECT SUM(AmountSpent) as total FROM Expenditure");
    $row = $result->fetch_assoc();
    $stats['total_spent'] = $row['total'] ?: 0;
    
    // Total beneficiaries
    $result = $conn->query("SELECT SUM(NoOfPeople) as total FROM Beneficiary");
    $row = $result->fetch_assoc();
    $stats['total_beneficiaries'] = $row['total'] ?: 0;
    
    // Total donors
    $result = $conn->query("SELECT COUNT(*) as count FROM Donor");
    $stats['total_donors'] = $result->fetch_assoc()['count'];
    
    return $stats;
}

// Flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Render flash message
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = $flash['type'] === 'success' ? 'success' : 'error';
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($flash['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

// Log audit trail (who edited/deleted records)
function logAudit($action, $tableName, $recordID, $recordName, $oldValue = null, $newValue = null) {
    global $conn;
    
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $staffID = (int)$_SESSION['user_id'];
    $staffEmail = $conn->real_escape_string($_SESSION['email'] ?? 'Unknown');
    $action = $conn->real_escape_string($action); // CREATE, UPDATE, DELETE
    $tableName = $conn->real_escape_string($tableName);
    $recordID = (int)$recordID;
    $recordName = $conn->real_escape_string($recordName ?? '');
    $oldValue = $conn->real_escape_string($oldValue ?? '');
    $newValue = $conn->real_escape_string($newValue ?? '');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    
    $query = "INSERT INTO AuditLog (StaffID, StaffEmail, Action, TableName, RecordID, RecordName, OldValue, NewValue, IPAddress, UserAgent) 
              VALUES ($staffID, '$staffEmail', '$action', '$tableName', $recordID, '$recordName', '$oldValue', '$newValue', '$ipAddress', '$userAgent')";
    
    return $conn->query($query);
}

// Get audit logs with pagination
function getAuditLogs($limit = 50, $offset = 0) {
    global $conn;
    $limit = (int)$limit;
    $offset = (int)$offset;
    $result = $conn->query("SELECT * FROM AuditLog ORDER BY ActionTime DESC LIMIT $offset, $limit");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Count total audit logs
function countAuditLogs() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) as total FROM AuditLog");
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Get audit logs filtered by staff
function getAuditLogsByStaff($staffID, $limit = 50, $offset = 0) {
    global $conn;
    $staffID = (int)$staffID;
    $limit = (int)$limit;
    $offset = (int)$offset;
    $result = $conn->query("SELECT * FROM AuditLog WHERE StaffID = $staffID ORDER BY ActionTime DESC LIMIT $offset, $limit");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get audit logs filtered by table
function getAuditLogsByTable($tableName, $limit = 50, $offset = 0) {
    global $conn;
    $tableName = $conn->real_escape_string($tableName);
    $limit = (int)$limit;
    $offset = (int)$offset;
    $result = $conn->query("SELECT * FROM AuditLog WHERE TableName = '$tableName' ORDER BY ActionTime DESC LIMIT $offset, $limit");
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>
