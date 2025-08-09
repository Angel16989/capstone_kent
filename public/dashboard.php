<?php require_once __DIR__ . '/../config/config.php'; require_once __DIR__ . '/../app/helpers/auth.php'; require_login(); ?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>
<div class="container py-5">
  <h1 class="h3 mb-4">Welcome, <?php echo htmlspecialchars(current_user()['name']); ?>!</h1>
  <div class="row g-4">
    <div class="col-md-4"><div class="card shadow-sm h-100"><div class="card-body">
      <h5 class="card-title">Membership</h5><p class="card-text">See your current plan and renewals.</p>
      <a class="btn btn-outline-primary btn-sm" href="memberships.php">Manage</a>
    </div></div></div>
    <div class="col-md-4"><div class="card shadow-sm h-100"><div class="card-body">
      <h5 class="card-title">Bookings</h5><p class="card-text">View and book classes.</p>
      <a class="btn btn-outline-primary btn-sm" href="classes.php">Browse</a>
    </div></div></div>
    <div class="col-md-4"><div class="card shadow-sm h-100"><div class="card-body">
      <h5 class="card-title">Profile</h5><p class="card-text">Update your details.</p>
      <a class="btn btn-outline-primary btn-sm" href="profile.php">Edit</a>
    </div></div></div>
  </div>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
