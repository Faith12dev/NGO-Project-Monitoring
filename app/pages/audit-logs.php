<?php
// Process all PHP logic BEFORE including header
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// All staff can view audit logs
requireAnyRole(['admin', 'project_manager', 'accountant', 'donor_liaison', 'supervisor', 'field_officer']);

$page = (int)($_GET['page'] ?? 1);
$limit = 50;
$offset = ($page - 1) * $limit;

$logs = getAuditLogs($limit, $offset);
$totalLogs = countAuditLogs();
$totalPages = ceil($totalLogs / $limit);

// Now include the header (after all logic is done)
$page_title = 'Audit Logs';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-history me-2"></i>Audit Logs</h1>
            <p>Track all user activities and changes made to records</p>
        </div>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Filter by Action:</label>
                <select class="form-select form-select-sm" id="actionFilter">
                    <option value="">All Actions</option>
                    <option value="CREATE">Create</option>
                    <option value="UPDATE">Update</option>
                    <option value="DELETE">Delete</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Filter by Table:</label>
                <select class="form-select form-select-sm" id="tableFilter">
                    <option value="">All Tables</option>
                    <option value="Donor">Donors</option>
                    <option value="Location">Locations</option>
                    <option value="Beneficiary">Beneficiaries</option>
                    <option value="Project">Projects</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search:</label>
                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search by email, name...">
            </div>
        </div>
    </div>
</div>
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span>All Activities (Total: <?php echo $totalLogs; ?>)</span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search by email, action, or table..." style="width: 300px;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="auditTable">
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Staff Member</th>
                    <th>Action</th>
                    <th>Table</th>
                    <th>Record Name</th>
                    <th>Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>
                            <small><?php echo date('d M Y, H:i:s', strtotime($log['ActionTime'])); ?></small>
                        </td>
                        <td>
                            <small>
                                <strong><?php echo htmlspecialchars($log['StaffEmail']); ?></strong><br>
                                <span class="text-muted">ID: <?php echo $log['StaffID']; ?></span>
                            </small>
                        </td>
                        <td>
                            <?php
                            $actionColor = 'secondary';
                            if ($log['Action'] === 'CREATE') {
                                $actionColor = 'success';
                            } elseif ($log['Action'] === 'UPDATE') {
                                $actionColor = 'info';
                            } elseif ($log['Action'] === 'DELETE') {
                                $actionColor = 'danger';
                            }
                            ?>
                            <span class="badge bg-<?php echo $actionColor; ?>"><?php echo htmlspecialchars($log['Action']); ?></span>
                        </td>
                        <td>
                            <small><strong><?php echo htmlspecialchars($log['TableName']); ?></strong></small>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars(substr($log['RecordName'], 0, 50)); ?></small>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $log['AuditID']; ?>" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars($log['IPAddress']); ?></small>
                        </td>
                    </tr>

                    <!-- Details Modal -->
                    <div class="modal fade" id="detailsModal<?php echo $log['AuditID']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <span class="badge bg-<?php echo ($log['Action'] === 'CREATE' ? 'success' : ($log['Action'] === 'DELETE' ? 'danger' : 'info')); ?>">
                                            <?php echo htmlspecialchars($log['Action']); ?>
                                        </span>
                                        - <?php echo htmlspecialchars($log['TableName']); ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p><strong>Staff Member:</strong><br><?php echo htmlspecialchars($log['StaffEmail']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Date/Time:</strong><br><?php echo date('d M Y, H:i:s', strtotime($log['ActionTime'])); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>IP Address:</strong><br><?php echo htmlspecialchars($log['IPAddress']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Table:</strong><br><?php echo htmlspecialchars($log['TableName']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Record ID:</strong><br><?php echo $log['RecordID']; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Record Name:</strong><br><?php echo htmlspecialchars($log['RecordName']); ?></p>
                                        </div>
                                    </div>

                                    <?php if ($log['Action'] === 'UPDATE'): ?>
                                        <hr>
                                        <h6>Changes Made:</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-danger">Previous Values:</h6>
                                                <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto;"><code><?php 
                                                    if ($log['OldValue']) {
                                                        echo htmlspecialchars(json_encode(json_decode($log['OldValue']), JSON_PRETTY_PRINT));
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                ?></code></pre>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-success">New Values:</h6>
                                                <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto;"><code><?php 
                                                    if ($log['NewValue']) {
                                                        echo htmlspecialchars(json_encode(json_decode($log['NewValue']), JSON_PRETTY_PRINT));
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                ?></code></pre>
                                            </div>
                                        </div>
                                    <?php elseif ($log['Action'] === 'DELETE'): ?>
                                        <hr>
                                        <h6 class="text-danger">Deleted Record Data:</h6>
                                        <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 400px; overflow-y: auto;"><code><?php 
                                            if ($log['OldValue']) {
                                                echo htmlspecialchars(json_encode(json_decode($log['OldValue']), JSON_PRETTY_PRINT));
                                            } else {
                                                echo 'N/A';
                                            }
                                        ?></code></pre>
                                    <?php elseif ($log['Action'] === 'CREATE'): ?>
                                        <hr>
                                        <h6 class="text-success">Created Record Data:</h6>
                                        <pre style="background: #f5f5f5; padding: 10px; border-radius: 5px; max-height: 400px; overflow-y: auto;"><code><?php 
                                            if ($log['NewValue']) {
                                                echo htmlspecialchars(json_encode(json_decode($log['NewValue']), JSON_PRETTY_PRINT));
                                            } else {
                                                echo 'N/A';
                                            }
                                        ?></code></pre>
                                    <?php endif; ?>
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

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=1">First</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $totalPages; ?>">Last</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('auditTable');
        const searchInput = document.getElementById('searchInput');
        const actionFilter = document.getElementById('actionFilter');
        const tableFilter = document.getElementById('tableFilter');

        function filterTable() {
            const searchValue = searchInput.value.toLowerCase();
            const actionValue = actionFilter.value.toUpperCase();
            const tableValue = tableFilter.value;

            Array.from(table.getElementsByTagName('tbody')[0].getElementsByTagName('tr')).forEach(row => {
                let match = true;

                // Search filter
                if (searchValue) {
                    const text = row.innerText.toLowerCase();
                    match = match && text.includes(searchValue);
                }

                // Action filter
                if (actionValue && match) {
                    const actionCell = row.querySelector('td:nth-child(3)');
                    match = match && actionCell.innerText.includes(actionValue);
                }

                // Table filter
                if (tableValue && match) {
                    const tableCell = row.querySelector('td:nth-child(4)');
                    match = match && tableCell.innerText.includes(tableValue);
                }

                row.style.display = match ? '' : 'none';
            });
        }

        searchInput.addEventListener('keyup', filterTable);
        actionFilter.addEventListener('change', filterTable);
        tableFilter.addEventListener('change', filterTable);
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
