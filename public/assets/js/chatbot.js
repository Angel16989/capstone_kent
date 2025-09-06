// L9 Fitness Advanced AI Chatbot - Next Generation with Scroll-Following
class L9Chatbot {
  constructor() {
    this.isOpen = false;
    this.isTyping = false;
    this.conversationHistory = [];
    this.currentUser = null;
    this.contextData = {};
    this.suggestionHistory = [];
    this.userPreferences = {};
    this.aiMode = true; // Use advanced AI API
    this.scrollTimeout = null;
    this.isScrolling = false;
    this.lastScrollTop = 0;
    
    // Initialize scroll tracking
    this.initScrollTracking();
    
    // Enhanced knowledge base for gym-related questions
    this.knowledgeBase = {
      greetings: [
        "Hey there, warrior! 💪 Welcome to L9 Fitness! How can I help you dominate today?",
        "What's up, beast! 🔥 Ready to crush your fitness goals? How can I assist?",
        "Welcome to the pit, champion! ⚡ What can I help you with today?"
      ],
      
      hours: {
        keywords: ['hours', 'open', 'close', 'time', 'schedule'],
        responses: [
          "🕐 L9 Fitness is open 24/7 for our members! The beast never sleeps. Staff hours are Mon-Fri 6AM-10PM, Sat-Sun 8AM-8PM.",
          "⏰ We're always open! 24/7 access for all members. Staff is available Mon-Fri 6AM-10PM, weekends 8AM-8PM."
        ]
      },
      
      membership: {
        keywords: ['membership', 'price', 'cost', 'plan', 'join', 'sign up'],
        responses: [
          "💪 We have 3 beast modes: Monthly Beast ($49), Quarterly Savage ($129), and Yearly Champion ($399). Want me to show you the details?",
          "🏆 Our warrior memberships start at $49/month. Check out our Memberships page for full details and epic features!"
        ]
      },
      
      classes: {
        keywords: ['class', 'classes', 'workout', 'training', 'group'],
        responses: [
          "🔥 We offer brutal classes like Beast Mode HIIT, Warrior Yoga, Savage Strength, and Destroyer Cardio! Check our Classes page to book.",
          "⚡ Our class schedule is packed with intense sessions! From yoga to HIIT to strength training - we've got your destruction covered."
        ]
      },
      
      booking: {
        keywords: ['book', 'reserve', 'schedule', 'appointment'],
        responses: [
          "📅 You can book classes directly from our Classes page! Just login to your account and click 'Book Now' on any class.",
          "🎯 Ready to book? Head to our Classes section, find your perfect workout, and secure your spot in the battle!"
        ]
      },
      
      location: {
        keywords: ['location', 'address', 'where', 'directions'],
        responses: [
          "📍 L9 Fitness Beast Pit is located at 123 Warrior Street, Fitness City. We're the building with all the champions walking in and out! 💪",
          "🗺️ Find us at 123 Warrior Street - you can't miss the intense energy radiating from our building!"
        ]
      },
      
      contact: {
        keywords: ['contact', 'phone', 'email', 'support'],
        responses: [
          "📞 Reach our beast squad at (555) 123-4567 or email warrior@l9fitness.com. We're here to help you dominate!",
          "💌 Contact us: Phone (555) 123-4567 | Email warrior@l9fitness.com | Or use our contact form for any questions!"
        ]
      },
      
      equipment: {
        keywords: ['equipment', 'machine', 'weights', 'cardio', 'facilities'],
        responses: [
          "🏋️ We have premium war machines: free weights, cardio beasts, strength stations, functional training area, and recovery chambers!",
          "⚔️ Our arsenal includes: Olympic lifting platforms, cable machines, dumbbells up to 150lbs, cardio with entertainment, and much more!"
        ]
      },
      
      personal_training: {
        keywords: ['personal trainer', 'pt', 'coach', 'training'],
        responses: [
          "🥊 Our certified beast masters offer personal training! It's included with Yearly Champion membership or available as an add-on.",
          "💯 Get a personal destroyer coach! Available with premium memberships or as a separate package. Ready to level up?"
        ]
      }
    };
    
    this.init();
  }
  
  init() {
    this.createChatbotHTML();
    this.bindEvents();
    this.loadUserInfo();
    this.preventPageReloads(); // Prevent all page reload issues
  }
  
  createChatbotHTML() {
    const chatbotHTML = `
      <div class="chatbot-container">
        <button type="button" class="chatbot-toggle" id="chatbotToggle">
          <div class="ai-pulse"></div>
          <i class="bi bi-robot"></i>
          <span class="ai-badge">AI</span>
        </button>
        
        <div class="chatbot-window" id="chatbotWindow">
          <div class="chatbot-header">
            <div class="ai-status">
              <div class="ai-indicator active"></div>
              <h4>🧠 L9 AI Assistant</h4>
            </div>
            <div class="chatbot-controls">
              <button type="button" class="chatbot-minimize" id="chatbotMinimize">−</button>
              <button type="button" class="chatbot-close" id="chatbotClose">×</button>
            </div>
          </div>
          
          <div class="chatbot-status-bar">
            <span class="ai-mode-indicator">🚀 Advanced AI Mode</span>
            <span class="user-indicator">${this.currentUser ? '👤 ' + this.currentUser.name : '👤 Guest'}</span>
          </div>
          
          <div class="chatbot-messages" id="chatbotMessages">
            <div class="chatbot-message bot">
              <div class="message-avatar bot">�</div>
              <div class="message-content bot">
                <div class="ai-thinking">Initializing advanced AI systems...</div>
                <div class="ai-response" style="display: none;">
                  ${this.getRandomResponse('greetings')}
                  <div class="quick-replies">
                    <button class="quick-reply" data-message="What are your hours?">🕐 Hours</button>
                    <button class="quick-reply" data-message="Show me live class schedules">📅 Live Classes</button>
                    <button class="quick-reply" data-message="Tell me about memberships with current stats">💎 Memberships</button>
                    <button class="quick-reply" data-message="Give me a personalized workout recommendation">🏋️ AI Workout</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="chatbot-suggestions" id="chatbotSuggestions" style="display: none;">
            <!-- Dynamic suggestions will appear here -->
          </div>
          
          <div class="chatbot-input-area">
            <div class="chatbot-input-group">
              <button type="button" class="voice-input-btn" id="voiceInput" title="Voice input">🎤</button>
              <input type="text" class="chatbot-input" id="chatbotInput" placeholder="Ask me anything about L9 Fitness..." maxlength="500">
              <button type="button" class="chatbot-send" id="chatbotSend">
                <i class="bi bi-send-fill"></i>
              </button>
            </div>
            <div class="input-features">
              <span class="feature-indicator">💡 Smart suggestions • 🎯 Personalized • ⚡ Real-time data</span>
            </div>
          </div>
        </div>
      </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    
    // Initialize AI animation
    setTimeout(() => {
      this.initializeAI();
    }, 1000);
  }
  
  bindEvents() {
    // Wait for elements to be available
    setTimeout(() => {
      const toggle = document.getElementById('chatbotToggle');
      const close = document.getElementById('chatbotClose');
      const minimize = document.getElementById('chatbotMinimize');
      const send = document.getElementById('chatbotSend');
      const input = document.getElementById('chatbotInput');
      const voiceInput = document.getElementById('voiceInput');
      
      // Debug: Check if elements exist
      console.log('Chatbot elements found:', {
        toggle: !!toggle,
        close: !!close,
        minimize: !!minimize,
        send: !!send,
        input: !!input,
        voiceInput: !!voiceInput
      });
      
      // Prevent page reload on all chatbot interactions
      if (toggle) {
        toggle.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          console.log('Toggle clicked');
          this.toggleChatbot();
        });
      }
      
      if (close) {
        close.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          this.closeChatbot();
        });
      }
      
      if (minimize) {
        minimize.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          this.minimizeChatbot();
        });
      }
    
    send.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.sendMessage();
    });
    
    input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        e.stopPropagation();
        this.sendMessage();
      }
    });
    
    // Voice input
    if (voiceInput) {
      voiceInput.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.startVoiceInput();
      });
    }
    
    // Smart input suggestions
    input.addEventListener('input', (e) => {
      this.handleSmartInput(e.target.value);
    });
    
    // Quick reply buttons - prevent page reload
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('quick-reply')) {
        e.preventDefault();
        e.stopPropagation();
        const message = e.target.dataset.message;
        this.addUserMessage(message);
        this.processAdvancedMessage(message);
      }
      
      // Suggestion buttons
      if (e.target.classList.contains('suggestion-btn')) {
        e.preventDefault();
        e.stopPropagation();
        const action = e.target.dataset.action;
        this.handleSuggestionAction(action);
      }
    });
    
    // Context menu for advanced features
    document.addEventListener('contextmenu', (e) => {
      if (e.target.closest('.chatbot-window')) {
        e.preventDefault();
        e.stopPropagation();
        this.showContextMenu(e);
      }
    });
    
    // Prevent any chatbot interactions from causing page reload
    document.addEventListener('click', (e) => {
      if (e.target.closest('.chatbot-container')) {
        // Only prevent default for chatbot buttons, not input fields
        if (!e.target.matches('input, textarea')) {
          e.stopPropagation();
        }
      }
    });
    
    // Prevent form submission on chatbot
    document.addEventListener('submit', (e) => {
      if (e.target.closest('.chatbot-container')) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
    });
  }
  
  // Scroll tracking for enhanced visibility
  initScrollTracking() {
    let ticking = false;
    
    const updateChatbotPosition = () => {
      const container = document.querySelector('.chatbot-container');
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      const scrollDirection = scrollTop > this.lastScrollTop ? 'down' : 'up';
      const scrollSpeed = Math.abs(scrollTop - this.lastScrollTop);
      
      if (container) {
        // Enhanced visibility during fast scrolling
        if (scrollSpeed > 5) {
          container.classList.add('super-visible');
          this.isScrolling = true;
          
          // Clear previous timeout
          if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
          }
          
          // Remove super-visible after scrolling stops
          this.scrollTimeout = setTimeout(() => {
            container.classList.remove('super-visible');
            container.classList.remove('scrolling');
            this.isScrolling = false;
          }, 2000);
        }
        
        // Add scrolling class during scroll
        if (this.isScrolling) {
          container.classList.add('scrolling');
        }
        
        // Smart positioning based on scroll position
        if (scrollTop > 100) {
          container.style.setProperty('--scroll-factor', Math.min(scrollTop / 1000, 1));
        }
      }
      
      this.lastScrollTop = scrollTop;
      ticking = false;
    };
    
    const requestTick = () => {
      if (!ticking) {
        requestAnimationFrame(updateChatbotPosition);
        ticking = true;
      }
    };
    
    // Throttled scroll listener
    window.addEventListener('scroll', requestTick, { passive: true });
    
    // Enhanced visibility on page load
    setTimeout(() => {
      const container = document.querySelector('.chatbot-container');
      if (container) {
        container.classList.add('super-visible');
        setTimeout(() => {
          container.classList.remove('super-visible');
        }, 5000);
      }
    }, 1000);
    
    // Intersection observer for better visibility management
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          const container = document.querySelector('.chatbot-container');
          if (entry.isIntersecting && container) {
            container.classList.add('super-visible');
            setTimeout(() => {
              container.classList.remove('super-visible');
            }, 3000);
          }
        });
      }, {
        threshold: 0.1,
        rootMargin: '50px'
      });
      
      // Observe key page elements for contextual visibility
      setTimeout(() => {
        const keyElements = document.querySelectorAll('.hero-section, .classes-section, .memberships-section, .card');
        keyElements.forEach(el => observer.observe(el));
      }, 2000);
    }
  }
  
  loadUserInfo() {
    // Try to get user info from the page
    try {
      if (window.userInfo) {
        this.currentUser = window.userInfo;
      }
    } catch (e) {
      // User not logged in
    }
  }
  
  toggleChatbot() {
    const window = document.getElementById('chatbotWindow');
    const toggle = document.getElementById('chatbotToggle');
    
    if (!this.isOpen) {
      // Opening chatbot
      this.isOpen = true;
      window.classList.add('active');
      toggle.classList.add('active');
      // Keep the original button content with AI badge
      toggle.innerHTML = `
        <div class="ai-pulse"></div>
        <i class="bi bi-x-lg"></i>
        <span class="ai-badge">AI</span>
      `;
      setTimeout(() => {
        const input = document.getElementById('chatbotInput');
        if (input) input.focus();
      }, 300);
    } else {
      // Closing chatbot
      this.closeChatbot();
    }
  }
  
  closeChatbot() {
    this.isOpen = false;
    const window = document.getElementById('chatbotWindow');
    const toggle = document.getElementById('chatbotToggle');
    
    window.classList.remove('active');
    toggle.classList.remove('active');
    // Restore original button content
    toggle.innerHTML = `
      <div class="ai-pulse"></div>
      <i class="bi bi-robot"></i>
      <span class="ai-badge">AI</span>
    `;
  }
  
  sendMessage() {
    const input = document.getElementById('chatbotInput');
    const message = input.value.trim();
    
    if (!message || this.isTyping) return;
    
    this.addUserMessage(message);
    input.value = '';
    this.processAdvancedMessage(message);
  }
  
  addUserMessage(message) {
    const messagesContainer = document.getElementById('chatbotMessages');
    
    const messageHTML = `
      <div class="chatbot-message user">
        <div class="message-content user">${this.escapeHtml(message)}</div>
        <div class="message-avatar user">${this.currentUser ? this.currentUser.name.charAt(0) : '👤'}</div>
      </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
    this.scrollToBottom();
    this.conversationHistory.push({ type: 'user', message: message });
  }
  
  addBotMessage(message, includeQuickReplies = false) {
    const messagesContainer = document.getElementById('chatbotMessages');
    
    let quickRepliesHTML = '';
    if (includeQuickReplies) {
      quickRepliesHTML = `
        <div class="quick-replies">
          <button class="quick-reply" data-message="What other services do you offer?">Other Services</button>
          <button class="quick-reply" data-message="How do I contact you?">Contact Info</button>
          <button class="quick-reply" data-message="Show me membership prices">Pricing</button>
        </div>
      `;
    }
    
    const messageHTML = `
      <div class="chatbot-message bot">
        <div class="message-avatar bot">🤖</div>
        <div class="message-content bot">
          ${message}
          ${quickRepliesHTML}
        </div>
      </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
    this.scrollToBottom();
    this.conversationHistory.push({ type: 'bot', message: message });
  }
  
  showTypingIndicator() {
    const messagesContainer = document.getElementById('chatbotMessages');
    
    const typingHTML = `
      <div class="chatbot-message bot typing-message">
        <div class="message-avatar bot">🤖</div>
        <div class="typing-indicator">
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
          <div class="typing-dot"></div>
        </div>
      </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', typingHTML);
    this.scrollToBottom();
  }
  
  hideTypingIndicator() {
    const typingMessage = document.querySelector('.typing-message');
    if (typingMessage) {
      typingMessage.remove();
    }
  }
  
  async processAdvancedMessage(message) {
    this.isTyping = true;
    this.showAdvancedTypingIndicator();
    
    // Add to conversation history with timestamp
    this.conversationHistory.push({
      type: 'user',
      message: message,
      timestamp: new Date().toISOString(),
      context: this.contextData
    });
    
    try {
      // Use advanced AI API
      const apiResponse = await this.getAdvancedAIResponse(message);
      
      this.hideTypingIndicator();
      
      if (apiResponse && apiResponse.success) {
        this.handleAdvancedResponse(apiResponse);
      } else {
        // Enhanced fallback
        const response = this.getEnhancedLocalResponse(message);
        this.addBotMessage(response.message, true);
        this.showSuggestions(response.suggestions);
      }
    } catch (error) {
      console.log('Advanced AI unavailable, using enhanced local responses');
      
      this.hideTypingIndicator();
      
      const response = this.getEnhancedLocalResponse(message);
      this.addBotMessage(response.message, true);
      this.showSuggestions(response.suggestions);
    }
    
    this.isTyping = false;
  }
  
  async getAdvancedAIResponse(message) {
    try {
      const response = await fetch('advanced_chatbot_api.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          message: message,
          context: this.conversationHistory.slice(-5), // Last 5 messages for context
          user_preferences: this.userPreferences,
          session_data: this.contextData
        })
      });
      
      if (!response.ok) {
        throw new Error('Advanced AI API request failed');
      }
      
      const data = await response.json();
      
      // Update context with response data
      if (data.context_data) {
        this.contextData = { ...this.contextData, ...data.context_data };
      }
      
      return data;
    } catch (error) {
      console.log('Advanced AI error:', error);
      return null;
    }
  }
  
  handleAdvancedResponse(response) {
    // Add main response
    this.addAdvancedBotMessage(response.response, response.ai_confidence);
    
    // Show suggestions if available
    if (response.suggestions && response.suggestions.length > 0) {
      this.showSuggestions(response.suggestions);
    }
    
    // Handle actions
    if (response.actions && response.actions.length > 0) {
      this.handleAutomaticActions(response.actions);
    }
    
    // Update conversation history
    this.conversationHistory.push({
      type: 'bot',
      message: response.response,
      timestamp: new Date().toISOString(),
      confidence: response.ai_confidence,
      suggestions: response.suggestions,
      actions: response.actions
    });
  }
  
  addAdvancedBotMessage(message, confidence = 0.8) {
    const messagesContainer = document.getElementById('chatbotMessages');
    
    const confidenceIndicator = this.getConfidenceIndicator(confidence);
    const timestamp = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    const messageHTML = `
      <div class="chatbot-message bot advanced">
        <div class="message-avatar bot">🧠</div>
        <div class="message-content bot">
          <div class="message-header">
            <span class="ai-badge">AI</span>
            <span class="confidence-indicator">${confidenceIndicator}</span>
            <span class="timestamp">${timestamp}</span>
          </div>
          <div class="message-text">${this.formatAdvancedMessage(message)}</div>
          <div class="message-actions">
            <button class="message-action" onclick="l9Chatbot.copyMessage(this)">📋</button>
            <button class="message-action" onclick="l9Chatbot.shareMessage(this)">📤</button>
            <button class="message-action" onclick="l9Chatbot.rateMessage(this)">⭐</button>
          </div>
        </div>
      </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
    this.scrollToBottom();
    this.animateNewMessage();
  }
  
  showSuggestions(suggestions) {
    const suggestionsContainer = document.getElementById('chatbotSuggestions');
    
    if (!suggestions || suggestions.length === 0) {
      suggestionsContainer.style.display = 'none';
      return;
    }
    
    let suggestionsHTML = '<div class="suggestions-header">💡 Smart Suggestions:</div><div class="suggestions-grid">';
    
    suggestions.forEach((suggestion, index) => {
      suggestionsHTML += `
        <button class="suggestion-btn" data-action="${suggestion.toLowerCase().replace(/\s+/g, '_')}">
          ${this.getSuggestionIcon(suggestion)} ${suggestion}
        </button>
      `;
    });
    
    suggestionsHTML += '</div>';
    suggestionsContainer.innerHTML = suggestionsHTML;
    suggestionsContainer.style.display = 'block';
    
    // Auto-hide suggestions after 30 seconds
    setTimeout(() => {
      suggestionsContainer.style.display = 'none';
    }, 30000);
  }
  
  showAdvancedTypingIndicator() {
    const messagesContainer = document.getElementById('chatbotMessages');
    
    const typingHTML = `
      <div class="chatbot-message bot typing-message advanced">
        <div class="message-avatar bot">🧠</div>
        <div class="ai-thinking-advanced">
          <div class="ai-process">🔍 Analyzing your request...</div>
          <div class="ai-process">📊 Accessing real-time data...</div>
          <div class="ai-process">🧠 Generating intelligent response...</div>
          <div class="neural-network">
            <div class="neural-dot"></div>
            <div class="neural-dot"></div>
            <div class="neural-dot"></div>
            <div class="neural-dot"></div>
          </div>
        </div>
      </div>
    `;
    
    messagesContainer.insertAdjacentHTML('beforeend', typingHTML);
    this.scrollToBottom();
  }
  
  initializeAI() {
    const aiThinking = document.querySelector('.ai-thinking');
    const aiResponse = document.querySelector('.ai-response');
    
    if (aiThinking && aiResponse) {
      setTimeout(() => {
        aiThinking.style.display = 'none';
        aiResponse.style.display = 'block';
        aiResponse.style.animation = 'fadeInUp 0.5s ease';
      }, 2000);
    }
  }
  
  startVoiceInput() {
    if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
      this.addBotMessage("🎤 Voice input not supported in this browser. Try Chrome or Edge!", false);
      return;
    }
    
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'en-US';
    
    const voiceBtn = document.getElementById('voiceInput');
    voiceBtn.innerHTML = '🔴';
    voiceBtn.style.animation = 'pulse 1s infinite';
    
    recognition.onresult = (event) => {
      const transcript = event.results[0][0].transcript;
      document.getElementById('chatbotInput').value = transcript;
      this.sendMessage();
    };
    
    recognition.onerror = (event) => {
      console.error('Speech recognition error:', event.error);
      this.addBotMessage("🎤 Sorry, I couldn't hear that clearly. Try typing instead!", false);
    };
    
    recognition.onend = () => {
      voiceBtn.innerHTML = '🎤';
      voiceBtn.style.animation = 'none';
    };
    
    recognition.start();
  }
  
  handleSmartInput(value) {
    // Smart autocomplete and suggestions as user types
    if (value.length > 2) {
      const suggestions = this.getSmartSuggestions(value);
      // Could implement autocomplete dropdown here
    }
  }
  
  getSmartSuggestions(input) {
    const suggestions = [
      'What are your hours?',
      'Show me class schedules',
      'Tell me about memberships',
      'Book a class',
      'Check gym capacity',
      'Personal training options',
      'Current promotions',
      'Nutrition advice'
    ];
    
    return suggestions.filter(s => 
      s.toLowerCase().includes(input.toLowerCase())
    ).slice(0, 3);
  }
  
  // Helper methods for advanced features
  getConfidenceIndicator(confidence) {
    if (confidence >= 0.9) return '🎯 High';
    if (confidence >= 0.7) return '👍 Good';
    if (confidence >= 0.5) return '🤔 Medium';
    return '❓ Low';
  }
  
  getSuggestionIcon(suggestion) {
    const icons = {
      'book': '📅',
      'class': '🏋️',
      'membership': '💎',
      'contact': '📞',
      'hours': '🕐',
      'workout': '💪',
      'nutrition': '🥗',
      'promotion': '🎉'
    };
    
    for (const [key, icon] of Object.entries(icons)) {
      if (suggestion.toLowerCase().includes(key)) {
        return icon;
      }
    }
    return '💡';
  }
  
  formatAdvancedMessage(message) {
    // Enhanced message formatting with markdown-like support
    return message
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\*(.*?)\*/g, '<em>$1</em>')
      .replace(/\n/g, '<br>')
      .replace(/🔥|💪|⚡|🎯|💎|🏋️/g, '<span class="emoji-large">$&</span>');
  }
  
  copyMessage(btn) {
    const messageText = btn.closest('.message-content').querySelector('.message-text').textContent;
    navigator.clipboard.writeText(messageText).then(() => {
      btn.innerHTML = '✅';
      setTimeout(() => btn.innerHTML = '📋', 2000);
    });
  }
  
  shareMessage(btn) {
    const messageText = btn.closest('.message-content').querySelector('.message-text').textContent;
    if (navigator.share) {
      navigator.share({
        title: 'L9 Fitness AI Response',
        text: messageText
      });
    }
  }
  
  rateMessage(btn) {
    // Implement message rating system
    btn.innerHTML = '⭐';
    setTimeout(() => btn.innerHTML = '✅', 1000);
  }
  
  animateNewMessage() {
    const lastMessage = document.querySelector('.chatbot-message:last-child');
    if (lastMessage) {
      lastMessage.style.animation = 'slideInRight 0.3s ease';
    }
  }
  
  minimizeChatbot() {
    const window = document.getElementById('chatbotWindow');
    window.classList.toggle('minimized');
  }
  
  generateResponse(message) {
    const lowercaseMessage = message.toLowerCase();
    
    // Check for specific patterns first
    if (this.isGreeting(lowercaseMessage)) {
      return this.getRandomResponse('greetings');
    }
    
    // Check knowledge base
    for (const [category, data] of Object.entries(this.knowledgeBase)) {
      if (category === 'greetings') continue;
      
      const isMatch = data.keywords.some(keyword => 
        lowercaseMessage.includes(keyword)
      );
      
      if (isMatch) {
        return this.getRandomResponse(category);
      }
    }
    
    // Handle user-specific queries
    if (this.currentUser && (lowercaseMessage.includes('my') || lowercaseMessage.includes('account'))) {
      return this.handleUserSpecificQuery(lowercaseMessage);
    }
    
    // Default responses for unmatched queries
    const defaultResponses = [
      "🤔 That's a great question! For detailed info, check our website or contact our beast squad at (555) 123-4567.",
      "💪 I'm still learning about that topic! Our staff at warrior@l9fitness.com can give you the complete breakdown.",
      "🔥 Let me connect you with our team for that! They're experts at (555) 123-4567 or visit our contact page.",
      "⚡ That's outside my current knowledge, but our warriors at the front desk have all the answers! Give us a call!"
    ];
    
    return defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
  }
  
  handleUserSpecificQuery(message) {
    if (message.includes('membership') || message.includes('plan')) {
      return `👋 Hey ${this.currentUser.name}! You can check your current membership details on your dashboard. Need to upgrade your beast mode? Visit our Memberships page!`;
    }
    
    if (message.includes('class') || message.includes('booking')) {
      return `🏃‍♂️ ${this.currentUser.name}, you can view and book classes from your dashboard or the Classes page. Ready to crush a workout?`;
    }
    
    return `Hey ${this.currentUser.name}! For account-specific questions, check your dashboard or contact our support team. How else can I help you dominate today? 💪`;
  }
  
  isGreeting(message) {
    const greetings = ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening', 'yo', 'sup', 'what\'s up'];
    return greetings.some(greeting => message.includes(greeting));
  }
  
  getRandomResponse(category) {
    if (category === 'greetings') {
      return this.knowledgeBase.greetings[Math.floor(Math.random() * this.knowledgeBase.greetings.length)];
    }
    
    const responses = this.knowledgeBase[category]?.responses;
    if (responses && responses.length > 0) {
      return responses[Math.floor(Math.random() * responses.length)];
    }
    
    return "🤖 I'm here to help! What would you like to know about L9 Fitness?";
  }
  
  scrollToBottom() {
    const messagesContainer = document.getElementById('chatbotMessages');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }
  
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
  
  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
  
  getEnhancedLocalResponse(message) {
    const response = this.generateResponse(message);
    return {
      message: response,
      suggestions: ['Try again', 'Contact us', 'View classes'],
      actions: ['retry', 'contact']
    };
  }
  
  handleSuggestionAction(action) {
    const actionMap = {
      'view_classes': 'Show me all available classes',
      'view_memberships': 'Tell me about membership options',
      'contact': 'How can I contact you?',
      'book_class': 'I want to book a class'
    };
    
    if (actionMap[action]) {
      this.addUserMessage(actionMap[action]);
      this.processAdvancedMessage(actionMap[action]);
    }
  }
  
  handleAutomaticActions(actions) {
    // Handle automatic actions like opening booking page, etc.
    actions.forEach(action => {
      switch(action) {
        case 'book_class':
          // Could open booking modal or redirect
          break;
        case 'view_classes':
          // Could highlight classes section
          break;
        case 'contact_phone':
          // Could open phone dialer
          break;
      }
    });
  }
  
  showContextMenu(e) {
    // Advanced context menu for power users
    const menu = document.createElement('div');
    menu.className = 'chatbot-context-menu';
    menu.style.position = 'absolute';
    menu.style.left = e.clientX + 'px';
    menu.style.top = e.clientY + 'px';
    menu.innerHTML = `
      <div class="context-item" onclick="l9Chatbot.exportConversation()">Export Chat</div>
      <div class="context-item" onclick="l9Chatbot.clearHistory()">Clear History</div>
      <div class="context-item" onclick="l9Chatbot.toggleAIMode()">Toggle AI Mode</div>
    `;
    document.body.appendChild(menu);
    
    setTimeout(() => menu.remove(), 3000);
  }
  
  exportConversation() {
    const conversation = this.conversationHistory.map(msg => 
      `${msg.type.toUpperCase()}: ${msg.message}`
    ).join('\n');
    
    const blob = new Blob([conversation], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'l9-fitness-chat.txt';
    a.click();
  }
  
  clearHistory() {
    this.conversationHistory = [];
    const messagesContainer = document.getElementById('chatbotMessages');
    messagesContainer.innerHTML = '';
    this.addBotMessage("🔥 Chat history cleared! Ready for a fresh start, warrior! How can I help you dominate today?", false);
  }
  
  toggleAIMode() {
    this.aiMode = !this.aiMode;
    const indicator = document.querySelector('.ai-mode-indicator');
    if (indicator) {
      indicator.textContent = this.aiMode ? '🚀 Advanced AI Mode' : '🤖 Basic Mode';
    }
    this.addBotMessage(`${this.aiMode ? '🧠 Advanced AI activated!' : '🤖 Basic mode enabled'} How can I help you?`, false);
  }
  
  // Comprehensive page reload prevention
  preventPageReloads() {
    // Flag to track chatbot interactions
    this.chatbotInteractionInProgress = false;
    
    // Global chatbot interaction handler
    document.addEventListener('click', (e) => {
      const chatbotElement = e.target.closest('.chatbot-container');
      if (chatbotElement) {
        this.chatbotInteractionInProgress = true;
        // Check if it's a button or interactive element within chatbot
        if (e.target.matches('button, .quick-reply, .suggestion-btn, .message-action') || 
            e.target.closest('button, .quick-reply, .suggestion-btn, .message-action')) {
          e.preventDefault();
          e.stopPropagation();
        }
        // Reset flag after interaction
        setTimeout(() => {
          this.chatbotInteractionInProgress = false;
        }, 100);
      }
    }, true); // Use capture phase
    
    // Prevent any form submission from chatbot
    document.addEventListener('submit', (e) => {
      if (e.target.closest('.chatbot-container')) {
        e.preventDefault();
        e.stopImmediatePropagation();
        console.log('Chatbot form submission prevented');
        return false;
      }
    }, true);
    
    // Initialize scroll tracking for enhanced behavior
    this.initScrollTracking();
    
    // Set current user if available
    try {
      if (window.userInfo) {
        this.currentUser = window.userInfo;
      }
    } catch (e) {
      // User not logged in
    }
    }, 100); // Close the setTimeout from bindEvents
  }
  
  initScrollTracking() {
    let ticking = false;
    
    const updateChatbotPosition = () => {
      const container = document.querySelector('.chatbot-container');
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      const scrollDirection = scrollTop > this.lastScrollTop ? 'down' : 'up';
      const scrollSpeed = Math.abs(scrollTop - this.lastScrollTop);
      
      if (container) {
        // Enhanced visibility during fast scrolling
        if (scrollSpeed > 5) {
          container.classList.add('super-visible');
          this.isScrolling = true;
          
          // Clear previous timeout
          if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
          }
          
          // Remove super-visible after scrolling stops
          this.scrollTimeout = setTimeout(() => {
            container.classList.remove('super-visible');
            container.classList.remove('scrolling');
            this.isScrolling = false;
          }, 2000);
        }
        
        // Add scrolling class during scroll
        if (this.isScrolling) {
          container.classList.add('scrolling');
        }
        
        // Smart positioning based on scroll position
        if (scrollTop > 100) {
          container.style.setProperty('--scroll-factor', Math.min(scrollTop / 1000, 1));
        }
      }
      
      this.lastScrollTop = scrollTop;
      ticking = false;
    };
    
    const requestTick = () => {
      if (!ticking) {
        requestAnimationFrame(updateChatbotPosition);
        ticking = true;
      }
    };
    
    // Throttled scroll listener
    window.addEventListener('scroll', requestTick, { passive: true });
    
    // Enhanced visibility on page load
    setTimeout(() => {
      const container = document.querySelector('.chatbot-container');
      if (container) {
        container.classList.add('super-visible');
        setTimeout(() => {
          container.classList.remove('super-visible');
        }, 5000);
      }
    }, 1000);
    
    // Initialize scroll tracking variables
    this.lastScrollTop = 0;
    this.isScrolling = false;
    this.scrollTimeout = null;
  }
}

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  // Add user info to window if available (for personalization)
  try {
    if (typeof userInfo !== 'undefined') {
      window.userInfo = userInfo;
    }
  } catch (e) {
    // User not logged in
  }
  
  // Initialize chatbot
  window.l9Chatbot = new L9Chatbot();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = L9Chatbot;
}
