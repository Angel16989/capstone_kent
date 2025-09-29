<?php
/**
 * Session Extension Endpoint
 * Extends user session when requested
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Extend the session
SessionManager::extendSession();

// Return success with new remaining time
echo json_encode([
    'success' => true,
    'message' => 'Session extended',
    'remaining_time' => SessionManager::getRemainingTime(),
    'new_timeout' => date('Y-m-d H:i:s', time() + SessionManager::getSessionTimeout())
]);
?>