<?php require_once __DIR__ . '/../config/config.php'; require_once __DIR__ . '/../app/helpers/auth.php'; require_once __DIR__ . '/../app/helpers/validator.php'; require_login(); ?>
<?php
$u=current_user(); $message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $phone=sanitize($_POST['phone']??''); $address=sanitize($_POST['address']??'');
  $stmt=$pdo->prepare('UPDATE users SET phone=?, address=? WHERE id=?'); $stmt->execute([$phone,$address,$u['id']]); $message='Profile updated.';
}
$stmt=$pdo->prepare('SELECT * FROM users WHERE id=?'); $stmt->execute([$u['id']]); $row=$stmt->fetch();
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>
<div class="container py-5">
  <h1 class="h3 mb-3">Your Profile</h1>
  <?php if($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
  <form method="post"><div class="row g-3" style="max-width:700px;">
    <div class="col-md-6"><label class="form-label">First name</label><input class="form-control" value="<?php echo htmlspecialchars($row['first_name']); ?>" disabled></div>
    <div class="col-md-6"><label class="form-label">Last name</label><input class="form-control" value="<?php echo htmlspecialchars($row['last_name']); ?>" disabled></div>
    <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" disabled></div>
    <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>"></div>
    <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="address" value="<?php echo htmlspecialchars($row['address'] ?? ''); ?>"></div>
    <div class="col-12"><button class="btn btn-primary">Save</button></div>
  </div></form>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
