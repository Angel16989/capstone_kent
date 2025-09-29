<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
$pageTitle = "WAKI - AI Beast Assistant";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | L9 Fitness</title>
    
    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        :root {
            --l9-primary: #FF4444;
            --l9-accent: #FFD700;
            --l9-secondary: #00CCFF;
            --l9-dark: #0a0a0a;
            --l9-darker: #050505;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: 
                radial-gradient(circle at 20% 10%, rgba(255,68,68,.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 90%, rgba(255,215,0,.1) 0%, transparent 50%),
                linear-gradient(135deg, var(--l9-darker) 0%, #111111 25%, #0a0a0a 50%, #050505 100%);
            color: white;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* WAKI Navigation */
        .waki-nav {
            background: rgba(5,5,5,0.98);
            backdrop-filter: blur(20px);
            border-bottom: 2px solid rgba(255, 68, 68, 0.3);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }

        .waki-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--l9-primary), var(--l9-accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            animation: wakiPulse 2s ease-in-out infinite;
        }

        .nav-brand-text h1 {
            background: linear-gradient(135deg, var(--l9-primary), var(--l9-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 900;
            margin: 0;
        }

        .nav-brand-text p {
            color: rgba(255, 255, 255, 0.7);
            margin: 0;
            font-size: 0.9rem;
        }

        .back-btn {
            background: linear-gradient(135deg, rgba(255, 68, 68, 0.2), rgba(255, 215, 0, 0.2));
            border: 2px solid rgba(255, 68, 68, 0.3);
            border-radius: 25px;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: linear-gradient(135deg, rgba(255, 68, 68, 0.4), rgba(255, 215, 0, 0.4));
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 8px 25px rgba(255, 68, 68, 0.3);
        }

        /* WAKI Interface Container */
        .waki-container {
            margin-top: 120px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 120px);
        }

        .waki-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .waki-title {
            font-size: 3.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--l9-primary), var(--l9-accent), var(--l9-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            animation: titleGlow 3s ease-in-out infinite;
        }

        .waki-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
        }

        .waki-status {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(0, 255, 0, 0.1);
            border: 1px solid rgba(0, 255, 0, 0.3);
            border-radius: 25px;
            padding: 8px 20px;
            font-size: 0.9rem;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            background: #00ff00;
            border-radius: 50%;
            animation: statusPulse 1.5s ease-in-out infinite;
        }

        /* Chat Interface */
        .waki-chat-interface {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .waki-sidebar {
            background: rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(255, 68, 68, 0.2);
            border-radius: 20px;
            padding: 1.5rem;
            backdrop-filter: blur(15px);
        }

        .sidebar-title {
            color: var(--l9-accent);
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .quick-btn {
            background: linear-gradient(135deg, rgba(255, 68, 68, 0.1), rgba(255, 215, 0, 0.1));
            border: 1px solid rgba(255, 68, 68, 0.3);
            border-radius: 12px;
            color: white;
            padding: 12px 15px;
            text-align: left;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .quick-btn:hover {
            background: linear-gradient(135deg, rgba(255, 68, 68, 0.2), rgba(255, 215, 0, 0.2));
            transform: translateX(5px);
            border-color: var(--l9-primary);
        }

        /* Main Chat Area */
        .waki-main-chat {
            background: rgba(0, 0, 0, 0.6);
            border: 2px solid rgba(255, 68, 68, 0.3);
            border-radius: 25px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            backdrop-filter: blur(20px);
        }

        .chat-header {
            background: linear-gradient(135deg, var(--l9-primary), var(--l9-accent));
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-avatar {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .chat-info h3 {
            margin: 0;
            font-weight: 700;
        }

        .chat-info p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .chat-messages {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            max-height: 400px;
        }

        .message {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .message.user {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .message.waki .message-avatar {
            background: linear-gradient(135deg, var(--l9-primary), var(--l9-accent));
        }

        .message.user .message-avatar {
            background: linear-gradient(135deg, var(--l9-secondary), #8A2BE2);
        }

        .message-content {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            padding: 12px 18px;
            max-width: 80%;
            backdrop-filter: blur(10px);
        }

        .message.waki .message-content {
            border: 1px solid rgba(255, 68, 68, 0.3);
        }

        .message.user .message-content {
            border: 1px solid rgba(0, 204, 255, 0.3);
        }

        .message-time {
            font-size: 0.75rem;
            opacity: 0.6;
            margin-top: 5px;
        }

        .message-source {
            font-size: 0.7rem;
            opacity: 0.5;
            font-style: italic;
        }

        .thinking-dots {
            animation: thinkingPulse 1.5s ease-in-out infinite;
        }

        .thinking-dots span {
            animation: thinkingDot 1.5s ease-in-out infinite;
        }

        .thinking-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .thinking-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        .message.thinking .message-content {
            background: rgba(255, 68, 68, 0.1);
            border: 1px dashed rgba(255, 68, 68, 0.4);
        }

        @keyframes thinkingPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        @keyframes thinkingDot {
            0%, 20%, 80%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        /* Chat Input */
        .chat-input-area {
            background: rgba(0, 0, 0, 0.4);
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 68, 68, 0.2);
        }

        .input-container {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .chat-input {
            flex: 1;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 68, 68, 0.3);
            border-radius: 25px;
            padding: 15px 20px;
            color: white;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .chat-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .chat-input:focus {
            border-color: var(--l9-primary);
            box-shadow: 0 0 20px rgba(255, 68, 68, 0.3);
            background: rgba(255, 255, 255, 0.15);
        }

        .send-btn {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--l9-primary), var(--l9-accent));
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .send-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(255, 68, 68, 0.4);
        }

        /* Stats Panel */
        .waki-stats {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .stat-card {
            background: rgba(0, 0, 0, 0.4);
            border: 2px solid rgba(255, 215, 0, 0.2);
            border-radius: 15px;
            padding: 1rem;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--l9-accent), var(--l9-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-top: 5px;
        }

        /* Animations */
        @keyframes wakiPulse {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 0 0 20px rgba(255, 68, 68, 0.3);
            }
            50% { 
                transform: scale(1.05);
                box-shadow: 0 0 30px rgba(255, 68, 68, 0.5);
            }
        }

        @keyframes titleGlow {
            0%, 100% { text-shadow: 0 0 20px rgba(255, 68, 68, 0.3); }
            50% { text-shadow: 0 0 30px rgba(255, 215, 0, 0.5); }
        }

        @keyframes statusPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Mobile Responsive */
        @media (max-width: 992px) {
            .waki-chat-interface {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto 1fr;
            }
            
            .waki-title {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .waki-container {
                padding: 1rem;
            }
            
            .waki-title {
                font-size: 2rem;
            }
            
            .input-container {
                flex-direction: column;
                gap: 10px;
            }
            
            .chat-input {
                width: 100%;
            }
        }

        /* Scrollbar Styling */
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--l9-primary), var(--l9-accent));
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- WAKI Navigation -->
    <nav class="waki-nav">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <a href="#" class="nav-brand">
                        <div class="waki-logo">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="nav-brand-text">
                            <h1>WAKI</h1>
                            <p>Your AI Beast Assistant</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i>
                        Back to L9 Fitness
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- WAKI Main Interface -->
    <div class="waki-container">
        <!-- Header -->
        <div class="waki-header">
            <h1 class="waki-title">WAKI AI BEAST</h1>
            <p class="waki-subtitle">Your Ultimate Fitness AI Assistant</p>
            <div class="waki-status">
                <div class="status-dot"></div>
                <span>Online & Ready to Help</span>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="waki-chat-interface">
            <!-- Left Sidebar -->
            <div class="waki-sidebar">
                <h5 class="sidebar-title">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h5>
                <div class="quick-actions">
                    <button class="quick-btn" onclick="sendQuickMessage('Tell me about L9 Fitness')">
                        <i class="fas fa-info-circle"></i> About L9 Fitness
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Show me workout plans')">
                        <i class="fas fa-dumbbell"></i> Workout Plans
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('What classes are available?')">
                        <i class="fas fa-calendar"></i> Classes Schedule
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Membership options')">
                        <i class="fas fa-crown"></i> Membership Info
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Nutrition advice')">
                        <i class="fas fa-apple-alt"></i> Nutrition Tips
                    </button>
                    <button class="quick-btn" onclick="sendQuickMessage('Contact information')">
                        <i class="fas fa-phone"></i> Contact Info
                    </button>
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="waki-main-chat">
                <div class="chat-header">
                    <div class="chat-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="chat-info">
                        <h3>WAKI Assistant</h3>
                        <p>AI-Powered Beast Mode Activated</p>
                    </div>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <div class="message waki">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-content">
                            <p><strong>💀 WAKI:</strong> Well well well... look who FINALLY decided to show up! �</p>
                            <p>I'm WAKI - your brutally honest, savagely motivational AI trainer who's about to turn your beautiful hot mess into an UNSTOPPABLE LEGEND! 💪🔥</p>
                            <p>Ready for some REAL TALK mixed with INSANE motivation? Your excuses just got their eviction notice! What's your damage today, future champion? 🚀💥</p>
                            <div class="message-time"><?php echo date('H:i'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="chat-input-area">
                    <div class="input-container">
                        <input type="text" class="chat-input" id="messageInput" placeholder="Ask WAKI anything about fitness, L9 Gym, or get motivated..." maxlength="500">
                        <button class="send-btn" id="sendBtn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Stats Panel -->
            <div class="waki-sidebar">
                <h5 class="sidebar-title">
                    <i class="fas fa-chart-line"></i>
                    WAKI Stats
                </h5>
                <div class="waki-stats">
                    <div class="stat-card">
                        <div class="stat-number" id="totalChats">247</div>
                        <div class="stat-label">Total Chats</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="questionsAnswered">1,523</div>
                        <div class="stat-label">Questions Answered</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="membersHelped">89</div>
                        <div class="stat-label">Members Helped</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">99.2%</div>
                        <div class="stat-label">Satisfaction Rate</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Availability</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // WAKI Chat Functionality
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const chatMessages = document.getElementById('chatMessages');

        // Predefined responses for WAKI
        const wakiResponses = {
            'about l9 fitness': "🔥 L9 Fitness is the ultimate beast mode gym! We're all about pushing limits, hardcore training, and unleashing your inner beast! With 24/7 access, state-of-the-art equipment, and a community of warriors, we're here to transform you into a fitness legend! 💪",
            
            'workout plans': "💪 Beast mode workout plans coming right up! We have:\n\n🔥 Beast Strength Program - Heavy lifting for maximum gains\n⚡ HIIT Fury - High-intensity interval training\n🏃 Cardio Crusher - Endurance and fat burning\n🤸 Functional Beast - Real-world strength\n🧘 Flexibility Flow - Recovery and mobility\n\nWhich one calls to your inner beast?",
            
            'classes': "📅 Our beast classes schedule is INSANE! Check out:\n\n🔥 Monday: Beast Mode Bootcamp (6AM, 7PM)\n💪 Tuesday: Iron Fury Strength (6:30AM, 6PM)\n⚡ Wednesday: HIIT Thunder (7AM, 8PM)\n🥊 Thursday: Combat Beast (6AM, 7:30PM)\n🏃 Friday: Cardio Chaos (6:30AM, 6PM)\n🤸 Saturday: Functional Beast (9AM, 11AM)\n🧘 Sunday: Recovery Flow (10AM, 5PM)\n\nAll classes are included with membership!",
            
            'membership': "👑 L9 Fitness Membership Options:\n\n🥉 Beast Starter: $29/month - Gym access + 2 classes\n🥈 Beast Warrior: $49/month - Unlimited access + all classes\n🥇 Beast Legend: $79/month - Everything + personal training sessions\n💎 Beast Ultimate: $129/month - VIP access + nutrition coaching\n\nAll memberships include 24/7 gym access and WAKI support!",
            
            'nutrition': "🍎 Beast Mode Nutrition Tips from WAKI:\n\n💪 Protein: 1g per lb bodyweight for muscle growth\n🔥 Hydration: Drink water like a beast - half your bodyweight in oz\n⚡ Pre-workout: Carbs + caffeine 30min before training\n🥩 Post-workout: Protein + carbs within 30min\n🥗 Veggies: Fill half your plate with colorful vegetables\n😴 Sleep: 7-9 hours for optimal recovery\n\nRemember: You can't out-train a bad diet, beast!",
            
            'contact': "📞 Get in touch with the L9 Beast team:\n\n🏢 Address: 123 Beast Mode Blvd, Fitness City\n📱 Phone: +1 (855) L9-BEAST\n📧 Email: beast@l9fitness.com\n🌐 Website: www.l9fitness.com\n\n💪 Gym Hours: 24/7 (Because beasts never sleep!)\n🕐 Staff Hours: Mon-Fri 6AM-10PM, Sat-Sun 8AM-8PM\n\nNeed immediate help? I'm here 24/7!",
            
            'motivation': "🔥 BEAST MODE MOTIVATION INCOMING! 💪\n\nYou didn't come this far to only come this far! Every rep, every set, every drop of sweat is building the LEGEND you're becoming!\n\n💀 Pain is temporary, but GLORY is FOREVER!\n⚡ Your only competition is who you were yesterday!\n🏆 Champions are made when nobody is watching!\n🔥 The beast inside you is hungry - FEED IT!\n\nNow DROP AND GIVE ME 20! Let's GO! 🚀",
            
            'default': "💪 That's a great question, beast! I'm here to help with anything L9 Fitness related - workouts, nutrition, classes, memberships, motivation, or just general fitness advice! What specific area would you like to dominate today? 🔥"
        };

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            // Disable input while processing
            messageInput.disabled = true;
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            // Add user message
            addMessage(message, 'user');
            messageInput.value = '';

            // Add thinking message
            const thinkingId = addThinkingMessage();

            try {
                // Call OpenAI API through our backend
                const response = await fetch('waki-api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();
                
                // Remove thinking message
                removeThinkingMessage(thinkingId);

                if (data.success) {
                    addMessage(data.response, 'waki', data.source);
                    
                    // Log debug info if available
                    if (data.debug) {
                        console.log('WAKI Debug:', data.debug);
                    }
                } else {
                    // Fallback response
                    const fallbackResponse = generateFallbackResponse(message);
                    addMessage(fallbackResponse, 'waki', 'fallback');
                    console.error('WAKI Error:', data.error || 'Unknown error');
                }
                
                updateStats();
            } catch (error) {
                console.error('WAKI API Error:', error);
                
                // Remove thinking message
                removeThinkingMessage(thinkingId);
                
                // Use fallback response
                const fallbackResponse = generateFallbackResponse(message);
                addMessage(fallbackResponse, 'waki', 'fallback');
                updateStats();
            }

            // Re-enable input
            messageInput.disabled = false;
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            messageInput.focus();
        }

        function sendQuickMessage(message) {
            messageInput.value = message;
            sendMessage();
        }

        function addMessage(content, sender, source = 'local') {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const currentTime = new Date().toLocaleTimeString('en-US', { 
                hour12: false, 
                hour: '2-digit', 
                minute: '2-digit' 
            });

            // Add source indicator for WAKI messages
            const sourceIcon = source === 'openai' ? '🧠' : source === 'fallback' ? '⚡' : '🤖';
            const sourceTitle = source === 'openai' ? 'AI-Powered Response' : source === 'fallback' ? 'Quick Response' : 'Local Response';

            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-${sender === 'waki' ? 'robot' : 'user'}"></i>
                </div>
                <div class="message-content">
                    <p><strong>${sender === 'waki' ? `${sourceIcon} WAKI:` : '💪 You:'}</strong> ${content.replace(/\n/g, '<br>')}</p>
                    <div class="message-time">
                        ${currentTime}
                        ${sender === 'waki' ? `<span class="message-source" title="${sourceTitle}"> • ${source}</span>` : ''}
                    </div>
                </div>
            `;

            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function addThinkingMessage() {
            const thinkingDiv = document.createElement('div');
            const thinkingId = 'thinking-' + Date.now();
            thinkingDiv.id = thinkingId;
            thinkingDiv.className = 'message waki thinking';
            
            thinkingDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-robot fa-pulse"></i>
                </div>
                <div class="message-content">
                    <p><strong>🧠 WAKI:</strong> <span class="thinking-dots">Thinking like a beast<span>.</span><span>.</span><span>.</span></span></p>
                </div>
            `;

            chatMessages.appendChild(thinkingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return thinkingId;
        }

        function removeThinkingMessage(thinkingId) {
            const thinkingMsg = document.getElementById(thinkingId);
            if (thinkingMsg) {
                thinkingMsg.remove();
            }
        }

        function generateFallbackResponse(message) {
            const lowerMessage = message.toLowerCase();
            
            // Enhanced fallback responses when API is unavailable
            if (lowerMessage.includes('workout') || lowerMessage.includes('training') || lowerMessage.includes('exercise')) {
                return "💪 BEAST MODE WORKOUT INCOMING! Here's what I recommend:\n\n🔥 Compound movements: Squat, Deadlift, Bench Press\n⚡ Progressive overload: Add weight weekly\n🏃 Mix strength + cardio for maximum gains\n😴 Rest 48-72hrs between muscle groups\n\nAPI temporarily offline, but the gains never stop! Keep crushing it, LEGEND! 🚀";
            }

            if (lowerMessage.includes('nutrition') || lowerMessage.includes('diet') || lowerMessage.includes('food')) {
                return "🍎 NUTRITION BEAST PROTOCOL!\n\n💪 Protein: 1g per lb bodyweight\n🔥 Hydration: Half your weight in oz daily\n⚡ Pre-workout fuel: Carbs + caffeine\n🥩 Post-workout: Protein within 30min\n🥗 Fill half your plate with veggies\n\nFuel like a CHAMPION! API offline but nutrition wisdom is eternal! �";
            }

            if (lowerMessage.includes('class') || lowerMessage.includes('schedule')) {
                return "� L9 FITNESS BEAST CLASSES:\n\n🔥 Monday: Beast Bootcamp\n💪 Tuesday: Iron Fury\n⚡ Wednesday: HIIT Thunder\n🥊 Thursday: Combat Beast\n� Friday: Cardio Chaos\n\nAll classes included with membership! API offline but the grind never stops! 🚀";
            }

            if (lowerMessage.includes('membership') || lowerMessage.includes('price')) {
                return "� L9 FITNESS MEMBERSHIPS:\n\n🥉 Beast Starter: $29/month\n🥈 Beast Warrior: $49/month\n🥇 Beast Legend: $79/month\n💎 Beast Ultimate: $129/month\n\nAll include 24/7 gym access! API offline but your beast journey awaits! 💪";
            }

            if (lowerMessage.includes('motivat') || lowerMessage.includes('inspire')) {
                return "🔥 BEAST MODE MOTIVATION!\n\nYou didn't come this far to only come this far! Every rep builds the LEGEND you're becoming!\n\n💀 Pain is temporary, GLORY is FOREVER!\n⚡ Your only competition is yesterday's you!\n🏆 Champions train when no one's watching!\n\nEven without AI, you're still a BEAST! GO CRUSH IT! 💪�";
            }

            // Default fallback
            return "🤖 Hey BEAST! I'm WAKI, running in offline mode right now, but I'm still here to help! Ask me about workouts, nutrition, L9 Fitness classes, memberships, or motivation. What do you want to DOMINATE today? 💪�";
        }

        function updateStats() {
            // Increment stats
            const totalChats = document.getElementById('totalChats');
            const questionsAnswered = document.getElementById('questionsAnswered');
            const membersHelped = document.getElementById('membersHelped');
            
            totalChats.textContent = parseInt(totalChats.textContent) + 1;
            questionsAnswered.textContent = parseInt(questionsAnswered.textContent) + 1;
            
            if (Math.random() > 0.7) { // 30% chance to increment members helped
                membersHelped.textContent = parseInt(membersHelped.textContent) + 1;
            }
        }

        // Event listeners
        sendBtn.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Auto-focus input on load
        window.addEventListener('load', function() {
            messageInput.focus();
        });

        console.log('🤖 WAKI AI Beast Assistant is ONLINE! 💪🔥');
    </script>
</body>
</html>