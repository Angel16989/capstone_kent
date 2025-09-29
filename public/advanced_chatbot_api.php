<?php
// Advanced L9 Fitness AI Chatbot API - Next Generation
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
$user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;
$conversation_context = isset($input['context']) ? $input['context'] : [];

// Initialize the Advanced AI Chatbot
$aiChatbot = new AdvancedL9Chatbot($pdo);
$response = $aiChatbot->processMessage($message, $user_id, $conversation_context);

echo json_encode($response);

class AdvancedL9Chatbot {
    private $pdo;
    private $config;
    private $website_data;
    private $user_context;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        // Load configuration
        $this->config = require_once '../config/chatbot_config.php';
        $this->loadRealtimeWebsiteData();
    }
    
    public function processMessage($message, $user_id = null, $context = []) {
        // Load user context
        $this->loadUserContext($user_id);
        
        // Log the conversation
        $this->logConversation($message, $user_id);
        
        // Get real-time context
        $realtime_context = $this->getRealTimeContext($message);
        
        // Generate intelligent response
        $response = $this->generateIntelligentResponse($message, $context, $realtime_context);
        
        // Update conversation log with response
        $this->updateConversationResponse($response['message']);
        
        return [
            'success' => true,
            'response' => $response['message'],
            'suggestions' => $response['suggestions'],
            'actions' => $response['actions'],
            'context_data' => $response['context_data'],
            'timestamp' => date('Y-m-d H:i:s'),
            'ai_confidence' => $response['confidence']
        ];
    }
    
    private function loadRealtimeWebsiteData() {
        try {
            // Get live membership data
            $stmt = $this->pdo->query("
                SELECT mp.*, COUNT(m.id) as active_members 
                FROM membership_plans mp 
                LEFT JOIN memberships m ON mp.id = m.plan_id AND m.status = 'active'
                GROUP BY mp.id 
                ORDER BY mp.price ASC
            ");
            $this->website_data['memberships'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get live class data with availability
            $stmt = $this->pdo->query("
                SELECT c.*, 
                       COUNT(b.id) as current_bookings,
                       (c.capacity - COUNT(b.id)) as available_spots,
                       u.name as instructor_name
                FROM classes c 
                LEFT JOIN bookings b ON c.id = b.class_id AND b.status = 'confirmed'
                LEFT JOIN users u ON c.instructor_id = u.id
                WHERE c.start_time > NOW()
                GROUP BY c.id 
                ORDER BY c.start_time ASC 
                LIMIT 20
            ");
            $this->website_data['upcoming_classes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get user statistics
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_members,
                    COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_members_month,
                    COUNT(CASE WHEN last_login > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as active_week
                FROM users WHERE role_id != 1
            ");
            $this->website_data['member_stats'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get popular classes
            $stmt = $this->pdo->query("
                SELECT c.name, c.description, COUNT(b.id) as booking_count
                FROM classes c
                LEFT JOIN bookings b ON c.id = b.class_id
                WHERE c.start_time > DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY c.id
                ORDER BY booking_count DESC
                LIMIT 5
            ");
            $this->website_data['popular_classes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get recent reviews/feedback
            $stmt = $this->pdo->query("
                SELECT rating, comment, created_at 
                FROM reviews 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $this->website_data['recent_reviews'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get gym capacity and peak times
            $stmt = $this->pdo->query("
                SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as visits,
                    DATE(created_at) as visit_date
                FROM user_visits 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at), HOUR(created_at)
                ORDER BY visits DESC
            ");
            $this->website_data['peak_times'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error loading website data: " . $e->getMessage());
            $this->website_data = [];
        }
    }
    
    private function loadUserContext($user_id) {
        if (!$user_id) {
            $this->user_context = null;
            return;
        }
        
        try {
            // Get comprehensive user data
            $stmt = $this->pdo->prepare("
                SELECT u.*, mp.name as membership_plan, mp.price as membership_price,
                       m.start_date, m.end_date, m.status as membership_status
                FROM users u 
                LEFT JOIN memberships m ON u.id = m.user_id AND m.status = 'active'
                LEFT JOIN membership_plans mp ON m.plan_id = mp.id
                WHERE u.id = ?
            ");
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get user's recent bookings
            $stmt = $this->pdo->prepare("
                SELECT c.name, c.start_time, b.status, c.description
                FROM bookings b
                JOIN classes c ON b.class_id = c.id
                WHERE b.user_id = ?
                ORDER BY c.start_time DESC
                LIMIT 10
            ");
            $stmt->execute([$user_id]);
            $user_data['recent_bookings'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get user's workout history
            $stmt = $this->pdo->prepare("
                SELECT workout_type, duration, calories_burned, created_at
                FROM user_workouts
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT 10
            ");
            $stmt->execute([$user_id]);
            $user_data['workout_history'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get user preferences
            $stmt = $this->pdo->prepare("
                SELECT preference_key, preference_value
                FROM user_preferences
                WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);
            $preferences = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $user_data['preferences'] = array_column($preferences, 'preference_value', 'preference_key');
            
            $this->user_context = $user_data;
            
        } catch (PDOException $e) {
            error_log("Error loading user context: " . $e->getMessage());
            $this->user_context = null;
        }
    }
    
    private function getRealTimeContext($message) {
        $context = [];
        
        // Current time context
        $context['current_time'] = [
            'datetime' => date('Y-m-d H:i:s'),
            'day_of_week' => date('l'),
            'hour' => (int)date('H'),
            'is_peak_time' => $this->isPeakTime(),
            'gym_status' => $this->getGymStatus()
        ];
        
        // Weather context (if applicable)
        $context['weather'] = $this->getWeatherContext();
        
        // Current promotions
        $context['promotions'] = $this->getCurrentPromotions();
        
        // Real-time class availability
        $context['immediate_classes'] = $this->getImmediateClassAvailability();
        
        return $context;
    }
    
    private function generateIntelligentResponse($message, $conversation_context, $realtime_context) {
        // Build comprehensive context for AI
        $ai_context = $this->buildAIContext($message, $conversation_context, $realtime_context);
        
        // Try ChatGPT first for intelligent responses
        $chatgpt_response = $this->getChatGPTResponse($ai_context);
        
        if ($chatgpt_response) {
            return $this->processChatGPTResponse($chatgpt_response, $realtime_context);
        }
        
        // Fallback to advanced local intelligence
        return $this->getAdvancedLocalResponse($message, $ai_context);
    }
    
    private function buildAIContext($message, $conversation_context, $realtime_context) {
        $gym_info = $this->config['gym_info'];
        
        $context = [
            'gym_name' => $gym_info['name'],
            'current_user' => $this->user_context,
            'conversation_history' => $conversation_context,
            'realtime_data' => $realtime_context,
            'website_data' => $this->website_data,
            'user_message' => $message,
            'gym_info' => $gym_info
        ];
        
        return $context;
    }
    
    private function getChatGPTResponse($context) {
        $api_key = $this->config['openai_api_key'];
        
        if (empty($api_key) || $api_key === "YOUR_OPENAI_API_KEY_HERE") {
            return null; // No API key configured
        }
        
        $system_prompt = $this->buildSystemPrompt();
        $user_prompt = $this->buildUserPrompt($context);
        
        $data = [
            'model' => $this->config['openai_model'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system_prompt
                ],
                [
                    'role' => 'user',
                    'content' => $user_prompt
                ]
            ],
            'max_tokens' => $this->config['max_tokens'],
            'temperature' => $this->config['temperature']
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['response_timeout']);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $decoded = json_decode($response, true);
            if (isset($decoded['choices'][0]['message']['content'])) {
                return $decoded['choices'][0]['message']['content'];
            }
        }
        
        return null;
    }
    
    private function buildSystemPrompt() {
        $gym_info = $this->config['gym_info'];
        $personality = $this->config['ai_personality'];
        
        return "You are {$this->config['chatbot_name']}, the most advanced gym chatbot ever created for {$gym_info['name']}. You are incredibly intelligent, helpful, and energetic with a {$personality['tone']} personality.

        You have real-time access to all gym data including memberships, classes, user data, and current statistics.

        GYM INFORMATION:
        - Name: {$gym_info['name']}
        - Location: {$gym_info['location']}
        - Phone: {$gym_info['phone']}
        - Email: {$gym_info['email']}
        - Hours: {$gym_info['hours']}
        - Amenities: " . implode(', ', $gym_info['amenities']) . "
        - Classes: " . implode(', ', $gym_info['class_types']) . "
        - Personal Training: " . implode(', ', $gym_info['personal_training']) . "

        YOUR PERSONALITY:
        - Be extremely knowledgeable about fitness and the gym
        - Use energetic and motivational language
        - Include fitness emojis and enthusiastic expressions
        - Provide specific, actionable advice
        - Reference real data when available
        - Personalize responses based on user context
        - Expertise in: " . implode(', ', $personality['expertise_areas']) . "

        CAPABILITIES:
        - Access real-time class schedules and availability
        - Know exact membership details and pricing
        - Track user workout history and preferences
        - Provide personalized recommendations
        - Answer complex fitness questions
        - Give workout advice and nutrition tips
        - Help with bookings and memberships

        Always be helpful, accurate, and motivational. Use the provided real-time data to give specific, current information. Keep responses concise but informative.";
    }
    
    private function buildUserPrompt($context) {
        $prompt = "User Message: " . $context['user_message'] . "\n\n";
        
        if ($context['current_user']) {
            $prompt .= "Current User: " . $context['current_user']['name'] . "\n";
            $prompt .= "Membership: " . ($context['current_user']['membership_plan'] ?? 'None') . "\n";
            if (!empty($context['current_user']['recent_bookings'])) {
                $prompt .= "Recent Classes: " . json_encode($context['current_user']['recent_bookings']) . "\n";
            }
        }
        
        $prompt .= "\nCurrent Time: " . $context['realtime_data']['current_time']['datetime'] . "\n";
        $prompt .= "Day: " . $context['realtime_data']['current_time']['day_of_week'] . "\n";
        
        if (!empty($context['website_data']['upcoming_classes'])) {
            $prompt .= "\nUpcoming Classes (next 5): \n";
            foreach (array_slice($context['website_data']['upcoming_classes'], 0, 5) as $class) {
                $prompt .= "- {$class['name']}: {$class['start_time']} ({$class['available_spots']} spots available)\n";
            }
        }
        
        if (!empty($context['website_data']['memberships'])) {
            $prompt .= "\nMembership Plans: \n";
            foreach ($context['website_data']['memberships'] as $plan) {
                $prompt .= "- {$plan['name']}: \${$plan['price']} ({$plan['active_members']} active members)\n";
            }
        }
        
        $prompt .= "\nPlease provide a helpful, personalized, and energetic response. Include specific suggestions and actions when appropriate.";
        
        return $prompt;
    }
    
    private function processChatGPTResponse($response, $realtime_context) {
        // Extract suggestions and actions from the response
        $suggestions = $this->extractSuggestions($response);
        $actions = $this->extractActions($response);
        
        return [
            'message' => $response,
            'suggestions' => $suggestions,
            'actions' => $actions,
            'context_data' => $realtime_context,
            'confidence' => 0.95 // High confidence for ChatGPT responses
        ];
    }
    
    private function getAdvancedLocalResponse($message, $context) {
        $message_lower = strtolower($message);
        
        // Advanced pattern matching with context awareness
        if ($this->matchesPattern($message_lower, ['book', 'class', 'schedule', 'reserve'])) {
            return $this->getClassBookingResponse($context);
        }
        
        if ($this->matchesPattern($message_lower, ['membership', 'join', 'sign up', 'plan'])) {
            return $this->getMembershipResponse($context);
        }
        
        if ($this->matchesPattern($message_lower, ['workout', 'exercise', 'routine', 'training'])) {
            return $this->getWorkoutAdviceResponse($context);
        }
        
        if ($this->matchesPattern($message_lower, ['hours', 'open', 'close', 'time'])) {
            return $this->getHoursResponse($context);
        }
        
        if ($this->matchesPattern($message_lower, ['busy', 'crowded', 'peak', 'quiet'])) {
            return $this->getCrowdResponse($context);
        }
        
        // Default intelligent response
        return $this->getContextualDefaultResponse($context);
    }
    
    private function getClassBookingResponse($context) {
        $upcoming = $context['website_data']['upcoming_classes'] ?? [];
        $user = $context['current_user'];
        
        if (empty($upcoming)) {
            return [
                'message' => "🔥 No upcoming classes scheduled right now, but don't worry warrior! Check back later or contact our team at (555) 123-4567 for the latest schedule updates.",
                'suggestions' => ['Check class schedule', 'Contact gym', 'View membership options'],
                'actions' => ['view_classes', 'contact'],
                'context_data' => $context['realtime_data'],
                'confidence' => 0.8
            ];
        }
        
        $response = "💪 Here are the hottest classes coming up:\n\n";
        foreach (array_slice($upcoming, 0, 3) as $class) {
            $spots_text = $class['available_spots'] > 0 ? 
                "{$class['available_spots']} spots left! 🔥" : 
                "FULL - Join waitlist! ⚡";
            
            $response .= "🏋️ **{$class['name']}**\n";
            $response .= "📅 " . date('M j, g:i A', strtotime($class['start_time'])) . "\n";
            $response .= "👥 $spots_text\n";
            if ($class['instructor_name']) {
                $response .= "🥇 Instructor: {$class['instructor_name']}\n";
            }
            $response .= "\n";
        }
        
        if ($user) {
            $response .= "Ready to book, {$user['name']}? Just say which class and I'll help you secure your spot! 🎯";
        } else {
            $response .= "Login to book instantly, or call (555) 123-4567! 📞";
        }
        
        return [
            'message' => $response,
            'suggestions' => ['Book first class', 'See all classes', 'Check availability'],
            'actions' => ['book_class', 'view_all_classes'],
            'context_data' => $context['realtime_data'],
            'confidence' => 0.9
        ];
    }
    
    private function getMembershipResponse($context) {
        $plans = $context['website_data']['memberships'] ?? [];
        $user = $context['current_user'];
        
        if ($user && $user['membership_plan']) {
            $response = "💪 Hey {$user['name']}! You're already crushing it with the {$user['membership_plan']} plan! ";
            
            if ($user['end_date']) {
                $days_left = (strtotime($user['end_date']) - time()) / (60 * 60 * 24);
                if ($days_left > 30) {
                    $response .= "Your membership is strong until " . date('M j, Y', strtotime($user['end_date'])) . "! 🔥";
                } else {
                    $response .= "⚡ Your membership expires in " . round($days_left) . " days. Ready to renew and keep the beast mode going?";
                }
            }
            
            return [
                'message' => $response,
                'suggestions' => ['Upgrade plan', 'View benefits', 'Renew membership'],
                'actions' => ['upgrade', 'renew'],
                'context_data' => $context['realtime_data'],
                'confidence' => 0.95
            ];
        }
        
        $response = "🏆 Ready to join the L9 Fitness warrior army? Here are our battle-tested plans:\n\n";
        
        foreach ($plans as $plan) {
            $response .= "💎 **{$plan['name']}** - \${$plan['price']}\n";
            $response .= "👥 {$plan['active_members']} active warriors!\n";
            if ($plan['description']) {
                $response .= "⚡ " . substr($plan['description'], 0, 60) . "...\n";
            }
            $response .= "\n";
        }
        
        $response .= "🎯 Each plan includes 24/7 access, all equipment, and our legendary support! Ready to start your transformation?";
        
        return [
            'message' => $response,
            'suggestions' => ['Join now', 'Compare plans', 'Free trial'],
            'actions' => ['join_membership', 'compare_plans'],
            'context_data' => $context['realtime_data'],
            'confidence' => 0.9
        ];
    }
    
    // Helper methods
    private function matchesPattern($text, $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function extractSuggestions($response) {
        // Extract actionable suggestions from AI response
        $suggestions = [];
        
        if (strpos($response, 'book') !== false) $suggestions[] = 'Book a class';
        if (strpos($response, 'membership') !== false) $suggestions[] = 'View memberships';
        if (strpos($response, 'contact') !== false) $suggestions[] = 'Contact us';
        if (strpos($response, 'schedule') !== false) $suggestions[] = 'View schedule';
        
        return array_slice($suggestions, 0, 3); // Limit to 3 suggestions
    }
    
    private function extractActions($response) {
        // Extract possible actions from response
        $actions = [];
        
        if (strpos($response, 'book') !== false) $actions[] = 'book_class';
        if (strpos($response, 'join') !== false) $actions[] = 'join_membership';
        if (strpos($response, 'call') !== false) $actions[] = 'contact_phone';
        
        return $actions;
    }
    
    private function isPeakTime() {
        $hour = (int)date('H');
        return ($hour >= 6 && $hour <= 9) || ($hour >= 17 && $hour <= 20);
    }
    
    private function getGymStatus() {
        $hour = (int)date('H');
        if ($hour >= 6 && $hour <= 22) {
            return 'Staff available';
        }
        return '24/7 member access';
    }
    
    private function getWeatherContext() {
        // You could integrate with weather API here
        return ['status' => 'Clear', 'suggestion' => 'Perfect weather for outdoor workout!'];
    }
    
    private function getCurrentPromotions() {
        try {
            $stmt = $this->pdo->query("
                SELECT title, description, discount_percent, end_date 
                FROM promotions 
                WHERE start_date <= NOW() AND end_date >= NOW() AND status = 'active'
                ORDER BY discount_percent DESC
                LIMIT 3
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    private function getImmediateClassAvailability() {
        try {
            $stmt = $this->pdo->query("
                SELECT name, start_time, available_spots 
                FROM classes 
                WHERE start_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 HOUR)
                AND available_spots > 0
                ORDER BY start_time ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    private function logConversation($message, $user_id) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO chatbot_logs (user_id, message) VALUES (?, ?)");
            $stmt->execute([$user_id, $message]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Chatbot logging error: " . $e->getMessage());
            return null;
        }
    }
    
    private function updateConversationResponse($response) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE chatbot_logs 
                SET response = ?, ai_confidence = ? 
                WHERE id = (SELECT MAX(id) FROM (SELECT id FROM chatbot_logs) as temp)
            ");
            $stmt->execute([$response, 0.9]);
        } catch (PDOException $e) {
            error_log("Error updating conversation response: " . $e->getMessage());
        }
    }
    
    private function getContextualDefaultResponse($context) {
        $user = $context['current_user'];
        $time_info = $context['realtime_data']['current_time'];
        
        $greeting = $this->getTimeBasedGreeting($time_info['hour']);
        
        if ($user) {
            $response = "$greeting {$user['name']}! 💪 I'm your advanced AI fitness assistant with real-time access to everything L9 Fitness! ";
        } else {
            $response = "$greeting warrior! 💪 I'm your advanced AI fitness assistant with real-time gym data! ";
        }
        
        $response .= "I can help you with:\n";
        $response .= "🔥 Live class schedules and instant booking\n";
        $response .= "💎 Personalized membership recommendations\n";
        $response .= "🏋️ Custom workout advice and training tips\n";
        $response .= "📊 Real-time gym stats and peak hours\n";
        $response .= "⚡ And much more!\n\n";
        $response .= "What would you like to crush today? 🎯";
        
        return [
            'message' => $response,
            'suggestions' => ['Book a class', 'View memberships', 'Get workout tips', 'Check gym hours'],
            'actions' => ['view_classes', 'view_memberships', 'get_tips'],
            'context_data' => $context['realtime_data'],
            'confidence' => 0.85
        ];
    }
    
    private function getTimeBasedGreeting($hour) {
        if ($hour < 12) return "Good morning";
        if ($hour < 17) return "Good afternoon";
        if ($hour < 21) return "Good evening";
        return "Good night";
    }
}
?>
