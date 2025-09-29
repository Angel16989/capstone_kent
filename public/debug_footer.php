<!DOCTYPE html>
<html>
<head>
    <title>Footer Debug - L9 Fitness</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #111; color: #fff; }
        .debug-section { background: #222; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { color: #ff4444; }
        .success { color: #00ff00; }
        .warning { color: #ffaa00; }
    </style>
</head>
<body>
    <h1>🔧 L9 Fitness Footer Diagnostic</h1>
    
    <div class="debug-section">
        <h3>Footer File Check</h3>
        <?php
        $footerPath = '../app/views/layouts/footer.php';
        if (file_exists($footerPath)) {
            echo "<p class='success'>✅ Footer file exists: $footerPath</p>";
            echo "<p>File size: " . filesize($footerPath) . " bytes</p>";
        } else {
            echo "<p class='error'>❌ Footer file missing: $footerPath</p>";
        }
        ?>
    </div>
    
    <div class="debug-section">
        <h3>CSS Files Check</h3>
        <?php
        $cssFiles = [
            'assets/css/universal-footer.css',
            'assets/css/main.css'
        ];
        
        foreach ($cssFiles as $css) {
            if (file_exists($css)) {
                echo "<p class='success'>✅ CSS exists: $css</p>";
            } else {
                echo "<p class='error'>❌ CSS missing: $css</p>";
            }
        }
        ?>
    </div>
    
    <div class="debug-section">
        <h3>Profile.php Footer Section</h3>
        <?php
        $profileContent = file_get_contents('profile.php');
        if (strpos($profileContent, 'include __DIR__ . \'/../app/views/layouts/footer.php\'') !== false) {
            echo "<p class='success'>✅ Footer include found in profile.php</p>";
        } else {
            echo "<p class='error'>❌ Footer include missing in profile.php</p>";
        }
        
        if (strpos($profileContent, '<main>') !== false) {
            echo "<p class='success'>✅ Main tag found in profile.php</p>";
        } else {
            echo "<p class='warning'>⚠️ Main tag missing in profile.php</p>";
        }
        ?>
    </div>
    
    <div class="debug-section">
        <h3>Footer Preview</h3>
        <iframe src="profile.php" width="100%" height="400" style="border: 1px solid #444; border-radius: 5px;"></iframe>
    </div>
    
    <div class="debug-section">
        <h3>Quick Fixes</h3>
        <button onclick="window.open('profile.php', '_blank')" style="background: #ff4444; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
            🔗 Open Profile Page
        </button>
        <button onclick="location.reload()" style="background: #00aa00; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            🔄 Refresh Diagnostic
        </button>
    </div>
</body>
</html>