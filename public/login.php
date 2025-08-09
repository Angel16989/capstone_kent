<?php require_once __DIR__ . '/../config/config.php'; require_once __DIR__ . '/../app/helpers/validator.php'; require_once __DIR__ . '/../app/helpers/csrf.php'; verify_csrf(); ?>
<?php
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
  if(!email_valid($email)||!not_empty($pass)){ $error='Enter a valid email and password.'; }
  else {
    $stmt=$pdo->prepare('SELECT * FROM users WHERE email=? LIMIT 1'); $stmt->execute([$email]); $u=$stmt->fetch();
    if($u && password_verify($pass,$u['password_hash'])){ require_once __DIR__.'/../app/helpers/auth.php'; login_user($u); header('Location: dashboard.php'); exit; }
    $error='Invalid credentials.';
  }
}
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>
<div class="container py-5" style="max-width:520px;">
  <h1 class="h3 mb-3">Login</h1>
  <?php if($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <form method="post"><?php echo csrf_field(); ?>
    <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
    <div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div>
    <button class="btn btn-primary">Login</button> <a class="btn btn-link" href="register.php">Create account</a>
  </form>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
