<?php
function csrf_token(): string { if (empty($_SESSION['csrf'])) { $_SESSION['csrf']=bin2hex(random_bytes(32)); } return $_SESSION['csrf']; }
function csrf_field(): string { $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); return '<input type="hidden" name="csrf" value="'.$t.'"/>'; }
function verify_csrf(): void { if ($_SERVER['REQUEST_METHOD']==='POST'){ $ok=isset($_POST['csrf']) && hash_equals($_SESSION['csrf']??'', $_POST['csrf']); if(!$ok){ http_response_code(403); exit('Invalid CSRF token'); } } }
