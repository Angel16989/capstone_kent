<?php
// settings.php - Helper functions for site settings
function get_setting($key, $default = null) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        
        return $result !== false ? $result : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function update_setting($key, $value) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()');
        return $stmt->execute([$key, $value, $value]);
    } catch (Exception $e) {
        return false;
    }
}

function get_all_settings() {
    global $pdo;
    
    try {
        $stmt = $pdo->query('SELECT setting_key, setting_value, description FROM site_settings ORDER BY setting_key');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Check if visual effects are enabled
function visual_effects_enabled() {
    return get_setting('visual_effects', '0') === '1';
}

function screen_glitch_enabled() {
    return get_setting('screen_glitch', '0') === '1';
}

function shake_animation_enabled() {
    return get_setting('shake_animation', '0') === '1';
}

function typing_sparks_enabled() {
    return get_setting('typing_sparks', '0') === '1';
}

function matrix_background_enabled() {
    return get_setting('matrix_background', '0') === '1';
}
