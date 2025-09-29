<?php
// WAKI AI Configuration

// OpenAI API Configuration
// Replace 'YOUR_OPENAI_API_KEY' with your actual OpenAI API key
// Get your API key from: https://platform.openai.com/api-keys
define('OPENAI_API_KEY', 'YOUR_OPENAI_API_KEY');

// API Settings
define('OPENAI_MODEL', 'gpt-3.5-turbo');
define('OPENAI_MAX_TOKENS', 300);
define('OPENAI_TEMPERATURE', 0.8);

// Rate Limiting (requests per minute per IP)
define('RATE_LIMIT_PER_MINUTE', 10);

// WAKI Personality Settings
define('WAKI_PERSONALITY', [
    'energetic' => true,
    'motivational' => true,
    'use_emojis' => true,
    'fitness_focused' => true,
    'l9_gym_expert' => true
]);

// Fallback Mode (when API is unavailable)
define('ENABLE_FALLBACK', true);

// Debug Mode
define('WAKI_DEBUG', false);

// L9 Fitness Information
define('L9_GYM_INFO', [
    'name' => 'L9 Fitness',
    'slogan' => 'Push Your Limits • Beast Mode • 24/7',
    'address' => '123 Beast Mode Blvd, Fitness City',
    'phone' => '+1 (855) L9-BEAST',
    'email' => 'beast@l9fitness.com',
    'hours' => '24/7 Access',
    'classes' => [
        'Monday' => 'Beast Mode Bootcamp (6AM, 7PM)',
        'Tuesday' => 'Iron Fury Strength (6:30AM, 6PM)',
        'Wednesday' => 'HIIT Thunder (7AM, 8PM)',
        'Thursday' => 'Combat Beast (6AM, 7:30PM)',
        'Friday' => 'Cardio Chaos (6:30AM, 6PM)',
        'Saturday' => 'Functional Beast (9AM, 11AM)',
        'Sunday' => 'Recovery Flow (10AM, 5PM)'
    ],
    'memberships' => [
        'Beast Starter' => ['price' => 29, 'features' => 'Gym access + 2 classes'],
        'Beast Warrior' => ['price' => 49, 'features' => 'Unlimited access + all classes'],
        'Beast Legend' => ['price' => 79, 'features' => 'Everything + personal training'],
        'Beast Ultimate' => ['price' => 129, 'features' => 'VIP access + nutrition coaching']
    ]
]);

// Security Settings
define('ALLOWED_ORIGINS', ['localhost', '127.0.0.1', $_SERVER['HTTP_HOST'] ?? 'localhost']);
define('ENABLE_CORS', true);

// Cache Settings
define('ENABLE_RESPONSE_CACHE', false);
define('CACHE_DURATION', 300); // 5 minutes

?>

<!-- SETUP INSTRUCTIONS -->
<!--
🤖 WAKI AI SETUP GUIDE:

1. GET OPENAI API KEY:
   - Visit: https://platform.openai.com/api-keys
   - Create account and get your API key
   - Replace 'YOUR_OPENAI_API_KEY' above with your actual key

2. API KEY SECURITY:
   - Never commit API keys to version control
   - Consider using environment variables in production
   - Monitor your API usage and costs

3. TESTING:
   - Visit: http://localhost/Capstone-latest/public/waki.php
   - Try asking questions about fitness, L9 gym, etc.
   - Check browser console for any errors

4. FALLBACK MODE:
   - If API key is not set, WAKI uses local responses
   - Fallback responses are still fitness-focused and helpful

5. CUSTOMIZATION:
   - Modify WAKI_PERSONALITY settings above
   - Update L9_GYM_INFO with your actual gym details
   - Adjust rate limiting and other settings as needed

💪 WAKI is ready to help your gym members crush their goals!
-->