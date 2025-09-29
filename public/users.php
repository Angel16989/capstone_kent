<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_admin()) {
    http_response_code(403);
    exit('Access denied. Admin privileges required.');
}

$pageTitle = "User Management";
$pageCSS = "assets/css/admin.css";

// Handle user actions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    try {
        switch ($action) {
            case 'delete':
                $stmt = $pdo->prepare('UPDATE users SET status = "deleted" WHERE id = ?');
                $stmt->execute([$user_id]);
                $message = 'User deactivated successfully.';
                break;
                
            case 'activate':
                $stmt = $pdo->prepare('UPDATE users SET status = "active" WHERE id = ?');
                $stmt->execute([$user_id]);
                $message = 'User activated successfully.';
                break;
                
            case 'suspend':
                $stmt = $pdo->prepare('UPDATE users SET status = "suspended" WHERE id = ?');
                $stmt->execute([$user_id]);
                $message = 'User suspended successfully.';
                break;
        }
    } catch (Exception $e) {
        $error = 'Error performing action: ' . $e->getMessage();
    }
}

// Get all users with role information
$stmt = $pdo->prepare('
    SELECT u.*, ur.name as role_name, CONCAT(u.first_name, " ", u.last_name) as full_name
    FROM users u 
    LEFT JOIN user_roles ur ON u.role_id = ur.id 
    ORDER BY u.created_at DESC
');
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-6 fw-bold text-gradient">
                    <i class="bi bi-people-fill me-3"></i>User Management
                </h1>
                <a href="admin.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Admin
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-light">
                    <h5 class="mb-0">
                        <i class="bi bi-table me-2"></i>All Users
                        <span class="badge bg-primary ms-2"><?= count($users) ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><span class="badge bg-secondary">#<?= $user['id'] ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-circle fs-4 me-2 text-muted"></i>
                                            <div>
                                                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                                                <?php if ($user['gender']): ?>
                                                    <br><small class="text-muted">
                                                        <i class="bi bi-<?= $user['gender'] === 'male' ? 'person' : 'person-dress' ?> me-1"></i>
                                                        <?= ucfirst($user['gender']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($user['email']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $user['role_name'] === 'admin' ? 'danger' : 
                                            ($user['role_name'] === 'trainer' ? 'warning' : 
                                            ($user['role_name'] === 'staff' ? 'info' : 'success')) 
                                        ?>">
                                            <i class="bi bi-<?= 
                                                $user['role_name'] === 'admin' ? 'shield-check' : 
                                                ($user['role_name'] === 'trainer' ? 'award' : 
                                                ($user['role_name'] === 'staff' ? 'person-gear' : 'person')) 
                                            ?> me-1"></i>
                                            <?= ucfirst($user['role_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $user['status'] === 'active' ? 'success' : 
                                            ($user['status'] === 'suspended' ? 'warning' : 'danger') 
                                        ?>">
                                            <?= ucfirst($user['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($user['id'] !== current_user()['id']): ?>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($user['status'] === 'active'): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="suspend">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <button type="submit" class="btn btn-warning btn-sm" 
                                                                onclick="return confirm('Suspend this user?')">
                                                            <i class="bi bi-pause-circle"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="activate">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="bi bi-play-circle"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('Deactivate this user permanently?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Current User</span>
                                        <?php endif; ?>
                                    </td>
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

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
