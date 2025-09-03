<?php
// test_registration_simple.php - Simple Registration Test
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔥 REGISTRATION TEST - SIMPLIFIED 🔥</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once __DIR__ . '/config/config.php';
        require_once __DIR__ . '/app/helpers/validator.php';
        require_once __DIR__ . '/app/helpers/auth.php';
        
        echo "<h3>✅ Files loaded successfully</h3>";
        
        // Get form data
        $full_name = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        echo "<p><strong>Form Data:</strong></p>";
        echo "<ul>";
        echo "<li>Name: " . htmlspecialchars($full_name) . "</li>";
        echo "<li>Email: " . htmlspecialchars($email) . "</li>";
        echo "<li>Password Length: " . strlen($password) . " chars</li>";
        echo "<li>Passwords Match: " . ($password === $confirm_password ? 'YES' : 'NO') . "</li>";
        echo "</ul>";
        
        // Validate
        $errors = [];
        
        if (empty($full_name)) {
            $errors[] = "Full name is required";
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
        
        if ($password !== $confirm_password) {
            $errors[] = "Passwords must match";
        }
        
        if (!empty($errors)) {
            echo "<h3>❌ Validation Errors:</h3><ul>";
            foreach ($errors as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<h3>✅ Validation passed, creating account...</h3>";
            
            // Check if user exists
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo "<p>❌ Email already exists</p>";
            } else {
                // Create user
                $name_parts = explode(' ', $full_name, 2);
                $first_name = $name_parts[0];
                $last_name = $name_parts[1] ?? '';
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare('
                    INSERT INTO users (role_id, first_name, last_name, email, password_hash, status, created_at) 
                    VALUES (4, ?, ?, ?, ?, "active", NOW())
                ');
                
                if ($stmt->execute([$first_name, $last_name, $email, $password_hash])) {
                    $user_id = $pdo->lastInsertId();
                    
                    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
                    echo "<h2>🎉 SUCCESS! Account Created!</h2>";
                    echo "<p><strong>User ID:</strong> {$user_id}</p>";
                    echo "<p><strong>Name:</strong> {$first_name} {$last_name}</p>";
                    echo "<p><strong>Email:</strong> {$email}</p>";
                    echo "<p><a href='public/login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a></p>";
                    echo "</div>";
                } else {
                    echo "<p>❌ Failed to create user in database</p>";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3>❌ Error:</h3>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
        echo "</div>";
    }
} else {
    ?>
    <div style="max-width: 500px; margin: 0 auto; background: #1a1a1a; padding: 30px; border-radius: 15px; border: 2px solid #FF4444;">
        <form method="post" style="color: white;">
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #FFD700; font-weight: bold; margin-bottom: 5px;">Full Name:</label>
                <input type="text" name="full_name" required style="width: 100%; padding: 10px; border: 2px solid #FFD700; border-radius: 5px; background: #2d2d2d; color: white;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #FFD700; font-weight: bold; margin-bottom: 5px;">Email:</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border: 2px solid #FFD700; border-radius: 5px; background: #2d2d2d; color: white;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #FFD700; font-weight: bold; margin-bottom: 5px;">Password:</label>
                <input type="password" name="password" required minlength="8" style="width: 100%; padding: 10px; border: 2px solid #FFD700; border-radius: 5px; background: #2d2d2d; color: white;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #FFD700; font-weight: bold; margin-bottom: 5px;">Confirm Password:</label>
                <input type="password" name="confirm_password" required minlength="8" style="width: 100%; padding: 10px; border: 2px solid #FFD700; border-radius: 5px; background: #2d2d2d; color: white;">
            </div>
            
            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #FF4444, #cc0000); color: white; padding: 15px; border: none; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;">
                🚀 CREATE ACCOUNT
            </button>
        </form>
    </div>
    <?php
}
?>

<style>
body {
    background: linear-gradient(135deg, #1a1a1a, #0a0a0a);
    color: white;
    font-family: 'Courier New', monospace;
    margin: 0;
    padding: 20px;
    min-height: 100vh;
}

button:hover {
    background: linear-gradient(135deg, #cc0000, #FF4444) !important;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 68, 68, 0.3);
    transition: all 0.3s ease;
}
</style>
