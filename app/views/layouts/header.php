<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// go up 3 dirs to reach project root from /app/views/layouts/
require_once dirname(__DIR__, 3) . '/config/config.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo APP_NAME; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  
  <!-- Global CSS -->
  <link href="<?php echo defined('ASSET_URL') ? ASSET_URL : BASE_URL . 'assets'; ?>/css/main.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : time(); ?>" rel="stylesheet">
  
  <!-- Page Transitions CSS -->
  <link href="<?php echo defined('ASSET_URL') ? ASSET_URL : BASE_URL . 'assets'; ?>/css/page-transitions.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : time(); ?>" rel="stylesheet">
  
  <!-- Chatbot CSS -->
  <link href="<?php echo defined('ASSET_URL') ? ASSET_URL : BASE_URL . 'assets'; ?>/css/chatbot.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : time(); ?>" rel="stylesheet">
  
  <!-- Universal Chatbot Fix CSS - Ensures chatbot works perfectly on all pages -->
  <link href="<?php echo defined('ASSET_URL') ? ASSET_URL : BASE_URL . 'assets'; ?>/css/chatbot-universal-fix.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : time(); ?>" rel="stylesheet">
  
  <!-- Universal Premium Footer CSS -->
  <link href="<?php echo defined('ASSET_URL') ? ASSET_URL : BASE_URL . 'assets'; ?>/css/universal-footer.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : time(); ?>" rel="stylesheet">
  <link href="<?php echo defined('ASSET_URL') ? ASSET_URL : BASE_URL . 'assets'; ?>/css/footer-emergency-fix.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : time(); ?>" rel="stylesheet">
  
  <!-- Text Visibility Fix CSS - Ensures all text is readable -->
  <link href="<?php echo defined('ASSET_URL') ? ASSET_URL : BASE_URL . 'assets'; ?>/css/text-visibility-fix.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : time(); ?>" rel="stylesheet">
  
  <!-- Form Visibility Fix CSS - Fixes form validation text visibility -->
  <link href="<?php echo defined('ASSET_URL') ? ASSET_URL : BASE_URL . 'assets'; ?>/css/form-visibility-fix.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : time(); ?>" rel="stylesheet">
  
  <!-- Font Awesome for Footer Icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  
  <!-- Offline Support -->
  <script src="<?php echo defined('ASSET_URL') ? ASSET_URL : BASE_URL . 'assets'; ?>/js/offline-support.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : time(); ?>" defer></script>
  
  <!-- Dynamic Effects JavaScript -->
  <script src="<?php echo defined('ASSET_URL') ? ASSET_URL : BASE_URL . 'assets'; ?>/js/dynamic-effects.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : time(); ?>"></script>
  
  <!-- Page-specific CSS -->
  <?php if (isset($pageCSS) && $pageCSS): ?>
    <?php if (is_array($pageCSS)): ?>
      <?php foreach ($pageCSS as $css): ?>
        <link href="<?php echo BASE_URL . $css; ?>" rel="stylesheet">
      <?php endforeach; ?>
    <?php else: ?>
      <link href="<?php echo BASE_URL . $pageCSS; ?>" rel="stylesheet">
    <?php endif; ?>
  <?php endif; ?>
  
  <!-- Auto-load CSS based on current page -->
  <?php 
  $current_page = basename($_SERVER['PHP_SELF'], '.php');
  $css_files = [
    'login' => 'assets/css/login.css',
    'dashboard' => 'assets/css/dashboard.css',
    'profile' => 'assets/css/profile.css',
    'admin' => 'assets/css/admin.css',
    'index' => 'assets/css/home.css',
    'memberships' => 'assets/css/membership.css',
    'classes' => 'assets/css/classes.css',
    'register' => 'assets/css/register.css',
    'checkout' => 'assets/css/checkout.css',
    'checkout-success' => 'assets/css/checkout-success.css',
    'forgot-password' => 'assets/css/legal.css',
    'reset-password' => 'assets/css/legal.css',
    'terms' => 'assets/css/legal.css',
    'privacy' => 'assets/css/legal.css',
    'contact' => 'assets/css/contact.css'
  ];
  
  if (isset($css_files[$current_page])):
  ?>
    <link href="<?php echo BASE_URL . $css_files[$current_page]; ?>?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : time(); ?>" rel="stylesheet">
  <?php endif; ?>
</head>

<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>index.php">L9 Fitness Gym</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>classes.php">Classes</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>memberships.php">Memberships</a></li>
        <li class="nav-item">
          <a class="nav-link text-warning fw-bold" href="<?php echo BASE_URL; ?>waki.php">
            <i class="fas fa-robot me-1"></i>WAKI AI
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>dashboard.php">
            <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
          </a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<?php 
// Show session status widget for logged-in users
if (isset($_SESSION['user'])):
  require_once dirname(__DIR__, 3) . '/app/helpers/session_widget.php';
  echo renderSessionStatus();
endif; 
?>

<main class="flex-grow-1">
