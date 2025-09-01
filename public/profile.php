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

<!-- Profile Hero Section -->
<div class="profile-hero py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="text-center mb-5">
          <div class="profile-badge mb-3">
            <i class="bi bi-person-circle"></i>
            Beast Profile
          </div>
          <h1 class="display-4 fw-bold text-gradient">Forge Your Identity</h1>
          <p class="lead">Update your warrior profile and beast credentials</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Profile Form Section -->
<div class="container pb-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <?php if($message): ?>
        <div class="alert alert-success alert-modern mb-4">
          <i class="bi bi-check-circle-fill me-2"></i>
          <?php echo $message; ?>
        </div>
      <?php endif; ?>
      
      <div class="profile-card">
        <div class="card-header-modern">
          <h3 class="h4 text-primary mb-0">Personal Information</h3>
          <p class="text-muted mb-0">Keep your profile information up to date</p>
        </div>
        
        <form method="post" class="profile-form">
          <div class="row g-4">
            <!-- Read-only Fields -->
            <div class="col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="firstName" 
                       value="<?php echo htmlspecialchars($row['first_name']); ?>" disabled>
                <label for="firstName">
                  <i class="bi bi-person me-2"></i>First Name
                </label>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="lastName" 
                       value="<?php echo htmlspecialchars($row['last_name']); ?>" disabled>
                <label for="lastName">
                  <i class="bi bi-person me-2"></i>Last Name
                </label>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="form-floating">
                <input type="email" class="form-control" id="email" 
                       value="<?php echo htmlspecialchars($row['email']); ?>" disabled>
                <label for="email">
                  <i class="bi bi-envelope me-2"></i>Email Address
                </label>
              </div>
            </div>
            
            <!-- Editable Fields -->
            <div class="col-md-6">
              <div class="form-floating">
                <input type="tel" class="form-control" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>">
                <label for="phone">
                  <i class="bi bi-telephone me-2"></i>Phone Number
                </label>
              </div>
            </div>
            
            <div class="col-12">
              <div class="form-floating">
                <input type="text" class="form-control" id="address" name="address" 
                       value="<?php echo htmlspecialchars($row['address'] ?? ''); ?>">
                <label for="address">
                  <i class="bi bi-geo-alt me-2"></i>Address
                </label>
              </div>
            </div>
            
            <div class="col-12">
              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="bi bi-check-circle me-2"></i>
                  Save Changes
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
