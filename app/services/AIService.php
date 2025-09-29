<?php
// Enhanced AI Service for L9 Fitness Chatbot
require_once __DIR__ . '/../../config/ai_config.php';

class AIService {
    
    public static function generateResponse($message, $user_context = null) {
        $provider = AI_PROVIDER;
        
        try {
            switch ($provider) {
                case 'openai':
                    return self::getOpenAIResponse($message, $user_context);
                case 'groq':
                    return self::getGroqResponse($message, $user_context);
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
                return self::getSmartFallbackResponse($message, $user_context);
            }

            return "I'm having trouble connecting to my AI brain right now. Please contact our staff at (555) 123-4567 for assistance!";
        }
    }
    
    private static function getOpenAIResponse($message, $user_context) {
        if (empty(OPENAI_API_KEY)) {
            throw new Exception("OpenAI API key not configured");
        }
        
        $systemMessage = L9_FITNESS_CONTEXT . "\n\nAlways respond as an enthusiastic, knowledgeable L9 Fitness assistant. Provide helpful, accurate information about fitness, health, and our gym services. Keep responses conversational but informative.";
        
        if ($user_context) {
            $systemMessage .= "\n\nUser context: " . $user_context;
        }
        
        $data = [
            'model' => OPENAI_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user', 'content' => $message]
            ],
            'max_tokens' => 200,
            'temperature' => 0.7,
            'presence_penalty' => 0.1,
            'frequency_penalty' => 0.1
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
    
    private static function getGroqResponse($message, $user_context) {
        // Groq provides free, fast AI API access
        $systemMessage = L9_FITNESS_CONTEXT . "\n\nYou are an enthusiastic, knowledgeable fitness assistant for L9 Fitness. Provide helpful, accurate, and engaging responses about fitness, health, and gym services. Keep responses under 200 words but be informative and motivating. Use emojis appropriately.";
        
        if ($user_context) {
            $systemMessage .= "\n\nUser context: " . $user_context;
        }
        
        $data = [
            'model' => defined('GROQ_MODEL') ? GROQ_MODEL : 'llama3-8b-8192',
            'messages' => [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user', 'content' => $message]
            ],
            'max_tokens' => 200,
            'temperature' => 0.7
        ];
        
        $headers = ['Content-Type: application/json'];
        if (!empty(GROQ_API_KEY)) {
            $headers[] = 'Authorization: Bearer ' . GROQ_API_KEY;
        }
        
        $response = self::makeAPIRequest(
            'https://api.groq.com/openai/v1/chat/completions',
            $data,
            $headers
        );
        
        if (isset($response['choices'][0]['message']['content'])) {
            return trim($response['choices'][0]['message']['content']);
        }
        
        throw new Exception("Invalid Groq response format");
    }
    
    private static function getHuggingFaceResponse($message, $user_context) {
        // Using a powerful conversational AI model from Hugging Face
        $systemPrompt = L9_FITNESS_CONTEXT . "\n\nYou are now chatting with a user. Provide helpful, accurate, and engaging responses about fitness and L9 Fitness. Keep responses under 150 words but be informative and enthusiastic.";
        
        // Create a conversational prompt with all L9 Fitness information
        $conversationPrompt = $systemPrompt . "\n\nUser: " . $message . "\nL9 Fitness Assistant:";
        
        $data = [
            'inputs' => $conversationPrompt,
            'parameters' => [
                'max_length' => 200,
                'temperature' => 0.7,
                'do_sample' => true,
                'top_p' => 0.9,
                'return_full_text' => false
            ]
        ];
        
        $headers = ['Content-Type: application/json'];
        if (!empty(HUGGINGFACE_API_KEY)) {
            $headers[] = 'Authorization: Bearer ' . HUGGINGFACE_API_KEY;
        }
        
        // Try multiple AI models for best results
        $models = [
            'microsoft/DialoGPT-large',
            'facebook/blenderbot-400M-distill',
            'microsoft/DialoGPT-medium'
        ];
        
        foreach ($models as $model) {
            try {
                $response = self::makeAPIRequest(
                    'https://api-inference.huggingface.co/models/' . $model,
                    $data,
                    $headers
                );
                
                if (isset($response[0]['generated_text'])) {
                    $generatedText = trim($response[0]['generated_text']);
                    
                    // Clean up the response
                    $generatedText = self::cleanAIResponse($generatedText, $message);
                    
                    if (!empty($generatedText) && strlen($generatedText) > 10) {
                        return $generatedText;
                    }
                }
            } catch (Exception $e) {
                error_log("Hugging Face model $model failed: " . $e->getMessage());
                continue; // Try next model
            }
        }
        
        throw new Exception("All Hugging Face models failed");
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
    
    // Clean and improve AI responses
    private static function cleanAIResponse($response, $originalMessage) {
        // Remove any repeated parts of the original message
        $response = str_ireplace($originalMessage, '', $response);
        
        // Remove common AI artifacts
        $response = preg_replace('/^(Assistant:|L9 Fitness Assistant:|User:|Human:)/i', '', $response);
        $response = preg_replace('/\n(Assistant:|L9 Fitness Assistant:|User:|Human:).*$/i', '', $response);
        
        // Clean up extra whitespace
        $response = trim($response);
        $response = preg_replace('/\s+/', ' ', $response);
        
        // Remove incomplete sentences at the end
        if (strlen($response) > 100 && !preg_match('/[.!?]$/', $response)) {
            $lastSentence = strrpos($response, '.');
            if ($lastSentence !== false) {
                $response = substr($response, 0, $lastSentence + 1);
            }
        }
        
        // Add L9 Fitness branding if response is generic
        if (!stripos($response, 'L9') && !stripos($response, 'fitness') && strlen($response) > 20) {
            $response .= " At L9 Fitness, we're here to support your fitness journey! 💪";
        }
        
        return trim($response);
    }
    
    private static function getSmartFallbackResponse($message, $user_context) {
        $message_lower = strtolower($message);
        
        // Greetings and basic interaction
        if (preg_match('/\b(hi|hello|hey|good morning|good afternoon|good evening|yo|sup|what\'s up|how are you|greetings)\b/i', $message_lower)) {
            return "Welcome to L9 Fitness! 💪 I'm your AI fitness assistant, here to help with all your gym, fitness, and health questions. What can I help you with today?";
        }
        
        // Fitness and workout advice
        if (preg_match('/\b(workout|exercise|fitness|training|routine|program|plan)\b/i', $message_lower)) {
            if (preg_match('/\b(beginner|new|start|first time|never)\b/i', $message_lower)) {
                return "🌟 **Great choice to start your fitness journey!**\n\nFor beginners, I recommend:\n\n1. **Start with 3 days/week** - Give your body time to adapt\n2. **Focus on compound movements** - squats, deadlifts, push-ups, rows\n3. **Begin with bodyweight or light weights** - Perfect your form first\n4. **Include cardio** - 20-30 minutes walking, biking, or elliptical\n\n**At L9 Fitness:** Try our Beginner Basics class or book a session with a personal trainer to get started safely!\n\nWhat specific goals do you have in mind?";
            }
            if (preg_match('/\b(weight loss|lose weight|fat loss|burn fat)\b/i', $message_lower)) {
                return "🔥 **Weight Loss Success Strategy:**\n\n💪 **Exercise (30% of success):**\n• Combine cardio + strength training\n• HIIT classes are excellent fat burners\n• Aim for 4-5 workouts per week\n• Progressive overload with weights\n\n🍎 **Nutrition (70% of success):**\n• Create a caloric deficit (burn more than you eat)\n• Focus on whole foods, lean proteins\n• Stay hydrated (8+ glasses water daily)\n\n**At L9 Fitness:** Our HIIT Training and Cardio Blast classes are perfect for fat burning! Plus our trainers can design a personalized plan.\n\nWhat's your current activity level?";
            }
            if (preg_match('/\b(muscle|build|gain|bulk|strength|strong)\b/i', $message_lower)) {
                return "💪 **Building Muscle & Strength:**\n\n🏋️ **Training Essentials:**\n• Progressive overload - gradually increase weight/reps\n• Compound exercises - squats, deadlifts, bench press\n• 3-4 strength sessions per week\n• 6-12 reps for muscle, 1-5 for strength\n\n🥩 **Nutrition for Gains:**\n• Protein: 0.8-1g per lb bodyweight\n• Slight caloric surplus (eat more than you burn)\n• Post-workout nutrition within 30 minutes\n\n**At L9 Fitness:** Our Strength Training classes and free weight area are perfect! Consider personal training for proper form.\n\nWhat's your experience with weightlifting?";
            }
            return "🏋️ **Fitness Training Tips:**\n\n• **Consistency beats perfection** - Regular workouts trump occasional intense ones\n• **Progressive overload** - Gradually increase difficulty\n• **Recovery matters** - Get 7-9 hours sleep, rest days\n• **Form first** - Perfect technique before adding weight\n\nAt L9 Fitness, we have programs for every goal! What specific type of training interests you most?";
        }
        
        // Nutrition and diet questions
        if (preg_match('/\b(nutrition|diet|food|eat|meal|protein|carbs|calories|supplements)\b/i', $message_lower)) {
            return "🍎 **Nutrition Fundamentals:**\n\n**The Basics:**\n• **Protein:** 0.8-1g per lb bodyweight (muscle building & recovery)\n• **Carbs:** Your body's preferred energy source (focus on complex carbs)\n• **Fats:** 20-30% of calories (hormone production, satiety)\n• **Water:** Half your bodyweight in ounces daily\n\n**General Guidelines:**\n• Eat whole, minimally processed foods\n• Include protein at every meal\n• Time carbs around workouts\n• Don't eliminate entire food groups\n\n⚠️ **Important:** For personalized nutrition plans or medical conditions, consult with a registered dietitian or your healthcare provider.\n\nNeed help with workout nutrition specifically?";
        }
        
        // Motivation and mental health
        if (preg_match('/\b(motivation|motivated|give up|quit|hard|difficult|discouraged|lazy|procrastinate)\b/i', $message_lower)) {
            return "🔥 **Staying Motivated - You've Got This!**\n\n**Mindset Strategies:**\n• **Start small** - 10 minutes is better than 0 minutes\n• **Focus on how you FEEL** after workouts, not just appearance\n• **Track progress** - strength gains, endurance, energy levels\n• **Find your 'why'** - health, confidence, stress relief?\n\n**Practical Tips:**\n• **Schedule workouts** like important appointments\n• **Find a workout buddy** or join group classes\n• **Celebrate small wins** - every workout matters!\n• **Mix it up** - try new classes to stay engaged\n\n**At L9 Fitness:** Our group classes create accountability and our trainers provide motivation!\n\nWhat's your biggest motivation challenge right now?";
        }
        
        // Gym-specific questions  
        if (preg_match('/\b(tell me about|about your gym|about this gym|gym info|what is|facilities|equipment)\b/i', $message_lower)) {
            return "🏋️ **About L9 Fitness - Your Premium Fitness Destination:**\n\nWe're more than just a gym - we're a fitness community dedicated to helping you achieve your goals!\n\n**🎯 What Makes Us Special:**\n• 24/7 member access - work out on YOUR schedule\n• State-of-the-art equipment (free weights, machines, cardio)\n• Expert-led group classes for all fitness levels\n• Certified personal trainers with diverse specializations\n• Clean, modern facilities with full locker rooms\n\n**💪 Our Philosophy:** Whether you're a complete beginner or seasoned athlete, we provide the tools, support, and environment for success.\n\nWhat aspect of L9 Fitness interests you most?";
        }
        
        // Hours and scheduling
        if (preg_match('/\b(hours|open|close|time|schedule|when|availability)\b/i', $message_lower)) {
            return "🕐 **L9 Fitness Hours & Access:**\n\n🔓 **Gym Access:** 24/7 for ALL members!\n👥 **Staff Hours:**\n• Monday-Friday: 6AM-10PM\n• Saturday-Sunday: 8AM-8PM\n\n**Why 24/7 Access?** We know life is busy! Whether you're an early bird, night owl, or have an unpredictable schedule, your fitness shouldn't suffer.\n\n**During Staff Hours:** Personal training, class instruction, member support\n**After Hours:** Full gym access with your member keycard, emergency contacts posted\n\nPerfect for shift workers, busy parents, or anyone who wants flexibility! When do you prefer to work out?";
        }
        
        // Membership and pricing
        if (preg_match('/\b(membership|price|cost|plan|join|sign up|pricing|fee|monthly|yearly)\b/i', $message_lower)) {
            return "💰 **L9 Fitness Membership Plans - Choose Your Level:**\n\n🥉 **Monthly Beast:** $49/month\n• Perfect for testing the waters\n• Month-to-month flexibility\n• All gym access & classes included\n\n🥈 **Quarterly Savage:** $129/3 months *(Save $18!)*\n• Great for building habits\n• Priority class booking\n• Better value for committed members\n\n🥇 **Yearly Champion:** $399/year *(Save $189!)*\n• Best value - less than $1.10/day!\n• Personal training discounts\n• Guest passes included\n• Maximum savings\n\n**ALL PLANS INCLUDE:** 24/7 access, group classes, equipment, locker rooms, WiFi, parking!\n\n💡 **Tip:** Most members find the Yearly Champion plan offers the best motivation AND savings!\n\nWhich plan sounds right for your goals?";
        }
        
        // Classes and group fitness
        if (preg_match('/\b(class|classes|group|schedule|instructor|yoga|hiit|cardio|strength)\b/i', $message_lower)) {
            return "🔥 **L9 Fitness Classes - Find Your Perfect Match:**\n\n⚡ **High-Energy Options:**\n• **HIIT Training** - Maximum calorie burn in minimum time\n• **Cardio Blast** - Heart-pumping, music-driven workouts\n\n💪 **Strength & Power:**\n• **Strength Training** - Build muscle with expert guidance\n• **Functional Training** - Real-world movement patterns\n\n🧘 **Mind-Body Connection:**\n• **Yoga & Flexibility** - All levels, stress relief + mobility\n• **Recovery Sessions** - Stretching, foam rolling, relaxation\n\n**Class Benefits:**\n• Professional instruction & motivation\n• Built-in workout structure\n• Community support & accountability\n• Modifications for all fitness levels\n\n**Scheduling:** Multiple times daily, book through our app or at the front desk!\n\nNew to group fitness? Our instructors are experts at helping newcomers feel comfortable!\n\nWhat type of class sounds most appealing?";
        }
        
        // Personal training
        if (preg_match('/\b(personal trainer|pt|coach|training|trainer|one on one)\b/i', $message_lower)) {
            return "🥊 **Personal Training at L9 Fitness - Accelerate Your Results:**\n\n**👨‍💼 Our Certified Trainers Help You:**\n• Reach goals 3x faster with expert guidance\n• Learn perfect form to prevent injuries\n• Break through plateaus with advanced techniques\n• Stay accountable and motivated\n• Create personalized programs for YOUR body\n\n**💵 Investment Options:**\n• **Single Session:** $75 (perfect for trying it out)\n• **4-Session Pack:** $280 *(Save $20)*\n• **8-Session Pack:** $520 *(Save $80 - Most Popular!)*\n\n**🎯 Perfect For:**\n• Complete beginners wanting proper guidance\n• Experienced athletes seeking optimization\n• Injury recovery and rehabilitation\n• Sport-specific training needs\n• Breaking through fitness plateaus\n\n**Free Consultation:** Meet with a trainer to discuss your goals and see if it's a good fit!\n\nWhat fitness goals are you hoping to achieve?";
        }
        
        // Location and contact
        if (preg_match('/\b(location|address|where|directions|find|contact|phone|email)\b/i', $message_lower)) {
            return "📍 **L9 Fitness Location & Contact:**\n\n🏢 **Address:**\n123 Warrior Street\nFitness City, FC 12345\n\n📞 **Contact Info:**\n• Phone: (555) 123-4567\n• Email: info@l9fitness.com\n\n🚗 **Getting Here:**\n• Free parking lot with plenty of spaces\n• Easy highway access (Exit 15 off Route 95)\n• Public bus route #42 stops nearby\n• Fully wheelchair accessible\n\n🗺️ **Landmarks:**\n• Next to FitMart Shopping Center\n• Across from City Park (great for outdoor runs!)\n• 5 minutes from downtown area\n\n**Need Directions?** Call us or use GPS - we're easy to find! Staff available during business hours to help with anything you need.\n\nPlanning to visit soon?";
        }
        
        // Health and injury questions
        if (preg_match('/\b(injury|hurt|pain|medical|doctor|health|rehab|physical therapy)\b/i', $message_lower)) {
            return "⚠️ **Health & Injury Considerations:**\n\n**Important:** For any pain, injury, or medical concerns, always consult with healthcare professionals first - your doctor, physical therapist, or sports medicine specialist.\n\n**General Fitness & Injury Prevention:**\n• Always warm up before workouts (5-10 minutes)\n• Focus on proper form over heavy weights\n• Include mobility and stretching in your routine\n• Listen to your body - rest when needed\n• Progress gradually, don't rush\n\n**At L9 Fitness:**\n• Our trainers can work with cleared injuries (with doctor approval)\n• We have equipment suitable for rehabilitation exercises\n• Gentle classes like yoga can aid recovery\n\n**Recovery Resources:**\n• Stretching and recovery zone\n• Foam rollers and mobility tools\n• Low-impact cardio options\n\nAlways prioritize your health and safety! Have you been cleared by a healthcare provider for exercise?";
        }
        
        // Goal-specific questions
        if (preg_match('/\b(goal|goals|want to|trying to|hope to|looking to)\b/i', $message_lower)) {
            return "🎯 **Let's Talk About Your Fitness Goals:**\n\n**Common Goals We Help With:**\n• **Weight Loss** - Combining cardio, strength, and nutrition guidance\n• **Muscle Building** - Progressive strength training programs\n• **General Health** - Cardiovascular fitness and functional strength\n• **Sports Performance** - Sport-specific training and conditioning\n• **Stress Relief** - Exercise as mental health support\n• **Social Connection** - Meeting like-minded fitness enthusiasts\n\n**Goal-Setting Tips:**\n• Be specific (lose 20 lbs vs. 'get in shape')\n• Set both short-term and long-term targets\n• Track progress beyond just the scale\n• Celebrate small victories along the way\n\n**At L9 Fitness:** Whether your goal is running your first 5K or competing in bodybuilding, we have the tools and expertise to help you succeed!\n\nWhat specific goal are you working toward?";
        }
        
        // Default response - much more helpful and encouraging
        return "💪 **I'm here to help with all your fitness questions!**\n\n**Popular Topics I Can Help With:**\n🏋️ Workout advice and exercise tips\n🍎 Basic nutrition and healthy eating\n💰 L9 Fitness memberships and pricing\n🔥 Class schedules and descriptions\n🥊 Personal training information\n📍 Gym location, hours, and facilities\n🎯 Goal setting and motivation\n⚠️ General fitness safety (always consult professionals for medical advice)\n\n**Try asking me:**\n• 'How do I start working out as a beginner?'\n• 'What's the best way to lose weight?'\n• 'Tell me about your membership plans'\n• 'I need motivation to keep going'\n• 'What classes do you recommend?'\n\n**Remember:** I'm here to provide general fitness information and help you learn about L9 Fitness. For medical concerns, always consult healthcare professionals!\n\nWhat would you like to know more about? 💪";
    }
}
?>
