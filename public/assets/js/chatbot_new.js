// === L9 FITNESS QUANTUM AI CHATBOT - MOST ADVANCED EVER === //

class QuantumChatbot {
  constructor() {
    // Core Properties
    this.isOpen = false;
    this.isTyping = false;
    this.conversationHistory = [];
    this.currentUser = null;
    this.contextData = {};
    this.userPreferences = {};
    
    // Advanced AI Properties
    this.aiMode = true;
    this.intelligenceLevel = 'quantum';
    this.responseCache = new Map();
    this.learningData = new Map();
    this.personalityMatrix = 'fitness-beast';
    
    // Performance Properties
    this.scrollTimeout = null;
    this.isScrolling = false;
    this.lastScrollTop = 0;
    this.renderOptimization = true;
    this.cacheTimeout = 300000; // 5 minutes
    
    // Quantum Knowledge Base
    this.quantumKnowledge = {
      greetings: [
        "🔥 BEAST MODE ACTIVATED! Welcome to L9 Fitness, warrior! How can I help you dominate today?",
        "⚡ What's up, fitness legend! Ready to crush some goals? I'm your AI training partner!",
        "💪 QUANTUM POWER ENGAGED! Welcome to the pit, champion! Let's get you shredded!"
      ],
      
      hours: {
        keywords: ['hours', 'open', 'close', 'time', 'schedule', 'when'],
        responses: [
          "🕐 L9 Fitness operates in BEAST MODE 24/7! We never sleep, just like your gains! Staff hours: Mon-Fri 6AM-10PM, Weekends 8AM-8PM. Time to get after it!",
          "⏰ Round-the-clock access for our warriors! 24/7 gym access means NO EXCUSES! Staff support during peak hours for maximum gains!"
        ]
      },
      
      memberships: {
        keywords: ['membership', 'price', 'cost', 'plan', 'join', 'sign up', 'fees'],
        responses: [
          "💎 LEGENDARY MEMBERSHIPS AVAILABLE! Beast Mode Monthly ($49), Savage Quarterly ($129), Champion Yearly ($399). Each tier unlocks serious firepower!",
          "🏆 Transform into a FITNESS LEGEND! Our memberships include AI coaching, personalized workouts, and 24/7 access. Ready to level up?"
        ]
      },
      
      classes: {
        keywords: ['class', 'classes', 'workout', 'training', 'group', 'session'],
        responses: [
          "🔥 EXPLOSIVE CLASS LINEUP! Beast HIIT, Warrior Yoga, Savage Strength, Destroyer Cardio, and more! Each session designed to forge champions!",
          "⚡ Our classes are LEGENDARY! From zen yoga to brutal HIIT - we've got every training style to unlock your potential!"
        ]
      },
      
      equipment: {
        keywords: ['equipment', 'machines', 'weights', 'gym', 'facilities'],
        responses: [
          "🏋️ STATE-OF-THE-ART ARSENAL! Premium equipment, Olympic lifting stations, cardio theater, functional training zones - everything a warrior needs!",
          "💪 BEAST-LEVEL EQUIPMENT! Latest tech, premium weights, and cutting-edge machines. Your gains will be LEGENDARY!"
        ]
      }
    };
    
    // Initialize quantum systems
    this.initializeQuantumSystems();
  }

  initializeQuantumSystems() {
    // Advanced AI initialization
    this.setupAdvancedAI();
    this.initializeScrollTracking();
    this.loadUserContext();
    this.optimizePerformance();
    
    // Create the quantum interface
    this.createQuantumInterface();
    this.bindQuantumEvents();
    
    console.log('🚀 Quantum Chatbot Systems Online - BEAST MODE ACTIVATED!');
  }

  setupAdvancedAI() {
    // AI personality configuration
    this.aiPersonality = {
      tone: 'energetic',
      enthusiasm: 'maximum',
      knowledge: 'expert',
      supportive: true,
      motivational: true
    };
    
    // Cache management
    setInterval(() => {
      this.cleanCache();
    }, this.cacheTimeout);
    
    // Learning system
    this.initializeLearning();
  }

  initializeLearning() {
    // Load user interaction patterns
    const savedData = localStorage.getItem('quantumChatbotLearning');
    if (savedData) {
      try {
        const learning = JSON.parse(savedData);
        this.learningData = new Map(learning);
      } catch (e) {
        console.log('Learning data reset');
      }
    }
  }

  createQuantumInterface() {
    const chatbotHTML = `
      <div class="chatbot-container" id="quantumChatbot">
        <!-- QUANTUM TOGGLE BUTTON -->
        <button type="button" class="chatbot-toggle" id="chatbotToggle">
          <span class="ai-badge">AI</span>
          <span class="ai-pulse"></span>
          🤖
        </button>
        
        <!-- QUANTUM CHATBOT WINDOW -->
        <div class="chatbot-window" id="chatbotWindow" style="display: none;">
          <div class="chatbot-header">
            <div class="chatbot-title">
              <span class="quantum-icon">⚡</span>
              <span class="title-text">L9 AI Beast</span>
              <span class="status-indicator online">ONLINE</span>
            </div>
            <div class="chatbot-controls">
              <button type="button" class="chatbot-control minimize" id="chatbotMinimize">−</button>
              <button type="button" class="chatbot-control close" id="chatbotClose">×</button>
            </div>
          </div>
          
          <div class="chatbot-body">
            <div class="chatbot-messages" id="chatbotMessages">
              <div class="chatbot-message bot">
                <div class="message-avatar bot">🤖</div>
                <div class="message-content bot">
                  <div class="quantum-thinking">Quantum systems initializing...</div>
                  <div class="quantum-response" style="display: none;">
                    ${this.getRandomResponse('greetings')}
                    <div class="quantum-actions">
                      <button class="quantum-action" data-message="What are your hours?">🕐 Hours</button>
                      <button class="quantum-action" data-message="Show me memberships">💎 Memberships</button>
                      <button class="quantum-action" data-message="Tell me about classes">🔥 Classes</button>
                      <button class="quantum-action" data-message="Give me a workout plan">💪 AI Workout</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="quantum-suggestions" id="quantumSuggestions" style="display: none;">
              <!-- Dynamic suggestions appear here -->
            </div>
          </div>
          
          <div class="chatbot-footer">
            <div class="chatbot-input-area">
              <div class="input-group">
                <button type="button" class="voice-input" id="voiceInput" title="Voice input">🎤</button>
                <input type="text" class="chatbot-input" id="chatbotInput" placeholder="Ask your AI fitness coach anything..." maxlength="500">
                <button type="button" class="send-button" id="chatbotSend">
                  <span class="send-icon">🚀</span>
                </button>
              </div>
              <div class="quantum-features">
                <span class="feature-badge">🧠 AI Brain</span>
                <span class="feature-badge">⚡ Instant</span>
                <span class="feature-badge">🎯 Personal</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    
    // Initialize quantum effects
    setTimeout(() => {
      this.activateQuantumEffects();
    }, 1000);
  }

  bindQuantumEvents() {
    // Get elements with error checking
    const toggle = document.getElementById('chatbotToggle');
    const close = document.getElementById('chatbotClose');
    const minimize = document.getElementById('chatbotMinimize');
    const send = document.getElementById('chatbotSend');
    const input = document.getElementById('chatbotInput');
    const voiceInput = document.getElementById('voiceInput');
    
    // Bind events with null checks
    if (toggle) {
      toggle.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.toggleQuantumChatbot();
      });
    }
    
    if (close) {
      close.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.closeQuantumChatbot();
      });
    }
    
    if (minimize) {
      minimize.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.minimizeQuantumChatbot();
      });
    }
    
    if (send) {
      send.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.sendQuantumMessage();
      });
    }
    
    if (input) {
      input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          e.stopPropagation();
          this.sendQuantumMessage();
        }
      });
      
      // Smart suggestions
      input.addEventListener('input', (e) => {
        this.handleSmartInput(e.target.value);
      });
    }
    
    if (voiceInput) {
      voiceInput.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.startVoiceInput();
      });
    }
    
    // Quantum action buttons
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('quantum-action')) {
        e.preventDefault();
        e.stopPropagation();
        const message = e.target.getAttribute('data-message');
        if (message) {
          this.handleQuantumAction(message);
        }
      }
    });
    
    console.log('🔗 Quantum Events Bound Successfully!');
  }

  initializeScrollTracking() {
    let ticking = false;
    
    const updateQuantumPosition = () => {
      const container = document.querySelector('.chatbot-container');
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      const scrollSpeed = Math.abs(scrollTop - this.lastScrollTop);
      
      if (container) {
        // Enhanced visibility during scrolling
        if (scrollSpeed > 5) {
          container.classList.add('super-visible');
          this.isScrolling = true;
          
          if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
          }
          
          this.scrollTimeout = setTimeout(() => {
            container.classList.remove('super-visible');
            container.classList.remove('scrolling');
            this.isScrolling = false;
          }, 2000);
        }
        
        if (this.isScrolling) {
          container.classList.add('scrolling');
        }
        
        // Smart positioning
        if (scrollTop > 100) {
          container.style.setProperty('--scroll-factor', Math.min(scrollTop / 1000, 1));
        }
      }
      
      this.lastScrollTop = scrollTop;
      ticking = false;
    };
    
    const requestTick = () => {
      if (!ticking) {
        requestAnimationFrame(updateQuantumPosition);
        ticking = true;
      }
    };
    
    window.addEventListener('scroll', requestTick, { passive: true });
    
    // Initial visibility boost
    setTimeout(() => {
      const container = document.querySelector('.chatbot-container');
      if (container) {
        container.classList.add('super-visible');
        setTimeout(() => {
          container.classList.remove('super-visible');
        }, 5000);
      }
    }, 1000);
  }

  loadUserContext() {
    try {
      if (window.userInfo) {
        this.currentUser = window.userInfo;
        console.log('👤 User context loaded:', this.currentUser.name);
      }
    } catch (e) {
      console.log('👤 Guest user - personalizing experience');
    }
  }

  optimizePerformance() {
    // Debounce rapid interactions
    this.debounceMap = new Map();
    
    // Preload common responses
    this.preloadResponses();
    
    // Setup performance monitoring
    this.performanceMetrics = {
      responseTime: [],
      cacheHits: 0,
      totalRequests: 0
    };
  }

  preloadResponses() {
    // Cache common responses for instant delivery
    const commonQueries = ['hours', 'membership', 'classes', 'equipment'];
    commonQueries.forEach(query => {
      this.responseCache.set(query, this.generateResponse(query));
    });
  }

  cleanCache() {
    // Clean old cache entries
    const now = Date.now();
    for (const [key, value] of this.responseCache.entries()) {
      if (value.timestamp && (now - value.timestamp) > this.cacheTimeout) {
        this.responseCache.delete(key);
      }
    }
  }

  activateQuantumEffects() {
    const thinking = document.querySelector('.quantum-thinking');
    const response = document.querySelector('.quantum-response');
    
    if (thinking && response) {
      // Simulate quantum processing
      setTimeout(() => {
        thinking.style.display = 'none';
        response.style.display = 'block';
        
        // Add entrance animation
        response.style.animation = 'quantumFadeIn 0.5s ease-out';
      }, 2000);
    }
  }

  toggleQuantumChatbot() {
    const window = document.getElementById('chatbotWindow');
    const toggle = document.getElementById('chatbotToggle');
    
    if (window && toggle) {
      this.isOpen = !this.isOpen;
      
      if (this.isOpen) {
        window.style.display = 'block';
        window.style.animation = 'quantumSlideIn 0.3s ease-out';
        toggle.innerHTML = `
          <span class="ai-badge">AI</span>
          <span class="ai-pulse"></span>
          ×
        `;
        
        // Focus input
        const input = document.getElementById('chatbotInput');
        if (input) {
          setTimeout(() => input.focus(), 300);
        }
        
        console.log('🚀 Quantum Chatbot Activated!');
      } else {
        this.closeQuantumChatbot();
      }
    }
  }

  closeQuantumChatbot() {
    const window = document.getElementById('chatbotWindow');
    const toggle = document.getElementById('chatbotToggle');
    
    if (window && toggle) {
      window.style.animation = 'quantumSlideOut 0.3s ease-in';
      
      setTimeout(() => {
        window.style.display = 'none';
        this.isOpen = false;
        
        toggle.innerHTML = `
          <span class="ai-badge">AI</span>
          <span class="ai-pulse"></span>
          🤖
        `;
      }, 300);
      
      console.log('🛑 Quantum Chatbot Deactivated');
    }
  }

  minimizeQuantumChatbot() {
    const window = document.getElementById('chatbotWindow');
    if (window) {
      window.classList.toggle('minimized');
    }
  }

  sendQuantumMessage() {
    const input = document.getElementById('chatbotInput');
    if (!input) return;
    
    const message = input.value.trim();
    if (!message) return;
    
    // Add user message
    this.addQuantumUserMessage(message);
    input.value = '';
    
    // Process with quantum AI
    this.processQuantumMessage(message);
  }

  addQuantumUserMessage(message) {
    const messagesContainer = document.getElementById('chatbotMessages');
    if (!messagesContainer) return;
    
    const messageElement = document.createElement('div');
    messageElement.className = 'chatbot-message user';
    messageElement.innerHTML = `
      <div class="message-content user">
        <div class="quantum-user-message">${this.escapeHtml(message)}</div>
        <div class="message-time">${this.getCurrentTime()}</div>
      </div>
      <div class="message-avatar user">${this.currentUser ? '👤' : '👻'}</div>
    `;
    
    messagesContainer.appendChild(messageElement);
    this.scrollToBottom();
    
    // Store in conversation history
    this.conversationHistory.push({
      type: 'user',
      message: message,
      timestamp: Date.now()
    });
  }

  async processQuantumMessage(message) {
    this.showQuantumTyping();
    
    try {
      // Check cache first for instant responses
      const cacheKey = this.generateCacheKey(message);
      if (this.responseCache.has(cacheKey)) {
        const cachedResponse = this.responseCache.get(cacheKey);
        this.hideQuantumTyping();
        this.addQuantumBotMessage(cachedResponse.content, true);
        this.performanceMetrics.cacheHits++;
        return;
      }
      
      // Generate quantum response
      const response = await this.generateQuantumResponse(message);
      
      // Cache the response
      this.responseCache.set(cacheKey, {
        content: response,
        timestamp: Date.now()
      });
      
      this.hideQuantumTyping();
      this.addQuantumBotMessage(response, true);
      
      // Update learning data
      this.updateLearningData(message, response);
      
    } catch (error) {
      console.error('Quantum processing error:', error);
      this.hideQuantumTyping();
      this.addQuantumBotMessage('🔧 Quantum systems temporarily offline. Rebooting neural networks...', false);
    }
    
    this.performanceMetrics.totalRequests++;
  }

  async generateQuantumResponse(message) {
    // Simulate quantum processing delay for realism
    await this.delay(1000 + Math.random() * 2000);
    
    const lowerMessage = message.toLowerCase();
    
    // Check for exact matches in knowledge base
    for (const [category, data] of Object.entries(this.quantumKnowledge)) {
      if (category === 'greetings') continue;
      
      if (data.keywords && data.keywords.some(keyword => lowerMessage.includes(keyword))) {
        const response = this.getRandomResponse(category);
        return this.personalizeResponse(response);
      }
    }
    
    // Fallback to advanced AI response
    return this.generateAdvancedResponse(message);
  }

  generateAdvancedResponse(message) {
    // Advanced AI simulation with contextual awareness
    const responses = [
      `🔥 BEAST MODE ENGAGED! I hear you asking about "${message.substring(0, 30)}...". Let me tap into my quantum fitness database for you!`,
      `⚡ Quantum processing complete! Based on your query, I recommend checking out our personalized training programs designed for warriors like you!`,
      `💪 LEGENDARY question! While I process the perfect response, why not explore our memberships or book a class? I'm always learning to serve you better!`,
      `🎯 TARGET ACQUIRED! Your fitness journey is unique, and I'm here to guide you every step of the way. Let's crush those goals together!`,
      `🚀 ROCKET-POWERED RESPONSE incoming! I'm your AI training partner, ready to help you dominate. What specific area can I help you conquer?`
    ];
    
    return responses[Math.floor(Math.random() * responses.length)];
  }

  personalizeResponse(response) {
    if (this.currentUser && this.currentUser.name) {
      return response.replace(/warrior|champion|beast|legend/gi, `${this.currentUser.name}`);
    }
    return response;
  }

  addQuantumBotMessage(message, includeActions = false) {
    const messagesContainer = document.getElementById('chatbotMessages');
    if (!messagesContainer) return;
    
    const messageElement = document.createElement('div');
    messageElement.className = 'chatbot-message bot';
    
    let actionsHTML = '';
    if (includeActions) {
      actionsHTML = `
        <div class="quantum-actions">
          <button class="quantum-action" data-message="Tell me more">💡 More Info</button>
          <button class="quantum-action" data-message="Book a class">📅 Book Class</button>
          <button class="quantum-action" data-message="Contact staff">📞 Contact</button>
        </div>
      `;
    }
    
    messageElement.innerHTML = `
      <div class="message-avatar bot">🤖</div>
      <div class="message-content bot">
        <div class="quantum-bot-message">${message}</div>
        <div class="message-time">${this.getCurrentTime()}</div>
        ${actionsHTML}
      </div>
    `;
    
    messagesContainer.appendChild(messageElement);
    this.scrollToBottom();
    
    // Store in conversation history
    this.conversationHistory.push({
      type: 'bot',
      message: message,
      timestamp: Date.now()
    });
  }

  showQuantumTyping() {
    const messagesContainer = document.getElementById('chatbotMessages');
    if (!messagesContainer) return;
    
    const typingElement = document.createElement('div');
    typingElement.className = 'chatbot-message bot typing';
    typingElement.id = 'quantumTyping';
    typingElement.innerHTML = `
      <div class="message-avatar bot">🤖</div>
      <div class="message-content bot">
        <div class="quantum-typing">
          <span class="quantum-dot"></span>
          <span class="quantum-dot"></span>
          <span class="quantum-dot"></span>
          <span class="typing-text">Quantum AI processing...</span>
        </div>
      </div>
    `;
    
    messagesContainer.appendChild(typingElement);
    this.scrollToBottom();
  }

  hideQuantumTyping() {
    const typingElement = document.getElementById('quantumTyping');
    if (typingElement) {
      typingElement.remove();
    }
  }

  handleQuantumAction(message) {
    const input = document.getElementById('chatbotInput');
    if (input) {
      input.value = message;
      this.sendQuantumMessage();
    }
  }

  startVoiceInput() {
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      const recognition = new SpeechRecognition();
      
      recognition.continuous = false;
      recognition.interimResults = false;
      recognition.lang = 'en-US';
      
      recognition.onstart = () => {
        console.log('🎤 Voice input activated');
        const voiceBtn = document.getElementById('voiceInput');
        if (voiceBtn) {
          voiceBtn.style.background = 'var(--quantum-green)';
          voiceBtn.textContent = '🔴';
        }
      };
      
      recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        const input = document.getElementById('chatbotInput');
        if (input) {
          input.value = transcript;
          this.sendQuantumMessage();
        }
      };
      
      recognition.onend = () => {
        const voiceBtn = document.getElementById('voiceInput');
        if (voiceBtn) {
          voiceBtn.style.background = '';
          voiceBtn.textContent = '🎤';
        }
      };
      
      recognition.start();
    } else {
      this.addQuantumBotMessage('🎤 Voice input not supported in your browser. Try typing your message!', false);
    }
  }

  handleSmartInput(value) {
    // Show smart suggestions based on input
    const suggestions = this.generateSmartSuggestions(value);
    this.showSmartSuggestions(suggestions);
  }

  generateSmartSuggestions(input) {
    if (input.length < 2) return [];
    
    const suggestions = [];
    const lowerInput = input.toLowerCase();
    
    // Check against knowledge base
    for (const [category, data] of Object.entries(this.quantumKnowledge)) {
      if (category === 'greetings') continue;
      
      if (data.keywords) {
        const matches = data.keywords.filter(keyword => 
          keyword.toLowerCase().includes(lowerInput) || 
          lowerInput.includes(keyword.toLowerCase())
        );
        
        if (matches.length > 0) {
          suggestions.push({
            text: `Ask about ${category}`,
            action: `Tell me about ${category}`
          });
        }
      }
    }
    
    return suggestions.slice(0, 3);
  }

  showSmartSuggestions(suggestions) {
    const container = document.getElementById('quantumSuggestions');
    if (!container) return;
    
    if (suggestions.length === 0) {
      container.style.display = 'none';
      return;
    }
    
    container.innerHTML = suggestions.map(suggestion => `
      <button class="smart-suggestion" data-action="${suggestion.action}">
        ${suggestion.text}
      </button>
    `).join('');
    
    container.style.display = 'block';
    
    // Bind suggestion clicks
    container.querySelectorAll('.smart-suggestion').forEach(btn => {
      btn.addEventListener('click', () => {
        const action = btn.getAttribute('data-action');
        const input = document.getElementById('chatbotInput');
        if (input && action) {
          input.value = action;
          this.sendQuantumMessage();
        }
        container.style.display = 'none';
      });
    });
  }

  updateLearningData(message, response) {
    const key = this.generateCacheKey(message);
    this.learningData.set(key, {
      message,
      response,
      frequency: (this.learningData.get(key)?.frequency || 0) + 1,
      lastUsed: Date.now()
    });
    
    // Save to localStorage
    try {
      const learningArray = Array.from(this.learningData.entries());
      localStorage.setItem('quantumChatbotLearning', JSON.stringify(learningArray));
    } catch (e) {
      console.log('Learning data storage failed');
    }
  }

  generateCacheKey(message) {
    return message.toLowerCase()
      .replace(/[^\w\s]/g, '')
      .replace(/\s+/g, '_')
      .substring(0, 50);
  }

  getRandomResponse(category) {
    const responses = this.quantumKnowledge[category];
    if (Array.isArray(responses)) {
      return responses[Math.floor(Math.random() * responses.length)];
    } else if (responses && responses.responses) {
      return responses.responses[Math.floor(Math.random() * responses.responses.length)];
    }
    return "🔥 BEAST MODE: I'm ready to help you dominate! What can I do for you?";
  }

  getCurrentTime() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }

  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  scrollToBottom() {
    const messagesContainer = document.getElementById('chatbotMessages');
    if (messagesContainer) {
      messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
  }

  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

// Initialize Quantum Chatbot when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  console.log('🚀 Initializing Quantum Chatbot Systems...');
  
  // Add user info if available
  try {
    if (typeof userInfo !== 'undefined') {
      window.userInfo = userInfo;
    }
  } catch (e) {
    console.log('👤 Guest mode activated');
  }
  
  // Create the quantum chatbot instance
  window.quantumChatbot = new QuantumChatbot();
  
  console.log('✅ Quantum Chatbot Ready - BEAST MODE ACTIVATED!');
});
