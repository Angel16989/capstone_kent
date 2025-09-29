<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_admin()) {
    header('Location: /Capstone-latest/public/login.php?admin=1&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$pageTitle = "Equipment Tracking";
$pageCSS = "/assets/css/admin.css";

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_equipment'])) {
            // Add new equipment
            $stmt = $pdo->prepare('INSERT INTO gym_equipment (name, category, serial_number, purchase_date, purchase_price, location, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $_POST['name'],
                $_POST['category'],
                $_POST['serial_number'],
                $_POST['purchase_date'],
                $_POST['purchase_price'],
                $_POST['location'],
                $_POST['status'],
                $_POST['notes']
            ]);
            $message = 'Equipment added successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['update_equipment'])) {
            // Update equipment
            $stmt = $pdo->prepare('UPDATE gym_equipment SET name = ?, category = ?, serial_number = ?, purchase_date = ?, purchase_price = ?, location = ?, status = ?, last_maintenance = ?, next_maintenance = ?, notes = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([
                $_POST['name'],
                $_POST['category'],
                $_POST['serial_number'],
                $_POST['purchase_date'],
                $_POST['purchase_price'],
                $_POST['location'],
                $_POST['status'],
                $_POST['last_maintenance'],
                $_POST['next_maintenance'],
                $_POST['notes'],
                $_POST['equipment_id']
            ]);
            $message = 'Equipment updated successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['delete_equipment'])) {
            // Delete equipment
            $stmt = $pdo->prepare('DELETE FROM gym_equipment WHERE id = ?');
            $stmt->execute([$_POST['equipment_id']]);
            $message = 'Equipment deleted successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['add_maintenance'])) {
            // Add maintenance record
            $stmt = $pdo->prepare('INSERT INTO equipment_maintenance (equipment_id, maintenance_type, description, cost, performed_by) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $_POST['equipment_id'],
                $_POST['maintenance_type'],
                $_POST['maintenance_description'],
                $_POST['maintenance_cost'],
                $_POST['performed_by']
            ]);
            $message = 'Maintenance record added successfully!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get equipment data
$stmt = $pdo->query('SELECT * FROM gym_equipment ORDER BY status ASC, name ASC');
$equipment = $stmt->fetchAll();

// Get equipment stats
$stmt = $pdo->query('SELECT COUNT(*) as total FROM gym_equipment');
$total_equipment = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as active FROM gym_equipment WHERE status = "active"');
$active_equipment = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as maintenance FROM gym_equipment WHERE status = "maintenance"');
$maintenance_equipment = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as needs_maintenance FROM gym_equipment WHERE next_maintenance <= CURDATE() AND next_maintenance IS NOT NULL');
$needs_maintenance = $stmt->fetchColumn();

// Get recent maintenance
$stmt = $pdo->query('
    SELECT em.*, ge.name as equipment_name
    FROM equipment_maintenance em
    JOIN gym_equipment ge ON em.equipment_id = ge.id
    ORDER BY em.performed_at DESC
    LIMIT 10
');
$recent_maintenance = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="admin-hero py-4">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Equipment Tracking</li>
          </ol>
        </nav>
        <h1 class="display-5 fw-bold text-gradient mb-0">Equipment Tracking</h1>
        <p class="lead">Monitor gym equipment status, maintenance schedules, and usage</p>
      </div>
    </div>
  </div>
</div>

<div class="container pb-5">
  <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($message); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Stats Overview -->
  <div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-tools"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $total_equipment; ?></h4>
          <p class="card-description">Total Equipment</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $active_equipment; ?></h4>
          <p class="card-description">Active Equipment</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-wrench"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $maintenance_equipment; ?></h4>
          <p class="card-description">Under Maintenance</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card <?php echo $needs_maintenance > 0 ? 'border-warning' : ''; ?>">
        <div class="card-icon">
          <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $needs_maintenance; ?></h4>
          <p class="card-description">Needs Maintenance</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Equipment Modal -->
  <div class="modal fade" id="addEquipmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Equipment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Equipment Name</label>
                  <input type="text" class="form-control" name="name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Category</label>
                  <select class="form-control" name="category">
                    <option value="Cardio">Cardio</option>
                    <option value="Strength">Strength</option>
                    <option value="Flexibility">Flexibility</option>
                    <option value="Functional">Functional</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Serial Number</label>
                  <input type="text" class="form-control" name="serial_number">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Location</label>
                  <input type="text" class="form-control" name="location" placeholder="Main Floor, Weight Room, etc.">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Purchase Date</label>
                  <input type="date" class="form-control" name="purchase_date">
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Purchase Price</label>
                  <input type="number" step="0.01" class="form-control" name="purchase_price" placeholder="0.00">
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-control" name="status">
                <option value="active">Active</option>
                <option value="maintenance">Under Maintenance</option>
                <option value="out_of_order">Out of Order</option>
                <option value="retired">Retired</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea class="form-control" name="notes" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add_equipment" class="btn btn-admin">Add Equipment</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="row mb-4">
    <div class="col-12">
      <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
        <i class="bi bi-plus-circle"></i> Add Equipment
      </button>
    </div>
  </div>

  <!-- Equipment Table -->
  <div class="row mb-5">
    <div class="col-12">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">Equipment Inventory</h4>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Category</th>
                  <th>Location</th>
                  <th>Status</th>
                  <th>Last Maintenance</th>
                  <th>Next Maintenance</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($equipment)): ?>
                  <?php foreach ($equipment as $item): ?>
                  <tr>
                    <td>
                      <div>
                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                        <?php if ($item['serial_number']): ?>
                          <br><small class="text-muted">SN: <?php echo htmlspecialchars($item['serial_number']); ?></small>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                    <td><?php echo htmlspecialchars($item['location']); ?></td>
                    <td>
                      <span class="badge bg-<?php
                        echo $item['status'] === 'active' ? 'success' :
                             ($item['status'] === 'maintenance' ? 'warning' :
                             ($item['status'] === 'out_of_order' ? 'danger' : 'secondary'));
                      ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $item['status'])); ?>
                      </span>
                    </td>
                    <td><?php echo $item['last_maintenance'] ? date('M j, Y', strtotime($item['last_maintenance'])) : 'Never'; ?></td>
                    <td>
                      <?php if ($item['next_maintenance']): ?>
                        <span class="<?php echo strtotime($item['next_maintenance']) <= time() ? 'text-danger fw-bold' : ''; ?>">
                          <?php echo date('M j, Y', strtotime($item['next_maintenance'])); ?>
                        </span>
                      <?php else: ?>
                        Not Scheduled
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="editEquipment(<?php echo $item['id']; ?>)">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="addMaintenance(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
                          <i class="bi bi-wrench"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteEquipment(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>')">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center py-5">
                      <i class="bi bi-tools fs-1 text-muted"></i>
                      <p class="text-muted">No equipment added yet</p>
                      <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">Add Your First Equipment</button>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Maintenance -->
  <div class="row">
    <div class="col-12">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">Recent Maintenance</h4>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Equipment</th>
                  <th>Type</th>
                  <th>Description</th>
                  <th>Cost</th>
                  <th>Performed By</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($recent_maintenance)): ?>
                  <?php foreach ($recent_maintenance as $maintenance): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($maintenance['equipment_name']); ?></td>
                    <td><?php echo htmlspecialchars($maintenance['maintenance_type']); ?></td>
                    <td><?php echo htmlspecialchars(substr($maintenance['description'], 0, 50)) . (strlen($maintenance['description']) > 50 ? '...' : ''); ?></td>
                    <td>$<?php echo number_format($maintenance['cost'], 2); ?></td>
                    <td><?php echo htmlspecialchars($maintenance['performed_by']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($maintenance['performed_at'])); ?></td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center py-5">
                      <i class="bi bi-wrench fs-1 text-muted"></i>
                      <p class="text-muted">No maintenance records yet</p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Maintenance Modal -->
<div class="modal fade" id="addMaintenanceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Maintenance Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="maintenanceForm">
        <div class="modal-body">
          <input type="hidden" name="equipment_id" id="maintenance_equipment_id">
          <div class="mb-3">
            <label class="form-label">Equipment</label>
            <input type="text" class="form-control" id="maintenance_equipment_name" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Maintenance Type</label>
            <select class="form-control" name="maintenance_type" required>
              <option value="Preventive">Preventive Maintenance</option>
              <option value="Repair">Repair</option>
              <option value="Inspection">Inspection</option>
              <option value="Replacement">Part Replacement</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="maintenance_description" rows="3" required></textarea>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Cost</label>
                <input type="number" step="0.01" class="form-control" name="maintenance_cost" placeholder="0.00">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Performed By</label>
                <input type="text" class="form-control" name="performed_by" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_maintenance" class="btn btn-admin">Add Record</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editEquipment(equipmentId) {
    // Implement edit functionality
    alert('Edit functionality coming soon for equipment ID: ' + equipmentId);
}

function addMaintenance(equipmentId, equipmentName) {
    document.getElementById('maintenance_equipment_id').value = equipmentId;
    document.getElementById('maintenance_equipment_name').value = equipmentName;
    new bootstrap.Modal(document.getElementById('addMaintenanceModal')).show();
}

function deleteEquipment(equipmentId, name) {
    if (confirm('Are you sure you want to delete "' + name + '"?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="equipment_id" value="${equipmentId}">
            <input type="hidden" name="delete_equipment" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>