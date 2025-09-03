<?php
function require_login(): void { 
    if (!isset($_SESSION['user'])) { 
        header('Location: login.php'); 
        exit; 
    } 
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
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
    session_destroy(); 
    header('Location: index.php'); 
    exit; 
}

function current_user(){ 
    return $_SESSION['user'] ?? null; 
}

function is_admin(): bool { 
    return (($_SESSION['user']['role_id'] ?? 4) === 1); 
}
