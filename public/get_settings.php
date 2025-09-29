<?php
// get_settings.php - API endpoint to get current settings
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/settings.php';

header('Content-Type: application/json');

try {
    $all_settings = get_all_settings();
    $settings = [];
    
    foreach ($all_settings as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
    
    // Set defaults if settings don't exist
    $defaults = [
        'visual_effects' => '0',
        'shake_animation' => '0',
        'screen_glitch' => '0',
        'typing_sparks' => '0',
        'matrix_background' => '0',
        'password_strength_check' => '1'
    ];
    
    foreach ($defaults as $key => $default) {
        if (!isset($settings[$key])) {
            $settings[$key] = $default;
        }
    }
    
    echo json_encode($settings);
    
} catch (Exception $e) {
    // Return safe defaults on error
    echo json_encode([
        'visual_effects' => '0',
        'shake_animation' => '0',
        'screen_glitch' => '0',
        'typing_sparks' => '0',
        'matrix_background' => '0',
        'password_strength_check' => '1'
    ]);
}
?>
