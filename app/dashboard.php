<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$stats = getProjectStats();
$projects = getAllProjects();
$recent_projects = array_slice($projects, 0, 5);
?>

<div class="page-header">
    <h1><i class="fas fa-chart-line me-2"></i>Dashboard</h1>
    <p>Welcome back! Here's an overview of your NGO activities.</p>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-project-diagram"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_projects']; ?></div>
            <div class="stat-label">Total Projects</div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value"><?php echo number_format($stats['total_beneficiaries']); ?></div>
            <div class="stat-label">Beneficiaries</div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-value"><?php echo formatCurrency($stats['total_budget']); ?></div>
            <div class="stat-label">Total Budget</div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-value"><?php echo formatCurrency($stats['total_spent']); ?></div>
            <div class="stat-label">Amount Spent</div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="stat-value"><?php echo $stats['total_donors']; ?></div>
            <div class="stat-label">Total Donors</div>
        </div>
    </div>
</div>

<!-- Budget vs Expenditure Chart -->
<div class="row mt-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-bar me-2"></i>Budget vs Expenditure
            </div>
            <div class="card-body">
                <canvas id="budgetChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Summary
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted mb-2">Budget Utilization</label>
                    <div class="progress">
                        <div class="progress-bar" style="width: <?php echo ($stats['total_spent'] / $stats['total_budget'] * 100); ?>%">
                            <?php echo round(($stats['total_spent'] / $stats['total_budget'] * 100), 1); ?>%
                        </div>
                    </div>
                </div>
                <hr>
                <p class="mb-2">
                    <strong>Budget Remaining:</strong><br>
                    <span class="text-success"><?php echo formatCurrency($stats['total_budget'] - $stats['total_spent']); ?></span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Projects -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i>Recent Projects</span>
                <a href="<?php echo BASE_URL; ?>app/pages/projects.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Location</th>
                            <th>Donor</th>
                            <th>Budget</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_projects as $project): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($project['ProjectName']); ?></strong></td>
                                <td><?php echo htmlspecialchars($project['District'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($project['DonorName'] ?? 'N/A'); ?></td>
                                <td><?php echo formatCurrency($project['Budget']); ?></td>
                                <td><?php echo formatDate($project['StartDate']); ?></td>
                                <td><?php echo formatDate($project['EndDate']); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>app/pages/projects.php?view=<?php echo $project['ProjectID']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tasks me-2"></i>Quick Actions
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (hasAnyRole(['admin', 'project_manager'])): ?>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo BASE_URL; ?>app/pages/projects.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-plus me-2"></i>New Project
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (hasAnyRole(['admin', 'donor_liaison'])): ?>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo BASE_URL; ?>app/pages/donors.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-plus me-2"></i>New Donor
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (hasAnyRole(['admin', 'accountant'])): ?>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo BASE_URL; ?>app/pages/expenditures.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-plus me-2"></i>Add Expenditure
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (hasAnyRole(['admin', 'project_manager', 'field_officer'])): ?>
                        <div class="col-md-3 mb-3">
                            <a href="<?php echo BASE_URL; ?>app/pages/beneficiaries.php" class="btn btn-outline-primary w-100">
                                <i class="fas fa-plus me-2"></i>Add Beneficiary
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Budget vs Expenditure Chart
    const ctx = document.getElementById('budgetChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Budget vs Expenditure'],
                datasets: [
                    {
                        label: 'Budget',
                        data: [<?php echo $stats['total_budget']; ?>],
                        backgroundColor: 'rgba(102, 126, 234, 0.8)',
                        borderRadius: 8
                    },
                    {
                        label: 'Spent',
                        data: [<?php echo $stats['total_spent']; ?>],
                        backgroundColor: 'rgba(245, 101, 101, 0.8)',
                        borderRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'UGX ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
