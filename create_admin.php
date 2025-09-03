<?php
// create_admin.php - Create admin user for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Create Admin User</h2>";

try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/app/helpers/validator.php';
    require_once __DIR__ . '/app/helpers/auth.php';
    
    // Check if admin already exists
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role_id = 1');
    $stmt->execute();
    $admin_count = $stmt->fetchColumn();
    
    if ($admin_count > 0) {
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
        echo "✅ Admin user already exists!<br>";
        echo "You can login with an existing admin account or create a new one below.";
        echo "</div><br>";
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        
        if (empty($email) || empty($password) || empty($first_name)) {
            throw new Exception('All fields are required');
        }
        
        if (!email_valid($email)) {
            throw new Exception('Invalid email address');
        }
        
        // Check password policy
        $password_errors = password_validate($password);
        if (!empty($password_errors)) {
            throw new Exception('Password does not meet requirements: ' . implode(', ', $password_errors));
        }
        
        // Check if email already exists
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Email already exists');
        }
        
        // Create admin user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (role_id, first_name, last_name, email, password_hash, created_at) VALUES (1, ?, ?, ?, ?, NOW())');
        $stmt->execute([$first_name, $last_name, $email, $hash]);
        
        $id = (int)$pdo->lastInsertId();
        
        // Get the created user
        $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, password_hash, role_id FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Auto-login the admin
        login_user($user);
        
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
        echo "<strong>🎉 Admin Created Successfully!</strong><br>";
        echo "Email: {$email}<br>";
        echo "Name: {$first_name} {$last_name}<br>";
        echo "Role: Administrator<br><br>";
        echo "<a href='public/admin.php' class='btn' style='background: #FF4444; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Admin Dashboard</a>";
        echo "</div>";
        
    } else {
        ?>
        <form method="post" style="max-width: 500px; margin: 20px auto; padding: 20px; border: 2px solid #FF4444; border-radius: 12px; background: linear-gradient(135deg, #1a1a1a, #2d2d2d);">
            <h3 style="color: #FFD700; text-align: center; margin-bottom: 20px;">🛡️ Create Admin Account</h3>
            
            <div style="margin-bottom: 15px;">
                <label for="first_name" style="color: #FFD700; font-weight: bold;">First Name:</label><br>
                <input type="text" id="first_name" name="first_name" required style="width: 100%; padding: 10px; border: 2px solid #FFD700; border-radius: 5px; background: #2d2d2d; color: white;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="last_name" style="color: #FFD700; font-weight: bold;">Last Name:</label><br>
                <input type="text" id="last_name" name="last_name" required style="width: 100%; padding: 10px; border: 2px solid #FFD700; border-radius: 5px; background: #2d2d2d; color: white;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="email" style="color: #FFD700; font-weight: bold;">Email:</label><br>
                <input type="email" id="email" name="email" required style="width: 100%; padding: 10px; border: 2px solid #FFD700; border-radius: 5px; background: #2d2d2d; color: white;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="password" style="color: #FFD700; font-weight: bold;">Password:</label><br>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 10px; border: 2px solid #FFD700; border-radius: 5px; background: #2d2d2d; color: white;">
                <small style="color: #ccc; display: block; margin-top: 5px;">Must be at least 8 characters with uppercase, lowercase, number, and special character</small>
            </div>
            
            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #FF4444, #cc0000); color: white; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;">
                🔥 CREATE ADMIN ACCOUNT
            </button>
        </form>
        <?php
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
    
    if (isset($_POST['email'])) {
        echo "<p><a href='create_admin.php'>← Try Again</a></p>";
    }
}
?>

<style>
body {
    background: linear-gradient(135deg, #1a1a1a, #0a0a0a);
    color: white;
    font-family: 'Courier New', monospace;
    margin: 0;
    padding: 20px;
    min-height: 100vh;
}

.btn:hover {
    background: linear-gradient(135deg, #cc0000, #FF4444) !important;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 68, 68, 0.3);
}
</style>
