<?php
/**
 * Simple Google Demo Login
 * Simplified version with better error handling
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/auth.php';

// Handle quick login
if (isset($_GET['login']) && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    
    try {
        // Get the user
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Log them in
            login_user($user);
            $_SESSION['welcome_message'] = "Demo login successful! Welcome, " . $user['first_name'] . "!";
            
            // Redirect based on role - use full localhost URLs
            if ($user['role_id'] == 1) {
                $redirectUrl = 'http://localhost/Capstone-latest/public/admin.php';
            } else {
                $redirectUrl = 'http://localhost/Capstone-latest/public/dashboard.php';
            }
            
            // Use proper PHP header redirect
            header('Location: ' . $redirectUrl);
            exit;
        }
    } catch (Exception $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}

// Get demo accounts
$stmt = $pdo->query("SELECT id, first_name, last_name, email, role_id FROM users WHERE google_id LIKE 'fake_%' ORDER BY role_id, first_name");
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Demo Google Login";
include __DIR__ . '/../../app/views/layouts/header.php';
?>

<style>
.demo-container {
    max-width: 600px;
    margin: 50px auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.demo-header {
    background: linear-gradient(135deg, #4285f4, #34a853);
    color: white;
    padding: 30px;
    text-align: center;
}

.account-list {
    padding: 30px;
}

.account-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 2px solid #f1f3f4;
    border-radius: 8px;
    margin-bottom: 10px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
}

.account-item:hover {
    border-color: #4285f4;
    background: #f8f9fa;
    text-decoration: none;
    color: inherit;
}

.account-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4285f4, #ea4335);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 15px;
}

.account-info {
    flex: 1;
}

.role-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.admin { background: #fee; color: #d93025; }
.trainer { background: #fff3cd; color: #856404; }
.member { background: #e8f5e9; color: #137333; }
</style>

<div class="container">
    <div class="demo-container">
        <div class="demo-header">
            <h2>Demo Google Accounts</h2>
            <p>Click any account to login instantly!</p>
        </div>
        
        <div class="account-list">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (empty($accounts)): ?>
                <div class="text-center">
                    <p>No demo accounts found.</p>
                    <a href="/Capstone-latest/setup_demo_google_accounts.php" class="btn btn-primary">Create Demo Accounts</a>
                </div>
            <?php else: ?>
                <?php foreach ($accounts as $account): ?>
                    <?php 
                    $roleNames = [1 => 'Admin', 2 => 'Trainer', 4 => 'Member'];
                    $roleName = $roleNames[$account['role_id']] ?? 'User';
                    $roleClass = strtolower($roleName);
                    ?>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?login=1&id=<?php echo $account['id']; ?>" class="account-item">
                        <div class="account-avatar">
                            <?php echo strtoupper(substr($account['first_name'], 0, 1)); ?>
                        </div>
                        <div class="account-info">
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($account['first_name'] . ' ' . $account['last_name']); ?></div>
                            <div style="font-size: 14px; color: #666;"><?php echo htmlspecialchars($account['email']); ?></div>
                        </div>
                        <div class="role-badge <?php echo $roleClass; ?>"><?php echo $roleName; ?></div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="/Capstone-latest/public/login.php" class="btn btn-outline-secondary">
                    Back to Regular Login
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../app/views/layouts/footer.php'; ?>