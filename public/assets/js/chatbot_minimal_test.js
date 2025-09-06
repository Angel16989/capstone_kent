// Minimal L9 Fitness Chatbot Test
console.log('🚀 Minimal L9 Fitness Chatbot Loading...');

try {
    // Simple chatbot class
    class L9ChatbotTest {
        constructor() {
            console.log('🎯 L9ChatbotTest constructor called');
            this.createButton();
        }

        createButton() {
            console.log('🎨 Creating test button...');
            
            // Remove existing if any
            const existing = document.getElementById('testChatButton');
            if (existing) existing.remove();
            
            // Create simple test button
            const button = document.createElement('button');
            button.id = 'testChatButton';
            button.innerHTML = '💬 L9 Test';
            button.style.cssText = `
                position: fixed !important;
                bottom: 30px !important;
                right: 30px !important;
                width: 80px !important;
                height: 80px !important;
                border-radius: 50% !important;
                background: linear-gradient(135deg, #FF4444, #00CCFF) !important;
                color: white !important;
                border: none !important;
                font-size: 16px !important;
                cursor: pointer !important;
                z-index: 999999 !important;
                box-shadow: 0 10px 30px rgba(255, 68, 68, 0.5) !important;
            `;
            
            button.onclick = () => {
                alert('L9 Fitness Chatbot Test Button Clicked!');
                console.log('✅ Test button clicked!');
            };
            
            document.body.appendChild(button);
            console.log('✅ Test button created and added');
        }
    }

    // Initialize immediately
    console.log('🔧 Initializing test chatbot...');
    window.l9TestChatbot = new L9ChatbotTest();
    console.log('✅ Test chatbot initialized!');

} catch (error) {
    console.error('❌ Minimal chatbot error:', error);
    
    // Fallback: create basic button directly
    setTimeout(() => {
        const fallbackButton = document.createElement('div');
        fallbackButton.innerHTML = '🔴 ERROR';
        fallbackButton.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 80px;
            height: 80px;
            background: red;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            z-index: 999999;
            cursor: pointer;
        `;
        fallbackButton.onclick = () => alert('Fallback button - there was an error!');
        document.body.appendChild(fallbackButton);
    }, 1000);
}

console.log('🎉 Minimal chatbot script finished!');
