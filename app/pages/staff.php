<?php
// Process all PHP logic BEFORE including header
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['fullName'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($fullName && $email && $role && $password) {
        // Validate password
        if (strlen($password) < 8) {
            setFlashMessage('error', 'Password must be at least 8 characters long!');
        } else {
            // Check if email already exists
            $checkEmail = $conn->query("SELECT * FROM Staff WHERE Email = '$email'");
            if ($checkEmail->num_rows > 0) {
                setFlashMessage('error', 'Email already exists!');
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                $query = "INSERT INTO Staff (FullName, Email, Phone, Role, Gender, Password) 
                          VALUES ('$fullName', '$email', '$phone', '$role', '$gender', '$hashedPassword')";
                
                if ($conn->query($query)) {
                    $newID = $conn->insert_id;
                    $newValue = json_encode(['FullName' => $fullName, 'Email' => $email, 'Phone' => $phone, 'Role' => $role, 'Gender' => $gender]);
                    logAudit('CREATE', 'Staff', $newID, $fullName, '', $newValue);
                    setFlashMessage('success', 'Staff member added successfully!');
                    header('Location: staff.php');
                    exit;
                } else {
                    setFlashMessage('error', 'Error adding staff member.');
                }
            }
        }
    } else {
        if (!$password) {
            setFlashMessage('error', 'Password is required!');
        } else {
            setFlashMessage('error', 'Please fill in all required fields!');
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $staffID = (int)$_GET['delete'];
    $staff = getStaffByID($staffID);
    if ($conn->query("DELETE FROM Staff WHERE StaffID = $staffID")) {
        $deletedValue = json_encode($staff);
        logAudit('DELETE', 'Staff', $staffID, $staff['FullName'], $deletedValue, '');
        setFlashMessage('success', 'Staff member deleted successfully!');
    } else {
        setFlashMessage('error', 'Error deleting staff member.');
    }
    header('Location: staff.php');
    exit;
}

// Get staff data
$page_title = 'Staff';
$staff = getAllStaff();

// Now include the header (after all logic is done)
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-user-tie me-2"></i>Staff</h1>
            <p>Manage organization staff and team members</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staffModal">
            <i class="fas fa-plus me-2"></i>Add Staff Member
        </button>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Staff Summary -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value"><?php echo count($staff); ?></div>
            <div class="stat-label">Total Staff Members</div>
        </div>
    </div>
</div>

<!-- Staff List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span>All Staff Members</span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search staff..." style="width: 250px;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="staffTable">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Gender</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff as $member): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($member['FullName']); ?></strong></td>
                        <td><?php echo htmlspecialchars($member['Email']); ?></td>
                        <td><?php echo htmlspecialchars($member['Phone'] ?? 'N/A'); ?></td>
                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($member['Role']); ?></span></td>
                        <td><?php echo htmlspecialchars($member['Gender'] ?? 'N/A'); ?></td>
                        <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $member['StaffID']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="handleDeleteStaff(<?php echo $member['StaffID']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- View Modal -->
                    <div class="modal fade" id="viewModal<?php echo $member['StaffID']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?php echo htmlspecialchars($member['FullName']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($member['Email']); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($member['Phone'] ?? 'N/A'); ?></p>
                                    <p><strong>Role:</strong> <?php echo htmlspecialchars($member['Role']); ?></p>
                                    <p><strong>Gender:</strong> <?php echo htmlspecialchars($member['Gender'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="staffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Staff Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="fullName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <select name="role" class="form-select" required>
                            <option value="">-- Select Role --</option>
                            <option value="admin">Administrator</option>
                            <option value="project_manager">Project Manager</option>
                            <option value="field_officer">Field Officer</option>
                            <option value="donor_liaison">Donor Liaison Officer</option>
                            <option value="accountant">Accountant</option>
                            <option value="supervisor">Supervisor</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">-- Select Gender --</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required placeholder="At least 8 characters">
                        <small class="text-muted">Min 8 characters, include uppercase, lowercase, numbers, and special characters</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Staff Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        filterTable('searchInput', 'staffTable');
    });

    function handleDeleteStaff(staffID) {
        if (confirm('Delete this staff member?')) {
            window.location.href = 'staff.php?delete=' + staffID;
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
