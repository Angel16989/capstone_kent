<?php include '../includes/header.php'; ?>
<div class="container py-5">
  <h2 class="text-center mb-4">Create Your Account</h2>
  <form action="register_process.php" method="POST" class="col-md-6 offset-md-3">
    <div class="mb-3">
      <label>Full Name</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Email Address</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Confirm Password</label>
      <input type="password" name="confirm_password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-warning w-100">Register</button>
    <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a></p>
  </form>
</div>
<?php include '../includes/footer.php'; ?>
