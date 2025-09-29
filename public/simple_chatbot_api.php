<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message'])) {
    echo json_encode(['error' => 'Message required']);
    exit;
}

$message = trim($input['message']);

// Try to load AI service for intelligent responses
try {
    require_once '../app/services/AIService.php';
    $result = AIService::generateResponse($message, null);
    
    // Check if result is array with status info or just a string
    if (is_array($result)) {
        $response = $result['response'];
        $ai_powered = $result['ai_powered'] ?? false;
    } else {
        $response = $result;
        $ai_powered = true; // Assume AI if it's just a string response
    }
} catch (Exception $e) {
    // Fallback to intelligent pattern matching if AI fails completely
    $response = getIntelligentResponse($message);
    $ai_powered = false;
}

echo json_encode([
    'success' => true,
    'response' => $response,
    'timestamp' => date('Y-m-d H:i:s'),
    'ai_powered' => $ai_powered
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

function getIntelligentResponse($message) {
    $message = strtolower($message);
    
    // Hours and schedule
    if (preg_match('/\b(hours?|time|schedule|open|close|when)\b/', $message)) {
        return "🕒 **L9 Fitness Hours:**\n24/7 access for members!\n📞 Staff available: Mon-Fri 6AM-10PM, Weekends 8AM-8PM\n📍 Located at 123 Warrior Street, Fitness City";
    }
    
    // Membership and pricing
    if (preg_match('/\b(member|price|cost|fee|plan|join)\b/', $message)) {
        return "💪 **Membership Plans:**\n🥉 Basic: $29.99/month - Access to gym equipment\n🥈 Premium: $49.99/month - Includes classes + personal training session\n🥇 Elite: $79.99/month - Unlimited everything + nutrition consultation";
    }
    
    // Classes and services
    if (preg_match('/\b(class|yoga|spin|training|workout|exercise)\b/', $message)) {
        return "🏃‍♀️ **Fitness Classes:**\n🧘‍♀️ Yoga: Daily 7AM & 6PM\n🚴‍♂️ Spin Classes: Mon/Wed/Fri 6AM & 7PM\n💪 Strength Training: Tue/Thu/Sat 8AM & 5PM\n🏃‍♂️ HIIT: Daily 12PM & 8PM";
    }
    
    // Equipment and facilities
    if (preg_match('/\b(equipment|machine|pool|sauna|locker)\b/', $message)) {
        return "🏋️‍♂️ **Our Facilities:**\n🏃‍♂️ Cardio: 50+ treadmills, bikes, ellipticals\n💪 Strength: Full free weights + machines\n🏊‍♂️ Pool: Olympic-size with lap lanes\n🧖‍♂️ Wellness: Sauna, steam room, massage\n🚿 Amenities: Clean lockers, showers, towels";
    }
    
    // Contact and location
    if (preg_match('/\b(contact|phone|address|location|where|call)\b/', $message)) {
        return "📞 **Contact L9 Fitness:**\n📱 Phone: (555) 123-4567\n📧 Email: info@l9fitness.com\n📍 Address: 123 Warrior Street, Fitness City\n🌐 Website: www.l9fitness.com";
    }
    
    // Greeting responses
    if (preg_match('/\b(hi|hello|hey|good morning|good afternoon)\b/', $message)) {
        return "👋 Welcome to L9 Fitness! I'm here to help you with information about our gym, classes, memberships, and facilities. What would you like to know?";
    }
    
    // Default response
    return "💪 Welcome to L9 Fitness! I can help you with:\n🕒 Gym hours & schedules\n💳 Membership plans & pricing\n🏃‍♀️ Fitness classes & training\n🏋️‍♂️ Equipment & facilities\n📞 Contact information\n\nWhat would you like to know about?";
}
?>
