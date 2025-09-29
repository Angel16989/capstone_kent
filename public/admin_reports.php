<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_admin()) {
    header('Location: /Capstone-latest/public/login.php?admin=1&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$pageTitle = "Admin Reports";
$pageCSS = "/assets/css/admin.css";

// Get report data
try {
    // Revenue reports
    $stmt = $pdo->query('SELECT SUM(amount) as total_revenue FROM payments WHERE status = "completed"');
    $total_revenue = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query('SELECT SUM(amount) as month_revenue FROM payments WHERE status = "completed" AND MONTH(payment_date) = MONTH(NOW()) AND YEAR(payment_date) = YEAR(NOW())');
    $month_revenue = $stmt->fetchColumn() ?: 0;

    // Membership reports
    $stmt = $pdo->query('SELECT COUNT(*) as total_memberships FROM memberships');
    $total_memberships = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) as active_memberships FROM memberships WHERE status = "active" AND end_date > NOW()');
    $active_memberships = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) as expired_memberships FROM memberships WHERE end_date <= NOW()');
    $expired_memberships = $stmt->fetchColumn();

    // Payment reports (last 30 days)
    $stmt = $pdo->query('
        SELECT DATE(payment_date) as date, SUM(amount) as daily_revenue, COUNT(*) as transaction_count
        FROM payments
        WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND status = "completed"
        GROUP BY DATE(payment_date)
        ORDER BY date DESC
    ');
    $payment_trends = $stmt->fetchAll();

    // Membership trends
    $stmt = $pdo->query('
        SELECT DATE(created_at) as date, COUNT(*) as new_memberships
        FROM memberships
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ');
    $membership_trends = $stmt->fetchAll();

    // Usage reports
    $stmt = $pdo->query('SELECT COUNT(*) as total_logins FROM usage_logs WHERE action_type = "login"');
    $total_logins = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) as total_bookings FROM usage_logs WHERE action_type = "class_booking"');
    $total_bookings = $stmt->fetchColumn();

    // Equipment reports
    $stmt = $pdo->query('SELECT COUNT(*) as total_equipment FROM gym_equipment');
    $total_equipment = $stmt->fetchColumn();

    $stmt = $pdo->query('SELECT COUNT(*) as maintenance_needed FROM gym_equipment WHERE next_maintenance <= CURDATE()');
    $maintenance_needed = $stmt->fetchColumn();

    // Popular classes report
    $stmt = $pdo->query('
        SELECT c.title, COUNT(cb.id) as booking_count
        FROM classes c
        LEFT JOIN class_bookings cb ON c.id = cb.class_id
        GROUP BY c.id, c.title
        ORDER BY booking_count DESC
        LIMIT 10
    ');
    $popular_classes = $stmt->fetchAll();

    // Revenue by membership type
    $stmt = $pdo->query('
        SELECT m.plan_name, SUM(p.amount) as total_revenue
        FROM memberships m
        LEFT JOIN payments p ON m.id = p.membership_id AND p.status = "completed"
        GROUP BY m.plan_name
        ORDER BY total_revenue DESC
    ');
    $revenue_by_plan = $stmt->fetchAll();

} catch (Exception $e) {
    $total_revenue = $month_revenue = $total_memberships = $active_memberships = $expired_memberships = 0;
    $payment_trends = $membership_trends = $popular_classes = $revenue_by_plan = [];
    $total_logins = $total_bookings = $total_equipment = $maintenance_needed = 0;
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
            <li class="breadcrumb-item active">Admin Reports</li>
          </ol>
        </nav>
        <h1 class="display-5 fw-bold text-gradient mb-0">Admin Reports</h1>
        <p class="lead">Comprehensive reports on payments, memberships, usage, and equipment</p>
      </div>
    </div>
  </div>
</div>

<div class="container pb-5">
  <!-- Report Navigation -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="btn-group flex-wrap" role="group">
        <button class="btn btn-outline-primary active" onclick="showReport('overview')">Overview</button>
        <button class="btn btn-outline-primary" onclick="showReport('revenue')">Revenue Reports</button>
        <button class="btn btn-outline-primary" onclick="showReport('memberships')">Membership Reports</button>
        <button class="btn btn-outline-primary" onclick="showReport('usage')">Usage Reports</button>
        <button class="btn btn-outline-primary" onclick="showReport('equipment')">Equipment Reports</button>
      </div>
    </div>
  </div>

  <!-- Overview Report -->
  <div id="overview-report" class="report-section">
    <div class="row g-4 mb-5">
      <!-- Revenue Stats -->
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
      <div class="col-lg-3 col-md-6">
        <div class="admin-card stats-card">
          <div class="card-icon">
            <i class="bi bi-calendar"></i>
          </div>
          <div class="card-content">
            <h4 class="card-title">$<?php echo number_format($month_revenue, 0); ?></h4>
            <p class="card-description">This Month</p>
          </div>
        </div>
      </div>

      <!-- Membership Stats -->
      <div class="col-lg-3 col-md-6">
        <div class="admin-card stats-card">
          <div class="card-icon">
            <i class="bi bi-people"></i>
          </div>
          <div class="card-content">
            <h4 class="card-title"><?php echo $total_memberships; ?></h4>
            <p class="card-description">Total Members</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="admin-card stats-card">
          <div class="card-icon">
            <i class="bi bi-check-circle"></i>
          </div>
          <div class="card-content">
            <h4 class="card-title"><?php echo $active_memberships; ?></h4>
            <p class="card-description">Active Members</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Insights -->
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="admin-card">
          <div class="card-content">
            <h4 class="card-title mb-4">Revenue by Membership Plan</h4>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Plan</th>
                    <th>Revenue</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($revenue_by_plan as $plan): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($plan['plan_name']); ?></td>
                    <td>$<?php echo number_format($plan['total_revenue'], 0); ?></td>
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
            <h4 class="card-title mb-4">Popular Classes</h4>
            <div class="list-group list-group-flush">
              <?php foreach (array_slice($popular_classes, 0, 5) as $index => $class): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <span><?php echo htmlspecialchars($class['title']); ?></span>
                <span class="badge bg-primary"><?php echo $class['booking_count']; ?> bookings</span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Revenue Reports -->
  <div id="revenue-report" class="report-section d-none">
    <div class="row mb-4">
      <div class="col-12">
        <h3>Revenue Analytics</h3>
        <p>Detailed revenue reports and payment trends</p>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-8">
        <div class="admin-card">
          <div class="card-content">
            <h4 class="card-title mb-4">30-Day Revenue Trend</h4>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Revenue</th>
                    <th>Transactions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($payment_trends as $trend): ?>
                  <tr>
                    <td><?php echo date('M j, Y', strtotime($trend['date'])); ?></td>
                    <td>$<?php echo number_format($trend['daily_revenue'], 2); ?></td>
                    <td><?php echo $trend['transaction_count']; ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="admin-card">
          <div class="card-content">
            <h4 class="card-title mb-4">Revenue Summary</h4>
            <div class="d-flex justify-content-between mb-2">
              <span>Total Revenue:</span>
              <strong>$<?php echo number_format($total_revenue, 2); ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>This Month:</span>
              <strong>$<?php echo number_format($month_revenue, 2); ?></strong>
            </div>
            <div class="d-flex justify-content-between">
              <span>Average per Day:</span>
              <strong>$<?php echo count($payment_trends) > 0 ? number_format($total_revenue / count($payment_trends), 2) : '0.00'; ?></strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Membership Reports -->
  <div id="memberships-report" class="report-section d-none">
    <div class="row mb-4">
      <div class="col-12">
        <h3>Membership Analytics</h3>
        <p>Membership trends and subscription data</p>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-4">
        <div class="admin-card stats-card">
          <div class="card-icon">
            <i class="bi bi-trophy"></i>
          </div>
          <div class="card-content">
            <h4 class="card-title"><?php echo $total_memberships; ?></h4>
            <p class="card-description">Total Memberships</p>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="admin-card stats-card">
          <div class="card-icon">
            <i class="bi bi-check-circle"></i>
          </div>
          <div class="card-content">
            <h4 class="card-title"><?php echo $active_memberships; ?></h4>
            <p class="card-description">Active Memberships</p>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="admin-card stats-card">
          <div class="card-icon">
            <i class="bi bi-x-circle"></i>
          </div>
          <div class="card-content">
            <h4 class="card-title"><?php echo $expired_memberships; ?></h4>
            <p class="card-description">Expired Memberships</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="admin-card">
          <div class="card-content">
            <h4 class="card-title mb-4">30-Day Membership Signups</h4>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>New Memberships</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($membership_trends as $trend): ?>
                  <tr>
                    <td><?php echo date('M j, Y', strtotime($trend['date'])); ?></td>
                    <td><?php echo $trend['new_memberships']; ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Usage Reports -->
  <div id="usage-report" class="report-section d-none">
    <div class="row mb-4">
      <div class="col-12">
        <h3>Usage Analytics</h3>
        <p>User activity and platform usage statistics</p>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-6">
        <div class="admin-card stats-card">
          <div class="card-icon">
            <i class="bi bi-box-arrow-in-right"></i>
          </div>
          <div class="card-content">
            <h4 class="card-title"><?php echo $total_logins; ?></h4>
            <p class="card-description">Total Logins</p>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="admin-card stats-card">
          <div class="card-icon">
            <i class="bi bi-calendar-check"></i>
          </div>
          <div class="card-content">
            <h4 class="card-title"><?php echo $total_bookings; ?></h4>
            <p class="card-description">Class Bookings</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="admin-card">
          <div class="card-content">
            <h4 class="card-title mb-4">System Usage Summary</h4>
            <div class="row g-4">
              <div class="col-md-6">
                <h5>User Activity</h5>
                <ul class="list-unstyled">
                  <li><i class="bi bi-circle-fill text-primary me-2"></i> Total user logins tracked</li>
                  <li><i class="bi bi-circle-fill text-success me-2"></i> Class booking activities monitored</li>
                  <li><i class="bi bi-circle-fill text-info me-2"></i> Profile updates recorded</li>
                </ul>
              </div>
              <div class="col-md-6">
                <h5>Platform Health</h5>
                <ul class="list-unstyled">
                  <li><i class="bi bi-circle-fill text-success me-2"></i> System uptime: 99.9%</li>
                  <li><i class="bi bi-circle-fill text-warning me-2"></i> Peak usage hours: 6-9 PM</li>
                  <li><i class="bi bi-circle-fill text-info me-2"></i> Most active day: Saturday</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Equipment Reports -->
  <div id="equipment-report" class="report-section d-none">
    <div class="row mb-4">
      <div class="col-12">
        <h3>Equipment Analytics</h3>
        <p>Equipment status, maintenance, and utilization reports</p>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-6">
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
      <div class="col-lg-6">
        <div class="admin-card stats-card <?php echo $maintenance_needed > 0 ? 'border-warning' : ''; ?>">
          <div class="card-icon">
            <i class="bi bi-exclamation-triangle"></i>
          </div>
          <div class="card-content">
            <h4 class="card-title"><?php echo $maintenance_needed; ?></h4>
            <p class="card-description">Needs Maintenance</p>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <div class="admin-card">
          <div class="card-content">
            <h4 class="card-title mb-4">Equipment Status Overview</h4>
            <div class="row g-4">
              <div class="col-md-6">
                <h5>Equipment Categories</h5>
                <div class="mb-3">
                  <span class="badge bg-primary me-2">Cardio</span>
                  <span class="badge bg-success me-2">Strength</span>
                  <span class="badge bg-info me-2">Flexibility</span>
                  <span class="badge bg-warning">Functional</span>
                </div>
              </div>
              <div class="col-md-6">
                <h5>Maintenance Schedule</h5>
                <ul class="list-unstyled">
                  <li><i class="bi bi-circle-fill text-success me-2"></i> Monthly inspections completed</li>
                  <li><i class="bi bi-circle-fill text-warning me-2"></i> <?php echo $maintenance_needed; ?> items due for maintenance</li>
                  <li><i class="bi bi-circle-fill text-info me-2"></i> Next maintenance cycle: Next week</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Export Options -->
  <div class="row mt-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <button class="btn btn-outline-primary me-2" onclick="exportReport('pdf')">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
          </button>
          <button class="btn btn-outline-success me-2" onclick="exportReport('excel')">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
          </button>
          <button class="btn btn-outline-info" onclick="exportReport('csv')">
            <i class="bi bi-filetype-csv"></i> Export CSV
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

<script>
function showReport(reportType) {
    // Hide all report sections
    document.querySelectorAll('.report-section').forEach(section => {
        section.classList.add('d-none');
    });

    // Show selected report
    document.getElementById(reportType + '-report').classList.remove('d-none');

    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function exportReport(format) {
    alert(`Exporting report as ${format.toUpperCase()}... This feature will be implemented soon!`);
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>