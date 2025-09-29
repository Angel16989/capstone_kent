<?php
/**
 * Google OAuth 2.0 Configuration
 * PHP 8+ Compatible - L9 Fitness Gym
 */

declare(strict_types=1);

// Google OAuth Settings - Temporarily disabled for development
// To enable Google OAuth, get credentials from https://console.developers.google.com/
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');

// Auto-detect redirect URI
if (php_sapi_name() !== 'cli') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    if (strpos($host, 'ngrok') !== false) {
        define('GOOGLE_REDIRECT_URI', $protocol . $host . '/auth/google_callback.php');
    } else {
        define('GOOGLE_REDIRECT_URI', $protocol . $host . '/Capstone-latest/public/auth/google_callback.php');
    }
} else {
    define('GOOGLE_REDIRECT_URI', 'http://localhost/Capstone-latest/public/auth/google_callback.php');
}

// Google OAuth URLs
define('GOOGLE_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');

// OAuth Scopes
define('GOOGLE_SCOPES', [
    'https://www.googleapis.com/auth/userinfo.email',
    'https://www.googleapis.com/auth/userinfo.profile'
]);

/**
 * Generate Google OAuth login URL - DEMO VERSION
 * Redirects to our fake Google system
 */
function getGoogleAuthUrl(): string {
    // For demo purposes, redirect to our simple Google demo
    return BASE_URL . 'auth/simple_google_demo.php';
}

/**
 * Check if Google OAuth is properly configured - DEMO VERSION
 * Always returns true to show the fake Google login button
 */
function isGoogleOAuthConfigured(): bool {
    return true; // Always enabled for demo
}

/**
 * Exchange authorization code for access token
 */
function getGoogleAccessToken(string $code): ?array {
    $postData = [
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'code' => $code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => GOOGLE_REDIRECT_URI
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => GOOGLE_TOKEN_URL,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded'
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response !== false) {
        return json_decode($response, true);
    }
    
    return null;
}

/**
 * Get user info from Google
 */
function getGoogleUserInfo(string $accessToken): ?array {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => GOOGLE_USERINFO_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response !== false) {
        return json_decode($response, true);
    }
    
    return null;
}