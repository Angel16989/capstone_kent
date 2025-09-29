<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_admin()) {
    header('Location: /Capstone-latest/public/login.php?admin=1&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$pageTitle = "Admin Class Management";
$pageCSS = "/assets/css/admin.css";

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create_class'])) {
            // Create new class
            $stmt = $pdo->prepare('INSERT INTO classes (title, description, trainer_id, start_time, end_time, capacity) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['trainer_id'],
                $_POST['start_time'],
                $_POST['end_time'],
                $_POST['capacity']
            ]);
            $message = 'Class created successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['update_class'])) {
            // Update existing class
            $stmt = $pdo->prepare('UPDATE classes SET title = ?, description = ?, trainer_id = ?, start_time = ?, end_time = ?, capacity = ? WHERE id = ?');
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['trainer_id'],
                $_POST['start_time'],
                $_POST['end_time'],
                $_POST['capacity'],
                $_POST['class_id']
            ]);
            $message = 'Class updated successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['delete_class'])) {
            // Delete class
            $stmt = $pdo->prepare('DELETE FROM classes WHERE id = ?');
            $stmt->execute([$_POST['class_id']]);
            $message = 'Class deleted successfully!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get classes with trainer information
$stmt = $pdo->query('
    SELECT c.*, CONCAT(u.first_name, " ", u.last_name) as trainer_name,
           COUNT(b.id) as booking_count
    FROM classes c
    LEFT JOIN users u ON c.trainer_id = u.id
    LEFT JOIN bookings b ON c.id = b.class_id AND b.status IN ("booked", "attended")
    GROUP BY c.id
    ORDER BY c.start_time DESC
');
$classes = $stmt->fetchAll();

// Get trainers
$stmt = $pdo->query('SELECT id, first_name, last_name FROM users WHERE role_id = 3 AND status = "active" ORDER BY first_name');
$trainers = $stmt->fetchAll();

// Get class statistics
$stmt = $pdo->query('SELECT COUNT(*) as total_classes FROM classes');
$total_classes = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as upcoming_classes FROM classes WHERE start_time > NOW()');
$upcoming_classes = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as today_classes FROM classes WHERE DATE(start_time) = CURDATE()');
$today_classes = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as active_trainers FROM users WHERE role_id = 3 AND status = "active"');
$active_trainers = $stmt->fetchColumn();

// Get popular classes
$stmt = $pdo->query('
    SELECT c.title, COUNT(b.id) as booking_count, AVG(c.capacity) as avg_capacity
    FROM classes c
    LEFT JOIN bookings b ON c.id = b.class_id AND b.status IN ("booked", "attended")
    WHERE c.start_time > DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY c.id, c.title
    ORDER BY booking_count DESC
    LIMIT 5
');
$popular_classes = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="admin-hero py-4">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Class Management</li>
          </ol>
        </nav>
        <h1 class="display-5 fw-bold text-gradient mb-0">Class Management</h1>
        <p class="lead">Schedule classes, manage instructors, and monitor attendance</p>
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
          <i class="bi bi-calendar-event"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $total_classes; ?></h4>
          <p class="card-description">Total Classes</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-clock"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $upcoming_classes; ?></h4>
          <p class="card-description">Upcoming Classes</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-calendar-today"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $today_classes; ?></h4>
          <p class="card-description">Today's Classes</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-person-workspace"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $active_trainers; ?></h4>
          <p class="card-description">Active Trainers</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Navigation Tabs -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="btn-group flex-wrap" role="group">
        <button class="btn btn-outline-primary active" onclick="showTab('all')">All Classes</button>
        <button class="btn btn-outline-primary" onclick="showTab('upcoming')">Upcoming</button>
        <button class="btn btn-outline-primary" onclick="showTab('today')">Today</button>
        <button class="btn btn-outline-primary" onclick="showTab('analytics')">Analytics</button>
      </div>
    </div>
  </div>

  <!-- All Classes Tab -->
  <div id="all-tab" class="class-tab">
    <div class="row mb-4">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
          <h3>All Classes</h3>
          <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#createClassModal">
            <i class="bi bi-plus-circle"></i> Schedule New Class
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
                    <th>Class</th>
                    <th>Trainer</th>
                    <th>Date & Time</th>
                    <th>Capacity</th>
                    <th>Bookings</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($classes)): ?>
                    <?php foreach ($classes as $class): ?>
                    <tr>
                      <td>
                        <strong><?php echo htmlspecialchars($class['title']); ?></strong>
                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($class['description'], 0, 50)) . (strlen($class['description']) > 50 ? '...' : ''); ?></small>
                      </td>
                      <td><?php echo htmlspecialchars($class['trainer_name'] ?? 'Unassigned'); ?></td>
                      <td>
                        <?php echo date('M j, Y', strtotime($class['start_time'])); ?>
                        <br><small><?php echo date('H:i', strtotime($class['start_time'])) . ' - ' . date('H:i', strtotime($class['end_time'])); ?></small>
                      </td>
                      <td><?php echo $class['capacity']; ?></td>
                      <td>
                        <span class="badge bg-<?php echo $class['booking_count'] > 0 ? 'success' : 'secondary'; ?>">
                          <?php echo $class['booking_count']; ?> / <?php echo $class['capacity']; ?>
                        </span>
                      </td>
                      <td>
                        <span class="badge bg-success">Active</span>
                      </td>
                      <td>
                        <div class="btn-group">
                          <button class="btn btn-sm btn-outline-primary" onclick="editClass(<?php echo $class['id']; ?>)">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button class="btn btn-sm btn-outline-info" onclick="viewBookings(<?php echo $class['id']; ?>)">
                            <i class="bi bi-eye"></i>
                          </button>
                          <?php if (strtotime($class['start_time']) > time()): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteClass(<?php echo $class['id']; ?>, '<?php echo htmlspecialchars($class['title']); ?>')">
                              <i class="bi bi-trash"></i>
                            </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="7" class="text-center py-5">
                        <i class="bi bi-calendar-x fs-1 text-muted"></i>
                        <p class="text-muted">No classes scheduled</p>
                        <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#createClassModal">Schedule Your First Class</button>
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

  <!-- Upcoming Classes Tab -->
  <div id="upcoming-tab" class="class-tab d-none">
    <div class="row mb-4">
      <div class="col-12">
        <h3>Upcoming Classes</h3>
        <p>Classes scheduled for the future</p>
      </div>
    </div>

    <div class="row">
      <?php
      $upcoming = array_filter($classes, function($class) {
          return strtotime($class['start_time']) > time();
      });
      ?>
      <?php if (!empty($upcoming)): ?>
        <?php foreach ($upcoming as $class): ?>
        <div class="col-lg-6 col-md-6 mb-4">
          <div class="admin-card">
            <div class="card-content">
              <h5 class="card-title"><?php echo htmlspecialchars($class['title']); ?></h5>
              <p class="text-muted"><?php echo htmlspecialchars($class['description']); ?></p>
              <div class="row g-2">
                <div class="col-6">
                  <small><i class="bi bi-person"></i> <?php echo htmlspecialchars($class['trainer_name'] ?? 'Unassigned'); ?></small>
                </div>
                <div class="col-6">
                  <small><i class="bi bi-calendar"></i> <?php echo date('M j, Y', strtotime($class['start_time'])); ?></small>
                </div>
                <div class="col-6">
                  <small><i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($class['start_time'])); ?></small>
                </div>
                <div class="col-6">
                  <small><i class="bi bi-people"></i> <?php echo $class['booking_count']; ?>/<?php echo $class['max_capacity']; ?></small>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="admin-card">
            <div class="card-content text-center py-5">
              <i class="bi bi-calendar-x fs-1 text-muted"></i>
              <p class="text-muted">No upcoming classes</p>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Today's Classes Tab -->
  <div id="today-tab" class="class-tab d-none">
    <div class="row mb-4">
      <div class="col-12">
        <h3>Today's Classes</h3>
        <p>Classes scheduled for today</p>
      </div>
    </div>

    <div class="row">
      <?php
      $today = array_filter($classes, function($class) {
          return date('Y-m-d', strtotime($class['start_time'])) === date('Y-m-d');
      });
      ?>
      <?php if (!empty($today)): ?>
        <?php foreach ($today as $class): ?>
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="admin-card">
            <div class="card-content">
              <h5 class="card-title"><?php echo htmlspecialchars($class['title']); ?></h5>
              <div class="mb-3">
                <span class="badge bg-success">Scheduled</span>
              </div>
              <div class="row g-2">
                <div class="col-12">
                  <small><i class="bi bi-person"></i> <?php echo htmlspecialchars($class['trainer_name'] ?? 'Unassigned'); ?></small>
                </div>
                <div class="col-12">
                  <small><i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($class['start_time'])) . ' - ' . date('H:i', strtotime($class['end_time'])); ?></small>
                </div>
                <div class="col-12">
                  <small><i class="bi bi-people"></i> <?php echo $class['booking_count']; ?>/<?php echo $class['max_capacity']; ?> booked</small>
                </div>
              </div>
              <div class="mt-3">
                <button class="btn btn-sm btn-outline-primary me-2" onclick="viewBookings(<?php echo $class['id']; ?>)">
                  View Bookings
                </button>
                <button class="btn btn-sm btn-outline-info" onclick="editClass(<?php echo $class['id']; ?>)">
                  Edit
                </button>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="admin-card">
            <div class="card-content text-center py-5">
              <i class="bi bi-calendar-x fs-1 text-muted"></i>
              <p class="text-muted">No classes scheduled for today</p>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Analytics Tab -->
  <div id="analytics-tab" class="class-tab d-none">
    <div class="row mb-4">
      <div class="col-12">
        <h3>Class Analytics</h3>
        <p>Popular classes and booking trends</p>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="admin-card">
          <div class="card-content">
            <h4 class="card-title mb-4">Most Popular Classes (30 Days)</h4>
            <div class="list-group list-group-flush">
              <?php if (!empty($popular_classes)): ?>
                <?php foreach ($popular_classes as $index => $class): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <strong><?php echo htmlspecialchars($class['title']); ?></strong>
                    <br><small class="text-muted"><?php echo $class['booking_count']; ?> bookings</small>
                  </div>
                  <span class="badge bg-primary rounded-pill">#<?php echo $index + 1; ?></span>
                </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="list-group-item text-center py-4">
                  <i class="bi bi-star fs-1 text-muted"></i>
                  <p class="text-muted mb-0">No booking data available</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="admin-card">
          <div class="card-content">
            <h4 class="card-title mb-4">Class Performance Overview</h4>
            <div class="row g-3">
              <div class="col-6">
                <div class="text-center">
                  <div class="h4 text-success"><?php echo array_sum(array_column($classes, 'booking_count')); ?></div>
                  <small class="text-muted">Total Bookings</small>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center">
                  <div class="h4 text-primary"><?php echo count(array_filter($classes, function($c) { return $c['status'] === 'completed'; })); ?></div>
                  <small class="text-muted">Completed Classes</small>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center">
                  <div class="h4 text-warning">0</div>
                  <small class="text-muted">Cancelled Classes</small>
                </div>
              </div>
              <div class="col-6">
                <div class="text-center">
                  <div class="h4 text-info"><?php echo round(array_sum(array_column($classes, 'booking_count')) / max(1, count($classes)), 1); ?></div>
                  <small class="text-muted">Avg Bookings/Class</small>
                </div>
              </div>
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

<!-- Create Class Modal -->
<div class="modal fade" id="createClassModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Schedule New Class</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Class Title</label>
                <input type="text" class="form-control" name="title" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Trainer</label>
                <select class="form-control" name="trainer_id" required>
                  <option value="">Select Trainer</option>
                  <?php foreach ($trainers as $trainer): ?>
                    <option value="<?php echo $trainer['id']; ?>">
                      <?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3" required></textarea>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Start Date & Time</label>
                <input type="datetime-local" class="form-control" name="start_time" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">End Date & Time</label>
                <input type="datetime-local" class="form-control" name="end_time" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Max Capacity</label>
                <input type="number" class="form-control" name="capacity" min="1" required>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="create_class" class="btn btn-admin">Schedule Class</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.class-tab').forEach(tab => {
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

function editClass(classId) {
    // Implement edit functionality
    alert('Edit functionality for class ID: ' + classId + ' will be implemented');
}

function viewBookings(classId) {
    // Implement view bookings functionality
    alert('View bookings for class ID: ' + classId + ' will be implemented');
}

function deleteClass(classId, className) {
    if (confirm('Are you sure you want to permanently delete "' + className + '"? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="class_id" value="${classId}">
            <input type="hidden" name="delete_class" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>