<?php
// L9 Fitness Chatbot API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';
require_once '../app/helpers/auth.php';

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

// Log the conversation
logChatbotConversation($message, $user_id);

// Generate response
$response = generateChatbotResponse($message, $user_id);

echo json_encode([
    'success' => true,
    'response' => $response,
    'timestamp' => date('Y-m-d H:i:s')
]);

function logChatbotConversation($message, $user_id = null) {
    global $pdo;
    
    try {
        // Create chatbot_logs table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS chatbot_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                message TEXT NOT NULL,
                response TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ";
        $pdo->exec($createTable);
        
        // Insert the conversation
        $stmt = $pdo->prepare("INSERT INTO chatbot_logs (user_id, message) VALUES (?, ?)");
        $stmt->execute([$user_id, $message]);
        
    } catch (PDOException $e) {
        // Silent fail for logging
        error_log("Chatbot logging error: " . $e->getMessage());
    }
}

function generateChatbotResponse($message, $user_id = null) {
    global $pdo;
    
    $message_lower = strtolower($message);
    $user_info = null;
    
    // Get user info if logged in
    if ($user_id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user info: " . $e->getMessage());
        }
    }
    
    // Greetings
    if (preg_match('/\b(hi|hello|hey|good morning|good afternoon|good evening|yo|sup|what\'s up)\b/i', $message_lower)) {
        $greetings = [
            "Hey there, warrior! 💪 Welcome to L9 Fitness! How can I help you dominate today?",
            "What's up, beast! 🔥 Ready to crush your fitness goals? How can I assist?",
            "Welcome to the pit, champion! ⚡ What can I help you with today?"
        ];
        return $greetings[array_rand($greetings)];
    }
    
    // Hours
    if (preg_match('/\b(hours|open|close|time|schedule)\b/i', $message_lower)) {
        return "🕐 L9 Fitness is open 24/7 for our members! The beast never sleeps. Staff hours are Mon-Fri 6AM-10PM, Sat-Sun 8AM-8PM.";
    }
    
    // Membership
    if (preg_match('/\b(membership|price|cost|plan|join|sign up)\b/i', $message_lower)) {
        if ($user_info) {
            return "💪 Hey " . htmlspecialchars($user_info['name']) . "! Check your current membership on your dashboard. Want to upgrade your beast mode? Our plans are Monthly Beast ($49), Quarterly Savage ($129), and Yearly Champion ($399).";
        }
        return "💪 We have 3 beast modes: Monthly Beast ($49), Quarterly Savage ($129), and Yearly Champion ($399). Ready to join the warriors? Sign up on our registration page!";
    }
    
    // Classes
    if (preg_match('/\b(class|classes|workout|training|group)\b/i', $message_lower)) {
        $response = "🔥 We offer brutal classes like Beast Mode HIIT, Warrior Yoga, Savage Strength, and Destroyer Cardio!";
        if ($user_info) {
            $response .= " " . htmlspecialchars($user_info['name']) . ", you can book directly from our Classes page!";
        } else {
            $response .= " Login to book your spot in the battle!";
        }
        return $response;
    }
    
    // Booking
    if (preg_match('/\b(book|reserve|schedule|appointment)\b/i', $message_lower)) {
        if ($user_info) {
            return "📅 Hey " . htmlspecialchars($user_info['name']) . "! You can book classes directly from our Classes page. Just click 'Book Now' on any class that catches your warrior spirit!";
        }
        return "📅 To book classes, you'll need to login first. Once you're in, head to our Classes section and secure your spot in the battle!";
    }
    
    // Location
    if (preg_match('/\b(location|address|where|directions)\b/i', $message_lower)) {
        return "📍 L9 Fitness Beast Pit is located at 123 Warrior Street, Fitness City. We're the building with all the champions walking in and out! 💪";
    }
    
    // Contact
    if (preg_match('/\b(contact|phone|email|support)\b/i', $message_lower)) {
        return "📞 Reach our beast squad at (555) 123-4567 or email warrior@l9fitness.com. We're here to help you dominate!";
    }
    
    // Equipment
    if (preg_match('/\b(equipment|machine|weights|cardio|facilities)\b/i', $message_lower)) {
        return "🏋️ We have premium war machines: free weights, cardio beasts, strength stations, functional training area, and recovery chambers! Everything you need to become a legend.";
    }
    
    // Personal Training
    if (preg_match('/\b(personal trainer|pt|coach|training)\b/i', $message_lower)) {
        return "🥊 Our certified beast masters offer personal training! It's included with Yearly Champion membership or available as an add-on. Ready to level up with a personal destroyer coach?";
    }
    
    // Account specific
    if ($user_info && preg_match('/\b(my|account|dashboard|profile)\b/i', $message_lower)) {
        return "👋 Hey " . htmlspecialchars($user_info['name']) . "! You can check your account details, membership status, and booked classes on your dashboard. Need help with anything specific?";
    }
    
    // Get real membership data
    if (preg_match('/\b(membership plans|plans|pricing)\b/i', $message_lower)) {
        try {
            $stmt = $pdo->query("SELECT DISTINCT name, price, duration FROM membership_plans ORDER BY price ASC LIMIT 3");
            $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($plans) {
                $response = "💪 Here are our current warrior plans:\n\n";
                foreach ($plans as $plan) {
                    $response .= "🔥 " . htmlspecialchars($plan['name']) . " - $" . number_format($plan['price'], 2) . " (" . htmlspecialchars($plan['duration']) . ")\n";
                }
                $response .= "\nReady to join the beast army?";
                return $response;
            }
        } catch (PDOException $e) {
            error_log("Error fetching membership plans: " . $e->getMessage());
        }
    }
    
    // Get real class data
    if (preg_match('/\b(available classes|class schedule|what classes)\b/i', $message_lower)) {
        try {
            $stmt = $pdo->query("SELECT DISTINCT name, description FROM classes ORDER BY name LIMIT 5");
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($classes) {
                $response = "🔥 Here are some of our epic classes:\n\n";
                foreach ($classes as $class) {
                    $response .= "⚡ " . htmlspecialchars($class['name']);
                    if ($class['description']) {
                        $response .= " - " . htmlspecialchars(substr($class['description'], 0, 50)) . "...";
                    }
                    $response .= "\n";
                }
                $response .= "\nCheck our Classes page for full schedule and booking!";
                return $response;
            }
        } catch (PDOException $e) {
            error_log("Error fetching classes: " . $e->getMessage());
        }
    }
    
    // Default responses
    $default_responses = [
        "🤔 That's a great question! For detailed info, check our website or contact our beast squad at (555) 123-4567.",
        "💪 I'm still learning about that topic! Our staff at warrior@l9fitness.com can give you the complete breakdown.",
        "🔥 Let me connect you with our team for that! They're experts at (555) 123-4567 or visit our contact page.",
        "⚡ That's outside my current knowledge, but our warriors at the front desk have all the answers! Give us a call!"
    ];
    
    return $default_responses[array_rand($default_responses)];
}
?>
