// L9 Fitness AI Chatbot - Clean Version
console.log('🚀 Loading L9 Fitness AI Chatbot...');

class SimpleChatbot {
  constructor() {
    console.log('🎯 L9 Fitness AI Chatbot constructor called');
    
    this.isOpen = false;
    this.conversationHistory = [];
    
    console.log('✅ L9 Fitness AI Chatbot initialized');
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
          <div class="chat-input-container">
            <input type="text" id="chatInput" placeholder="Ask about memberships, classes, hours..." />
            <button id="chatSend">Send</button>
          </div>
        </div>
      </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    this.attachEventListeners();
    console.log('✅ L9 Fitness interface created successfully');
  }

  attachEventListeners() {
    console.log('🔗 Attaching L9 Fitness event listeners...');
    
    const toggleBtn = document.getElementById('chatToggle');
    const closeBtn = document.getElementById('chatClose');
    const sendBtn = document.getElementById('chatSend');
    const input = document.getElementById('chatInput');
    
    if (toggleBtn) {
      toggleBtn.addEventListener('click', () => this.toggleChat());
    }
    
    if (closeBtn) {
      closeBtn.addEventListener('click', () => this.closeChat());
    }
    
    if (sendBtn) {
      sendBtn.addEventListener('click', () => this.sendMessage());
    }
    
    if (input) {
      input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          this.sendMessage();
        }
      });
    }
  }

  toggleChat() {
    console.log('🔄 Toggling L9 Fitness chat...');
    const chatWindow = document.getElementById('chatWindow');
    if (!chatWindow) return;
    
    this.isOpen = !this.isOpen;
    chatWindow.style.display = this.isOpen ? 'flex' : 'none';
    
    if (this.isOpen) {
      document.getElementById('chatInput')?.focus();
    }
  }

  openChat() {
    console.log('📖 Opening L9 Fitness chat...');
    const chatWindow = document.getElementById('chatWindow');
    if (!chatWindow) return;
    
    this.isOpen = true;
    chatWindow.style.display = 'flex';
    document.getElementById('chatInput')?.focus();
  }

  closeChat() {
    console.log('📕 Closing L9 Fitness chat...');
    const chatWindow = document.getElementById('chatWindow');
    if (!chatWindow) return;
    
    this.isOpen = false;
    chatWindow.style.display = 'none';
  }

  sendMessage() {
    const input = document.getElementById('chatInput');
    if (!input || !input.value.trim()) return;
    
    const message = input.value.trim();
    console.log('📤 Sending message to L9 Fitness AI:', message);
    
    this.addUserMessage(message);
    input.value = '';
    
    // Call AI API instead of local responses
    this.callAIAPI(message);
  }

  async callAIAPI(message) {
    try {
      console.log('🤖 Calling L9 Fitness AI API with message:', message);
      
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
      console.log('✅ L9 Fitness AI Response:', data);
      
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
      console.error('❌ L9 Fitness AI API Error:', error);
      
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

  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
}

// Initialize when DOM is ready
console.log('🔧 Setting up L9 Fitness DOM ready listener...');

function initializeChatbot() {
  console.log('🚀 Initializing L9 Fitness AI Chatbot...');
  try {
    window.simpleChatbot = new SimpleChatbot();
    console.log('✅ L9 Fitness AI Chatbot Ready!');
  } catch (error) {
    console.error('❌ L9 Fitness Chatbot initialization failed:', error);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeChatbot);
} else {
  initializeChatbot();
}

console.log('🎉 L9 Fitness AI Chatbot script loaded!');
