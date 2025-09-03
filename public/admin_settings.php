<?php 
require_once __DIR__ . '/../config/config.php'; 
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/helpers/settings.php';

if (!is_admin()) { 
    http_response_code(403); 
    exit('Admins only.'); 
}

$pageTitle = "Admin Settings";
$pageCSS = "assets/css/admin.css";

$message = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST as $key => $value) {
            if ($key !== 'csrf_token') {
                update_setting($key, $value === 'on' ? '1' : '0');
            }
        }
        $message = '<div class="alert alert-success">Settings updated successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
}

// Get all current settings
$all_settings = get_all_settings();
$settings = [];
foreach ($all_settings as $setting) {
    $settings[$setting['setting_key']] = $setting;
}

include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="admin-header py-3 px-4 mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <span class="admin-title"><i class="bi bi-gear-fill"></i> Admin Settings</span>
            <span class="admin-subtitle">Control Visual Effects & System Behavior</span>
        </div>
        <a href="admin.php" class="btn btn-admin-warning">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="container">
    <?= $message ?>
    
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <!-- Visual Effects Section -->
        <div class="admin-card mb-4">
            <div class="admin-card-title"><i class="bi bi-eye"></i> Visual Effects</div>
            <p class="text-muted mb-4">Control the intensity and visual effects throughout the site</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="visual_effects" name="visual_effects" 
                               <?= (($settings['visual_effects']['setting_value'] ?? '0') === '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="visual_effects">
                            <strong>Master Visual Effects</strong><br>
                            <small class="text-muted">Enable/disable all visual effects at once</small>
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="shake_animation" name="shake_animation" 
                               <?= (($settings['shake_animation']['setting_value'] ?? '0') === '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="shake_animation">
                            <strong>Shake Animations</strong><br>
                            <small class="text-muted">Form validation shake effects</small>
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="screen_glitch" name="screen_glitch" 
                               <?= (($settings['screen_glitch']['setting_value'] ?? '0') === '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="screen_glitch">
                            <strong>Screen Glitch Effects</strong><br>
                            <small class="text-muted">Random screen glitch on typing</small>
                        </label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="typing_sparks" name="typing_sparks" 
                               <?= (($settings['typing_sparks']['setting_value'] ?? '0') === '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="typing_sparks">
                            <strong>Typing Spark Effects</strong><br>
                            <small class="text-muted">Spark animations when typing</small>
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="matrix_background" name="matrix_background" 
                               <?= (($settings['matrix_background']['setting_value'] ?? '0') === '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="matrix_background">
                            <strong>Matrix Background</strong><br>
                            <small class="text-muted">Matrix rain effect on focus</small>
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="password_strength_check" name="password_strength_check" 
                               <?= (($settings['password_strength_check']['setting_value'] ?? '1') === '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="password_strength_check">
                            <strong>Password Strength Check</strong><br>
                            <small class="text-muted">Real-time password validation</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- System Settings Section -->
        <div class="admin-card mb-4">
            <div class="admin-card-title"><i class="bi bi-cpu"></i> System Settings</div>
            <p class="text-muted mb-4">General system configuration options</p>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Note:</strong> Changes take effect immediately. Some effects may require page refresh.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-admin btn-lg">
                <i class="bi bi-check-circle"></i>
                Save Settings
            </button>
            
            <button type="button" class="btn btn-admin-danger btn-lg" onclick="resetToDefaults()">
                <i class="bi bi-arrow-clockwise"></i>
                Reset to Defaults
            </button>
        </div>
    </form>
</div>

<script>
function resetToDefaults() {
    if (confirm('Reset all settings to default values? This will turn off all visual effects.')) {
        // Uncheck all checkboxes except password_strength_check
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            if (checkbox.name === 'password_strength_check') {
                checkbox.checked = true;
            } else {
                checkbox.checked = false;
            }
        });
    }
}

// Master toggle functionality
document.getElementById('visual_effects').addEventListener('change', function() {
    const isEnabled = this.checked;
    const effectCheckboxes = ['shake_animation', 'screen_glitch', 'typing_sparks', 'matrix_background'];
    
    effectCheckboxes.forEach(id => {
        document.getElementById(id).checked = isEnabled;
    });
});
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
