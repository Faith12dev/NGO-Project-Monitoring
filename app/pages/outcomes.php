<?php
// Process all PHP logic BEFORE including header
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAnyRole(['admin', 'project_manager', 'supervisor']);

$outcomes = getAllOutcomes();
$projects = getAllProjects();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectID = (int)($_POST['projectID'] ?? 0);
    $targetValue = (float)($_POST['targetValue'] ?? 0);
    $achievedValue = (float)($_POST['achievedValue'] ?? 0);
    $reportDate = sanitize($_POST['reportDate'] ?? '');
    $comments = sanitize($_POST['comments'] ?? '');

    if ($projectID && $reportDate) {
        $query = "INSERT INTO Outcome (ProjectID, TargetValue, AchievedValue, ReportDate, Comments) 
                  VALUES ($projectID, $targetValue, $achievedValue, '$reportDate', '$comments')";
        
        if ($conn->query($query)) {
            setFlashMessage('success', 'Outcome recorded successfully!');
            header('Location: outcomes.php');
            exit;
        } else {
            setFlashMessage('error', 'Error recording outcome.');
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $outcomeID = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM Outcome WHERE OutcomeID = $outcomeID")) {
        setFlashMessage('success', 'Outcome deleted successfully!');
    } else {
        setFlashMessage('error', 'Error deleting outcome.');
    }
    header('Location: outcomes.php');
    exit;
}

// Now include the header (after all logic is done)
$page_title = 'Outcomes';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-trophy me-2"></i>Outcomes</h1>
            <p>Track project outcomes and achievement indicators</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#outcomeModal">
            <i class="fas fa-plus me-2"></i>Record Outcome
        </button>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value"><?php echo count($outcomes); ?></div>
            <div class="stat-label">Total Outcomes</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-bullseye"></i>
            </div>
            <div class="stat-value"><?php echo number_format(array_sum(array_column($outcomes, 'TargetValue')), 0); ?></div>
            <div class="stat-label">Target Value</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-value"><?php echo number_format(array_sum(array_column($outcomes, 'AchievedValue')), 0); ?></div>
            <div class="stat-label">Achieved Value</div>
        </div>
    </div>
</div>

<!-- Outcomes List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span>All Outcomes</span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search outcomes..." style="width: 250px;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="outcomesTable">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Target Value</th>
                    <th>Achieved Value</th>
                    <th>Progress %</th>
                    <th>Report Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($outcomes as $outcome): ?>
                    <?php 
                    $progress = $outcome['TargetValue'] > 0 ? ($outcome['AchievedValue'] / $outcome['TargetValue']) * 100 : 0;
                    $progressClass = $progress >= 100 ? 'success' : ($progress >= 75 ? 'info' : ($progress >= 50 ? 'warning' : 'danger'));
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($outcome['ProjectName'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo number_format($outcome['TargetValue'], 0); ?></td>
                        <td><?php echo number_format($outcome['AchievedValue'], 0); ?></td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-<?php echo $progressClass; ?>" style="width: <?php echo min($progress, 100); ?>%">
                                    <?php echo number_format($progress, 1); ?>%
                                </div>
                            </div>
                        </td>
                        <td><?php echo formatDate($outcome['ReportDate']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $outcome['OutcomeID']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="handleDeleteOutcome(<?php echo $outcome['OutcomeID']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- View Modal -->
                    <div class="modal fade" id="viewModal<?php echo $outcome['OutcomeID']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Outcome Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Project:</strong> <?php echo htmlspecialchars($outcome['ProjectName'] ?? 'N/A'); ?></p>
                                    <p><strong>Target Value:</strong> <?php echo number_format($outcome['TargetValue'], 0); ?></p>
                                    <p><strong>Achieved Value:</strong> <?php echo number_format($outcome['AchievedValue'], 0); ?></p>
                                    <p><strong>Progress:</strong> 
                                        <div class="progress">
                                            <div class="progress-bar bg-<?php echo $progressClass; ?>" style="width: <?php echo min(($outcome['AchievedValue'] / $outcome['TargetValue']) * 100, 100); ?>%">
                                                <?php echo number_format(($outcome['AchievedValue'] / $outcome['TargetValue']) * 100, 1); ?>%
                                            </div>
                                        </div>
                                    </p>
                                    <p><strong>Report Date:</strong> <?php echo formatDate($outcome['ReportDate']); ?></p>
                                    <p><strong>Comments:</strong> <?php echo htmlspecialchars($outcome['Comments'] ?? 'None'); ?></p>
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

<!-- Add Outcome Modal -->
<div class="modal fade" id="outcomeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Outcome</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Project *</label>
                        <select name="projectID" class="form-select" required>
                            <option value="">-- Select Project --</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['ProjectID']; ?>"><?php echo htmlspecialchars($project['ProjectName']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Target Value</label>
                        <input type="number" name="targetValue" class="form-control" step="0.01">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Achieved Value</label>
                        <input type="number" name="achievedValue" class="form-control" step="0.01">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Report Date *</label>
                        <input type="date" name="reportDate" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Comments</label>
                        <textarea name="comments" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Outcome</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        filterTable('searchInput', 'outcomesTable');
        initializeFormElements();
    });

    function handleDeleteOutcome(outcomeID) {
        if (confirm('Delete this outcome?')) {
            window.location.href = 'outcomes.php?delete=' + outcomeID;
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
