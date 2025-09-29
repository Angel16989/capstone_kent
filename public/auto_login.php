<?php
/**
 * Auto-Login Development Helper
 * Automatically logs in as admin for development purposes
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/helpers/auth.php';

// Check if already logged in
if (is_logged_in()) {
    $current = current_user();
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

try {
    // Get the admin user
    $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, role_id, phone, address, created_at, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->execute(['admin@l9.local']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        // Auto-login as admin
        login_user($admin);
        
        // Set welcome message
        $_SESSION['welcome_message'] = "🚀 Development Mode: Auto-logged in as Admin!";
        
        // Redirect to admin dashboard
        header('Location: ' . BASE_URL . 'admin.php');
        exit;
    } else {
        echo "❌ Admin account not found. Please run setup_admin_accounts.php first.";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>