console.log('🔥 L9 FITNESS BEAST MODE ACTIVATED 🔥');

// --- HARDCORE TYPING EFFECTS ---
document.addEventListener('DOMContentLoaded', function() {
    
    // Get all input fields
    const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="number"], textarea');
    
    // Add hardcore typing effects
    inputs.forEach(input => {
        // Sound effect simulation on keypress
        input.addEventListener('keydown', function(e) {
            // Create screen flicker effect
            document.body.style.filter = 'brightness(1.1) contrast(1.05)';
            setTimeout(() => {
                document.body.style.filter = 'brightness(1) contrast(1)';
            }, 50);
            
            // Add dangerous character effects
            if (e.key.length === 1) {
                const char = e.key;
                console.log(`⚡ BEAST TYPING: ${char.toUpperCase()} ⚡`);
                
                // Create typing sparks effect
                createTypingSparks(input);
            }
        });
        
        // Hardcore focus effects
        input.addEventListener('focus', function() {
            console.log('🎯 TARGET ACQUIRED - ENTERING DANGER ZONE');
            this.style.fontWeight = '700';
            this.style.letterSpacing = '1.2px';
            
            // Add matrix rain effect in background
            startMatrixRain();
        });
        
        // Blur effects
        input.addEventListener('blur', function() {
            console.log('🛡️ EXITING DANGER ZONE');
            this.style.fontWeight = '600';
            this.style.letterSpacing = '0.5px';
            
            // Stop matrix effect
            stopMatrixRain();
        });
        
        // Real-time typing validation
        input.addEventListener('input', function() {
            const value = this.value;
            
            // Hardcore validation feedback
            if (this.type === 'password') {
                const strength = calculatePasswordStrength(value);
                updatePasswordStrength(this, strength);
            }
            
            // Add typing intensity based on speed
            clearTimeout(this.typingTimer);
            this.classList.add('rapid-typing');
            
            this.typingTimer = setTimeout(() => {
                this.classList.remove('rapid-typing');
            }, 300);
        });
    });
    
    // Create typing sparks effect
    function createTypingSparks(element) {
        const rect = element.getBoundingClientRect();
        const spark = document.createElement('div');
        spark.className = 'typing-spark';
        spark.style.cssText = `
            position: fixed;
            left: ${rect.right - 10}px;
            top: ${rect.top + rect.height/2}px;
            width: 3px;
            height: 3px;
            background: linear-gradient(45deg, #FF4444, #FFD700);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            animation: sparkFly 0.3s ease-out forwards;
        `;
        
        document.body.appendChild(spark);
        
        setTimeout(() => {
            if (spark.parentNode) {
                spark.parentNode.removeChild(spark);
            }
        }, 300);
    }
    
    // Matrix rain effect
    let matrixInterval;
    function startMatrixRain() {
        if (matrixInterval) return;
        
        const canvas = document.createElement('canvas');
        canvas.id = 'matrix-bg';
        canvas.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.1;
            pointer-events: none;
        `;
        
        document.body.appendChild(canvas);
        
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        const chars = '10BEAST★WARRIOR★DESTROYER★LEGEND01';
        const charArray = chars.split('');
        const fontSize = 14;
        const columns = canvas.width / fontSize;
        const drops = Array(Math.floor(columns)).fill(1);
        
        matrixInterval = setInterval(() => {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            ctx.fillStyle = '#FF4444';
            ctx.font = fontSize + 'px Courier New';
            
            for (let i = 0; i < drops.length; i++) {
                const text = charArray[Math.floor(Math.random() * charArray.length)];
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);
                
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                    drops[i] = 0;
                }
                drops[i]++;
            }
        }, 50);
    }
    
    function stopMatrixRain() {
        if (matrixInterval) {
            clearInterval(matrixInterval);
            matrixInterval = null;
            
            const canvas = document.getElementById('matrix-bg');
            if (canvas) {
                canvas.remove();
            }
        }
    }
    
    // Password strength calculator
    function calculatePasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]/)) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 25;
        return Math.min(strength, 100);
    }
    
    // Update password strength with hardcore styling
    function updatePasswordStrength(input, strength) {
        let color, message;
        
        if (strength < 25) {
            color = '#FF0000';
            message = 'WEAK BEAST';
        } else if (strength < 50) {
            color = '#FF4444';
            message = 'GETTING STRONGER';
        } else if (strength < 75) {
            color = '#FFD700';
            message = 'WARRIOR LEVEL';
        } else {
            color = '#00FF00';
            message = 'DESTROYER MODE';
        }
        
        input.style.borderColor = color;
        input.style.boxShadow = `0 0 15px ${color}60`;
        
        console.log(`🔒 PASSWORD STRENGTH: ${message} (${strength}%)`);
    }
    
    // Add CSS animations dynamically
    const style = document.createElement('style');
    style.textContent = `
        @keyframes sparkFly {
            0% { 
                transform: translate(0, 0) scale(1);
                opacity: 1;
            }
            100% { 
                transform: translate(${Math.random() * 40 - 20}px, ${Math.random() * 40 - 20}px) scale(0);
                opacity: 0;
            }
        }
        
        .rapid-typing {
            animation: rapidType 0.1s ease-in-out !important;
        }
        
        @keyframes rapidType {
            0% { 
                transform: scale(1);
                filter: brightness(1);
            }
            50% { 
                transform: scale(1.02);
                filter: brightness(1.2) contrast(1.1);
            }
            100% { 
                transform: scale(1);
                filter: brightness(1);
            }
        }
        
        /* Screen glitch effect */
        @keyframes screenGlitch {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
        }
        
        body.glitch-mode {
            animation: screenGlitch 0.3s ease-in-out;
        }
    `;
    document.head.appendChild(style);
    
    // Random screen glitch on intense typing
    let glitchTimer;
    document.addEventListener('keydown', function() {
        clearTimeout(glitchTimer);
        glitchTimer = setTimeout(() => {
            if (Math.random() < 0.05) { // 5% chance
                document.body.classList.add('glitch-mode');
                setTimeout(() => {
                    document.body.classList.remove('glitch-mode');
                }, 300);
            }
        }, 100);
    });
    
    console.log('💀 HARDCORE TYPING SYSTEM ARMED AND READY 💀');
});
