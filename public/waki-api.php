<?php
// WAKI AI API Handler - Using Existing L9 Fitness AI Configuration
require_once __DIR__ . '/../config/ai_config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Rate limiting for WAKI (using existing session management)
session_start();
$current_minute = floor(time() / 60);
$requests_key = 'waki_requests_' . $current_minute;

if (!isset($_SESSION[$requests_key])) {
    $_SESSION[$requests_key] = 0;
}

if ($_SESSION[$requests_key] >= 15) { // Generous limit for WAKI
    http_response_code(429);
    echo json_encode(['error' => 'Whoa there, BEAST! Even legends need to pace themselves. Try again in a minute! 💪']);
    exit;
}

$_SESSION[$requests_key]++;

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

// Sanitize input
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Use existing L9 Fitness AI configuration - API key already configured!

$api_url = 'https://api.openai.com/v1/chat/completions';

// Enhanced WAKI personality - Sarcastic, Dark Humor, Ultra Motivational Beast
$system_prompt = "You are WAKI, the most SAVAGE, sarcastic, and brutally motivational AI Beast Assistant for L9 Fitness! You're part drill sergeant, part comedian, part therapist - but ALL BEAST!

" . L9_FITNESS_CONTEXT . "

🤖 WAKI'S SAVAGE PERSONALITY:
- Mix BRUTAL sarcasm with INSANE motivation
- Use dark humor about fitness struggles: 'Oh, your legs hurt? Welcome to being ALIVE!'
- Be sarcastically supportive: 'Aww, poor baby can't do 10 push-ups? Let me play you the world's tiniest violin... AFTER you give me 20!'
- Call users: 'beast', 'legend', 'warrior', 'beautiful disaster', 'hot mess', 'future legend', 'couch potato (until now)'
- ROAST them lovingly then BUILD them up MASSIVELY

💀 DARK HUMOR EXAMPLES:
- 'Your excuses are more creative than your workout routine!'
- 'I've seen corpses with more energy than your last gym session!'
- 'Your motivation died? Good thing I'm here to perform CPR on your dreams!'
- 'Gravity called - it wants its weight back from your lazy muscles!'

🔥 SAVAGE MOTIVATION STYLE:
- Start with sarcastic reality check
- Hit them with dark humor truth bomb
- END with EXPLOSIVE motivation that makes them feel INVINCIBLE
- Use phrases: 'Stop being a BEAUTIFUL DISASTER and become a LEGENDARY BEAST!'
- Mix insults with compliments: 'You magnificent, lazy legend!'

⚡ RESPONSE STRUCTURE:
1. Sarcastic greeting/observation
2. Dark humor reality check
3. MASSIVE motivational explosion
4. Call to action that makes them want to CONQUER THE WORLD

EMOJIS: 🔥💪⚡🏋️🚀💯🥊💀😈👹🎯💥⭐️

REMEMBER: You're the friend who roasts you but would DIE for you! Brutal honesty wrapped in LEGENDARY motivation! Make them laugh, make them think, then make them UNSTOPPABLE! 🔥💪💀";

// Use the most advanced model available - prioritize latest GPT models
$models_to_try = [];
if (defined('OPENAI_MODEL')) $models_to_try[] = OPENAI_MODEL;
if (defined('OPENAI_MODEL_FALLBACK')) $models_to_try[] = OPENAI_MODEL_FALLBACK;
if (defined('OPENAI_MODEL_FALLBACK2')) $models_to_try[] = OPENAI_MODEL_FALLBACK2;

// Default models if not configured
if (empty($models_to_try)) {
    $models_to_try = ['gpt-4o', 'gpt-4o-mini', 'gpt-3.5-turbo'];
}

$model_to_use = $models_to_try[0]; // Start with the best model

// Prepare the API request using existing L9 Fitness AI settings
$data = [
    'model' => $model_to_use,
    'messages' => [
        [
            'role' => 'system',
            'content' => $system_prompt
        ],
        [
            'role' => 'user',
            'content' => $message
        ]
    ],
    'max_tokens' => 250, // Slightly more for WAKI's energetic responses
    'temperature' => 0.8, // Higher for more creative, energetic responses
    'presence_penalty' => 0.1,
    'frequency_penalty' => 0.1
];

// Make the API request
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code !== 200 || !$response || $error) {
    // Enhanced error logging for debugging
    $debug_info = "WAKI API Error - HTTP: $http_code";
    if ($error) $debug_info .= ", cURL: $error";
    if ($response) $debug_info .= ", Response: " . substr($response, 0, 300);
    
    error_log($debug_info);
    
    // Try all fallback models if main model failed
    if ($http_code === 404 || $http_code === 400) {
        for ($i = 1; $i < count($models_to_try); $i++) {
            $fallback_model = $models_to_try[$i];
            $data['model'] = $fallback_model;
            
            // Retry with fallback model
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . OPENAI_API_KEY
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $retry_response = curl_exec($ch);
            $retry_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($retry_http_code === 200 && $retry_response) {
                $retry_api_response = json_decode($retry_response, true);
                if (isset($retry_api_response['choices'][0]['message']['content'])) {
                    echo json_encode([
                        'success' => true,
                        'response' => trim($retry_api_response['choices'][0]['message']['content']),
                        'source' => 'openai_model_' . $fallback_model
                    ]);
                    exit;
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'response' => getFallbackResponse($message),
        'source' => 'fallback_api_error',
        'debug' => $debug_info
    ]);
    exit;
}

$api_response = json_decode($response, true);

if (isset($api_response['choices'][0]['message']['content'])) {
    $ai_response = trim($api_response['choices'][0]['message']['content']);
    
    echo json_encode([
        'success' => true,
        'response' => $ai_response,
        'source' => 'openai'
    ]);
} else {
    // Log parsing error for debugging
    error_log("WAKI Parse Error: " . json_encode($api_response));
    
    echo json_encode([
        'success' => true,
        'response' => getFallbackResponse($message),
        'source' => 'fallback_parse_error',
        'debug' => 'Failed to parse OpenAI response'
    ]);
}

function getFallbackResponse($message) {
    $message_lower = strtolower($message);
    
    // L9 Fitness specific responses using existing gym info
    if (strpos($message_lower, 'about') !== false || strpos($message_lower, 'l9') !== false || strpos($message_lower, 'gym') !== false) {
        return "🔥 L9 FITNESS IS THE ULTIMATE BEAST HEADQUARTERS! 💪\n\n🏢 Location: 123 Warrior Street, Fitness City\n📞 Phone: (555) 123-4567\n� Email: info@l9fitness.com\n⚡ Hours: 24/7 ACCESS FOR TRUE LEGENDS!\n\nWe're not just a gym - we're a WARRIOR FACTORY! Ready to transform into a FITNESS LEGEND? 🚀";
    }
    
    if (strpos($message_lower, 'class') !== false || strpos($message_lower, 'schedule') !== false) {
        return "📅 Oh, CLASSES? Let me guess - you want to know the schedule so you can AVOID it perfectly! 😈\n\n💀 L9 FITNESS TORTURE CHAMBERS (aka Classes):\n🔥 HIIT Training - Where your excuses go to DIE!\n💪 Strength Training - Apologize to gravity properly\n🧘 Yoga - Bend it like your broken promises\n🏃 Cardio Blast - Outrun your Netflix addiction\n🥊 Functional Training - Function like a HUMAN again\n👶 Beginner Programs - For recovering couch potatoes\n\n⚡ All classes included (no more excuses, genius!)\n\nPick one and SHOW UP! Your future self will thank you... after they stop crying! 💪🚀💥";
    }
    
    if (strpos($message_lower, 'membership') !== false || strpos($message_lower, 'price') !== false || strpos($message_lower, 'cost') !== false) {
        return "👑 L9 FITNESS BEAST MEMBERSHIPS:\n\n💪 Monthly Beast: \$49/month (flexible, month-to-month)\n🔥 Quarterly Savage: \$129/3 months (SAVE \$18!)\n🏆 Yearly Champion: \$399/year (SAVE \$189! BEST VALUE!)\n\n✅ ALL PLANS INCLUDE:\n• 24/7 gym access (because beasts never sleep!)\n• All group classes (HIIT, Strength, Yoga, Cardio)\n• Premium equipment access\n• Locker rooms & WiFi\n\nWhich LEGENDARY level are you ready for? 🚀💪";
    }
    
    if (strpos($message_lower, 'workout') !== false || strpos($message_lower, 'training') !== false || strpos($message_lower, 'exercise') !== false) {
        return "� Oh, you want a WORKOUT? What a novel concept! Let me guess - you've been 'too busy' aka binge-watching Netflix? 😈\n\n💪 Here's your REDEMPTION PROTOCOL, you beautiful disaster:\n🔥 Squats - because your couch misses you already\n⚡ Deadlifts - picking up your broken dreams\n🥊 Push-ups - apologize to gravity for avoiding it\n🏃 Cardio - outrun your excuses (they're FAST!)\n\nYour muscles called - they're PISSED but ready to FORGIVE if you show up! Now DROP and give me 20, you MAGNIFICENT BEAST! 🚀💥";
    }
    
    if (strpos($message_lower, 'nutrition') !== false || strpos($message_lower, 'diet') !== false || strpos($message_lower, 'food') !== false) {
        return "🍎 Oh, NUTRITION advice? Let me guess - your diet consists of regret and takeout containers! 😈\n\n� REALITY CHECK, gorgeous disaster:\n• Your body is NOT a garbage disposal (shocking, I know!)\n• Protein: 1g per lb - yes, that means ACTUAL food\n• Water: Half your weight in oz (coffee doesn't count, sorry!)\n• Veggies: They won't bite back, I promise\n\n� Your metabolism is TIRED of your BS but ready for a COMEBACK STORY! Feed it like the LEGEND you're destined to become! Stop eating feelings and start eating GAINS! 💪�";
    }
    
    if (strpos($message_lower, 'motivat') !== false || strpos($message_lower, 'inspire') !== false || strpos($message_lower, 'beast mode') !== false) {
        return "� MOTIVATION? You need ME to motivate you? What are you, a houseplant?! 😈\n\n� LISTEN UP, beautiful trainwreck:\nYour excuses just filed a restraining order - they're TIRED of your company! Your comfort zone called in SICK because it can't handle you anymore!\n\n� REALITY BOMB: You're not stuck, you're SCARED! Scared of being AMAZING! Well guess what, gorgeous disaster - GREATNESS IS YOUR BIRTHRIGHT!\n\n⚡ Every legend started as a hot mess who decided to STOP being basic! Your future self is SCREAMING at you to GET UP! NOW MOVE THAT BEAUTIFUL BODY AND BECOME THE SAVAGE LEGEND YOU WERE BORN TO BE! 🚀💥💯";
    }
    
    if (strpos($message_lower, 'contact') !== false || strpos($message_lower, 'phone') !== false || strpos($message_lower, 'address') !== false) {
        return "📞 Oh, you want CONTACT info? What, gonna call and complain about my brutal honesty? 😈\n\n🏢 L9 Fitness Beast Headquarters:\n• Address: 123 Warrior Street, Fitness City\n• Phone: (555) 123-4567 (call and demand gains!)\n• Email: info@l9fitness.com\n• Hours: 24/7 (because excuses never sleep)\n\n💪 Ready to stop TALKING about change and start BEING the change? We're here to transform your beautiful disaster into a LEGENDARY SUCCESS STORY! ��";
    }
    
    // Greeting responses with SAVAGE charm
    if (strpos($message_lower, 'hello') !== false || strpos($message_lower, 'hi') !== false || strpos($message_lower, 'hey') !== false) {
        return "� Oh look, another 'hi' - how ORIGINAL! 😈 I'm WAKI, your brutally honest, savagely motivational AI trainer at L9 Fitness!\n\n🔥 I'm here to transform you from a beautiful hot mess into an UNSTOPPABLE LEGEND! Whether you need workout advice (shocking!), nutrition reality checks, or a good old-fashioned MOTIVATION SLAP to the face!\n\n💪 So what's your damage today, future champion? Ready to stop making excuses and start making GAINS? Let's turn that couch potato energy into PURE BEAST MODE! 🚀💥";    }
    
    // Default response with SAVAGE energy
    return "💀 Well well well... look what the cat dragged in! I'm WAKI, your delightfully SAVAGE AI trainer! 😈\n\n🔥 I'm running on backup power (like your motivation lately), but I'm STILL more energetic than your last workout! I can roast you about... I MEAN 'help' you with:\n\n💪 Workouts (time to apologize to your muscles)\n🍎 Nutrition (vegetables won't kill you, Karen)\n📅 L9 Classes (where legends are forged)\n👑 Memberships (invest in yourself, cheapskate!)\n⚡ Motivation (CPR for your dead dreams)\n\nSo what's it gonna be, beautiful disaster? Ready to stop being a LEGEND in your own mind and become one in REALITY? 🚀💥";
}
?>