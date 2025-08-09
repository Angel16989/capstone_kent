<?php require_once __DIR__ . '/../config/config.php'; require_once __DIR__ . '/../app/helpers/validator.php'; require_once __DIR__ . '/../app/helpers/csrf.php'; verify_csrf(); ?>
<?php
$error=''; $ok=false;
if($_SERVER['REQUEST_METHOD']==='POST'){
  $first=trim($_POST['first_name']??''); $last=trim($_POST['last_name']??''); $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
  if(!not_empty($first)||!not_empty($last)||!email_valid($email)||strlen($pass)<6){ $error='Fill all fields (password 6+ chars).'; }
  else { $hash=password_hash($pass,PASSWORD_DEFAULT); try{
      $stmt=$pdo->prepare('INSERT INTO users (role_id, first_name, last_name, email, password_hash) VALUES (4,?,?,?,?)');
      $stmt->execute([$first,$last,$email,$hash]); $ok=true;
    }catch(PDOException $e){ $error='Email already used.'; } }
}
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>
<div class="container py-5" style="max-width:640px;">
  <h1 class="h3 mb-3">Create your account</h1>
  <?php if($ok): ?><div class="alert alert-success">Registration successful. <a href="login.php">Login</a></div><?php endif; ?>
  <?php if($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <form method="post"><?php echo csrf_field(); ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">First name</label><input class="form-control" type="text" name="first_name" required></div>
      <div class="col-md-6"><label class="form-label">Last name</label><input class="form-control" type="text" name="last_name" required></div>
      <div class="col-12"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
      <div class="col-12"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div>
    </div>
    <button class="btn btn-primary mt-3">Create Account</button>
  </form>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
