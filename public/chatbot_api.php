<?php
// L9 Fitness AI-Powered Chatbot API
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session before anything else
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    require_once '../config/db.php';
    require_once '../app/helpers/auth.php';
    require_once '../app/services/AIService.php';
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to load dependencies: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

$message = trim($input['message']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get user context for personalized responses
$user_context = null;
if ($user_id) {
    try {
        $stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user_info) {
            $user_context = "User is logged in as: " . $user_info['name'] . " (" . $user_info['email'] . ")";
        }
    } catch (PDOException $e) {
        error_log("Error fetching user info: " . $e->getMessage());
    }
}

// Log the conversation
logChatbotConversation($message, $user_id);

try {
    // Generate AI-powered response
    $response = AIService::generateResponse($message, $user_context);
    
    // Clean and ensure proper encoding
    $response = html_entity_decode($response, ENT_QUOTES, 'UTF-8');
    $response = mb_convert_encoding($response, 'UTF-8', 'UTF-8');
    
    // Log the response
    logChatbotResponse($message, $response, $user_id);
    
    echo json_encode([
        'success' => true,
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s'),
        'ai_powered' => true
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    error_log("Chatbot AI Error: " . $e->getMessage());
    
    // Fallback to simple response
    $fallback_response = "I'm experiencing some technical difficulties right now. For immediate assistance, please contact our team at (555) 123-4567 or email info@l9fitness.com. Our staff hours are Mon-Fri 6AM-10PM, Weekends 8AM-8PM.";
    
    echo json_encode([
        'success' => true,
        'response' => $fallback_response,
        'timestamp' => date('Y-m-d H:i:s'),
        'ai_powered' => false,
        'fallback' => true
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function logChatbotConversation($message, $user_id = null) {
    global $pdo;
    
    try {
        // Create chatbot_logs table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS chatbot_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                message TEXT NOT NULL,
                response TEXT NULL,
                ai_powered BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_created_at (created_at)
            )
        ";
        $pdo->exec($createTable);
        
        // Insert the conversation
        $stmt = $pdo->prepare("INSERT INTO chatbot_logs (user_id, message) VALUES (?, ?)");
        $stmt->execute([$user_id, $message]);
        
        return $pdo->lastInsertId();
        
    } catch (PDOException $e) {
        error_log("Chatbot logging error: " . $e->getMessage());
        return null;
    }
}

function logChatbotResponse($message, $response, $user_id = null) {
    global $pdo;
    
    try {
        // Update the latest log entry with the response
        $stmt = $pdo->prepare("
            UPDATE chatbot_logs 
            SET response = ?, ai_powered = TRUE 
            WHERE user_id = ? AND message = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$response, $user_id, $message]);
        
        // If no user_id, find by message and recent timestamp
        if (!$user_id) {
            $stmt = $pdo->prepare("
                UPDATE chatbot_logs 
                SET response = ?, ai_powered = TRUE 
                WHERE message = ? AND response IS NULL 
                AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$response, $message]);
        }
        
    } catch (PDOException $e) {
        error_log("Response logging error: " . $e->getMessage());
    }
}
?>
