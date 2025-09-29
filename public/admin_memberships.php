<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_admin()) {
    header('Location: /Capstone-latest/public/login.php?admin=1&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Handle AJAX requests for edit functionality
if (isset($_GET['get_plan'])) {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare('SELECT * FROM membership_plans WHERE id = ?');
        $stmt->execute([$_GET['get_plan']]);
        $plan = $stmt->fetch();

        if ($plan) {
            echo json_encode(['success' => true, 'plan' => $plan]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Plan not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

if (isset($_GET['get_subscription'])) {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare('
            SELECT m.*, CONCAT(u.first_name, " ", u.last_name) as member_name, mp.name as plan_name
            FROM memberships m
            LEFT JOIN users u ON m.member_id = u.id
            LEFT JOIN membership_plans mp ON m.plan_id = mp.id
            WHERE m.id = ?
        ');
        $stmt->execute([$_GET['get_subscription']]);
        $subscription = $stmt->fetch();

        if ($subscription) {
            echo json_encode(['success' => true, 'subscription' => $subscription]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Subscription not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

$pageTitle = "Admin Membership Management";
$pageCSS = "/assets/css/admin.css";

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create_plan'])) {
            // Create new membership plan
            $stmt = $pdo->prepare('INSERT INTO membership_plans (name, description, price, duration_days, is_active) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $_POST['plan_name'],
                $_POST['description'],
                $_POST['price'],
                $_POST['duration_days'],
                isset($_POST['is_active']) ? 1 : 0
            ]);
            $message = 'Membership plan created successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['update_plan'])) {
            // Update existing plan
            $stmt = $pdo->prepare('UPDATE membership_plans SET name = ?, description = ?, price = ?, duration_days = ?, is_active = ? WHERE id = ?');
            $stmt->execute([
                $_POST['plan_name'],
                $_POST['description'],
                $_POST['price'],
                $_POST['duration_days'],
                isset($_POST['is_active']) ? 1 : 0,
                $_POST['plan_id']
            ]);
            $message = 'Membership plan updated successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['delete_plan'])) {
            // Delete plan (soft delete by setting inactive)
            $stmt = $pdo->prepare('UPDATE membership_plans SET is_active = 0 WHERE id = ?');
            $stmt->execute([$_POST['plan_id']]);
            $message = 'Membership plan deactivated successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['update_subscription'])) {
            // Update subscription status
            $stmt = $pdo->prepare('UPDATE memberships SET status = ?, end_date = ? WHERE id = ?');
            $stmt->execute([
                $_POST['status'],
                $_POST['end_date'],
                $_POST['subscription_id']
            ]);
            $message = 'Subscription updated successfully!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get membership plans
$stmt = $pdo->query('SELECT * FROM membership_plans ORDER BY is_active DESC, price ASC');
$membership_plans = $stmt->fetchAll();

// Get active subscriptions with user details
$stmt = $pdo->query('
    SELECT m.*, u.first_name, u.last_name, u.email, mp.name as plan_name
    FROM memberships m
    LEFT JOIN users u ON m.member_id = u.id
    LEFT JOIN membership_plans mp ON m.plan_id = mp.id
    ORDER BY m.created_at DESC
    LIMIT 50
');
$active_subscriptions = $stmt->fetchAll();

// Get membership statistics
$stmt = $pdo->query('SELECT COUNT(*) as total_plans FROM membership_plans WHERE is_active = 1');
$total_plans = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as total_subscriptions FROM memberships');
$total_subscriptions = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as active_subscriptions FROM memberships WHERE status = "active" AND end_date > NOW()');
$active_subscriptions_count = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT SUM(price) as total_revenue FROM memberships m JOIN membership_plans mp ON m.plan_id = mp.id WHERE m.status = "active"');
$total_revenue = $stmt->fetchColumn() ?: 0;
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="admin-hero py-4">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Membership Management</li>
          </ol>
        </nav>
        <h1 class="display-5 fw-bold text-gradient mb-0">Membership Management</h1>
        <p class="lead">Manage membership plans, pricing, and active subscriptions</p>
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

  <!-- Statistics Overview -->
  <div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-trophy"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $total_plans; ?></h4>
          <p class="card-description">Active Plans</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-people"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $total_subscriptions; ?></h4>
          <p class="card-description">Total Subscriptions</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $active_subscriptions_count; ?></h4>
          <p class="card-description">Active Members</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-cash-stack"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">$<?php echo number_format($total_revenue, 0); ?></h4>
          <p class="card-description">Total Revenue</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Navigation Tabs -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="btn-group flex-wrap" role="group">
        <button class="btn btn-outline-primary active" onclick="showTab('plans')">Membership Plans</button>
        <button class="btn btn-outline-primary" onclick="showTab('subscriptions')">Active Subscriptions</button>
        <button class="btn btn-outline-primary" onclick="showTab('analytics')">Analytics</button>
      </div>
    </div>
  </div>

  <!-- Membership Plans Tab -->
  <div id="plans-tab" class="management-tab">
    <div class="row mb-4">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
          <h3>Membership Plans</h3>
          <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#createPlanModal">
            <i class="bi bi-plus-circle"></i> Create New Plan
          </button>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="admin-card">
          <div class="card-content">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Plan Name</th>
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($membership_plans)): ?>
                    <?php foreach ($membership_plans as $plan): ?>
                    <tr>
                      <td>
                        <strong><?php echo htmlspecialchars($plan['name']); ?></strong>
                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($plan['description'], 0, 50)) . (strlen($plan['description']) > 50 ? '...' : ''); ?></small>
                      </td>
                      <td>$<?php echo number_format($plan['price'], 2); ?></td>
                      <td><?php echo $plan['duration_days']; ?> days</td>
                      <td>
                        <span class="badge bg-<?php echo $plan['is_active'] ? 'success' : 'secondary'; ?>">
                          <?php echo $plan['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                      </td>
                      <td>
                        <div class="btn-group">
                          <button class="btn btn-sm btn-outline-primary" onclick="editPlan(<?php echo $plan['id']; ?>)">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <?php if ($plan['is_active']): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deletePlan(<?php echo $plan['id']; ?>, '<?php echo htmlspecialchars($plan['name']); ?>')">
                              <i class="bi bi-trash"></i>
                            </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="5" class="text-center py-5">
                        <i class="bi bi-trophy fs-1 text-muted"></i>
                        <p class="text-muted">No membership plans found</p>
                        <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#createPlanModal">Create Your First Plan</button>
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

  <!-- Active Subscriptions Tab -->
  <div id="subscriptions-tab" class="management-tab d-none">
    <div class="row mb-4">
      <div class="col-12">
        <h3>Active Subscriptions</h3>
        <p>Manage current member subscriptions and renewals</p>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="admin-card">
          <div class="card-content">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Member</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($active_subscriptions)): ?>
                    <?php foreach ($active_subscriptions as $subscription): ?>
                    <tr>
                      <td>
                        <strong><?php echo htmlspecialchars($subscription['first_name'] . ' ' . $subscription['last_name']); ?></strong>
                        <br><small class="text-muted"><?php echo htmlspecialchars($subscription['email']); ?></small>
                      </td>
                      <td><?php echo htmlspecialchars($subscription['plan_name'] ?? 'Custom Plan'); ?></td>
                      <td>
                        <span class="badge bg-<?php
                          echo $subscription['status'] === 'active' ? 'success' :
                               ($subscription['status'] === 'expired' ? 'danger' :
                               ($subscription['status'] === 'cancelled' ? 'warning' : 'secondary'));
                        ?>">
                          <?php echo ucfirst($subscription['status']); ?>
                        </span>
                      </td>
                      <td><?php echo date('M j, Y', strtotime($subscription['start_date'])); ?></td>
                      <td><?php echo date('M j, Y', strtotime($subscription['end_date'])); ?></td>
                      <td>
                        <div class="btn-group">
                          <button class="btn btn-sm btn-outline-primary" onclick="editSubscription(<?php echo $subscription['id']; ?>)">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button class="btn btn-sm btn-outline-info" onclick="viewDetails(<?php echo $subscription['id']; ?>)">
                            <i class="bi bi-eye"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="6" class="text-center py-5">
                        <i class="bi bi-people fs-1 text-muted"></i>
                        <p class="text-muted">No active subscriptions found</p>
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

  <!-- Analytics Tab -->
  <div id="analytics-tab" class="management-tab d-none">
    <div class="row mb-4">
      <div class="col-12">
        <h3>Membership Analytics</h3>
        <p>Revenue trends and subscription insights</p>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="admin-card">
          <div class="card-content">
            <h4 class="card-title mb-4">Revenue by Plan</h4>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Plan</th>
                    <th>Revenue</th>
                    <th>Count</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $plan_revenue = $pdo->query('
                    SELECT mp.name as plan_name, SUM(mp.price) as revenue, COUNT(m.id) as count
                    FROM memberships m
                    JOIN membership_plans mp ON m.plan_id = mp.id
                    WHERE m.status = "active"
                    GROUP BY mp.id, mp.name
                    ORDER BY revenue DESC
                  ')->fetchAll();
                  ?>
                  <?php foreach ($plan_revenue as $plan): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($plan['plan_name']); ?></td>
                    <td>$<?php echo number_format($plan['revenue'], 2); ?></td>
                    <td><?php echo $plan['count']; ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="admin-card">
          <div class="card-content">
            <h4 class="card-title mb-4">Subscription Status Overview</h4>
            <div class="row g-3">
              <?php
              $status_counts = $pdo->query('
                SELECT status, COUNT(*) as count
                FROM memberships
                GROUP BY status
              ')->fetchAll();
              ?>
              <?php foreach ($status_counts as $status): ?>
              <div class="col-6">
                <div class="text-center">
                  <div class="h4 text-primary"><?php echo $status['count']; ?></div>
                  <small class="text-muted"><?php echo ucfirst($status['status']); ?></small>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Back to Dashboard -->
  <div class="row mt-4">
    <div class="col-12">
      <a href="admin.php" class="btn btn-admin">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
      </a>
    </div>
  </div>
</div>

<!-- Create Plan Modal -->
<div class="modal fade" id="createPlanModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create Membership Plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Plan Name</label>
                <input type="text" class="form-control" name="plan_name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Price ($)</label>
                <input type="number" step="0.01" class="form-control" name="price" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Duration (Days)</label>
            <input type="number" class="form-control" name="duration_days" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_active" checked>
              <label class="form-check-label">Active Plan</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="create_plan" class="btn btn-admin">Create Plan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Plan Modal -->
<div class="modal fade" id="editPlanModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Membership Plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="plan_id" id="edit_plan_id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Plan Name</label>
                <input type="text" class="form-control" name="plan_name" id="edit_plan_name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Price ($)</label>
                <input type="number" step="0.01" class="form-control" name="price" id="edit_plan_price" required>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Duration (Days)</label>
            <input type="number" class="form-control" name="duration_days" id="edit_plan_duration" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" id="edit_plan_description" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_active" id="edit_plan_active">
              <label class="form-check-label">Active Plan</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_plan" class="btn btn-admin">Update Plan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Subscription Modal -->
<div class="modal fade" id="editSubscriptionModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Subscription</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="subscription_id" id="edit_subscription_id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Member</label>
                <input type="text" class="form-control" id="edit_subscription_member" readonly>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Plan</label>
                <input type="text" class="form-control" id="edit_subscription_plan" readonly>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-control" name="status" id="edit_subscription_status">
                  <option value="active">Active</option>
                  <option value="expired">Expired</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" id="edit_subscription_end_date" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_subscription" class="btn btn-admin">Update Subscription</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.management-tab').forEach(tab => {
        tab.classList.add('d-none');
    });

    // Show selected tab
    document.getElementById(tabName + '-tab').classList.remove('d-none');

    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function editPlan(planId) {
    // Fetch plan data and populate edit modal
    fetch(`admin_memberships.php?get_plan=${planId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_plan_id').value = data.plan.id;
                document.getElementById('edit_plan_name').value = data.plan.name;
                document.getElementById('edit_plan_price').value = data.plan.price;
                document.getElementById('edit_plan_duration').value = data.plan.duration_days;
                document.getElementById('edit_plan_description').value = data.plan.description;
                document.getElementById('edit_plan_active').checked = data.plan.is_active == 1;

                const modal = new bootstrap.Modal(document.getElementById('editPlanModal'));
                modal.show();
            } else {
                alert('Error loading plan data: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function deletePlan(planId, planName) {
    if (confirm('Are you sure you want to deactivate "' + planName + '"?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="plan_id" value="${planId}">
            <input type="hidden" name="delete_plan" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function editSubscription(subscriptionId) {
    // Fetch subscription data and populate edit modal
    fetch(`admin_memberships.php?get_subscription=${subscriptionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_subscription_id').value = data.subscription.id;
                document.getElementById('edit_subscription_member').value = data.subscription.member_name;
                document.getElementById('edit_subscription_plan').value = data.subscription.plan_name;
                document.getElementById('edit_subscription_status').value = data.subscription.status;
                document.getElementById('edit_subscription_end_date').value = data.subscription.end_date.split(' ')[0]; // Extract date part

                const modal = new bootstrap.Modal(document.getElementById('editSubscriptionModal'));
                modal.show();
            } else {
                alert('Error loading subscription data: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function viewDetails(subscriptionId) {
    // For now, just show edit modal - can be enhanced later
    editSubscription(subscriptionId);
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>