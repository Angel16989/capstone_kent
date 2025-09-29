console.log('🔥 L9 FITNESS BEAST MODE ACTIVATED 🔥');

// --- ENHANCED COMPATIBILITY WITH PAGE TRANSITIONS ---
document.addEventListener('DOMContentLoaded', function() {
    // Initialize after page transitions are ready
    if (window.ultraSmoothTransitions) {
        initializeHardcoreFeatures();
    } else {
        // Wait for transition system to load
        window.addEventListener('load', initializeHardcoreFeatures);
    }
});

function initializeHardcoreFeatures() {
    initializeTypingEffects();
    initializeDashboardFeatures();
    initializeFormEnhancements();
    initializeVisualEffects();
    
    console.log('💀 HARDCORE SYSTEM FULLY ARMED AND READY 💀');
}

// --- HARDCORE TYPING EFFECTS ---
function initializeTypingEffects() {
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
}

// Dashboard Interactive Features
function initializeDashboardFeatures() {
    // Dashboard card expand/collapse functionality
    const dashboardCards = document.querySelectorAll('.dashboard-card[data-bs-toggle="collapse"]');
    
    dashboardCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Prevent default if clicking on links
            if (e.target.closest('a')) return;
            
            const targetId = this.getAttribute('data-bs-target');
            const collapseElement = document.querySelector(targetId);
            
            if (collapseElement) {
                const bsCollapse = new bootstrap.Collapse(collapseElement, {
                    toggle: false
                });
                
                if (collapseElement.classList.contains('show')) {
                    bsCollapse.hide();
                    this.setAttribute('aria-expanded', 'false');
                } else {
                    bsCollapse.show();
                    this.setAttribute('aria-expanded', 'true');
                    
                    // Add subtle animation to feature items
                    setTimeout(() => {
                        const featureItems = collapseElement.querySelectorAll('.feature-item');
                        featureItems.forEach((item, index) => {
                            setTimeout(() => {
                                item.style.animation = `slideInFromLeft 0.3s ease forwards`;
                            }, index * 50);
                        });
                    }, 100);
                }
            }
        });
        
        // Add hover effect for mouse tracking
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            
            this.style.setProperty('--mouse-x', x + '%');
            this.style.setProperty('--mouse-y', y + '%');
        });
    });
    
    // Quick action button interactions
    const quickActionBtns = document.querySelectorAll('.quick-action-btn');
    quickActionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Add click ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 215, 0, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

function initializeFormEnhancements() {
    // Category card auto-scroll on expand
    if (document.querySelector('.class-types-section')) {
        document.querySelectorAll('.class-types-section .category-card').forEach(card => {
            card.addEventListener('click', function(e) {
                const targetId = this.getAttribute('data-bs-target');
                if (!targetId) return;
                setTimeout(() => {
                    const details = document.querySelector(targetId);
                    if (details && details.classList.contains('show')) {
                        details.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 350); // Wait for collapse animation
            });
        });
    }
}

function initializeVisualEffects() {
    // Add CSS animations dynamically
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInFromLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes ripple {
            from {
                transform: scale(0);
                opacity: 1;
            }
            to {
                transform: scale(2);
                opacity: 0;
            }
        }
        
        @keyframes sparkFly {
            0% { 
                transform: translate(0, 0) scale(1);
                opacity: 1;
            }
            100% { 
                transform: translate(20px, -20px) scale(0);
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
        
        /* Screen glitch effect - DISABLED */
        @keyframes screenGlitch {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(0); }
            40% { transform: translate(0); }
            60% { transform: translate(0); }
            80% { transform: translate(0); }
        }
        
        body.glitch-mode {
            /* Glitch animation disabled */
        }
    `;
    document.head.appendChild(style);
    
    // Random screen glitch on intense typing - DISABLED
    let glitchTimer;
    document.addEventListener('keydown', function() {
        clearTimeout(glitchTimer);
        glitchTimer = setTimeout(() => {
            // Glitch effect disabled
            if (Math.random() < -1) { // Never triggers
                document.body.classList.add('glitch-mode');
                setTimeout(() => {
                    document.body.classList.remove('glitch-mode');
                }, 300);
            }
        }, 100);
    });
}

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

// Coming soon notification
function showComingSoon() {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #FF4444, #FFD700);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        font-weight: 600;
        z-index: 9999;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    `;
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-rocket-takeoff"></i>
            <span>🚀 Progress tracking coming soon!</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
