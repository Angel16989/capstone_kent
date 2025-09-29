<?php
/**
 * PayPal Payment Configuration
 * PHP 8+ Compatible - L9 Fitness Gym
 */

declare(strict_types=1);

// PayPal Settings - Change to live for production
define('PAYPAL_ENVIRONMENT', 'sandbox'); // 'sandbox' or 'live'

// PayPal API Credentials - Replace with your actual credentials
if (PAYPAL_ENVIRONMENT === 'sandbox') {
    define('PAYPAL_CLIENT_ID', 'your-sandbox-client-id');
    define('PAYPAL_CLIENT_SECRET', 'your-sandbox-client-secret');
    define('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com');
    define('PAYPAL_WEB_URL', 'https://www.sandbox.paypal.com');
} else {
    define('PAYPAL_CLIENT_ID', 'your-live-client-id');
    define('PAYPAL_CLIENT_SECRET', 'your-live-client-secret');
    define('PAYPAL_BASE_URL', 'https://api-m.paypal.com');
    define('PAYPAL_WEB_URL', 'https://www.paypal.com');
}

// PayPal Currency
define('PAYPAL_CURRENCY', 'USD');

/**
 * Get PayPal access token
 */
function getPayPalAccessToken(): ?string {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => PAYPAL_BASE_URL . '/v1/oauth2/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_USERPWD => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Accept-Language: en_US',
            'Content-Type: application/x-www-form-urlencoded'
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response !== false) {
        $tokenData = json_decode($response, true);
        return $tokenData['access_token'] ?? null;
    }
    
    return null;
}

/**
 * Create PayPal payment
 */
function createPayPalPayment(array $orderData): ?array {
    $accessToken = getPayPalAccessToken();
    if (!$accessToken) {
        return null;
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => PAYPAL_BASE_URL . '/v2/checkout/orders',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($orderData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'PayPal-Request-Id: ' . uniqid('L9_', true)
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 201 && $response !== false) {
        return json_decode($response, true);
    }
    
    return null;
}

/**
 * Capture PayPal payment
 */
function capturePayPalPayment(string $orderId): ?array {
    $accessToken = getPayPalAccessToken();
    if (!$accessToken) {
        return null;
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => PAYPAL_BASE_URL . '/v2/checkout/orders/' . $orderId . '/capture',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => '{}',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 201 && $response !== false) {
        return json_decode($response, true);
    }
    
    return null;
}

/**
 * Verify PayPal payment
 */
function verifyPayPalPayment(string $orderId): ?array {
    $accessToken = getPayPalAccessToken();
    if (!$accessToken) {
        return null;
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => PAYPAL_BASE_URL . '/v2/checkout/orders/' . $orderId,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
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