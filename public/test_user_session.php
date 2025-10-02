<?php
// Test session and user validation
require_once '../config/config.php';
require_once '../app/helpers/auth.php';

header('Content-Type: application/json');

$user = get_current_user();

echo json_encode([
    'session_active' => session_status() === PHP_SESSION_ACTIVE,
    'session_id' => session_id(),
    'user_exists' => isset($_SESSION['user']),
    'user_is_array' => is_array($_SESSION['user'] ?? null),
    'user_has_id' => isset($_SESSION['user']['id'] ?? null),
    'user_data' => $user,
    'is_logged_in' => is_logged_in(),
    'session_expired' => SessionManager::isSessionExpired(),
    'remaining_time' => SessionManager::getRemainingTime(),
], JSON_PRETTY_PRINT);
