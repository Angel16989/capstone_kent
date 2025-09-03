<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/csrf.php';

echo "Session CSRF token: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "<br>";
echo "Generated CSRF token: " . csrf_token() . "<br>";
echo "Session CSRF token after generation: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "<br>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<br>POST data:<br>";
    echo "csrf_token from form: " . ($_POST['csrf_token'] ?? 'NOT SET') . "<br>";
    echo "Session csrf_token: " . ($_SESSION['csrf_token'] ?? 'NOT SET') . "<br>";
    echo "Do they match? " . (($_POST['csrf_token'] ?? '') === ($_SESSION['csrf_token'] ?? '') ? 'YES' : 'NO') . "<br>";
    
    try {
        verify_csrf();
        echo "CSRF verification: PASSED";
    } catch (Exception $e) {
        echo "CSRF verification: FAILED - " . $e->getMessage();
    }
}
?>

<form method="post">
    <?= csrf_field(); ?>
    <button type="submit">Test CSRF</button>
</form>
