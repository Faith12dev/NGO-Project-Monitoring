<?php
// Process all PHP logic BEFORE including header
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAnyRole(['admin', 'project_manager', 'field_officer']);

$beneficiaries = getAllBeneficiaries();
$projects = getAllProjects();
$editBeneficiary = null;

// Handle form submission (Add or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $beneficiaryID = (int)($_POST['beneficiaryID'] ?? 0);
    $beneficiaryName = sanitize($_POST['beneficiaryName'] ?? '');
    $beneficiaryType = sanitize($_POST['beneficiaryType'] ?? '');
    $projectID = (int)($_POST['projectID'] ?? 0);
    $noOfPeople = (int)($_POST['noOfPeople'] ?? 0);

    if ($beneficiaryName && $projectID) {
        if ($beneficiaryID > 0) {
            // UPDATE existing beneficiary
            $oldBeneficiary = $conn->query("SELECT * FROM Beneficiary WHERE BeneficiaryID = $beneficiaryID")->fetch_assoc();
            $query = "UPDATE Beneficiary SET BeneficiaryName = '$beneficiaryName', BeneficiaryType = '$beneficiaryType', ProjectID = $projectID, NoOfPeople = $noOfPeople WHERE BeneficiaryID = $beneficiaryID";
            if ($conn->query($query)) {
                $oldValue = json_encode($oldBeneficiary);
                $newValue = json_encode(['BeneficiaryName' => $beneficiaryName, 'BeneficiaryType' => $beneficiaryType, 'ProjectID' => $projectID, 'NoOfPeople' => $noOfPeople]);
                logAudit('UPDATE', 'Beneficiary', $beneficiaryID, $beneficiaryName, $oldValue, $newValue);
                setFlashMessage('success', 'Beneficiary updated successfully!');
                header('Location: beneficiaries.php');
                exit;
            } else {
                setFlashMessage('error', 'Error updating beneficiary.');
            }
        } else {
            $query = "INSERT INTO Beneficiary (BeneficiaryName, BeneficiaryType, ProjectID, NoOfPeople) 
                      VALUES ('$beneficiaryName', '$beneficiaryType', $projectID, $noOfPeople)";
            if ($conn->query($query)) {
                $newID = $conn->insert_id;
                $newValue = json_encode(['BeneficiaryName' => $beneficiaryName, 'BeneficiaryType' => $beneficiaryType, 'ProjectID' => $projectID, 'NoOfPeople' => $noOfPeople]);
                logAudit('CREATE', 'Beneficiary', $newID, $beneficiaryName, '', $newValue);
                setFlashMessage('success', 'Beneficiary added successfully!');
                header('Location: beneficiaries.php');
                exit;
            } else {
                setFlashMessage('error', 'Error adding beneficiary.');
            }
        }
    }
}

// Load beneficiary data if editing
if (isset($_GET['edit'])) {
    $editID = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM Beneficiary WHERE BeneficiaryID = $editID");
    if ($result && $result->num_rows > 0) {
        $editBeneficiary = $result->fetch_assoc();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $beneficiaryName = sanitize($_POST['beneficiaryName'] ?? '');
    $beneficiaryType = sanitize($_POST['beneficiaryType'] ?? '');
    $projectID = (int)($_POST['projectID'] ?? 0);
    $noOfPeople = (int)($_POST['noOfPeople'] ?? 0);

    if ($beneficiaryName && $projectID) {
        $query = "INSERT INTO Beneficiary (BeneficiaryName, BeneficiaryType, ProjectID, NoOfPeople) 
                  VALUES ('$beneficiaryName', '$beneficiaryType', $projectID, $noOfPeople)";
        
        if ($conn->query($query)) {
            setFlashMessage('success', 'Beneficiary added successfully!');
            header('Location: beneficiaries.php');
            exit;
        } else {
            setFlashMessage('error', 'Error adding beneficiary.');
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $beneficiaryID = (int)$_GET['delete'];
    $result = $conn->query("SELECT * FROM Beneficiary WHERE BeneficiaryID = $beneficiaryID");
    $beneficiary = $result->fetch_assoc();
    if ($conn->query("DELETE FROM Beneficiary WHERE BeneficiaryID = $beneficiaryID")) {
        $deletedValue = json_encode($beneficiary);
        logAudit('DELETE', 'Beneficiary', $beneficiaryID, $beneficiary['BeneficiaryName'], $deletedValue, '');
        setFlashMessage('success', 'Beneficiary deleted successfully!');
    } else {
        setFlashMessage('error', 'Error deleting beneficiary.');
    }
    header('Location: beneficiaries.php');
    exit;
}

// Now include the header (after all logic is done)
$page_title = 'Beneficiaries';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-users me-2"></i>Beneficiaries</h1>
            <p>Track community members benefiting from projects</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#beneficiaryModal">
            <i class="fas fa-plus me-2"></i>Add Beneficiary
        </button>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Beneficiaries List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span>All Beneficiaries</span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search beneficiaries..." style="width: 250px;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="beneficiariesTable">
            <thead>
                <tr>
                    <th>Beneficiary Name</th>
                    <th>Type</th>
                    <th>Project</th>
                    <th>Number of People</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($beneficiaries as $beneficiary): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($beneficiary['BeneficiaryName']); ?></strong></td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($beneficiary['BeneficiaryType'] ?? 'General'); ?></span></td>
                        <td><?php echo htmlspecialchars($beneficiary['ProjectName'] ?? 'N/A'); ?></td>
                        <td><?php echo $beneficiary['NoOfPeople']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editBeneficiary(<?php echo $beneficiary['BeneficiaryID']; ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="handleDeleteBeneficiary(<?php echo $beneficiary['BeneficiaryID']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Beneficiary Modal -->
<div class="modal fade" id="beneficiaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="beneficiaryModalTitle">Add New Beneficiary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="beneficiaryID" id="beneficiaryID" value="0">
                    <div class="form-group">
                        <label class="form-label">Beneficiary Name *</label>
                        <input type="text" name="beneficiaryName" id="beneficiaryName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Beneficiary Type</label>
                        <select name="beneficiaryType" id="beneficiaryType" class="form-select">
                            <option value="">-- Select Type --</option>
                            <option value="Individual">Individual</option>
                            <option value="Community">Community</option>
                            <option value="Organization">Organization</option>
                            <option value="Group">Group</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Project *</label>
                        <select name="projectID" id="projectID" class="form-select" required>
                            <option value="">-- Select Project --</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['ProjectID']; ?>"><?php echo htmlspecialchars($project['ProjectName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Number of People</label>
                        <input type="number" name="noOfPeople" id="noOfPeople" class="form-control" min="1" value="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Beneficiary</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        filterTable('searchInput', 'beneficiariesTable');

        <?php if ($editBeneficiary): ?>
            loadBeneficiaryData(<?php echo htmlspecialchars(json_encode($editBeneficiary)); ?>);
        <?php endif; ?>
    });

    function editBeneficiary(id) {
        window.location.href = 'beneficiaries.php?edit=' + id;
    }

    function handleDeleteBeneficiary(beneficiaryID) {
        if (confirm('Delete this beneficiary?')) {
            window.location.href = 'beneficiaries.php?delete=' + beneficiaryID;
        }
    }

    function loadBeneficiaryData(b) {
        document.getElementById('beneficiaryID').value = b.BeneficiaryID;
        document.getElementById('beneficiaryName').value = b.BeneficiaryName;
        document.getElementById('beneficiaryType').value = b.BeneficiaryType || '';
        document.getElementById('projectID').value = b.ProjectID;
        document.getElementById('noOfPeople').value = b.NoOfPeople;
        document.getElementById('beneficiaryModalTitle').textContent = 'Edit Beneficiary';

        const modal = new bootstrap.Modal(document.getElementById('beneficiaryModal'));
        modal.show();
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
