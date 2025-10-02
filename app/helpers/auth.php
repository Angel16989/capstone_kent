<?php
require_once __DIR__ . '/session.php';

function require_login(): void { 
    // Check if session is expired or invalid
    if (!SessionManager::isLoggedIn() || SessionManager::isSessionExpired()) {
        // Store the intended URL for after login
        if (!isset($_SESSION['intended_url'])) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '';
        }
        
        // Destroy expired session
        if (SessionManager::isSessionExpired()) {
            SessionManager::destroySession();
        }
        
        header('Location: login.php'); 
        exit; 
    } 
    
    // Extend session on page access
    SessionManager::extendSession();
}

function is_logged_in(): bool {
    return SessionManager::isLoggedIn() && !SessionManager::isSessionExpired();
}

function login_user(array $u): void { 
    // Handle both first_name/last_name and full_name formats
    $name = isset($u['full_name']) ? $u['full_name'] : ($u['first_name'] . ' ' . $u['last_name']);
    
    $_SESSION['user'] = [
        'id' => $u['id'],
        'name' => $name,
        'email' => $u['email'],
        'role_id' => (int)($u['role_id'] ?? 4),
        'first_name' => $u['first_name'] ?? '',
        'last_name' => $u['last_name'] ?? '',
        'phone' => $u['phone'] ?? '',
        'address' => $u['address'] ?? '',
        'password' => $u['password_hash'] ?? '',
        'created_at' => $u['created_at'] ?? ''
    ]; 
}

function logout_user(): void { 
    SessionManager::destroySession();
    header('Location: index.php'); 
    exit; 
}

function current_user(){ 
    // Check if session is valid and user is logged in
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return null;
    }
    
    // Check if session is expired
    if (SessionManager::isSessionExpired()) {
        SessionManager::destroySession();
        return null;
    }
    
    // Extend session on access
    SessionManager::extendSession();
    
    return $_SESSION['user'];
}

function is_admin(): bool { 
    return (($_SESSION['user']['role_id'] ?? 4) === 1); 
}
