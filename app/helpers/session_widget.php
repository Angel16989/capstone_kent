<?php
/**
 * Session Status Widget
 * Shows session timeout and auto-logout warning
 */

if (!function_exists('renderSessionStatus')) {
    function renderSessionStatus(): string {
        if (!SessionManager::isLoggedIn()) {
            return '';
        }
        
        $remainingTime = SessionManager::getRemainingTime();
        $remainingMinutes = floor($remainingTime / 60);
        $remainingSeconds = $remainingTime % 60;
        
        $isWarning = $remainingTime <= 300; // Warning when 5 minutes or less
        $alertClass = $isWarning ? 'alert-warning' : 'alert-info';
        
        ob_start();
        ?>
        <div id="session-status" class="session-status-widget d-none d-lg-block">
            <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show mb-0" role="alert">
                <small>
                    <i class="fas fa-clock"></i>
                    Session expires in: <span id="session-timer"><?php echo sprintf('%02d:%02d', $remainingMinutes, $remainingSeconds); ?></span>
                    <?php if ($isWarning): ?>
                        <strong class="ms-2">⚠️ Auto-logout soon!</strong>
                    <?php endif; ?>
                </small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        
        <script>
        // Session timer countdown
        let sessionTimeRemaining = <?php echo $remainingTime; ?>;
        let sessionWarningShown = false;
        
        function updateSessionTimer() {
            sessionTimeRemaining--;
            
            if (sessionTimeRemaining <= 0) {
                // Session expired - redirect to login
                alert('Your session has expired. Please log in again.');
                window.location.href = 'login.php';
                return;
            }
            
            // Update display
            const minutes = Math.floor(sessionTimeRemaining / 60);
            const seconds = sessionTimeRemaining % 60;
            const timerElement = document.getElementById('session-timer');
            
            if (timerElement) {
                timerElement.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            }
            
            // Show warning when 5 minutes remaining
            if (sessionTimeRemaining <= 300 && !sessionWarningShown) {
                sessionWarningShown = true;
                const statusWidget = document.getElementById('session-status');
                if (statusWidget) {
                    statusWidget.querySelector('.alert').classList.remove('alert-info');
                    statusWidget.querySelector('.alert').classList.add('alert-warning');
                    statusWidget.querySelector('.alert').innerHTML = 
                        '<small><i class="fas fa-exclamation-triangle"></i> Session expires in: <span id="session-timer">' + 
                        String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0') + 
                        '</span> <strong class="ms-2">⚠️ Auto-logout soon!</strong></small>' +
                        '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>';
                }
                
                // Show browser notification if supported
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('L9 Fitness - Session Warning', {
                        body: 'Your session will expire in 5 minutes. Click here to stay logged in.',
                        icon: '/favicon.ico'
                    });
                }
            }
            
            // Final warning at 1 minute
            if (sessionTimeRemaining === 60) {
                if (confirm('Your session will expire in 1 minute. Do you want to extend your session?')) {
                    // Extend session by making a request
                    fetch('session_extend.php', {method: 'POST'})
                        .then(() => {
                            sessionTimeRemaining = <?php echo SessionManager::getSessionTimeout(); ?>;
                            sessionWarningShown = false;
                            const statusWidget = document.getElementById('session-status');
                            if (statusWidget) {
                                statusWidget.querySelector('.alert').classList.remove('alert-warning');
                                statusWidget.querySelector('.alert').classList.add('alert-info');
                            }
                        })
                        .catch(() => {
                            alert('Failed to extend session. Please save your work.');
                        });
                }
            }
        }
        
        // Update timer every second
        setInterval(updateSessionTimer, 1000);
        
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
        </script>
        
        <style>
        .session-status-widget {
            position: fixed;
            top: 70px;
            right: 20px;
            z-index: 1050;
            max-width: 300px;
        }
        
        .session-status-widget .alert {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .session-status-widget .alert-info {
            background: rgba(13, 202, 240, 0.1);
            border-color: rgba(13, 202, 240, 0.3);
            color: #0dcaf0;
        }
        
        .session-status-widget .alert-warning {
            background: rgba(255, 193, 7, 0.1);
            border-color: rgba(255, 193, 7, 0.3);
            color: #ffc107;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        @media (max-width: 991px) {
            .session-status-widget {
                display: none !important;
            }
        }
        </style>
        <?php
        return ob_get_clean();
    }
}
?>