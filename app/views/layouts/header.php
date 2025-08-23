<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// go up 3 dirs to reach project root from /app/views/layouts/
require_once dirname(__DIR__, 3) . '/config/config.php';
?>
<?php if (!empty($pageCSS)): ?>
  <link rel="stylesheet" href="<?= htmlspecialchars($pageCSS) ?>">
<?php endif; ?>
<!doctype html>
<html lang="en">
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?php echo APP_NAME; ?></title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!-- Global CSS -->
  <link href="<?php echo BASE_URL; ?>assets/css/main.css" rel="stylesheet">

  <!-- Optional page-specific CSS -->
  <?php if (!empty($pageCSS)): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($pageCSS) ?>">
  <?php endif; ?>
</head>

<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>/index.php">L9 Fitness Gym</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/classes.php">Classes</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/memberships.php">Memberships</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/profile.php">
            <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
          </a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main class="flex-grow-1">
