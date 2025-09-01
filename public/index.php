<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pageTitle = "Home";
$pageCSS = "assets/css/home.css";
include __DIR__ . '/../app/views/layouts/header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
  <div class="container py-5">
    <div class="row align-items-center min-vh-75">
      <div class="col-lg-6">
        <div class="hero-content animate-fadeInUp">
          <div class="hero-badge mb-4">
            <svg width="20" height="20" fill="currentColor" class="bi bi-lightning-charge-fill">
              <path d="M11.251.068a.5.5 0 0 1 .227.58L9.677 6.5H13a.5.5 0 0 1 .364.843l-8 8.5a.5.5 0 0 1-.842-.49L6.323 9.5H3a.5.5 0 0 1-.364-.843l8-8.5a.5.5 0 0 1 .615-.09z"/>
            </svg>
            Beast Mode • 24/7 Access
          </div>
          
          <h1 class="display-3 fw-bold mb-4">
            Push <br>
            <span class="text-gradient">Your Limits</span>
          </h1>
          
          <p class="lead mb-5">
            No excuses. No shortcuts. Just pure grind.<br>
            Step into L9 Fitness and unleash your inner beast with hardcore training, iron discipline, and relentless dedication.
          </p>
          
          <div class="hero-buttons">
            <a class="btn btn-hero btn-primary btn-lg me-3 mb-3" href="memberships.php">
              <span>Train Hard</span>
              <i class="bi bi-arrow-right"></i>
            </a>
            <a class="btn btn-hero btn-outline btn-lg mb-3" href="classes.php">
              <span>Beast Classes</span>
              <i class="bi bi-play-circle"></i>
            </a>
          </div>
          
          <div class="hero-stats mt-5">
            <div class="row g-4">
              <div class="col-4">
                <div class="stat-item">
                  <div class="stat-number">500+</div>
                  <div class="stat-label">Active Members</div>
                </div>
              </div>
              <div class="col-4">
                <div class="stat-item">
                  <div class="stat-number">50+</div>
                  <div class="stat-label">Classes Weekly</div>
                </div>
              </div>
              <div class="col-4">
                <div class="stat-item">
                  <div class="stat-number">15+</div>
                  <div class="stat-label">Expert Trainers</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-lg-6">
        <div class="hero-image animate-slideInRight">
          <div class="hero-visual">
            <!-- Geometric Hero Design -->
            <div class="hero-shape hero-shape-1"></div>
            <div class="hero-shape hero-shape-2"></div>
            <div class="hero-shape hero-shape-3"></div>
            
            <!-- Central Focus Element -->
            <div class="hero-center">
              <div class="hero-logo">
                <i class="bi bi-lightning-charge-fill"></i>
              </div>
              <div class="hero-pulse"></div>
            </div>
            
            <!-- Floating Cards -->
            <div class="floating-card floating-card-1">
              <i class="bi bi-heart-pulse-fill"></i>
              <span>Heart Rate Zones</span>
            </div>
            <div class="floating-card floating-card-2">
              <i class="bi bi-trophy-fill"></i>
              <span>Personal Bests</span>
            </div>
            <div class="floating-card floating-card-3">
              <i class="bi bi-people-fill"></i>
              <span>Group Classes</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Features Section -->
<div class="features-section py-5">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-12">
        <h2 class="display-5 fw-bold mb-3">Forge Your Strength</h2>
        <p class="lead">Hardcore training zones built for champions who refuse to settle for average</p>
      </div>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-3 col-md-6">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="bi bi-lightning-charge-fill"></i>
          </div>
          <h4>Power Training</h4>
          <p>Unleash raw power with hardcore strength protocols designed to push your limits beyond breaking point.</p>
        </div>
      </div>
      
      <div class="col-lg-3 col-md-6">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="bi bi-heart-pulse-fill"></i>
          </div>
          <h4>Mind-Body Domination</h4>
          <p>Master your inner warrior through intense focus training and mental resilience conditioning.</p>
        </div>
      </div>
      
      <div class="col-lg-3 col-md-6">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="bi bi-bicycle"></i>
          </div>
          <h4>Beast Cardio</h4>
          <p>Torch calories and build endurance through savage cardio sessions that separate warriors from wannabes.</p>
        </div>
      </div>
      
      <div class="col-lg-3 col-md-6">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="bi bi-person-check-fill"></i>
          </div>
          <h4>Elite Mentorship</h4>
          <p>Train with certified beasts who'll push you past every excuse and into your strongest self.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CTA Section -->
<div class="cta-section">
  <div class="container text-center py-5">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h2 class="display-4 fw-bold mb-4">Ready to Unleash the Beast?</h2>
        <p class="lead mb-5">Join the elite who refuse to settle for weakness. Transform into the strongest version of yourself. No excuses, just results.</p>
        <a href="register.php" class="btn btn-cta btn-lg">
          <span>Beast Mode On</span>
          <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
