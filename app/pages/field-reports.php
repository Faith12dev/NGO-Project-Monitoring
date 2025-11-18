<?php
// Process all PHP logic BEFORE including header
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAnyRole(['admin', 'field_officer']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectID = (int)($_POST['projectID'] ?? 0);
    $report_date = sanitize($_POST['report_date'] ?? '');
    $activities = sanitize($_POST['activities'] ?? '');
    $challenges = sanitize($_POST['challenges'] ?? '');
    $field_officer_id = getCurrentUserID();

    if ($projectID && $report_date && $activities) {
        $query = "INSERT INTO FieldReport (ProjectID, ReportDate, Activities, Challenges, StaffID) 
                  VALUES ($projectID, '$report_date', '$activities', '$challenges', $field_officer_id)";
        
        if ($conn->query($query)) {
            setFlashMessage('success', 'Field report submitted successfully!');
            header('Location: field-reports.php');
            exit;
        } else {
            setFlashMessage('error', 'Error submitting field report.');
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $reportID = (int)$_GET['delete'];
    $report = $conn->query("SELECT * FROM FieldReport WHERE ReportID = $reportID")->fetch_assoc();
    
    if ($report && ($report['StaffID'] == getCurrentUserID() || hasRole('admin'))) {
        if ($conn->query("DELETE FROM FieldReport WHERE ReportID = $reportID")) {
            setFlashMessage('success', 'Field report deleted successfully!');
        } else {
            setFlashMessage('error', 'Error deleting field report.');
        }
    } else {
        setFlashMessage('error', 'You can only delete your own reports.');
    }
    header('Location: field-reports.php');
    exit;
}

// Get field reports
$userID = getCurrentUserID();
$userRole = getCurrentRole();

if ($userRole === 'admin') {
    $reports = $conn->query("SELECT fr.*, p.ProjectName, s.FullName FROM FieldReport fr 
                            JOIN Projects p ON fr.ProjectID = p.ProjectID 
                            JOIN Staff s ON fr.StaffID = s.StaffID 
                            ORDER BY fr.ReportDate DESC")->fetch_all(MYSQLI_ASSOC);
} else {
    $reports = $conn->query("SELECT fr.*, p.ProjectName, s.FullName FROM FieldReport fr 
                            JOIN Projects p ON fr.ProjectID = p.ProjectID 
                            JOIN Staff s ON fr.StaffID = s.StaffID 
                            WHERE fr.StaffID = $userID 
                            ORDER BY fr.ReportDate DESC")->fetch_all(MYSQLI_ASSOC);
}

$projects = getAllProjects();

// Now include the header (after all logic is done)
$page_title = 'Field Reports';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-file-alt me-2"></i>Field Reports</h1>
            <p>Submit field updates, activities, and challenges</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reportModal">
            <i class="fas fa-plus me-2"></i>Submit Report
        </button>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Reports List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span>All Field Reports</span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search reports..." style="width: 250px;">
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="reportsTable">
                <thead class="table-light">
                    <tr>
                        <th>Report Date</th>
                        <th>Project</th>
                        <th>Field Officer</th>
                        <th>Activities</th>
                        <th>Challenges</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reports)): ?>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?php echo formatDate($report['ReportDate']); ?></td>
                                <td><?php echo htmlspecialchars($report['ProjectName']); ?></td>
                                <td><?php echo htmlspecialchars($report['FullName']); ?></td>
                                <td><?php echo htmlspecialchars(substr($report['Activities'], 0, 50)); ?>...</td>
                                <td><?php echo htmlspecialchars(substr($report['Challenges'], 0, 30)); ?>...</td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewReportModal<?php echo $report['ReportID']; ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <?php if ($report['StaffID'] == getCurrentUserID() || hasRole('admin')): ?>
                                        <a href="?delete=<?php echo $report['ReportID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this report?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- View Report Modal -->
                            <div class="modal fade" id="viewReportModal<?php echo $report['ReportID']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Field Report - <?php echo formatDate($report['ReportDate']); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Project:</strong></label>
                                                <p><?php echo htmlspecialchars($report['ProjectName']); ?></p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Field Officer:</strong></label>
                                                <p><?php echo htmlspecialchars($report['FullName']); ?></p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Activities:</strong></label>
                                                <p><?php echo htmlspecialchars($report['Activities']); ?></p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Challenges:</strong></label>
                                                <p><?php echo htmlspecialchars($report['Challenges']); ?></p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"><strong>Report Date:</strong></label>
                                                <p><?php echo formatDate($report['ReportDate']); ?></p>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-inbox me-2"></i>No field reports yet
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Submit Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Submit Field Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Project *</label>
                        <select name="projectID" class="form-select" required>
                            <option value="">-- Select Project --</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['ProjectID']; ?>">
                                    <?php echo htmlspecialchars($project['ProjectName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Report Date *</label>
                        <input type="date" name="report_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Activities *</label>
                        <textarea name="activities" class="form-control" rows="4" placeholder="Describe field activities..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Challenges Encountered</label>
                        <textarea name="challenges" class="form-control" rows="3" placeholder="Any challenges faced..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        filterTable('reportsTable', this.value);
    });

    function filterTable(tableId, searchTerm) {
        const table = document.getElementById(tableId);
        const rows = table.getElementsByTagName('tr');
        
        for (let i = 1; i < rows.length; i++) {
            const text = rows[i].textContent.toLowerCase();
            rows[i].style.display = text.includes(searchTerm.toLowerCase()) ? '' : 'none';
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
