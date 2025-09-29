<?php
/**
 * Session Destroy Endpoint
 * Force destroys the current session for testing
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Destroy the session completely
SessionManager::destroySession();

echo json_encode([
    'success' => true,
    'message' => 'Session destroyed'
]);
?>