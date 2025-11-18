<?php
// Process all PHP logic BEFORE including header
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAnyRole(['admin', 'project_manager', 'donor_liaison']);

$donors = getAllDonors();
$editDonor = null;

// Handle form submission (Add or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donorID = (int)($_POST['donorID'] ?? 0);
    $donorName = sanitize($_POST['donorName'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $country = sanitize($_POST['country'] ?? '');

    if ($donorName) {
        if ($donorID > 0) {
            // UPDATE existing donor
            $oldDonor = getDonorByID($donorID);
            $query = "UPDATE Donor SET DonorName = '$donorName', Email = '$email', Phonenumber = '$phone', 
                      Address = '$address', Country = '$country', UpdatedAt = NOW() WHERE DonorID = $donorID";
            
            if ($conn->query($query)) {
                $oldValue = json_encode($oldDonor);
                $newValue = json_encode(['DonorName' => $donorName, 'Email' => $email, 'Phonenumber' => $phone, 'Address' => $address, 'Country' => $country]);
                logAudit('UPDATE', 'Donor', $donorID, $donorName, $oldValue, $newValue);
                setFlashMessage('success', 'Donor updated successfully!');
                header('Location: donors.php');
                exit;
            } else {
                setFlashMessage('error', 'Error updating donor.');
            }
        } else {
            // INSERT new donor
            $query = "INSERT INTO Donor (DonorName, Email, Phonenumber, Address, Country) 
                      VALUES ('$donorName', '$email', '$phone', '$address', '$country')";
            
            if ($conn->query($query)) {
                $newID = $conn->insert_id;
                $newValue = json_encode(['DonorName' => $donorName, 'Email' => $email, 'Phonenumber' => $phone, 'Address' => $address, 'Country' => $country]);
                logAudit('CREATE', 'Donor', $newID, $donorName, '', $newValue);
                setFlashMessage('success', 'Donor added successfully!');
                header('Location: donors.php');
                exit;
            } else {
                setFlashMessage('error', 'Error adding donor.');
            }
        }
    }
}

// Load donor data if editing
if (isset($_GET['edit'])) {
    $editID = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM Donor WHERE DonorID = $editID");
    if ($result && $result->num_rows > 0) {
        $editDonor = $result->fetch_assoc();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $donorID = (int)$_GET['delete'];
    $donor = getDonorByID($donorID);
    if ($conn->query("DELETE FROM Donor WHERE DonorID = $donorID")) {
        $deletedValue = json_encode($donor);
        logAudit('DELETE', 'Donor', $donorID, $donor['DonorName'], $deletedValue, '');
        setFlashMessage('success', 'Donor deleted successfully!');
    } else {
        setFlashMessage('error', 'Error deleting donor.');
    }
    header('Location: donors.php');
    exit;
}

// Now include the header (after all logic is done)
$page_title = 'Donors';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-handshake me-2"></i>Donors</h1>
            <p>Manage donor information and relationships</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#donorModal">
            <i class="fas fa-plus me-2"></i>New Donor
        </button>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Donors List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span>All Donors</span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search donors..." style="width: 250px;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="donorsTable">
            <thead>
                <tr>
                    <th>Donor Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Country</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($donors as $donor): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($donor['DonorName']); ?></strong></td>
                        <td><?php echo htmlspecialchars($donor['Email'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($donor['Phonenumber'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($donor['Country'] ?? 'N/A'); ?></td>
                        <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $donor['DonorID']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="editDonor(<?php echo $donor['DonorID']; ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="handleDeleteDonor(<?php echo $donor['DonorID']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- View Modal -->
                    <div class="modal fade" id="viewModal<?php echo $donor['DonorID']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?php echo htmlspecialchars($donor['DonorName']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($donor['Email'] ?? 'N/A'); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($donor['Phonenumber'] ?? 'N/A'); ?></p>
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($donor['Address'] ?? 'N/A'); ?></p>
                                    <p><strong>Country:</strong> <?php echo htmlspecialchars($donor['Country'] ?? 'N/A'); ?></p>
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

<!-- Add/Edit Donor Modal -->
<div class="modal fade" id="donorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="donorModalTitle">Add New Donor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="donorID" id="donorID" value="0">
                    
                    <div class="form-group">
                        <label class="form-label">Donor Name *</label>
                        <input type="text" name="donorName" id="donorName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="donorEmail" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" id="donorPhone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea name="address" id="donorAddress" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" id="donorCountry" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Donor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        filterTable('searchInput', 'donorsTable');
        
        // Load existing donor data if editing
        <?php if ($editDonor): ?>
            loadDonorData(<?php echo htmlspecialchars(json_encode($editDonor)); ?>);
        <?php endif; ?>
    });
    
    function editDonor(donorID) {
        const modal = new bootstrap.Modal(document.getElementById('donorModal'));
        
        // Fetch donor data via AJAX
        fetch('donors.php?edit=' + donorID)
            .then(response => response.text())
            .then(() => {
                // Reload page with edit parameter to populate form
                window.location.href = 'donors.php?edit=' + donorID;
            });
    }
    
    function handleDeleteDonor(donorID) {
        if (confirm('Delete this donor?')) {
            window.location.href = 'donors.php?delete=' + donorID;
        }
    }
    
    function loadDonorData(donor) {
        document.getElementById('donorID').value = donor.DonorID;
        document.getElementById('donorName').value = donor.DonorName;
        document.getElementById('donorEmail').value = donor.Email || '';
        document.getElementById('donorPhone').value = donor.Phonenumber || '';
        document.getElementById('donorAddress').value = donor.Address || '';
        document.getElementById('donorCountry').value = donor.Country || '';
        document.getElementById('donorModalTitle').textContent = 'Edit Donor';
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('donorModal'));
        modal.show();
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
