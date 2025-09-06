<?php
// AI Service for L9 Fitness Chatbot
require_once __DIR__ . '/../../config/ai_config.php';

class AIService {
    
    public static function generateResponse($message, $user_context = null) {
        $provider = AI_PROVIDER;
        
        try {
            switch ($provider) {
                case 'openai':
                    return self::getOpenAIResponse($message, $user_context);
                case 'huggingface':
                    return self::getHuggingFaceResponse($message, $user_context);
                case 'ollama':
                    return self::getOllamaResponse($message, $user_context);
                default:
                    throw new Exception("Unknown AI provider: $provider");
            }
        } catch (Exception $e) {
            error_log("AI Service Error: " . $e->getMessage());
            
            if (USE_AI_FALLBACK) {
                return self::getFallbackResponse($message, $user_context);
            }
            
            return "I'm having trouble connecting to my AI brain right now. Please contact our staff at (555) 123-4567 for assistance!";
        }
    }
    
    private static function getOpenAIResponse($message, $user_context) {
        if (empty(OPENAI_API_KEY)) {
            throw new Exception("OpenAI API key not configured");
        }
        
        $context = L9_FITNESS_CONTEXT;
        if ($user_context) {
            $context .= "\n\nUser context: " . $user_context;
        }
        
        $data = [
            'model' => OPENAI_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $context],
                ['role' => 'user', 'content' => $message]
            ],
            'max_tokens' => AI_MAX_TOKENS,
            'temperature' => AI_TEMPERATURE
        ];
        
        $response = self::makeAPIRequest(
            'https://api.openai.com/v1/chat/completions',
            $data,
            ['Authorization: Bearer ' . OPENAI_API_KEY, 'Content-Type: application/json']
        );
        
        if (isset($response['choices'][0]['message']['content'])) {
            return trim($response['choices'][0]['message']['content']);
        }
        
        throw new Exception("Invalid OpenAI response format");
    }
    
    private static function getHuggingFaceResponse($message, $user_context) {
        // Using a free conversational AI model from Hugging Face
        $context = "L9 Fitness Gym Assistant. " . substr(L9_FITNESS_CONTEXT, 0, 200);
        $fullMessage = $context . "\n\nUser: " . $message . "\nAssistant:";
        
        $data = [
            'inputs' => $fullMessage,
            'parameters' => [
                'max_length' => 100,
                'temperature' => AI_TEMPERATURE,
                'return_full_text' => false
            ]
        ];
        
        $headers = ['Content-Type: application/json'];
        if (!empty(HUGGINGFACE_API_KEY)) {
            $headers[] = 'Authorization: Bearer ' . HUGGINGFACE_API_KEY;
        }
        
        $response = self::makeAPIRequest(
            'https://api-inference.huggingface.co/models/microsoft/DialoGPT-medium',
            $data,
            $headers
        );
        
        if (isset($response[0]['generated_text'])) {
            return trim($response[0]['generated_text']);
        }
        
        throw new Exception("Invalid Hugging Face response format");
    }
    
    private static function getOllamaResponse($message, $user_context) {
        $context = L9_FITNESS_CONTEXT;
        if ($user_context) {
            $context .= "\n\nUser context: " . $user_context;
        }
        
        $data = [
            'model' => OLLAMA_MODEL,
            'prompt' => $context . "\n\nUser: " . $message . "\nAssistant:",
            'stream' => false
        ];
        
        $response = self::makeAPIRequest(
            OLLAMA_ENDPOINT . '/api/generate',
            $data,
            ['Content-Type: application/json']
        );
        
        if (isset($response['response'])) {
            return trim($response['response']);
        }
        
        throw new Exception("Invalid Ollama response format");
    }
    
    private static function makeAPIRequest($url, $data, $headers) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: $httpCode - $response");
        }
        
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }
        
        return $decoded;
    }
    
    private static function getFallbackResponse($message, $user_context) {
        $message_lower = strtolower($message);
        
        // Enhanced pattern matching with L9 Fitness context
        if (preg_match('/\b(hi|hello|hey|good morning|good afternoon|good evening|yo|sup|what\'s up)\b/i', $message_lower)) {
            return "Welcome to L9 Fitness! 💪 I'm here to help you with any questions about our gym, memberships, classes, or facilities. What would you like to know?";
        }
        
        // About the gym / general info
        if (preg_match('/\b(tell me about|about your gym|about this gym|gym info|what is|gym details)\b/i', $message_lower)) {
            return "🏋️ **About L9 Fitness:**\n\nWe're a premium fitness center dedicated to helping you achieve your goals! Here's what makes us special:\n\n🏆 **What We Offer:**\n• 24/7 member access\n• Professional-grade equipment\n• Expert-led group classes\n• Personal training services\n• Clean, modern facilities\n\n💪 **Our Mission:** To provide a supportive environment where everyone from beginners to athletes can thrive.\n\nWhat specific aspect would you like to know more about?";
        }
        
        // Beginner questions
        if (preg_match('/\b(beginner|new to|just starting|first time|never worked out)\b/i', $message_lower)) {
            return "🌟 **Perfect! We love helping beginners start their fitness journey!**\n\nHere's what I recommend:\n\n1. **Start with a gym tour** - Get familiar with our equipment\n2. **Try our beginner-friendly classes** like Basic Strength or Intro to Fitness\n3. **Consider a personal trainer** for your first few sessions\n4. **Take it slow** - consistency beats intensity when starting out\n\n**Beginner-Friendly Options:**\n• Yoga & Flexibility classes\n• Basic cardio equipment orientation\n• Personal training packages\n\nWould you like to know about our membership options or schedule a tour?";
        }
        
        // Hours
        if (preg_match('/\b(hours|open|close|time|schedule|when)\b/i', $message_lower)) {
            return "🕐 **L9 Fitness Hours:**\n\n🔓 **Gym Access:** 24/7 for all members\n👥 **Staff Available:**\n• Monday-Friday: 6AM-10PM\n• Saturday-Sunday: 8AM-8PM\n\n**Need help outside staff hours?** Our facility is fully accessible with your member keycard, and we have emergency contact numbers posted throughout the gym.";
        }
        
        // Membership
        if (preg_match('/\b(membership|price|cost|plan|join|sign up|pricing)\b/i', $message_lower)) {
            return "� **L9 Fitness Membership Plans:**\n\n🥉 **Monthly Beast:** $49/month\n• Month-to-month flexibility\n• All gym access & classes\n\n🥈 **Quarterly Savage:** $129/3 months *(Save $18!)*\n• Better value for committed members\n• Priority class booking\n\n🥇 **Yearly Champion:** $399/year *(Save $189!)*\n• Best value - less than $1.10/day!\n• Personal training discount\n• Guest passes included\n\n**All plans include:** 24/7 access, group classes, locker rooms, and free WiFi!\n\nReady to join the L9 family?";
        }
        
        // Classes
        if (preg_match('/\b(class|classes|workout|training|group|fitness)\b/i', $message_lower)) {
            return "🔥 **L9 Fitness Classes - Something for Everyone!**\n\n⚡ **High Energy:**\n• HIIT Training - Burn calories fast\n• Cardio Blast - Heart-pumping fun\n\n💪 **Strength & Power:**\n• Strength Training - Build muscle\n• Functional Training - Real-world fitness\n\n🧘 **Mind & Body:**\n• Yoga & Flexibility - Find your zen\n• Recovery & Stretching - Heal and restore\n\n**Class Schedule:** Check our app or front desk for current times. Most classes run multiple times per day!\n\nNew to group fitness? Try our 'Beginner Basics' class!";
        }
        
        // Equipment
        if (preg_match('/\b(equipment|machine|weights|cardio|facilities|gym)\b/i', $message_lower)) {
            return "🏋️ **L9 Fitness Equipment & Facilities:**\n\n💪 **Strength Zone:**\n• Complete free weight collection\n• Olympic barbells & bumper plates\n• Cable machines & functional trainers\n\n🏃 **Cardio Area:**\n• Latest treadmills with entertainment\n• Ellipticals & stationary bikes\n• Rowing machines & stair climbers\n\n🎯 **Functional Training:**\n• Battle ropes & kettlebells\n• TRX suspension trainers\n• Agility ladder & plyometric boxes\n\n🚿 **Amenities:**\n• Clean locker rooms with showers\n• Stretching & recovery area\n• Water stations throughout\n\nEverything is commercial-grade and regularly maintained!";
        }
        
        // Personal Training
        if (preg_match('/\b(personal trainer|pt|coach|training|trainer)\b/i', $message_lower)) {
            return "🥊 **Personal Training at L9 Fitness:**\n\n👨‍💼 **Our Certified Trainers** help you:\n• Reach your goals faster\n• Learn proper form & technique\n• Stay motivated & accountable\n• Create customized workout plans\n\n💵 **Training Packages:**\n• **Single Session:** $75\n• **4-Session Pack:** $280 *(Save $20)*\n• **8-Session Pack:** $520 *(Save $80)*\n\n🎯 **Perfect For:**\n• Beginners wanting guidance\n• Breaking through plateaus\n• Sport-specific training\n• Injury rehabilitation\n\nInterested in meeting with a trainer? We offer free consultations!";
        }
        
        // Location
        if (preg_match('/\b(location|address|where|directions|find)\b/i', $message_lower)) {
            return "📍 **Find L9 Fitness:**\n\n🏢 **Address:**\n123 Warrior Street\nFitness City, FC 12345\n\n🚗 **Parking & Access:**\n• Free parking lot\n• Easy highway access\n• Public transit nearby\n• Wheelchair accessible\n\n🗺️ **Landmarks:**\n• Next to the big shopping center\n• Across from City Park\n• 5 minutes from downtown\n\nNeed specific directions? Call us at (555) 123-4567!";
        }
        
        // Contact
        if (preg_match('/\b(contact|phone|email|support|help)\b/i', $message_lower)) {
            return "📞 **Get in Touch with L9 Fitness:**\n\n☎️ **Phone:** (555) 123-4567\n📧 **Email:** info@l9fitness.com\n\n⏰ **Staff Available:**\n• Monday-Friday: 6AM-10PM\n• Weekends: 8AM-8PM\n\n💬 **What We Can Help With:**\n• Membership questions\n• Class schedules & booking\n• Personal training appointments\n• Facility tours\n• Technical support\n\n🏃‍♂️ **Visit Us:** Stop by anytime for a tour - no appointment needed during staff hours!";
        }
        
        // Default intelligent response - much more helpful
        return "I'd be happy to help you learn more about L9 Fitness! 💪\n\n**I can tell you about:**\n• 🏋️ Our equipment and facilities\n• 💰 Membership plans and pricing\n• 🔥 Group classes and schedules\n• 🥊 Personal training services\n• 📍 Location and hours\n• 🌟 Programs for beginners\n\n**Just ask me something like:**\n• 'Tell me about your gym'\n• 'What membership plans do you have?'\n• 'I'm a beginner, what should I do?'\n• 'What classes do you offer?'\n\nWhat would you like to know more about?";
    }
}
?>
