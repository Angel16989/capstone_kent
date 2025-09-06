// === L9 FITNESS CHATBOT - WEBSITE INTEGRATED VERSION === //

console.log('🚀 Loading L9 Fitness Chatbot...');

class SimpleChatbot {
  constructor() {
    console.log('🎯 L9 Fitness Chatbot constructor called');
    
    this.isOpen = false;
    this.conversationHistory = [];
    
    // L9 Fitness knowledge base
    this.responses = {
      greetings: [
        "🔥 Welcome to L9 Fitness! How can I help you today?",
        "💪 Hey there! Ready to start your fitness journey?",
        "⚡ Hello! What can I assist you with at L9 Fitness?"
      ],
      hours: [
        "🕐 L9 Fitness Hours: 24/7 member access! Staff: Mon-Fri 6AM-10PM, Weekends 8AM-8PM.",
        "⏰ We're always open for members! Staff available during business hours for assistance."
      ],
      membership: [
        "� Our Plans: Monthly Beast ($49), Quarterly Savage ($129), Yearly Champion ($399)!",
        "🏆 Check out our membership options - great value and benefits included!"
      ],
      classes: [
        "🔥 We offer HIIT, Yoga, Strength Training, Cardio, and more!",
        "⚡ Browse our Classes page to see schedules and book your sessions!"
      ]
    };
    
    console.log('✅ L9 Fitness chatbot initialized, creating interface...');
    this.createInterface();
  }

  createInterface() {
    console.log('🎨 Creating L9 Fitness chatbot interface...');
    
    const chatbotHTML = `
      <div id="simpleChatbot">
        <!-- L9 Fitness Toggle Button -->
        <button id="chatToggle" class="l9-glow">
          <span>💬</span>
        </button>
        
        <!-- L9 Fitness Chat Window -->
        <div id="chatWindow">
          <!-- L9 Header -->
          <div class="chat-header l9-shine-effect">
            <div class="title">💬 L9 Fitness Assistant</div>
            <button id="chatClose">×</button>
          </div>
          
          <!-- Messages Area -->
          <div id="chatMessages">
            <div class="chat-message bot">
              <strong>L9 Fitness:</strong> Welcome to L9 Fitness! 💪 I'm here to help with memberships, classes, and gym info. How can I assist you today?
            </div>
          </div>
          
          <!-- Input Area -->
          <div class="chat-input-area">
            <div class="chat-input-container">
              <input id="chatInput" type="text" placeholder="Ask about memberships, classes, hours..." />
              <button id="chatSend" class="l9-shine-effect">Send</button>
            </div>
          </div>
        </div>
      </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    console.log('✅ ULTIMATE Interface created, binding events...');
    
    // Bind events immediately
    setTimeout(() => {
      this.bindEvents();
    }, 100);
  }

  bindEvents() {
    console.log('🔗 Binding events...');
    
    const toggle = document.getElementById('chatToggle');
    const close = document.getElementById('chatClose');
    const send = document.getElementById('chatSend');
    const input = document.getElementById('chatInput');
    
    console.log('🔍 Found elements:', { toggle: !!toggle, close: !!close, send: !!send, input: !!input });
    
    if (toggle) {
      toggle.addEventListener('click', () => {
        console.log('🎯 Toggle clicked!');
        this.toggleChat();
      });
      
      // Add hover effect
      toggle.addEventListener('mouseenter', () => {
        toggle.style.transform = 'scale(1.1)';
      });
      toggle.addEventListener('mouseleave', () => {
        toggle.style.transform = 'scale(1)';
      });
    }
    
    if (close) {
      close.addEventListener('click', () => {
        console.log('❌ Close clicked!');
        this.closeChat();
      });
    }
    
    if (send) {
      send.addEventListener('click', () => {
        console.log('📤 Send clicked!');
        this.sendMessage();
      });
    }
    
    if (input) {
      input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          console.log('⌨️ Enter pressed!');
          this.sendMessage();
        }
      });
    }
    
    console.log('✅ All events bound successfully!');
  }

  toggleChat() {
    console.log('🔄 Toggling ULTIMATE chat, current state:', this.isOpen);
    
    const window = document.getElementById('chatWindow');
    const toggle = document.getElementById('chatToggle');
    
    if (window && toggle) {
      this.isOpen = !this.isOpen;
      
      if (this.isOpen) {
        console.log('📖 Opening ULTIMATE chat...');
        window.style.display = 'flex';
        window.classList.add('show');
        toggle.innerHTML = '<span>×</span>';
        
        // Focus input with a slight delay for better UX
        const input = document.getElementById('chatInput');
        if (input) {
          setTimeout(() => input.focus(), 300);
        }
      } else {
        this.closeChat();
      }
    }
  }

  closeChat() {
    console.log('📖 Closing ULTIMATE chat...');
    
    const window = document.getElementById('chatWindow');
    const toggle = document.getElementById('chatToggle');
    
    if (window && toggle) {
      window.style.display = 'none';
      window.classList.remove('show');
      toggle.innerHTML = '<span>🤖</span>';
      this.isOpen = false;
    }
  }

  sendMessage() {
    const input = document.getElementById('chatInput');
    if (!input || !input.value.trim()) return;
    
    const message = input.value.trim();
    console.log('📤 Sending message:', message);
    
    this.addUserMessage(message);
    input.value = '';
    
    // Call AI API instead of local responses
    this.callAIAPI(message);
  }

  async callAIAPI(message) {
    try {
      console.log('🤖 Calling AI API with message:', message);
      
      // Show typing indicator
      this.addBotMessage('⏳ L9 Fitness Assistant is typing...');
      
      const response = await fetch('/chatbot_api.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ message: message })
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      console.log('✅ AI API Response:', data);
      
      // Remove typing indicator
      const messages = document.getElementById('chatMessages');
      if (messages && messages.lastElementChild) {
        messages.removeChild(messages.lastElementChild);
      }
      
      // Add AI response
      if (data.success && data.response) {
        this.addBotMessage(data.response);
      } else {
        this.addBotMessage('🔧 Sorry, I encountered an issue. Please try again!');
      }
      
    } catch (error) {
      console.error('❌ AI API Error:', error);
      
      // Remove typing indicator
      const messages = document.getElementById('chatMessages');
      if (messages && messages.lastElementChild) {
        messages.removeChild(messages.lastElementChild);
      }
      
      // Fallback response
      this.addBotMessage('🔧 I\'m having trouble connecting right now. Please try again in a moment!');
    }
  }

  addUserMessage(message) {
    const messagesContainer = document.getElementById('chatMessages');
    if (!messagesContainer) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message user';
    messageDiv.innerHTML = `<strong>You:</strong> ${this.escapeHtml(message)}`;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  addBotMessage(message) {
    const messagesContainer = document.getElementById('chatMessages');
    if (!messagesContainer) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message bot';
    messageDiv.innerHTML = `<strong>L9 Fitness:</strong> ${message}`;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  showTypingIndicator() {
    const messagesContainer = document.getElementById('chatMessages');
    if (!messagesContainer) return;
    
    const typingDiv = document.createElement('div');
    typingDiv.className = 'typing-indicator';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = `
      <div class="typing-dot"></div>
      <div class="typing-dot"></div>
      <div class="typing-dot"></div>
    `;
    
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  hideTypingIndicator() {
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) {
      typingIndicator.remove();
    }
  }

  generateResponse(message) {
    const lowerMessage = message.toLowerCase();
    
    // Check for keywords with enhanced responses
    if (lowerMessage.includes('hour') || lowerMessage.includes('time') || lowerMessage.includes('open')) {
      const responses = [
        "🕐 L9 Fitness is open 24/7 for our warriors! The beast never sleeps! 💪 Staff hours: Mon-Fri 6AM-10PM, Weekends 8AM-8PM.",
        "⏰ We're always ready for action! 24/7 access for members. Our staff is here Mon-Fri 6AM-10PM, Weekends 8AM-8PM. Ready to train?",
        "🔥 Time doesn't stop, neither do we! 24/7 gym access. Staff support Mon-Fri 6AM-10PM, Weekends 8AM-8PM. Let's get beast mode!"
      ];
      return responses[Math.floor(Math.random() * responses.length)];
    }
    
    if (lowerMessage.includes('member') || lowerMessage.includes('price') || lowerMessage.includes('cost') || lowerMessage.includes('plan')) {
      const responses = [
        "💎 Transform into a BEAST with our epic plans: Monthly Beast ($49), Quarterly Savage ($129), Yearly Champion ($399)! Which warrior level calls to you?",
        "🏆 Choose your destiny: Monthly Beast Mode ($49), Quarterly Domination ($129), or Yearly LEGEND status ($399)! Ready to level up?",
        "⚡ Unleash your potential: Beast Monthly ($49), Savage Quarterly ($129), Champion Yearly ($399)! What's your fitness journey?"
      ];
      return responses[Math.floor(Math.random() * responses.length)];
    }
    
    if (lowerMessage.includes('class') || lowerMessage.includes('workout') || lowerMessage.includes('training')) {
      const responses = [
        "🔥 Our classes will forge you into a LEGEND: Beast Mode HIIT, Warrior Yoga, Savage Strength, Destroyer Cardio! Which battle calls to you?",
        "⚡ Choose your training battlefield: HIIT Destruction, Yoga Mastery, Strength Domination, Cardio Annihilation! Ready to conquer?",
        "💪 Epic training awaits: High-Intensity Beast Mode, Zen Warrior Yoga, Power Lifting Mayhem, Cardio Chaos! What's your weapon of choice?"
      ];
      return responses[Math.floor(Math.random() * responses.length)];
    }
    
    if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('hey') || lowerMessage.includes('sup')) {
      const responses = [
        "🤖 GREETINGS, FUTURE LEGEND! I'm your AI Beast Master, ready to guide you to FITNESS DOMINATION! How can I fuel your warrior spirit today?",
        "⚡ What's up, CHAMPION-IN-TRAINING! Your AI fitness companion is here to unleash your inner beast! What quest shall we embark on?",
        "🔥 HELLO THERE, MIGHTY WARRIOR! I'm your digital gym sensei, ready to help you CRUSH every fitness goal! What's your mission today?"
      ];
      return responses[Math.floor(Math.random() * responses.length)];
    }
    
    if (lowerMessage.includes('location') || lowerMessage.includes('address') || lowerMessage.includes('where')) {
      return "📍 Find us at the EPICENTER OF FITNESS: 123 Warrior Street, Beast City! Follow the sound of champions training and the glow of victory! 🏆";
    }
    
    if (lowerMessage.includes('contact') || lowerMessage.includes('phone') || lowerMessage.includes('email')) {
      return "� Connect with our Beast Squad: (555) 123-4567 or warrior@l9fitness.com! We're here to fuel your fitness journey 24/7! 💪";
    }
    
    // Enhanced default responses
    const defaultResponses = [
      "🤖 EXCELLENT question, future legend! I'm here to help you dominate your fitness journey! What specific information can I provide?",
      "⚡ Your AI Beast Master is ready to assist! Ask me about memberships, classes, hours, or anything fitness-related!",
      "🔥 I'm your digital fitness companion! Let me help you discover the path to GREATNESS! What interests you most?",
      "💪 Ready to help you become UNSTOPPABLE! I can guide you through memberships, class schedules, gym info, and more!",
      "🏆 Your journey to LEGENDARY status starts here! How can I help you conquer your fitness goals today?"
    ];
    
    return defaultResponses[Math.floor(Math.random() * defaultResponses.length)];
  }

  getRandomResponse(category) {
    const responses = this.responses[category];
    return responses[Math.floor(Math.random() * responses.length)];
  }

  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}

// Initialize when DOM is ready
console.log('🔧 Setting up DOM ready listener...');

function initializeChatbot() {
  console.log('🚀 Initializing Simple Chatbot...');
  try {
    window.simpleChatbot = new SimpleChatbot();
    console.log('✅ Simple Chatbot Ready!');
  } catch (error) {
    console.error('❌ Chatbot initialization failed:', error);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeChatbot);
} else {
  initializeChatbot();
}

console.log('📋 Chatbot script loaded successfully!');
