<?php
// test_register.php - Registration debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Registration Test</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Processing Registration...</h3>";
    
    try {
        require_once __DIR__ . '/config/config.php';
        require_once __DIR__ . '/app/helpers/validator.php';
        
        echo "✅ Config and helpers loaded<br>";
        
        // Debug POST data
        echo "<strong>POST Data:</strong><br>";
        foreach ($_POST as $key => $value) {
            if ($key === 'password' || $key === 'confirm_password') {
                echo "&nbsp;&nbsp;{$key}: [" . strlen($value) . " characters]<br>";
            } else {
                echo "&nbsp;&nbsp;{$key}: " . htmlspecialchars($value) . "<br>";
            }
        }
        echo "<br>";
        
        // CSRF Check
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Invalid CSRF token');
        }
        echo "✅ CSRF token valid<br>";
        
        // Get form data
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $accept = isset($_POST['accept_terms']);
        
        echo "✅ Form data extracted<br>";
        
        // Validation
        $errors = [];
        
        if (!not_empty($full_name)) {
            $errors['full_name'] = 'Full name is required.';
        }
        echo "✅ Name validation: " . ($errors['full_name'] ?? 'PASSED') . "<br>";
        
        if (!email_valid($email)) {
            $errors['email'] = 'Enter a valid email.';
        }
        echo "✅ Email validation: " . ($errors['email'] ?? 'PASSED') . "<br>";
        
        if (!not_empty($password) || strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        echo "✅ Password validation: " . ($errors['password'] ?? 'PASSED') . "<br>";
        
        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }
        echo "✅ Password match: " . ($errors['confirm_password'] ?? 'PASSED') . "<br>";
        
        if (!$accept) {
            $errors['accept_terms'] = 'You must accept the Terms and Privacy Policy.';
        }
        echo "✅ Terms acceptance: " . ($errors['accept_terms'] ?? 'PASSED') . "<br>";
        
        // Check existing user
        if (!$errors) {
            $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors['email'] = 'An account with this email already exists.';
            }
            echo "✅ Existing user check: " . ($errors['email'] ?? 'PASSED') . "<br>";
        }
        
        // Create account
        if (!$errors) {
            echo "<h4>Creating Account...</h4>";
            
            $hash = password_hash($password, PASSWORD_DEFAULT);
            echo "✅ Password hashed<br>";
            
            // Split full name into first and last name
            $name_parts = explode(' ', $full_name, 2);
            $first_name = $name_parts[0];
            $last_name = $name_parts[1] ?? '';
            echo "✅ Name split: '{$first_name}' + '{$last_name}'<br>";
            
            $stmt = $pdo->prepare('
              INSERT INTO users (role_id, first_name, last_name, email, password_hash, created_at)
              VALUES (4, ?, ?, ?, ?, NOW())
            ');
            
            echo "✅ Prepared INSERT statement<br>";
            
            $result = $stmt->execute([$first_name, $last_name, $email, $hash]);
            echo "✅ Insert executed: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";
            
            $id = (int)$pdo->lastInsertId();
            echo "✅ New user ID: {$id}<br>";
            
            if ($id > 0) {
                $stmt = $pdo->prepare('SELECT id, email, first_name, last_name, password_hash, role_id FROM users WHERE id = ?');
                $stmt->execute([$id]);
                $u = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($u) {
                    echo "✅ User data retrieved<br>";
                    
                    require_once __DIR__ . '/app/helpers/auth.php';
                    login_user($u);
                    echo "✅ User logged in<br>";
                    
                    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
                    echo "<strong>🎉 SUCCESS!</strong><br>";
                    echo "Account created successfully for: {$email}<br>";
                    echo "User ID: {$id}<br>";
                    echo "Name: {$first_name} {$last_name}<br>";
                    echo "<a href='public/dashboard.php'>Go to Dashboard</a>";
                    echo "</div>";
                } else {
                    throw new Exception('Failed to retrieve created user');
                }
            } else {
                throw new Exception('Failed to create user - no ID returned');
            }
        } else {
            echo "<h4>❌ Validation Errors:</h4>";
            foreach ($errors as $field => $error) {
                echo "&nbsp;&nbsp;<strong>{$field}:</strong> {$error}<br>";
            }
        }
        
    } catch (Throwable $e) {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;'>";
        echo "<strong>❌ ERROR:</strong> " . $e->getMessage() . "<br>";
        echo "<strong>File:</strong> " . $e->getFile() . "<br>";
        echo "<strong>Line:</strong> " . $e->getLine() . "<br>";
        echo "<strong>Stack Trace:</strong><br><pre style='font-size: 12px;'>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    }
} else {
    // Show test form
    session_start();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    ?>
    <form method="post" style="max-width: 500px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <div style="margin-bottom: 15px;">
            <label for="full_name">Full Name:</label><br>
            <input type="text" id="full_name" name="full_name" value="Test User" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" value="test<?= time() ?>@test.com" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" value="testpassword123" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="confirm_password">Confirm Password:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" value="testpassword123" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label>
                <input type="checkbox" name="accept_terms" value="1" checked> I accept the terms
            </label>
        </div>
        
        <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
            Test Registration
        </button>
    </form>
    <?php
}
?>
