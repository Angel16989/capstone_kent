// === L9 FITNESS SIMPLE AI CHATBOT - BULLETPROOF VERSION === //

console.log('🚀 Loading Simple Chatbot...');

class SimpleChatbot {
  constructor() {
    console.log('🎯 SimpleChatbot constructor called');
    
    this.isOpen = false;
    this.conversationHistory = [];
    
    // Simple knowledge base
    this.responses = {
      greetings: [
        "🔥 Welcome to L9 Fitness! How can I help you dominate today?",
        "⚡ Hey there, warrior! Ready to crush your fitness goals?",
        "💪 What's up, champion! How can I assist you today?"
      ],
      hours: [
        "🕐 L9 Fitness is open 24/7! Staff hours: Mon-Fri 6AM-10PM, Weekends 8AM-8PM.",
        "⏰ We're always open! 24/7 access for members. Staff available during peak hours."
      ],
      membership: [
        "💎 Our memberships: Monthly Beast ($49), Quarterly Savage ($129), Yearly Champion ($399)!",
        "🏆 Check out our amazing membership plans on our Memberships page!"
      ],
      classes: [
        "🔥 We offer HIIT, Yoga, Strength Training, Cardio, and more!",
        "⚡ Visit our Classes page to see schedules and book sessions!"
      ]
    };
    
    console.log('✅ Properties initialized, creating interface...');
    this.createInterface();
  }

  createInterface() {
    console.log('🎨 Creating chatbot interface...');
    
    const chatbotHTML = `
      <div id="simpleChatbot" style="position: fixed; bottom: 30px; right: 30px; z-index: 999999; font-family: Arial, sans-serif;">
        <!-- Toggle Button -->
        <button id="chatToggle" style="
          width: 80px; 
          height: 80px; 
          border-radius: 50%; 
          border: none; 
          background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
          color: white; 
          font-size: 24px; 
          cursor: pointer; 
          box-shadow: 0 8px 25px rgba(0,0,0,0.3);
          display: flex;
          align-items: center;
          justify-content: center;
          transition: all 0.3s ease;
        ">
          🤖
        </button>
        
        <!-- Chat Window -->
        <div id="chatWindow" style="
          position: absolute;
          bottom: 90px;
          right: 0;
          width: 350px;
          height: 500px;
          background: white;
          border-radius: 15px;
          box-shadow: 0 10px 30px rgba(0,0,0,0.3);
          display: none;
          flex-direction: column;
          overflow: hidden;
        ">
          <!-- Header -->
          <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: bold; font-size: 16px;">🤖 L9 AI Assistant</div>
            <button id="chatClose" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">×</button>
          </div>
          
          <!-- Messages -->
          <div id="chatMessages" style="flex: 1; padding: 20px; overflow-y: auto; background: #f8f9fa;">
            <div style="background: #e3f2fd; padding: 10px; border-radius: 10px; margin-bottom: 10px;">
              <strong>🤖 AI:</strong> Hey there! Welcome to L9 Fitness! How can I help you today?
            </div>
          </div>
          
          <!-- Input -->
          <div style="padding: 15px; border-top: 1px solid #eee; background: white;">
            <div style="display: flex; gap: 10px;">
              <input id="chatInput" type="text" placeholder="Ask me anything..." style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 20px; outline: none;">
              <button id="chatSend" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px 15px; border-radius: 20px; cursor: pointer;">Send</button>
            </div>
          </div>
        </div>
      </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    console.log('✅ Interface created, binding events...');
    
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
    console.log('🔄 Toggling chat, current state:', this.isOpen);
    
    const window = document.getElementById('chatWindow');
    const toggle = document.getElementById('chatToggle');
    
    if (window && toggle) {
      this.isOpen = !this.isOpen;
      
      if (this.isOpen) {
        console.log('📖 Opening chat...');
        window.style.display = 'flex';
        toggle.innerHTML = '×';
        toggle.style.background = 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';
        
        // Focus input
        const input = document.getElementById('chatInput');
        if (input) {
          setTimeout(() => input.focus(), 200);
        }
      } else {
        this.closeChat();
      }
    }
  }

  closeChat() {
    console.log('📖 Closing chat...');
    
    const window = document.getElementById('chatWindow');
    const toggle = document.getElementById('chatToggle');
    
    if (window && toggle) {
      window.style.display = 'none';
      toggle.innerHTML = '🤖';
      toggle.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
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
    
    // Generate response
    setTimeout(() => {
      const response = this.generateResponse(message);
      this.addBotMessage(response);
    }, 500);
  }

  addUserMessage(message) {
    const messagesContainer = document.getElementById('chatMessages');
    if (!messagesContainer) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.style.cssText = 'background: #e1f5fe; padding: 10px; border-radius: 10px; margin-bottom: 10px; text-align: right;';
    messageDiv.innerHTML = `<strong>You:</strong> ${this.escapeHtml(message)}`;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  addBotMessage(message) {
    const messagesContainer = document.getElementById('chatMessages');
    if (!messagesContainer) return;
    
    const messageDiv = document.createElement('div');
    messageDiv.style.cssText = 'background: #e3f2fd; padding: 10px; border-radius: 10px; margin-bottom: 10px;';
    messageDiv.innerHTML = `<strong>🤖 AI:</strong> ${message}`;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  generateResponse(message) {
    const lowerMessage = message.toLowerCase();
    
    // Check for keywords
    if (lowerMessage.includes('hour') || lowerMessage.includes('time') || lowerMessage.includes('open')) {
      return this.getRandomResponse('hours');
    }
    
    if (lowerMessage.includes('member') || lowerMessage.includes('price') || lowerMessage.includes('cost') || lowerMessage.includes('plan')) {
      return this.getRandomResponse('membership');
    }
    
    if (lowerMessage.includes('class') || lowerMessage.includes('workout') || lowerMessage.includes('training')) {
      return this.getRandomResponse('classes');
    }
    
    if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('hey')) {
      return this.getRandomResponse('greetings');
    }
    
    // Default responses
    const defaultResponses = [
      "🔥 Great question! I'm here to help with anything about L9 Fitness!",
      "⚡ Let me help you with that! What specific information do you need?",
      "💪 I'm your AI fitness assistant! Feel free to ask about hours, memberships, or classes!",
      "🎯 I can help you with gym information, schedules, and more! What interests you?"
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
