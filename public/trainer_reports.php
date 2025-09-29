<?php 
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../app/helpers/auth.php'; 

if (!is_admin()) { 
    header('Location: /Capstone-latest/public/login.php?admin=1&redirect=' . urlencode($_SERVER['REQUEST_URI'])); 
    exit(); 
}

$pageTitle = "Trainer Utilization Reports";
$pageCSS = "/assets/css/admin.css";

// Get trainer utilization data
try {
    // Get all trainers with their stats
    $stmt = $pdo->query('
        SELECT 
            u.id,
            CONCAT(u.first_name, " ", u.last_name) as trainer_name,
            u.email,
            u.status,
            COUNT(DISTINCT c.id) as total_classes,
            COUNT(DISTINCT cb.id) as total_bookings,
            AVG(cb.booking_count) as avg_bookings_per_class,
            COUNT(DISTINCT tm.id) as total_messages,
            COUNT(DISTINCT ts.id) as sick_leaves,
            COUNT(DISTINCT tsg.id) as suggestions_given
        FROM users u
        LEFT JOIN classes c ON u.id = c.trainer_id
        LEFT JOIN class_bookings cb ON c.id = cb.class_id
        LEFT JOIN trainer_messages tm ON u.id = tm.trainer_id
        LEFT JOIN trainer_sick_leaves ts ON u.id = ts.trainer_id
        LEFT JOIN trainer_suggestions tsg ON u.id = tsg.trainer_id
        WHERE u.role_id = 3
        GROUP BY u.id, u.first_name, u.last_name, u.email, u.status
        ORDER BY total_bookings DESC
    ');
    $trainers = $stmt->fetchAll();
    
    // Get monthly performance data
    $stmt = $pdo->query('
        SELECT 
            DATE_FORMAT(cb.created_at, "%Y-%m") as month,
            COUNT(cb.id) as monthly_bookings,
            COUNT(DISTINCT c.trainer_id) as active_trainers
        FROM class_bookings cb
        JOIN classes c ON cb.class_id = c.id
        WHERE cb.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(cb.created_at, "%Y-%m")
        ORDER BY month DESC
    ');
    $monthly_data = $stmt->fetchAll();
    
    // Top performing trainer this month
    $stmt = $pdo->query('
        SELECT 
            CONCAT(u.first_name, " ", u.last_name) as trainer_name,
            COUNT(cb.id) as month_bookings
        FROM users u
        JOIN classes c ON u.id = c.trainer_id
        JOIN class_bookings cb ON c.id = cb.class_id
        WHERE u.role_id = 3 
        AND MONTH(cb.created_at) = MONTH(NOW()) 
        AND YEAR(cb.created_at) = YEAR(NOW())
        GROUP BY u.id, trainer_name
        ORDER BY month_bookings DESC
        LIMIT 1
    ');
    $top_trainer = $stmt->fetch();
    
} catch (Exception $e) {
    $trainers = [];
    $monthly_data = [];
    $top_trainer = null;
}
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="admin-hero py-4">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Trainer Utilization Reports</li>
          </ol>
        </nav>
        <h1 class="display-5 fw-bold text-gradient mb-0">Trainer Utilization Reports</h1>
        <p class="lead">Monitor trainer performance, class assignments, and utilization metrics</p>
      </div>
    </div>
  </div>
</div>

<div class="container pb-5">
  <!-- Summary Stats -->
  <div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-people"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo count($trainers); ?></h4>
          <p class="card-description">Total Trainers</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-star"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $top_trainer ? $top_trainer['trainer_name'] : 'N/A'; ?></h4>
          <p class="card-description">Top Performer This Month</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-calendar-check"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $top_trainer ? $top_trainer['month_bookings'] : 0; ?></h4>
          <p class="card-description">Top Trainer Bookings</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-graph-up"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo count(array_filter($trainers, function($t) { return $t['status'] == 'active'; })); ?></h4>
          <p class="card-description">Active Trainers</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Monthly Performance Chart -->
  <?php if (!empty($monthly_data)): ?>
  <div class="row mb-5">
    <div class="col-12">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">Monthly Performance Trend</h4>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Month</th>
                  <th>Total Bookings</th>
                  <th>Active Trainers</th>
                  <th>Avg Bookings per Trainer</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($monthly_data as $month): ?>
                <tr>
                  <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                  <td><span class="badge bg-primary"><?php echo $month['monthly_bookings']; ?></span></td>
                  <td><?php echo $month['active_trainers']; ?></td>
                  <td><?php echo $month['active_trainers'] > 0 ? round($month['monthly_bookings'] / $month['active_trainers'], 1) : 0; ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Trainer Performance Table -->
  <div class="row">
    <div class="col-12">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">Individual Trainer Performance</h4>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Trainer Name</th>
                  <th>Status</th>
                  <th>Total Classes</th>
                  <th>Total Bookings</th>
                  <th>Avg Bookings/Class</th>
                  <th>Messages</th>
                  <th>Sick Leaves</th>
                  <th>Suggestions</th>
                  <th>Utilization</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($trainers)): ?>
                  <?php foreach ($trainers as $trainer): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="trainer-avatar me-3">
                          <i class="bi bi-person-circle fs-4"></i>
                        </div>
                        <div>
                          <strong><?php echo htmlspecialchars($trainer['trainer_name']); ?></strong>
                          <br><small class="text-muted"><?php echo htmlspecialchars($trainer['email']); ?></small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <?php if ($trainer['status'] == 'active'): ?>
                        <span class="badge bg-success">Active</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                      <?php endif; ?>
                    </td>
                    <td><span class="badge bg-info"><?php echo $trainer['total_classes']; ?></span></td>
                    <td><span class="badge bg-primary"><?php echo $trainer['total_bookings']; ?></span></td>
                    <td><?php echo $trainer['total_classes'] > 0 ? round($trainer['avg_bookings_per_class'], 1) : 0; ?></td>
                    <td><?php echo $trainer['total_messages']; ?></td>
                    <td><?php echo $trainer['sick_leaves']; ?></td>
                    <td><?php echo $trainer['suggestions_given']; ?></td>
                    <td>
                      <?php 
                      $utilization = $trainer['total_bookings'];
                      if ($utilization >= 50): ?>
                        <span class="badge bg-success">High (<?php echo $utilization; ?>)</span>
                      <?php elseif ($utilization >= 20): ?>
                        <span class="badge bg-warning">Medium (<?php echo $utilization; ?>)</span>
                      <?php else: ?>
                        <span class="badge bg-danger">Low (<?php echo $utilization; ?>)</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="btn-group">
                        <a href="trainer_details.php?id=<?php echo $trainer['id']; ?>" class="btn btn-sm btn-outline-primary">
                          <i class="bi bi-eye"></i> View
                        </a>
                        <a href="mailto:<?php echo $trainer['email']; ?>" class="btn btn-sm btn-outline-secondary">
                          <i class="bi bi-envelope"></i> Email
                        </a>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="10" class="text-center py-5">
                      <i class="bi bi-person-x fs-1 text-muted"></i>
                      <p class="text-muted">No trainers found</p>
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

  <!-- Export & Actions -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <button class="btn btn-outline-primary me-2">
            <i class="bi bi-download"></i> Export to Excel
          </button>
          <button class="btn btn-outline-secondary me-2">
            <i class="bi bi-printer"></i> Print Report
          </button>
        </div>
        <div>
          <a href="admin.php" class="btn btn-admin">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>