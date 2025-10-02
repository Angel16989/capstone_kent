<?php
// L9 Fitness Advanced AI Chatbot Configuration
// 
// INSTRUCTIONS TO ADD YOUR CHATGPT API KEY:
// 1. Get your API key from https://platform.openai.com/api-keys
// 2. Replace "YOUR_OPENAI_API_KEY_HERE" below with your actual API key
// 3. Save this file
// 4. The chatbot will automatically use ChatGPT for super intelligent responses!

return [
    // ChatGPT API Configuration
    // For security, use environment variable: getenv('OPENAI_API_KEY')
    'openai_api_key' => getenv('OPENAI_API_KEY') ?: 'YOUR_OPENAI_API_KEY_HERE',  // REPLACE THIS WITH YOUR API KEY
    'openai_model' => 'gpt-4o',  // or 'gpt-4' for even better responses
    'max_tokens' => 500,
    'temperature' => 0.7,
    
    // Chatbot Settings
    'chatbot_name' => 'L9 AI Assistant',
    'max_conversation_history' => 10,
    'response_timeout' => 30,
    
    // Gym Information (used by AI for context)
    'gym_info' => [
        'name' => 'L9 Fitness',
        'location' => '123 Warrior Street, Fitness City',
        'phone' => '(555) 123-4567',
        'email' => 'warrior@l9fitness.com',
        'hours' => '24/7 access for members, Staff: Mon-Fri 6AM-10PM, Sat-Sun 8AM-8PM',
        'amenities' => [
            'Free weights and dumbbells',
            'Cardio equipment (treadmills, bikes, ellipticals)',
            'Group fitness classes',
            'Personal training',
            'Locker rooms with showers',
            'Parking',
            'Recovery area',
            'Nutrition supplements'
        ],
        'class_types' => [
            'HIIT (High-Intensity Interval Training)',
            'Strength Training',
            'Yoga',
            'Cardio Kickboxing',
            'Functional Fitness',
            'Spin Classes',
            'Pilates',
            'Zumba'
        ],
        'personal_training' => [
            'Weight loss coaching',
            'Muscle building programs',
            'Athletic performance training',
            'Injury rehabilitation',
            'Nutrition counseling',
            'Fitness assessments'
        ]
    ],
    
    // AI Personality Settings
    'ai_personality' => [
        'tone' => 'energetic, motivational, and knowledgeable',
        'emoji_usage' => true,
        'fitness_slang' => true,
        'encouragement_level' => 'high',
        'expertise_areas' => [
            'fitness and exercise',
            'nutrition and diet',
            'gym equipment usage',
            'workout planning',
            'motivation and goal setting',
            'health and wellness'
        ]
    ],
    
    // Feature Flags
    'features' => [
        'voice_input' => true,
        'message_rating' => true,
        'conversation_export' => true,
        'smart_suggestions' => true,
        'real_time_data' => true,
        'personalization' => true
    ],
    
    // Advanced Settings
    'advanced' => [
        'fallback_to_local' => true,
        'cache_responses' => false,
        'analytics_enabled' => true,
        'debug_mode' => false
    ]
];
?>
