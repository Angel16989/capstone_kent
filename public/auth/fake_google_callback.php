<?php
/**
 * Fake Google OAuth Callback Handler
 * Processes the fake Google login and creates/logs in users
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/helpers/auth.php';

// Check if we have fake Google user data
if (!isset($_SESSION['fake_google_user'])) {
    // Add debug info
    $_SESSION['debug_info'] = 'No fake Google user data in session';
    header('Location: ' . BASE_URL . 'login.php?error=oauth_failed');
    exit;
}

$googleUser = $_SESSION['fake_google_user'];

try {
    // Check if user already exists in our database
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$googleUser['email']]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        // User exists, just log them in
        login_user($existingUser);
        $_SESSION['welcome_message'] = "Welcome back, " . $existingUser['first_name'] . "! Logged in via Google Demo.";
        
        // Clean up fake Google session data
        unset($_SESSION['fake_google_user']);
        
        // Redirect based on role
        if ($existingUser['role_id'] == 1) {
            header('Location: ' . BASE_URL . 'admin.php');
        } else {
            header('Location: ' . BASE_URL . 'dashboard.php');
        }
        exit;
        
    } else {
        // Create new user account
        $passwordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT); // Random password since they'll use Google
        
        $stmt = $pdo->prepare("
            INSERT INTO users (role_id, first_name, last_name, email, google_id, password_hash, email_verified, status, created_at, updated_at)
            VALUES (4, ?, ?, ?, ?, ?, 1, 'active', NOW(), NOW())
        ");
        
        $googleId = 'fake_' . md5($googleUser['email']); // Fake Google ID
        
        $stmt->execute([
            4, // member role
            $googleUser['first_name'],
            $googleUser['last_name'],
            $googleUser['email'],
            $googleId,
            $passwordHash
        ]);
        
        $newUserId = $pdo->lastInsertId();
        
        // Get the newly created user
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$newUserId]);
        $newUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Log them in
        login_user($newUser);
        $_SESSION['welcome_message'] = "Welcome to L9 Fitness, " . $newUser['first_name'] . "! Account created via Google Demo.";
        
        // Clean up fake Google session data
        unset($_SESSION['fake_google_user']);
        
        // Redirect to dashboard for new users
        header('Location: ' . BASE_URL . 'dashboard.php?new_user=1');
        exit;
    }
    
} catch (Exception $e) {
    error_log('Fake Google OAuth Error: ' . $e->getMessage());
    header('Location: ' . BASE_URL . 'login.php?error=oauth_error');
    exit;
}
?>