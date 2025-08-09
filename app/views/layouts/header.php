


<?php require_once __DIR__ . '/../../../config/config.php'; ?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo APP_NAME; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/main.css" rel="stylesheet"></head><body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark"><div class="container">
<a class="navbar-brand fw-bold" href="index.php">L9 Fitness Gym</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="nav">
<ul class="navbar-nav me-auto mb-2 mb-lg-0">
<li class="nav-item"><a class="nav-link" href="classes.php">Classes</a></li>
<li class="nav-item"><a class="nav-link" href="memberships.php">Memberships</a></li>
</ul>
<ul class="navbar-nav ms-auto">
<?php if (isset($_SESSION['user'])): ?>
<li class="nav-item"><a class="nav-link" href="profile.php"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></a></li>
<li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
<?php else: ?>
<li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
<li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
<?php endif; ?>
</ul></div></div></nav><main class="flex-grow-1">
