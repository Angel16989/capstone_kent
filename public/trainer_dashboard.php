<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

// Check if user is logged in and is a trainer
require_login();
$current_user = current_user();
if (!$current_user || ($current_user['role_id'] !== 3 && $current_user['role_id'] !== 1)) {
    header('Location: login.php');
    exit;
}

$trainer_id = $current_user['id'];
$trainer_name = $current_user['first_name'] . ' ' . $current_user['last_name'];

// Get trainer's assigned classes
$classes_query = "SELECT c.*, COUNT(cb.user_id) as booked_count 
                 FROM classes c 
                 LEFT JOIN class_bookings cb ON c.id = cb.class_id 
                 WHERE c.trainer_id = ? AND DATE(c.start_time) >= CURDATE()
                 GROUP BY c.id 
                 ORDER BY c.start_time ASC";
$classes_stmt = $pdo->prepare($classes_query);
$classes_stmt->execute([$trainer_id]);
$upcoming_classes = $classes_stmt->fetchAll();

// Get recent messages (last 10)
$messages_query = "SELECT tm.*, u.first_name, u.last_name 
                  FROM trainer_messages tm 
                  LEFT JOIN users u ON tm.from_user_id = u.id 
                  WHERE tm.trainer_id = ? 
                  ORDER BY tm.created_at DESC 
                  LIMIT 10";
$messages_stmt = $pdo->prepare($messages_query);
$messages_stmt->execute([$trainer_id]);
$recent_messages = $messages_stmt->fetchAll();

// Get customer files forwarded by admin
$files_query = "SELECT cf.*, u.first_name, u.last_name, cf.file_name, cf.notes 
               FROM customer_files cf 
               JOIN users u ON cf.customer_id = u.id 
               WHERE cf.assigned_trainer_id = ? 
               ORDER BY cf.forwarded_at DESC";
$files_stmt = $pdo->prepare($files_query);
$files_stmt->execute([$trainer_id]);
$customer_files = $files_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard - L9 Fitness</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        .dashboard-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-2px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
        }
        .class-card {
            border-left: 4px solid #28a745;
        }
        .message-card {
            border-left: 4px solid #007bff;
        }
        .file-card {
            border-left: 4px solid #ffc107;
        }
        .quick-action-btn {
            border-radius: 25px;
            font-weight: 600;
            padding: 8px 20px;
        }
        .trainer-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-dumbbell me-2"></i>L9 FITNESS
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i>Member Dashboard</a>
                <a class="nav-link" href="profile.php"><i class="fas fa-user me-1"></i>Profile</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="fw-bold text-primary">
                    <i class="fas fa-chalkboard-teacher me-2"></i>Trainer Dashboard
                </h1>
                <p class="text-muted">Welcome back, <?php echo htmlspecialchars($trainer_name); ?>! Ready to train some legends?</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="trainer-avatar">
                    <?php echo strtoupper(substr($current_user['first_name'], 0, 1) . substr($current_user['last_name'], 0, 1)); ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-bolt text-warning me-2"></i>Quick Actions
                        </h5>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-primary quick-action-btn" onclick="showCallInSickModal()">
                                <i class="fas fa-phone me-1"></i>Call in Sick
                            </button>
                            <button class="btn btn-success quick-action-btn" onclick="showUploadSuggestionModal()">
                                <i class="fas fa-upload me-1"></i>Upload Suggestion
                            </button>
                            <button class="btn btn-info quick-action-btn" onclick="showMessagesModal()">
                                <i class="fas fa-envelope me-1"></i>Send Message
                            </button>
                            <button class="btn btn-warning quick-action-btn" onclick="showCustomerFilesModal()">
                                <i class="fas fa-folder me-1"></i>View Customer Files
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                        <h3><?php echo count($upcoming_classes); ?></h3>
                        <p class="mb-0">Upcoming Classes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h3><?php echo array_sum(array_column($upcoming_classes, 'booked_count')); ?></h3>
                        <p class="mb-0">Total Bookings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-envelope fa-2x mb-2"></i>
                        <h3><?php echo count($recent_messages); ?></h3>
                        <p class="mb-0">Recent Messages</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-alt fa-2x mb-2"></i>
                        <h3><?php echo count($customer_files); ?></h3>
                        <p class="mb-0">Customer Files</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="row">
            <!-- Upcoming Classes -->
            <div class="col-lg-6 mb-4">
                <div class="card dashboard-card class-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check me-2"></i>Upcoming Classes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcoming_classes)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                <p>No upcoming classes scheduled</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Class</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Bookings</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($upcoming_classes as $class): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($class['title']); ?></strong>
                                                </td>
                                                <td><?php echo date('M j', strtotime($class['start_time'])); ?></td>
                                                <td><?php echo date('g:i A', strtotime($class['start_time'])); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo $class['booked_count']; ?>/<?php echo $class['capacity'] ?? 20; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Messages -->
            <div class="col-lg-6 mb-4">
                <div class="card dashboard-card message-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-comments me-2"></i>Recent Messages
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_messages)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No messages yet</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_messages as $message): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>
                                                <?php echo $message['from_user_id'] ? 
                                                    htmlspecialchars($message['first_name'] . ' ' . $message['last_name']) : 
                                                    'Admin'; ?>
                                            </strong>
                                            <p class="mb-1 text-muted small">
                                                <?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?>
                                                <?php echo strlen($message['message']) > 100 ? '...' : ''; ?>
                                            </p>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('M j', strtotime($message['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Files -->
        <div class="row">
            <div class="col-12">
                <div class="card dashboard-card file-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-folder-open me-2"></i>Customer Files (Forwarded by Admin)
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($customer_files)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-folder fa-3x mb-3"></i>
                                <p>No customer files assigned yet</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>File Name</th>
                                            <th>Notes</th>
                                            <th>Forwarded</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customer_files as $file): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($file['first_name'] . ' ' . $file['last_name']); ?></strong>
                                                </td>
                                                <td>
                                                    <i class="fas fa-file-alt me-1"></i>
                                                    <?php echo htmlspecialchars($file['file_name']); ?>
                                                </td>
                                                <td>
                                                    <span class="text-muted">
                                                        <?php echo htmlspecialchars(substr($file['notes'] ?? 'No notes', 0, 50)); ?>
                                                        <?php echo strlen($file['notes'] ?? '') > 50 ? '...' : ''; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y', strtotime($file['forwarded_at'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewFile('<?php echo $file['id']; ?>')">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            onclick="addSuggestion('<?php echo $file['customer_id']; ?>')">
                                                        <i class="fas fa-lightbulb me-1"></i>Suggest
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Call in Sick Modal -->
    <div class="modal fade" id="callInSickModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-phone me-2 text-danger"></i>Call in Sick
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="callInSickForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Class to Cancel:</label>
                            <select class="form-select" name="class_id" required>
                                <option value="">Choose a class...</option>
                                <?php foreach ($upcoming_classes as $class): ?>
                                    <option value="<?php echo $class['id']; ?>">
                                        <?php echo htmlspecialchars($class['name']); ?> - 
                                        <?php echo date('M j, g:i A', strtotime($class['start_time'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason:</label>
                            <textarea class="form-control" name="reason" rows="3" 
                                     placeholder="Please provide a reason for cancellation..." required></textarea>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This will notify all booked members about the cancellation.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-phone me-1"></i>Call in Sick
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Suggestion Modal -->
    <div class="modal fade" id="uploadSuggestionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-lightbulb me-2 text-success"></i>Upload Suggestion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="uploadSuggestionForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Suggestion Title:</label>
                            <input type="text" class="form-control" name="title" 
                                  placeholder="e.g., New HIIT Workout Plan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description:</label>
                            <textarea class="form-control" name="description" rows="3" 
                                     placeholder="Describe your suggestion..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Attach File (Optional):</label>
                            <input type="file" class="form-control" name="suggestion_file" 
                                  accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">Max file size: 5MB</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload me-1"></i>Upload Suggestion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Send Message Modal -->
    <div class="modal fade" id="sendMessageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope me-2 text-info"></i>Send Message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="sendMessageForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">To:</label>
                            <select class="form-select" name="to_user" required>
                                <option value="admin">Admin</option>
                                <option value="all_members">All My Class Members</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject:</label>
                            <input type="text" class="form-control" name="subject" 
                                  placeholder="Message subject..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message:</label>
                            <textarea class="form-control" name="message" rows="4" 
                                     placeholder="Type your message..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-paper-plane me-1"></i>Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show modals
        function showCallInSickModal() {
            new bootstrap.Modal(document.getElementById('callInSickModal')).show();
        }
        
        function showUploadSuggestionModal() {
            new bootstrap.Modal(document.getElementById('uploadSuggestionModal')).show();
        }
        
        function showMessagesModal() {
            new bootstrap.Modal(document.getElementById('sendMessageModal')).show();
        }
        
        function showCustomerFilesModal() {
            // Scroll to customer files section
            document.querySelector('.file-card').scrollIntoView({ 
                behavior: 'smooth' 
            });
        }

        // Form handlers
        document.getElementById('callInSickForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('trainer_actions.php?action=call_in_sick', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sick leave submitted successfully. Members will be notified.');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });

        document.getElementById('uploadSuggestionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('trainer_actions.php?action=upload_suggestion', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Suggestion uploaded successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('uploadSuggestionModal')).hide();
                    this.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });

        document.getElementById('sendMessageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('trainer_actions.php?action=send_message', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Message sent successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('sendMessageModal')).hide();
                    this.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });

        function viewFile(fileId) {
            window.open('view_customer_file.php?id=' + fileId, '_blank');
        }

        function addSuggestion(customerId) {
            // Open suggestion modal with customer pre-selected
            const modal = new bootstrap.Modal(document.getElementById('uploadSuggestionModal'));
            document.querySelector('[name="title"]').value = 'Suggestion for Customer #' + customerId;
            modal.show();
        }
    </script>
</body>
</html>