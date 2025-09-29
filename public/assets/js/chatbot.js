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
      <!-- Welcome Message Popup -->
      <div id="wakiWelcome" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000000; background: linear-gradient(135deg, #FF4444, #FFD700); padding: 20px; border-radius: 15px; color: white; font-weight: bold; font-size: 16px; box-shadow: 0 20px 40px rgba(255,68,68,0.6); opacity: 0; animation: welcomePopup 4s ease-out forwards; pointer-events: none;">
        🏋️‍♂️ Hi! Welcome to L9 Fitness! I'm your chatbot named <strong>WAKI</strong> and I'll help you out! 💪✨
      </div>
      
      <div id="simpleChatbot" style="display: block !important; visibility: visible !important;">
        <!-- L9 Fitness Toggle Button -->
        <button id="chatToggle" class="l9-glow" style="display: flex !important;">
          <span>🤖</span>
          <div class="chatbot-name">WAKI</div>
        </button>
        
        <!-- L9 Fitness Chat Window -->
        <div id="chatWindow" style="display: none !important;">
          <!-- L9 Header -->
          <div class="chat-header l9-shine-effect">
            <div class="title">🤖 WAKI - Your L9 Fitness Assistant</div>
            <button id="chatClose">×</button>
          </div>
          
          <!-- Messages Area -->
          <div id="chatMessages">
            <div class="chat-message bot">
              <strong>WAKI:</strong> Hey there! 🏋️‍♂️ I'm WAKI, your personal L9 Fitness assistant! I'm here to help with memberships, classes, gym info, and anything else you need. What can I help you with today? 💪
            </div>
          </div>
          
          <!-- Input Area -->
          <div class="chat-input-container">
            <input type="text" id="chatInput" placeholder="Ask WAKI about memberships, classes, hours..." />
            <button id="chatSend">Send</button>
          </div>
        </div>
      </div>
    `;
    
    // Always add to body for fixed positioning
    document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    this.attachEventListeners();
    console.log('✅ L9 Fitness interface created successfully');
  }

  attachEventListeners() {
    console.log('🔗 Attaching L9 Fitness event listeners...');
    
    try {
      const toggleBtn = document.getElementById('chatToggle');
      const closeBtn = document.getElementById('chatClose');
      const sendBtn = document.getElementById('chatSend');
      const input = document.getElementById('chatInput');
      
      if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          console.log('🔘 Toggle button clicked');
          this.toggleChat();
        });
        console.log('✅ Toggle button listener attached');
      } else {
        console.error('❌ Toggle button not found');
      }
      
      if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          console.log('🔘 Close button clicked');
          this.closeChat();
        });
        console.log('✅ Close button listener attached');
      }
      
      if (sendBtn) {
        sendBtn.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          console.log('🔘 Send button clicked');
          this.sendMessage();
        });
        console.log('✅ Send button listener attached');
      }
      
      if (input) {
        input.addEventListener('keypress', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            console.log('🔘 Enter key pressed');
            this.sendMessage();
          }
        });
        console.log('✅ Input listener attached');
      }
      
      // Add scroll behavior for better chatbot integration
      this.setupScrollBehavior();
      
    } catch (error) {
      console.error('❌ Error attaching event listeners:', error);
    }
  }
  
  setupScrollBehavior() {
    console.log('📜 Setting up WAKI floating behavior...');
    
    const chatbotContainer = document.getElementById('simpleChatbot');
    const welcomeMessage = document.getElementById('wakiWelcome');
    
    if (!chatbotContainer) {
      console.error('❌ WAKI container not found for scroll setup!');
      return;
    }
    
    // Setup WAKI welcome sequence
    this.setupWakiWelcome(welcomeMessage);
    
    // Force visibility
    chatbotContainer.style.display = 'block';
    chatbotContainer.style.visibility = 'visible';
    
    // Add smooth scroll class to html
    document.documentElement.classList.add('chatbot-scroll-smooth');
    
    let lastScrollY = window.scrollY;
    let scrollTimeout;
    
    // WAKI floating behavior - always sticks to bottom-right
    window.addEventListener('scroll', () => {
      const currentScrollY = window.scrollY;
      const scrollDirection = currentScrollY > lastScrollY ? 'down' : 'up';
      
      if (chatbotContainer) {
        // Ensure WAKI stays visible and floating at viewport bottom
        chatbotContainer.style.display = 'block';
        chatbotContainer.style.visibility = 'visible';
        chatbotContainer.style.position = 'fixed';
        chatbotContainer.style.bottom = '20px';
        chatbotContainer.style.right = '20px';
        chatbotContainer.style.zIndex = '999999';
        
        // Keep WAKI fixed at viewport bottom with subtle scale animation only
        if (scrollDirection === 'down') {
          chatbotContainer.style.transform = 'scale(0.98)';
        } else {
          chatbotContainer.style.transform = 'scale(1.02)';
        }
        
        // Clear timeout and reset to normal size - STAY AT VIEWPORT BOTTOM
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
          if (chatbotContainer) {
            chatbotContainer.style.transform = 'scale(1)';
            // Force viewport bottom positioning
            chatbotContainer.style.position = 'fixed';
            chatbotContainer.style.bottom = '20px';
            chatbotContainer.style.right = '20px';
            chatbotContainer.style.zIndex = '999999';
          }
        }, 150);
      }
      
      lastScrollY = currentScrollY;
    }, { passive: true });
    
    // Ensure WAKI is always floating after entrance
    setTimeout(() => {
      if (chatbotContainer) {
        chatbotContainer.style.display = 'block';
        chatbotContainer.style.visibility = 'visible';
        chatbotContainer.style.opacity = '1';
        chatbotContainer.style.position = 'fixed';
        console.log('✅ WAKI is now floating and ready!');
      }
    }, 4000);
    
    console.log('✅ WAKI floating behavior activated!');
  }
  
  setupWakiWelcome(welcomeMessage) {
    if (!welcomeMessage) return;
    
    // Show welcome message after chatbot entrance
    setTimeout(() => {
      console.log('👋 WAKI says hello!');
      // Welcome message animation handles itself via CSS
    }, 2000);
    
    // Remove welcome message after animation
    setTimeout(() => {
      if (welcomeMessage) {
        welcomeMessage.remove();
        console.log('✅ Welcome message completed!');
      }
    }, 6000);
  }

  toggleChat() {
    console.log('🔄 Toggling L9 Fitness chat...');
    const chatWindow = document.getElementById('chatWindow');
    if (!chatWindow) {
      console.error('❌ Chat window not found!');
      return;
    }
    
    this.isOpen = !this.isOpen;
    
    if (this.isOpen) {
      // Show chat window with !important to override CSS
      chatWindow.style.setProperty('display', 'flex', 'important');
      console.log('✅ Chat window opened');
      
      // Focus on input after a short delay
      setTimeout(() => {
        const input = document.getElementById('chatInput');
        if (input) input.focus();
      }, 100);
    } else {
      // Hide chat window
      chatWindow.style.setProperty('display', 'none', 'important');
      console.log('✅ Chat window closed');
    }
  }

  openChat() {
    console.log('📖 Opening L9 Fitness chat...');
    const chatWindow = document.getElementById('chatWindow');
    if (!chatWindow) {
      console.error('❌ Chat window not found!');
      return;
    }
    
    this.isOpen = true;
    chatWindow.style.setProperty('display', 'flex', 'important');
    
    setTimeout(() => {
      const input = document.getElementById('chatInput');
      if (input) input.focus();
    }, 100);
  }

  closeChat() {
    console.log('📕 Closing L9 Fitness chat...');
    const chatWindow = document.getElementById('chatWindow');
    if (!chatWindow) {
      console.error('❌ Chat window not found!');
      return;
    }
    
    this.isOpen = false;
    chatWindow.style.setProperty('display', 'none', 'important');
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
      
      const response = await fetch('simple_chatbot_api.php', {
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
    
    // Convert markdown-style formatting to HTML
    let formattedMessage = message
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')  // **bold** to <strong>
      .replace(/\*(.*?)\*/g, '<em>$1</em>')              // *italic* to <em>
      .replace(/\n/g, '<br>')                            // newlines to <br>
      .replace(/•/g, '&bull;');                          // bullet points
    
    messageDiv.innerHTML = `<strong>L9 Fitness:</strong> ${formattedMessage}`;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    
    console.log('✅ Bot message added:', message.substring(0, 50) + '...');
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
  console.log('Document ready state:', document.readyState);
  console.log('Body exists:', !!document.body);
  
  try {
    // Ensure body exists before creating chatbot
    if (!document.body) {
      console.log('⏳ Body not ready, waiting...');
      setTimeout(initializeChatbot, 100);
      return;
    }
    
    window.simpleChatbot = new SimpleChatbot();
    console.log('✅ L9 Fitness AI Chatbot Ready!');
    
    // Double-check the button was created
    setTimeout(() => {
      const toggleBtn = document.getElementById('chatToggle');
      if (toggleBtn) {
        console.log('✅ Chat toggle button confirmed in DOM');
      } else {
        console.error('❌ Chat toggle button NOT found in DOM');
      }
    }, 500);
    
  } catch (error) {
    console.error('❌ L9 Fitness Chatbot initialization failed:', error);
    console.error('Error details:', error.stack);
  }
}

// Multiple initialization attempts
if (document.readyState === 'loading') {
  console.log('📖 Document still loading, adding DOMContentLoaded listener');
  document.addEventListener('DOMContentLoaded', initializeChatbot);
} else {
  console.log('📖 Document already loaded, initializing immediately');
  initializeChatbot();
}

// Backup initialization
setTimeout(() => {
  if (!window.simpleChatbot) {
    console.log('🔄 Backup initialization attempt...');
    initializeChatbot();
  }
}, 2000);

console.log('🎉 L9 Fitness AI Chatbot script loaded!');
