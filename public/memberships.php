<?php require_once __DIR__ . '/../config/config.php'; ?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>
<div class="container py-5">
  <h1 class="h3 mb-4">Membership Plans</h1>
  <?php $plans=$pdo->query('SELECT * FROM membership_plans WHERE is_active=1 ORDER BY price ASC')->fetchAll(); ?>
  <div class="row g-4">
    <?php foreach($plans as $p): ?>
      <div class="col-md-4"><div class="card h-100 shadow-sm"><div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($p['name']); ?></h5>
        <p class="card-text"><?php echo nl2br(htmlspecialchars($p['description'])); ?></p>
        <div class="display-6">$<?php echo number_format($p['price'],2); ?></div>
        <div class="text-muted">Valid <?php echo (int)$p['duration_days']; ?> days</div>
        <button class="btn btn-primary mt-3" disabled>Choose (demo)</button>
      </div></div></div>
    <?php endforeach; ?>
    <?php if(empty($plans)): ?><p class="text-muted">No active plans yet. Import database/seed.sql.</p><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
