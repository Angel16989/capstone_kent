<?php
session_start();

echo "<h2>Session Test</h2>";

// Set a test value
if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = 'Session is working!';
}

echo "Session ID: " . session_id() . "<br>";
echo "Test value: " . ($_SESSION['test'] ?? 'NOT SET') . "<br>";

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "CSRF token: " . substr($_SESSION['csrf_token'], 0, 20) . "...<br>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<br><strong>POST Data:</strong><br>";
    echo "POST csrf_token: " . ($_POST['csrf_token'] ?? 'NOT SET') . "<br>";
    echo "SESSION csrf_token: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "<br>";
    echo "Tokens match: " . (($_POST['csrf_token'] ?? '') === ($_SESSION['csrf_token'] ?? '') ? 'YES' : 'NO') . "<br>";
}
?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="text" name="test_input" placeholder="Test input" required>
    <button type="submit">Test Form</button>
</form>
