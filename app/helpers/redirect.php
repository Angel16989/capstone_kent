<?php
/**
 * Smart Login Redirect Helper
 * Determines where users should go after login based on their role and account status
 */

function getPostLoginRedirect(array $user): string {
    $baseUrl = rtrim(BASE_URL, '/');
    
    // Check for explicit redirect parameter first
    if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
        $redirectUrl = urldecode($_GET['redirect']);
        // Security check: only allow internal redirects
        if (strpos($redirectUrl, '/') === 0 || strpos($redirectUrl, $baseUrl) === 0) {
            return $redirectUrl;
        }
    }
    
    // Check if user is admin
    if (($user['role_id'] ?? 4) === 1) {
        return $baseUrl . '/admin.php';
    }
    
    // Check if user is trainer
    if (($user['role_id'] ?? 4) === 3) {
        return $baseUrl . '/trainer_dashboard.php';
    }
    
    // For members, check if it's their first login or if they need to complete profile
    global $pdo;
    
    // Check if user has a fitness profile
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM user_fitness_profile WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $hasProfile = $stmt->fetchColumn() > 0;
    
    // Check if user has an active membership
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM memberships WHERE member_id = ? AND status = "active" AND end_date > NOW()');
    $stmt->execute([$user['id']]);
    $hasMembership = $stmt->fetchColumn() > 0;
    
    // First-time user flow
    if (!$hasProfile || !$hasMembership) {
        // New users should see the welcome/setup flow
        return $baseUrl . '/welcome.php';
    }
    
    // Regular members go to dashboard
    return $baseUrl . '/dashboard.php';
}

function isFirstTimeLogin(array $user): bool {
    global $pdo;
    
    // Check if user has logged in before by checking login_history
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM login_history WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $loginCount = $stmt->fetchColumn();
    
    return $loginCount <= 1; // Including current login
}

function setWelcomeMessage(array $user): void {
    if (isFirstTimeLogin($user)) {
        $_SESSION['welcome_message'] = "Welcome to L9 Fitness, " . ($user['first_name'] ?? 'Member') . "! Let's get you started.";
        $_SESSION['show_welcome_tour'] = true;
    } else {
        $_SESSION['welcome_message'] = "Welcome back, " . ($user['first_name'] ?? 'Member') . "!";
    }
}
?>