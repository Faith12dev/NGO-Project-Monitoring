<?php
// Process all PHP logic BEFORE including header
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAnyRole(['admin', 'accountant']);

$expenditures = getAllExpenditures();
$projects = getAllProjects();

// Determine if current user can add/delete (only accountants can)
$canAddEdit = hasRole('accountant');

// Handle form submission (only accountants)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canAddEdit) {
        setFlashMessage('error', 'You do not have permission to add expenditures.');
        header('Location: expenditures.php');
        exit;
    }

    $projectID = (int)($_POST['projectID'] ?? 0);
    $date = sanitize($_POST['date'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $amountSpent = (float)($_POST['amountSpent'] ?? 0);
    $remarks = sanitize($_POST['remarks'] ?? '');

    if ($projectID && $date && $category && $amountSpent) {
        $query = "INSERT INTO Expenditure (ProjectID, Date, Category, AmountSpent, Remarks) 
                  VALUES ($projectID, '$date', '$category', $amountSpent, '$remarks')";
        
        if ($conn->query($query)) {
            setFlashMessage('success', 'Expenditure recorded successfully!');
            header('Location: expenditures.php');
            exit;
        } else {
            setFlashMessage('error', 'Error recording expenditure.');
        }
    }
}

// Handle delete (only accountants)
if (isset($_GET['delete'])) {
    if (!$canAddEdit) {
        setFlashMessage('error', 'You do not have permission to delete expenditures.');
        header('Location: expenditures.php');
        exit;
    }

    $expenditureID = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM Expenditure WHERE ExpenditureID = $expenditureID")) {
        setFlashMessage('success', 'Expenditure deleted successfully!');
    } else {
        setFlashMessage('error', 'Error deleting expenditure.');
    }
    header('Location: expenditures.php');
    exit;
}

// Calculate totals
$totalExpenditure = 0;
foreach ($expenditures as $exp) {
    $totalExpenditure += $exp['AmountSpent'];
}

// Now include the header (after all logic is done)
$page_title = 'Expenditures';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-money-bill-wave me-2"></i>Expenditures</h1>
            <p>Track project spending and budget utilization</p>
        </div>
        <?php if ($canAddEdit): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#expenditureModal">
                <i class="fas fa-plus me-2"></i>Record Expenditure
            </button>
        <?php else: ?>
            <div class="alert alert-info mb-0" style="display: inline-block;">
                <small><i class="fas fa-info-circle me-2"></i>View only - Accountants can add/delete expenditures</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-value"><?php echo count($expenditures); ?></div>
            <div class="stat-label">Total Transactions</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="stat-value"><?php echo formatCurrency($totalExpenditure); ?></div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-value"><?php echo date('M Y'); ?></div>
            <div class="stat-label">Current Period</div>
        </div>
    </div>
</div>

<!-- Expenditures List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span>All Expenditures</span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search expenditures..." style="width: 250px;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="expendituresTable">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Amount (UGX)</th>
                    <th>Remarks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenditures as $expenditure): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($expenditure['ProjectName'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo formatDate($expenditure['Date']); ?></td>
                        <td><span class="badge bg-warning"><?php echo htmlspecialchars($expenditure['Category']); ?></span></td>
                        <td><?php echo formatCurrency($expenditure['AmountSpent']); ?></td>
                        <td><?php echo htmlspecialchars(substr($expenditure['Remarks'] ?? '', 0, 50)); ?></td>
                        <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $expenditure['ExpenditureID']; ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($canAddEdit): ?>
                                <button type="button" class="btn btn-sm btn-danger" onclick="handleDeleteExpenditure(<?php echo $expenditure['ExpenditureID']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- View Modal -->
                    <div class="modal fade" id="viewModal<?php echo $expenditure['ExpenditureID']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Expenditure Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Project:</strong> <?php echo htmlspecialchars($expenditure['ProjectName'] ?? 'N/A'); ?></p>
                                    <p><strong>Date:</strong> <?php echo formatDate($expenditure['Date']); ?></p>
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars($expenditure['Category']); ?></p>
                                    <p><strong>Amount:</strong> <?php echo formatCurrency($expenditure['AmountSpent']); ?></p>
                                    <p><strong>Remarks:</strong> <?php echo htmlspecialchars($expenditure['Remarks'] ?? 'None'); ?></p>
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

<!-- Add Expenditure Modal -->
<div class="modal fade" id="expenditureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Expenditure</h5>
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
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <option value="Transport">Transport</option>
                            <option value="Personnel">Personnel</option>
                            <option value="Materials">Materials</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Training">Training</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Amount (UGX) *</label>
                        <input type="number" name="amountSpent" class="form-control currency-input" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Expenditure</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        filterTable('searchInput', 'expendituresTable');
        initializeFormElements();
    });

    function handleDeleteExpenditure(expenditureID) {
        if (confirm('Delete this expenditure?')) {
            window.location.href = 'expenditures.php?delete=' + expenditureID;
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
