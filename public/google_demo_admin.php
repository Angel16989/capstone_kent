<?php
/**
 * Demo Google Accounts Management
 * Admin panel for managing fake Google accounts
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';
require_login();

if (!is_admin()) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$pageTitle = "Demo Google Accounts";
$pageCSS = "assets/css/admin.css";

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'delete_demo_account') {
                $userId = (int)$_POST['user_id'];
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND google_id LIKE "fake_%"');
                $stmt->execute([$userId]);
                $success_message = "Demo Google account deleted successfully!";
                
            } elseif ($_POST['action'] === 'create_demo_account') {
                $email = trim($_POST['email']);
                $firstName = trim($_POST['first_name']);
                $lastName = trim($_POST['last_name']);
                $roleId = (int)$_POST['role_id'];
                
                // Check if email already exists
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $error_message = "An account with this email already exists.";
                } else {
                    $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
                    $googleId = 'fake_' . md5($email);
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO users (role_id, first_name, last_name, email, google_id, password_hash, email_verified, status, created_at, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, 1, 'active', NOW(), NOW())
                    ");
                    
                    $stmt->execute([$roleId, $firstName, $lastName, $email, $googleId, $passwordHash]);
                    $success_message = "Demo Google account created successfully!";
                }
            }
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Get all demo Google accounts
$stmt = $pdo->query("
    SELECT u.*, 
           CASE u.role_id 
               WHEN 1 THEN 'Admin' 
               WHEN 2 THEN 'Trainer' 
               ELSE 'Member' 
           END as role_name
    FROM users u 
    WHERE google_id LIKE 'fake_%' 
    ORDER BY role_id, first_name
");
$demoAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="fab fa-google text-primary"></i>
                        Demo Google Accounts Management
                    </h1>
                    <p class="text-muted">Manage fake Google OAuth accounts for demonstration</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>admin.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Admin
                    </a>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i>
                        How Demo Google OAuth Works
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-mouse-pointer text-primary"></i> User Experience:</h6>
                            <ul class="list-unstyled">
                                <li>• Users click "Continue with Google" on login page</li>
                                <li>• They see a list of existing Google accounts (like real Google)</li>
                                <li>• Click any account for instant login</li>
                                <li>• Or create new accounts through the demo form</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-cog text-success"></i> Behind the Scenes:</h6>
                            <ul class="list-unstyled">
                                <li>• No real Google API calls are made</li>
                                <li>• Accounts are stored locally in your database</li>
                                <li>• Perfect for demos and testing</li>
                                <li>• Simulates the complete OAuth flow</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-success" style="border-left: 4px solid #28a745;">
                                <h6 class="alert-heading">
                                    <i class="fas fa-rocket"></i> Ready for Real Google OAuth?
                                </h6>
                                <p class="mb-2">
                                    <strong>All the hard work is done!</strong> We've built the complete OAuth system. 
                                    To switch to real Google authentication:
                                </p>
                                <ol class="mb-2" style="font-size: 14px;">
                                    <li>Go to <a href="https://console.developers.google.com/" target="_blank" class="alert-link">Google Cloud Console</a> (FREE)</li>
                                    <li>Create project → Enable Google+ API → Create OAuth credentials</li>
                                    <li>Update <code>config/google_config.php</code> with your Client ID & Secret</li>
                                    <li>Set redirect URL to: <code><?php echo BASE_URL; ?>auth/google_callback.php</code></li>
                                </ol>
                                <p class="mb-0">
                                    <strong>Time needed:</strong> 5 minutes • <strong>Cost:</strong> $0 (Google OAuth is free!) • 
                                    <strong>Users:</strong> Unlimited
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create New Demo Account -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle text-success"></i>
                        Create New Demo Google Account
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <input type="hidden" name="action" value="create_demo_account">
                        
                        <div class="col-md-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="email" class="form-label">Gmail Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="user@gmail.com" required>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="role_id" class="form-label">Role</label>
                            <select class="form-control" id="role_id" name="role_id" required>
                                <option value="4">Member</option>
                                <option value="2">Trainer</option>
                                <option value="1">Admin</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> Create Demo Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Existing Demo Accounts -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fab fa-google text-primary"></i>
                        Demo Google Accounts (<?php echo count($demoAccounts); ?>)
                    </h5>
                    <div>
                        <a href="<?php echo BASE_URL; ?>google_oauth_setup_guide.php" class="btn btn-success btn-sm me-2">
                            <i class="fas fa-rocket"></i> Setup Real Google OAuth
                        </a>
                        <a href="<?php echo BASE_URL; ?>auth/google_accounts.php" class="btn btn-info btn-sm" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Test Demo Login
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (empty($demoAccounts)): ?>
                        <div class="text-center py-4">
                            <i class="fab fa-google text-muted" style="font-size: 3rem;"></i>
                            <h4 class="mt-3 text-muted">No Demo Google Accounts</h4>
                            <p class="text-muted">Create some demo accounts to test the Google OAuth simulation.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Created</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($demoAccounts as $account): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 40px; height: 40px; font-weight: bold;">
                                                        <?php echo strtoupper(substr($account['first_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($account['first_name'] . ' ' . $account['last_name']); ?></strong>
                                                        <br>
                                                        <small class="text-muted">ID: <?php echo $account['id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-primary">
                                                    <i class="fab fa-google me-1"></i>
                                                    <?php echo htmlspecialchars($account['email']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $account['role_id'] == 1 ? 'danger' : ($account['role_id'] == 2 ? 'warning' : 'primary'); ?>">
                                                    <?php echo $account['role_name']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo date('M j, Y', strtotime($account['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo date('M j, g:i A', strtotime($account['updated_at'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo BASE_URL; ?>auth/google_accounts.php?quick_login=1&user_id=<?php echo $account['id']; ?>" 
                                                       class="btn btn-success btn-sm" title="Quick Login">
                                                        <i class="fas fa-sign-in-alt"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            onclick="deleteAccount(<?php echo $account['id']; ?>, '<?php echo htmlspecialchars($account['first_name'] . ' ' . $account['last_name']); ?>')"
                                                            title="Delete Account">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
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

<script>
function deleteAccount(userId, userName) {
    if (confirm(`Are you sure you want to delete the demo Google account for "${userName}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_demo_account">
            <input type="hidden" name="user_id" value="${userId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>