<?php include 'includes/header.php'; ?>

<section class="hero text-center text-white d-flex align-items-center" style="background: url('assets/images/gym-hero.jpg') center/cover; height: 90vh;">
  <div class="container">
    <h1 class="display-4">Welcome to L9 Fitness Gym</h1>
    <p class="lead">No Judgments. Just Progress. Your journey starts here.</p>
    <a href="auth/register.php" class="btn btn-warning btn-lg mt-4">Join Now</a>
  </div>
</section>

<section class="py-5 bg-light text-center">
  <div class="container">
    <h2 class="mb-4">Why Choose Us</h2>
    <div class="row">
      <div class="col-md-4">
        <i class="bi bi-bar-chart-fill fs-1 text-warning"></i>
        <h5 class="mt-3">Flexible Membership</h5>
        <p>No lock-in contract, just real results and low fees.</p>
      </div>
      <div class="col-md-4">
        <i class="bi bi-people-fill fs-1 text-warning"></i>
        <h5 class="mt-3">Top Trainers</h5>
        <p>Certified trainers with real motivation for your goals.</p>
      </div>
      <div class="col-md-4">
        <i class="bi bi-clock-history fs-1 text-warning"></i>
        <h5 class="mt-3">Open 24/7</h5>
        <p>Workout on your own schedule, anytime.</p>
      </div>
    </div>
  </div>
</section>

<section class="text-center py-5">
  <div class="container">
    <h2 class="mb-4">Start Your Fitness Journey</h2>
    <a href="auth/login.php" class="btn btn-outline-warning btn-lg">Login</a>
    <a href="auth/register.php" class="btn btn-warning btn-lg ms-2">Register</a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
