<?php
/**
 * Session Management System
 * Handles secure session configuration, timeouts, and automatic logout
 */

declare(strict_types=1);

class SessionManager {
    
    // Session timeout in seconds (30 minutes)
    private const SESSION_TIMEOUT = 1800;
    
    // Session regeneration interval (5 minutes)
    private const REGENERATION_INTERVAL = 300;
    
    public static function initialize(): void {
        // Don't start if session is already active
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        // Secure session configuration
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '0'); // Set to '1' for HTTPS
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_samesite', 'Lax');
        
        // Session timeout configuration
        ini_set('session.gc_maxlifetime', (string)self::SESSION_TIMEOUT);
        ini_set('session.cookie_lifetime', '0'); // Expire when browser closes
        
        // Start the session
        session_start();
        
        // Check and handle session validity
        self::validateSession();
    }
    
    private static function validateSession(): void {
        $now = time();
        
        // Check if session has timed out
        if (isset($_SESSION['last_activity'])) {
            $inactiveTime = $now - $_SESSION['last_activity'];
            
            if ($inactiveTime > self::SESSION_TIMEOUT) {
                self::destroySession();
                return;
            }
        }
        
        // Check if session should be regenerated (security measure)
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = $now;
        } elseif ($now - $_SESSION['created'] > self::REGENERATION_INTERVAL) {
            session_regenerate_id(true);
            $_SESSION['created'] = $now;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = $now;
        
        // Validate session fingerprint (prevent session hijacking)
        $fingerprint = self::generateFingerprint();
        if (!isset($_SESSION['fingerprint'])) {
            $_SESSION['fingerprint'] = $fingerprint;
        } elseif ($_SESSION['fingerprint'] !== $fingerprint) {
            // Potential session hijacking - destroy session
            self::destroySession();
            return;
        }
    }
    
    private static function generateFingerprint(): string {
        return hash('sha256', 
            ($_SERVER['HTTP_USER_AGENT'] ?? '') . 
            ($_SERVER['REMOTE_ADDR'] ?? '') .
            ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
        );
    }
    
    public static function destroySession(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Clear all session variables
            $_SESSION = [];
            
            // Delete the session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            
            // Destroy the session
            session_destroy();
        }
    }
    
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user']) && isset($_SESSION['last_activity']);
    }
    
    public static function getSessionTimeout(): int {
        return self::SESSION_TIMEOUT;
    }
    
    public static function getRemainingTime(): int {
        if (!isset($_SESSION['last_activity'])) {
            return 0;
        }
        
        $elapsed = time() - $_SESSION['last_activity'];
        return max(0, self::SESSION_TIMEOUT - $elapsed);
    }
    
    public static function extendSession(): void {
        $_SESSION['last_activity'] = time();
    }
    
    public static function isSessionExpired(): bool {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }
        
        return (time() - $_SESSION['last_activity']) > self::SESSION_TIMEOUT;
    }
}
?>