// dynamic-effects.js - Apply visual effects based on admin settings
document.addEventListener('DOMContentLoaded', function() {
    // Check if we should load effects
    checkEffectsSettings();
});

function checkEffectsSettings() {
    // Make an AJAX call to check current settings
    fetch('get_settings.php')
        .then(response => response.json())
        .then(settings => {
            applyEffectsBasedOnSettings(settings);
        })
        .catch(error => {
            console.log('Using default settings (effects disabled)');
            // Default to disabled effects
        });
}

function applyEffectsBasedOnSettings(settings) {
    // Apply or remove shake animations
    if (settings.shake_animation === '1') {
        enableShakeAnimations();
    } else {
        disableShakeAnimations();
    }
    
    // Apply or remove screen glitch effects
    if (settings.screen_glitch === '1') {
        enableScreenGlitch();
    } else {
        disableScreenGlitch();
    }
    
    // Apply or remove typing sparks
    if (settings.typing_sparks === '1') {
        enableTypingSparks();
    } else {
        disableTypingSparks();
    }
    
    // Apply or remove matrix background
    if (settings.matrix_background === '1') {
        enableMatrixBackground();
    } else {
        disableMatrixBackground();
    }
}

function enableShakeAnimations() {
    const style = document.createElement('style');
    style.id = 'shake-animation-styles';
    style.textContent = `
        .form-control.is-invalid {
            animation: shake 0.5s ease-in-out !important;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    `;
    document.head.appendChild(style);
}

function disableShakeAnimations() {
    const existingStyle = document.getElementById('shake-animation-styles');
    if (existingStyle) {
        existingStyle.remove();
    }
}

function enableScreenGlitch() {
    const style = document.createElement('style');
    style.id = 'screen-glitch-styles';
    style.textContent = `
        @keyframes screenGlitch {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
        }
        
        body.glitch-mode {
            animation: screenGlitch 0.3s ease-in-out !important;
        }
    `;
    document.head.appendChild(style);
    
    // Re-enable glitch event listener
    document.removeEventListener('keydown', disabledGlitchHandler);
    document.addEventListener('keydown', enabledGlitchHandler);
}

function disableScreenGlitch() {
    const existingStyle = document.getElementById('screen-glitch-styles');
    if (existingStyle) {
        existingStyle.remove();
    }
    
    // Remove glitch event listener
    document.removeEventListener('keydown', enabledGlitchHandler);
    document.addEventListener('keydown', disabledGlitchHandler);
}

// Glitch handlers
let glitchTimer;

function enabledGlitchHandler() {
    clearTimeout(glitchTimer);
    glitchTimer = setTimeout(() => {
        if (Math.random() < 0.05) { // 5% chance
            document.body.classList.add('glitch-mode');
            setTimeout(() => {
                document.body.classList.remove('glitch-mode');
            }, 300);
        }
    }, 100);
}

function disabledGlitchHandler() {
    // Do nothing - disabled
}

function enableTypingSparks() {
    window.typingSparksEnabled = true;
}

function disableTypingSparks() {
    window.typingSparksEnabled = false;
}

function enableMatrixBackground() {
    window.matrixBackgroundEnabled = true;
}

function disableMatrixBackground() {
    window.matrixBackgroundEnabled = false;
    
    // Remove existing matrix if active
    const existingMatrix = document.getElementById('matrix-bg');
    if (existingMatrix) {
        existingMatrix.remove();
    }
}
