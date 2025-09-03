<?php
function not_empty($v): bool { return isset($v) && trim((string)$v) !== ''; }
function email_valid($v): bool { return filter_var($v, FILTER_VALIDATE_EMAIL) !== false; }
function sanitize($v): string { return htmlspecialchars(trim((string)$v), ENT_QUOTES, 'UTF-8'); }

// Enhanced Password Policy Validation
function password_validate($password): array {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (strlen($password) > 128) {
        $errors[] = 'Password must be less than 128 characters';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character';
    }
    
    // Check for common weak passwords
    $weak_passwords = ['password', '12345678', 'qwertyui', 'abcdefgh', 'password123'];
    if (in_array(strtolower($password), $weak_passwords)) {
        $errors[] = 'Password is too common, please choose a stronger password';
    }
    
    return $errors;
}

// Calculate password strength score (0-100)
function password_strength($password): int {
    $score = 0;
    
    // Length bonus
    $score += min(25, strlen($password) * 2);
    
    // Character variety
    if (preg_match('/[a-z]/', $password)) $score += 15;
    if (preg_match('/[A-Z]/', $password)) $score += 15;
    if (preg_match('/[0-9]/', $password)) $score += 15;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 20;
    
    // Bonus for length
    if (strlen($password) >= 12) $score += 10;
    
    return min(100, $score);
}

// Get password strength label
function password_strength_label($score): array {
    if ($score < 30) return ['label' => 'WEAK', 'color' => '#FF4444', 'class' => 'danger'];
    if ($score < 60) return ['label' => 'FAIR', 'color' => '#FF8800', 'class' => 'warning'];  
    if ($score < 80) return ['label' => 'GOOD', 'color' => '#FFD700', 'class' => 'info'];
    return ['label' => 'STRONG', 'color' => '#00FF00', 'class' => 'success'];
}
