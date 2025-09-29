<?php
/**
 * Google OAuth Callback Handler
 * PHP 8+ Compatible - L9 Fitness Gym
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/google_config.php';
require_once __DIR__ . '/../../app/helpers/auth.php';

session_start();

// Debug logging
error_log('Google callback received. GET params: ' . print_r($_GET, true));
error_log('Session oauth_state: ' . ($_SESSION['oauth_state'] ?? 'not set'));

try {
    // Verify state parameter
    if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
        error_log('State verification failed. GET state: ' . ($_GET['state'] ?? 'not set') . ', Session state: ' . ($_SESSION['oauth_state'] ?? 'not set'));
        throw new Exception('Invalid state parameter');
    }
    
    // Handle OAuth error
    if (isset($_GET['error'])) {
        throw new Exception('OAuth error: ' . ($_GET['error_description'] ?? $_GET['error']));
    }
    
    // Get authorization code
    $code = $_GET['code'] ?? null;
    if (!$code) {
        throw new Exception('No authorization code received');
    }
    
    // Exchange code for token
    $tokenData = getGoogleAccessToken($code);
    if (!$tokenData || !isset($tokenData['access_token'])) {
        throw new Exception('Failed to get access token');
    }
    
    // Get user info from Google
    $userInfo = getGoogleUserInfo($tokenData['access_token']);
    if (!$userInfo) {
        throw new Exception('Failed to get user information');
    }
    
    // Check if user exists by Google ID or email
    $stmt = $pdo->prepare("
        SELECT u.*, ur.name as role_name 
        FROM users u 
        JOIN user_roles ur ON u.role_id = ur.id 
        WHERE u.google_id = ? OR u.email = ?
    ");
    $stmt->execute([$userInfo['id'], $userInfo['email']]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingUser) {
        // Update existing user with Google ID if not set
        if (!$existingUser['google_id']) {
            $stmt = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $stmt->execute([$userInfo['id'], $existingUser['id']]);
        }
        
        $userId = $existingUser['id'];
    } else {
        // Create new user
        $memberRoleStmt = $pdo->prepare("SELECT id FROM user_roles WHERE name = 'member'");
        $memberRoleStmt->execute();
        $memberRole = $memberRoleStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$memberRole) {
            throw new Exception('Member role not found');
        }
        
        // Parse name
        $nameParts = explode(' ', $userInfo['name'] ?? '', 2);
        $firstName = $nameParts[0] ?? 'User';
        $lastName = $nameParts[1] ?? '';
        
        // Create user account
        $stmt = $pdo->prepare("
            INSERT INTO users (
                role_id, first_name, last_name, email, google_id, 
                password_hash, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        
        $stmt->execute([
            $memberRole['id'],
            $firstName,
            $lastName,
            $userInfo['email'],
            $userInfo['id'],
            password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT) // Random password
        ]);
        
        $userId = $pdo->lastInsertId();
        
        // Create user profile
        $stmt = $pdo->prepare("
            INSERT INTO user_profiles (user_id, avatar_url, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$userId, $userInfo['picture'] ?? null]);
    }
    
    // Store OAuth token
    $stmt = $pdo->prepare("
        INSERT INTO oauth_tokens (
            user_id, provider, provider_user_id, access_token, 
            refresh_token, expires_at, created_at
        ) VALUES (?, 'google', ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            access_token = VALUES(access_token),
            refresh_token = VALUES(refresh_token),
            expires_at = VALUES(expires_at),
            updated_at = NOW()
    ");
    
    $expiresAt = isset($tokenData['expires_in']) 
        ? date('Y-m-d H:i:s', time() + (int)$tokenData['expires_in'])
        : null;
    
    $stmt->execute([
        $userId,
        $userInfo['id'],
        $tokenData['access_token'],
        $tokenData['refresh_token'] ?? null,
        $expiresAt
    ]);
    
    // Log login
    $stmt = $pdo->prepare("
        INSERT INTO login_history (user_id, login_method, ip_address, user_agent, success) 
        VALUES (?, 'google', ?, ?, TRUE)
    ");
    $stmt->execute([
        $userId,
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    // Get complete user data
    $stmt = $pdo->prepare("
        SELECT u.*, ur.name as role_name, up.avatar_url, up.bio
        FROM users u 
        JOIN user_roles ur ON u.role_id = ur.id 
        LEFT JOIN user_profiles up ON u.id = up.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set session
    $_SESSION['user'] = $user;
    $_SESSION['logged_in'] = true;
    $_SESSION['login_method'] = 'google';
    
    // Set welcome message for Google login
    require_once __DIR__ . '/../../app/helpers/redirect.php';
    setWelcomeMessage($user);
    
    // Clean up OAuth state
    unset($_SESSION['oauth_state']);
    
    // Smart redirect based on user role and status
    $redirectUrl = $_SESSION['intended_url'] ?? getPostLoginRedirect($user);
    unset($_SESSION['intended_url']);
    
    header('Location: ' . $redirectUrl);
    exit;
    
} catch (Exception $e) {
    error_log('Google OAuth Error: ' . $e->getMessage());
    
    $_SESSION['error'] = 'Login failed: ' . $e->getMessage();
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}
?>