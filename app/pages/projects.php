<?php
// Process all PHP logic BEFORE including header
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAnyRole(['admin', 'project_manager', 'supervisor']);

$action = $_GET['action'] ?? 'list';
$projects = getAllProjects();
$donors = getAllDonors();
$locations = getAllLocations();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName = sanitize($_POST['projectName'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $startDate = sanitize($_POST['startDate'] ?? '');
    $endDate = sanitize($_POST['endDate'] ?? '');
    $budget = (float)($_POST['budget'] ?? 0);
    $donorID = (int)($_POST['donorID'] ?? 0);
    $locationID = (int)($_POST['locationID'] ?? 0);

    if ($projectName && $startDate && $budget) {
        $query = "INSERT INTO Projects (ProjectName, Description, StartDate, EndDate, Budget, DonorID, LocationID) 
                  VALUES ('$projectName', '$description', '$startDate', '$endDate', $budget, $donorID, $locationID)";
        
        if ($conn->query($query)) {
            setFlashMessage('success', 'Project added successfully!');
            header('Location: projects.php');
            exit;
        } else {
            setFlashMessage('error', 'Error adding project: ' . $conn->error);
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $projectID = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM Projects WHERE ProjectID = $projectID")) {
        setFlashMessage('success', 'Project deleted successfully!');
    } else {
        setFlashMessage('error', 'Error deleting project.');
    }
    header('Location: projects.php');
    exit;
}

// Now include the header (after all logic is done)
$page_title = 'Projects';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-project-diagram me-2"></i>Projects</h1>
            <p>Manage all community development projects</p>
        </div>
        <?php if (hasAnyRole(['admin', 'project_manager'])): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectModal">
                <i class="fas fa-plus me-2"></i>New Project
            </button>
        <?php endif; ?>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Projects List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span>All Projects</span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search projects..." style="width: 250px;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="projectsTable">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Location</th>
                    <th>Donor</th>
                    <th>Budget</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <?php
                    $today = date('Y-m-d');
                    $status = 'Pending';
                    $statusClass = 'warning';
                    
                    if ($today > $project['EndDate']) {
                        $status = 'Completed';
                        $statusClass = 'success';
                    } elseif ($today >= $project['StartDate']) {
                        $status = 'Active';
                        $statusClass = 'primary';
                    }
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($project['ProjectName']); ?></strong></td>
                        <td><?php echo htmlspecialchars($project['District'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($project['DonorName'] ?? 'N/A'); ?></td>
                        <td><?php echo formatCurrency($project['Budget']); ?></td>
                        <td><?php echo formatDate($project['StartDate']); ?></td>
                        <td><?php echo formatDate($project['EndDate']); ?></td>
                        <td><span class="badge bg-<?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $project['ProjectID']; ?>" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if (hasAnyRole(['admin', 'project_manager'])): ?>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#projectModal" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="handleDeleteProject(<?php echo $project['ProjectID']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- View Modal -->
                    <div class="modal fade" id="viewModal<?php echo $project['ProjectID']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?php echo htmlspecialchars($project['ProjectName']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Description:</strong><br><?php echo htmlspecialchars($project['Description'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Location:</strong><br><?php echo htmlspecialchars($project['District'] ?? 'N/A') . ', ' . htmlspecialchars($project['Region'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Donor:</strong><br><?php echo htmlspecialchars($project['DonorName'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Budget:</strong><br><?php echo formatCurrency($project['Budget']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Start Date:</strong><br><?php echo formatDate($project['StartDate']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>End Date:</strong><br><?php echo formatDate($project['EndDate']); ?></p>
                                        </div>
                                    </div>
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

<!-- Add/Edit Project Modal -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Project Name *</label>
                        <input type="text" name="projectName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Start Date *</label>
                                <input type="date" name="startDate" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">End Date *</label>
                                <input type="date" name="endDate" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Budget (UGX) *</label>
                        <input type="number" name="budget" class="form-control currency-input" step="0.01" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Donor</label>
                                <select name="donorID" class="form-select">
                                    <option value="">-- Select Donor --</option>
                                    <?php foreach ($donors as $donor): ?>
                                        <option value="<?php echo $donor['DonorID']; ?>"><?php echo htmlspecialchars($donor['DonorName']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Location</label>
                                <select name="locationID" class="form-select">
                                    <option value="">-- Select Location --</option>
                                    <?php foreach ($locations as $location): ?>
                                        <option value="<?php echo $location['LocationID']; ?>"><?php echo htmlspecialchars($location['District']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        filterTable('searchInput', 'projectsTable');
        initializeFormElements();
    });

    function editProject(id) {
        // In a real application, this would load the project data and populate the form
        console.log('Edit project:', id);
    }

    function handleDeleteProject(projectID) {
        if (confirm('Delete this project?')) {
            window.location.href = 'projects.php?delete=' + projectID;
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
