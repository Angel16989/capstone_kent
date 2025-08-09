<?php require_once __DIR__ . '/../config/config.php'; require_once __DIR__ . '/../app/helpers/auth.php'; if(!is_admin()) { http_response_code(403); exit('Admins only.'); } ?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>
<div class="container py-5"><h1 class="h3 mb-4">Admin Dashboard (Demo)</h1>
<p>Extend this page to manage users, classes, plans, and reports.</p></div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
