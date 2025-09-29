<?php
session_start();
require_once '../config/config.php';
require_once '../app/helpers/auth.php';

// Admin only access
if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

$success_message = '';
$error_message = '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=l9_gym", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create_announcement':
                $stmt = $pdo->prepare("
                    INSERT INTO announcements (title, content, target_audience, announcement_type, created_by)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['content'],
                    $_POST['target_audience'],
                    $_POST['announcement_type'],
                    $_SESSION['user']['id']
                ]);
                $success_message = "Announcement created successfully!";
                break;
                
            case 'update_announcement':
                $stmt = $pdo->prepare("
                    UPDATE announcements 
                    SET title = ?, content = ?, target_audience = ?, announcement_type = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['content'],
                    $_POST['target_audience'],
                    $_POST['announcement_type'],
                    isset($_POST['is_active']) ? 1 : 0,
                    $_POST['announcement_id']
                ]);
                $success_message = "Announcement updated successfully!";
                break;
                
            case 'delete_announcement':
                $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
                $stmt->execute([$_POST['announcement_id']]);
                $success_message = "Announcement deleted successfully!";
                break;
        }
    }
    
    // Get all announcements
    $stmt = $pdo->prepare("
        SELECT a.*, u.first_name, u.last_name,
               COUNT(ar.id) as read_count
        FROM announcements a
        LEFT JOIN users u ON a.created_by = u.id
        LEFT JOIN announcement_reads ar ON a.id = ar.announcement_id
        GROUP BY a.id
        ORDER BY a.created_at DESC
    ");
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total user count for read statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users WHERE role != 'admin'");
    $stmt->execute();
    $user_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement Manager - L9 Fitness Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 1rem 0;
        }
        
        .announcement-card {
            border: 1px solid #333;
            border-radius: 10px;
            margin-bottom: 1rem;
            background: #1a1a1a;
        }
        
        .announcement-header {
            background: #2d2d2d;
            padding: 1rem;
            border-radius: 10px 10px 0 0;
            border-bottom: 1px solid #333;
        }
        
        .announcement-body {
            padding: 1rem;
        }
        
        .stats-badge {
            background: #ff6b35;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1><i class="fas fa-bullhorn"></i> Announcement Manager</h1>
            <nav>
                <a href="admin.php" class="text-white me-3">Admin Dashboard</a>
                <a href="index.php" class="text-white">Back to Site</a>
            </nav>
        </div>
    </div>

    <div class="container mt-4">
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Create New Announcement -->
        <div class="card bg-dark border-secondary mb-4">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> Create New Announcement</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="create_announcement">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea class="form-control" name="content" rows="4" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Target Audience</label>
                                <select class="form-control" name="target_audience" required>
                                    <option value="all">All Users</option>
                                    <option value="members">Members Only</option>
                                    <option value="trainers">Trainers Only</option>
                                    <option value="admins">Admins Only</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-control" name="announcement_type" required>
                                    <option value="general">General</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="event">Event</option>
                                    <option value="promotion">Promotion</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Announcement</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Announcements -->
        <h3>Existing Announcements</h3>
        <?php if ($announcements): ?>
            <?php foreach ($announcements as $announcement): ?>
                <div class="announcement-card">
                    <div class="announcement-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5><?= htmlspecialchars($announcement['title']) ?></h5>
                                <div class="d-flex gap-2 mt-2">
                                    <span class="badge bg-secondary"><?= ucfirst($announcement['target_audience']) ?></span>
                                    <span class="badge bg-info"><?= ucfirst($announcement['announcement_type']) ?></span>
                                    <?php if ($announcement['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Inactive</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="stats-badge">
                                    <?= $announcement['read_count'] ?> / <?= $user_stats['total_users'] ?> read
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <?= date('M j, Y g:i A', strtotime($announcement['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="announcement-body">
                        <p><?= nl2br(htmlspecialchars($announcement['content'])) ?></p>
                        <div class="mt-3">
                            <button class="btn btn-outline-primary btn-sm" onclick="editAnnouncement(<?= $announcement['id'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteAnnouncement(<?= $announcement['id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                        
                        <!-- Edit Form (Hidden by default) -->
                        <div id="edit-form-<?= $announcement['id'] ?>" class="mt-3" style="display: none;">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_announcement">
                                <input type="hidden" name="announcement_id" value="<?= $announcement['id'] ?>">
                                <div class="mb-3">
                                    <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($announcement['title']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" name="content" rows="3" required><?= htmlspecialchars($announcement['content']) ?></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <select class="form-control" name="target_audience" required>
                                            <option value="all" <?= $announcement['target_audience'] === 'all' ? 'selected' : '' ?>>All Users</option>
                                            <option value="members" <?= $announcement['target_audience'] === 'members' ? 'selected' : '' ?>>Members Only</option>
                                            <option value="trainers" <?= $announcement['target_audience'] === 'trainers' ? 'selected' : '' ?>>Trainers Only</option>
                                            <option value="admins" <?= $announcement['target_audience'] === 'admins' ? 'selected' : '' ?>>Admins Only</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-control" name="announcement_type" required>
                                            <option value="general" <?= $announcement['announcement_type'] === 'general' ? 'selected' : '' ?>>General</option>
                                            <option value="maintenance" <?= $announcement['announcement_type'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                            <option value="event" <?= $announcement['announcement_type'] === 'event' ? 'selected' : '' ?>>Event</option>
                                            <option value="promotion" <?= $announcement['announcement_type'] === 'promotion' ? 'selected' : '' ?>>Promotion</option>
                                            <option value="emergency" <?= $announcement['announcement_type'] === 'emergency' ? 'selected' : '' ?>>Emergency</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="is_active" <?= $announcement['is_active'] ? 'checked' : '' ?>>
                                            <label class="form-check-label">Active</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit(<?= $announcement['id'] ?>)">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                <p>No announcements created yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editAnnouncement(id) {
            document.getElementById('edit-form-' + id).style.display = 'block';
        }
        
        function cancelEdit(id) {
            document.getElementById('edit-form-' + id).style.display = 'none';
        }
        
        function deleteAnnouncement(id) {
            if (confirm('Are you sure you want to delete this announcement?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="delete_announcement"><input type="hidden" name="announcement_id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Auto-hide alerts
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>