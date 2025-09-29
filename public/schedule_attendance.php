<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_admin()) {
    header('Location: /Capstone-latest/public/login.php?admin=1&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$pageTitle = "Schedule & Attendance";
$pageCSS = "/assets/css/admin.css";

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['record_attendance'])) {
            // Record attendance
            $stmt = $pdo->prepare('
                INSERT INTO class_attendance (class_id, user_id, attendance_date, check_in_time, status, recorded_by)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                check_in_time = VALUES(check_in_time),
                status = VALUES(status),
                recorded_by = VALUES(recorded_by)
            ');
            $stmt->execute([
                $_POST['class_id'],
                $_POST['user_id'],
                $_POST['attendance_date'],
                $_POST['check_in_time'],
                $_POST['status'],
                $_SESSION['user']['id']
            ]);
            $message = 'Attendance recorded successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['update_schedule'])) {
            // Update class schedule
            $stmt = $pdo->prepare('UPDATE classes SET title = ?, description = ?, start_time = ?, end_time = ?, capacity = ?, status = ? WHERE id = ?');
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['start_time'],
                $_POST['end_time'],
                $_POST['capacity'],
                $_POST['status'],
                $_POST['class_id']
            ]);
            $message = 'Class schedule updated successfully!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get today's classes and attendance
$stmt = $pdo->query("
    SELECT c.*,
           CONCAT(u.first_name, ' ', u.last_name) as trainer_name,
           COUNT(ca.id) as attendance_count
    FROM classes c
    LEFT JOIN users u ON c.trainer_id = u.id
    LEFT JOIN class_attendance ca ON c.id = ca.class_id AND ca.attendance_date = CURDATE()
    WHERE DATE(c.start_time) = CURDATE()
    GROUP BY c.id
    ORDER BY c.start_time ASC
");
$todays_classes = $stmt->fetchAll();

// Get upcoming classes
$stmt = $pdo->query("
    SELECT c.*,
           CONCAT(u.first_name, ' ', u.last_name) as trainer_name,
           COUNT(cb.id) as bookings_count
    FROM classes c
    LEFT JOIN users u ON c.trainer_id = u.id
    LEFT JOIN class_bookings cb ON c.id = cb.class_id
    WHERE c.start_time > NOW()
    GROUP BY c.id
    ORDER BY c.start_time ASC
    LIMIT 10
");
$upcoming_classes = $stmt->fetchAll();

// Get attendance stats
$stmt = $pdo->query('SELECT COUNT(*) as today_attendance FROM class_attendance WHERE attendance_date = CURDATE()');
$today_attendance = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as total_classes_today FROM classes WHERE DATE(start_time) = CURDATE()');
$total_classes_today = $stmt->fetchColumn();

// Get class popularity (most booked classes this month)
$stmt = $pdo->query("
    SELECT c.title,
           COUNT(cb.id) as booking_count
    FROM classes c
    LEFT JOIN class_bookings cb ON c.id = cb.class_id
    WHERE MONTH(c.start_time) = MONTH(NOW()) AND YEAR(c.start_time) = YEAR(NOW())
    GROUP BY c.id, c.title
    ORDER BY booking_count DESC
    LIMIT 5
");
$popular_classes = $stmt->fetchAll();

// Get attendance trends (last 7 days)
$stmt = $pdo->query("
    SELECT DATE(attendance_date) as date,
           COUNT(*) as attendance_count
    FROM class_attendance
    WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(attendance_date)
    ORDER BY date ASC
");
$attendance_trend = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="admin-hero py-4">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Schedule & Attendance</li>
          </ol>
        </nav>
        <h1 class="display-5 fw-bold text-gradient mb-0">Schedule & Attendance</h1>
        <p class="lead">Monitor class schedules, track attendance, and view popularity</p>
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
          <i class="bi bi-calendar-event"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $total_classes_today; ?></h4>
          <p class="card-description">Today's Classes</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $today_attendance; ?></h4>
          <p class="card-description">Today's Attendance</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-graph-up"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo count($upcoming_classes); ?></h4>
          <p class="card-description">Upcoming Classes</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-star"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo count($popular_classes); ?></h4>
          <p class="card-description">Popular Classes</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Today's Classes & Attendance -->
  <div class="row mb-5">
    <div class="col-12">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">Today's Classes & Attendance</h4>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Class</th>
                  <th>Trainer</th>
                  <th>Time</th>
                  <th>Capacity</th>
                  <th>Attendance</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($todays_classes)): ?>
                  <?php foreach ($todays_classes as $class): ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($class['title']); ?></strong>
                      <br><small class="text-muted"><?php echo htmlspecialchars($class['description']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($class['trainer_name'] ?? 'Unassigned'); ?></td>
                    <td><?php echo date('H:i', strtotime($class['start_time'])) . ' - ' . date('H:i', strtotime($class['end_time'])); ?></td>
                    <td><?php echo $class['capacity']; ?></td>
                    <td>
                      <span class="badge bg-<?php echo $class['attendance_count'] > 0 ? 'success' : 'secondary'; ?>">
                        <?php echo $class['attendance_count']; ?> / <?php echo $class['capacity']; ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-<?php
                        echo $class['status'] === 'active' ? 'success' :
                             ($class['status'] === 'cancelled' ? 'danger' : 'warning');
                      ?>">
                        <?php echo ucfirst($class['status']); ?>
                      </span>
                    </td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewAttendance(<?php echo $class['id']; ?>)">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="recordAttendance(<?php echo $class['id']; ?>)">
                          <i class="bi bi-plus-circle"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="editSchedule(<?php echo $class['id']; ?>)">
                          <i class="bi bi-pencil"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="7" class="text-center py-5">
                      <i class="bi bi-calendar-x fs-1 text-muted"></i>
                      <p class="text-muted">No classes scheduled for today</p>
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

  <!-- Popular Classes & Upcoming Schedule -->
  <div class="row g-4 mb-5">
    <!-- Popular Classes -->
    <div class="col-lg-6">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">Most Popular Classes (This Month)</h4>
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

    <!-- Upcoming Classes -->
    <div class="col-lg-6">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">Upcoming Classes</h4>
          <div class="list-group list-group-flush">
            <?php if (!empty($upcoming_classes)): ?>
              <?php foreach ($upcoming_classes as $class): ?>
              <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <strong><?php echo htmlspecialchars($class['title']); ?></strong>
                    <br><small class="text-muted">
                      <?php echo date('M j, Y H:i', strtotime($class['start_time'])); ?> •
                      <?php echo htmlspecialchars($class['trainer_name'] ?? 'Unassigned'); ?>
                    </small>
                  </div>
                  <span class="badge bg-info"><?php echo $class['bookings_count']; ?> booked</span>
                </div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="list-group-item text-center py-4">
                <i class="bi bi-calendar fs-1 text-muted"></i>
                <p class="text-muted mb-0">No upcoming classes</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Attendance Trend -->
  <?php if (!empty($attendance_trend)): ?>
  <div class="row">
    <div class="col-12">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">7-Day Attendance Trend</h4>
          <div class="row g-3">
            <?php foreach ($attendance_trend as $day): ?>
            <div class="col-md-2">
              <div class="text-center">
                <div class="fw-bold"><?php echo date('M j', strtotime($day['date'])); ?></div>
                <div class="h4 text-primary"><?php echo $day['attendance_count']; ?></div>
                <small class="text-muted">attendees</small>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Record Attendance Modal -->
<div class="modal fade" id="recordAttendanceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Record Attendance</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="attendanceForm">
        <div class="modal-body">
          <input type="hidden" name="class_id" id="attendance_class_id">
          <div class="mb-3">
            <label class="form-label">Member</label>
            <select class="form-control" name="user_id" required>
              <option value="">Select Member</option>
              <!-- Options will be loaded dynamically -->
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Check-in Time</label>
            <input type="time" class="form-control" name="check_in_time" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-control" name="status">
              <option value="present">Present</option>
              <option value="late">Late</option>
              <option value="absent">Absent</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="record_attendance" class="btn btn-admin">Record Attendance</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function viewAttendance(classId) {
    // Implement view attendance functionality
    alert('View attendance for class ID: ' + classId);
}

function recordAttendance(classId) {
    document.getElementById('attendance_class_id').value = classId;
    // Load members for this class
    fetch(`get_class_members.php?class_id=${classId}`)
        .then(response => response.json())
        .then(data => {
            const select = document.querySelector('select[name="user_id"]');
            select.innerHTML = '<option value="">Select Member</option>';
            data.forEach(member => {
                select.innerHTML += `<option value="${member.id}">${member.name}</option>`;
            });
            new bootstrap.Modal(document.getElementById('recordAttendanceModal')).show();
        })
        .catch(error => {
            alert('Error loading class members');
        });
}

function editSchedule(classId) {
    // Implement edit schedule functionality
    alert('Edit schedule for class ID: ' + classId);
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>