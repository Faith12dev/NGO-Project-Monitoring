<?php
// Process all PHP logic BEFORE including header
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAnyRole(['admin', 'project_manager', 'field_officer']);

$locations = getAllLocations();
$editLocation = null;

// Handle form submission (Add or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $locationID = (int)($_POST['locationID'] ?? 0);
    $district = sanitize($_POST['district'] ?? '');
    $region = sanitize($_POST['region'] ?? '');
    $country = sanitize($_POST['country'] ?? '');

    if ($district) {
        if ($locationID > 0) {
            // UPDATE existing location
            $oldLocation = getLocationByID($locationID);
            $query = "UPDATE Location SET District = '$district', Region = '$region', Country = '$country' WHERE LocationID = $locationID";
            
            if ($conn->query($query)) {
                $oldValue = json_encode($oldLocation);
                $newValue = json_encode(['District' => $district, 'Region' => $region, 'Country' => $country]);
                logAudit('UPDATE', 'Location', $locationID, $district, $oldValue, $newValue);
                setFlashMessage('success', 'Location updated successfully!');
                header('Location: locations.php');
                exit;
            } else {
                setFlashMessage('error', 'Error updating location.');
            }
        } else {
            // INSERT new location
            $query = "INSERT INTO Location (District, Region, Country) VALUES ('$district', '$region', '$country')";
            
            if ($conn->query($query)) {
                $newID = $conn->insert_id;
                $newValue = json_encode(['District' => $district, 'Region' => $region, 'Country' => $country]);
                logAudit('CREATE', 'Location', $newID, $district, '', $newValue);
                setFlashMessage('success', 'Location added successfully!');
                header('Location: locations.php');
                exit;
            } else {
                setFlashMessage('error', 'Error adding location.');
            }
        }
    }
}

// Load location data if editing
if (isset($_GET['edit'])) {
    $editID = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM Location WHERE LocationID = $editID");
    if ($result && $result->num_rows > 0) {
        $editLocation = $result->fetch_assoc();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $locationID = (int)$_GET['delete'];
    $location = getLocationByID($locationID);
    if ($conn->query("DELETE FROM Location WHERE LocationID = $locationID")) {
        $deletedValue = json_encode($location);
        logAudit('DELETE', 'Location', $locationID, $location['District'], $deletedValue, '');
        setFlashMessage('success', 'Location deleted successfully!');
    } else {
        setFlashMessage('error', 'Error deleting location.');
    }
    header('Location: locations.php');
    exit;
}

// Now include the header (after all logic is done)
$page_title = 'Locations';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-map-marker-alt me-2"></i>Locations</h1>
            <p>Manage project locations and regions</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#locationModal">
            <i class="fas fa-plus me-2"></i>New Location
        </button>
    </div>
</div>

<?php displayFlashMessage(); ?>

<!-- Locations List -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <span>All Locations</span>
            <input type="text" id="searchInput" class="form-control" placeholder="Search locations..." style="width: 250px;">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" id="locationsTable">
            <thead>
                <tr>
                    <th>District</th>
                    <th>Region</th>
                    <th>Country</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($locations as $location): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($location['District']); ?></strong></td>
                        <td><?php echo htmlspecialchars($location['Region'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($location['Country'] ?? 'N/A'); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editLocation(<?php echo $location['LocationID']; ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="handleDeleteLocation(<?php echo $location['LocationID']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="locationModalTitle">Add New Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="locationID" id="locationID" value="0">
                    
                    <div class="form-group">
                        <label class="form-label">District *</label>
                        <input type="text" name="district" id="locationDistrict" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Region</label>
                        <input type="text" name="region" id="locationRegion" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" id="locationCountry" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        filterTable('searchInput', 'locationsTable');
        
        // Load existing location data if editing
        <?php if ($editLocation): ?>
            loadLocationData(<?php echo htmlspecialchars(json_encode($editLocation)); ?>);
        <?php endif; ?>
    });
    
    function editLocation(locationID) {
        // Reload page with edit parameter to populate form
        window.location.href = 'locations.php?edit=' + locationID;
    }
    
    function handleDeleteLocation(locationID) {
        if (confirm('Delete this location?')) {
            window.location.href = 'locations.php?delete=' + locationID;
        }
    }
    
    function loadLocationData(location) {
        document.getElementById('locationID').value = location.LocationID;
        document.getElementById('locationDistrict').value = location.District;
        document.getElementById('locationRegion').value = location.Region || '';
        document.getElementById('locationCountry').value = location.Country || '';
        document.getElementById('locationModalTitle').textContent = 'Edit Location';
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('locationModal'));
        modal.show();
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
