<?php require_once __DIR__ . '/../config/config.php'; ?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>
<div class="container py-5">
  <h1 class="h3 mb-4">Classes</h1>
  <p>This starter lists upcoming classes from the <code>classes</code> table.</p>
  <?php $stmt=$pdo->query('SELECT c.*, u.first_name, u.last_name FROM classes c JOIN users u ON u.id=c.trainer_id ORDER BY start_time ASC LIMIT 12'); $rows=$stmt->fetchAll(); ?>
  <div class="row g-4">
    <?php foreach($rows as $cls): ?>
      <div class="col-md-4"><div class="card h-100 shadow-sm"><div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($cls['title']); ?></h5>
        <div class="small text-muted mb-2"><?php echo date('M d, Y H:i', strtotime($cls['start_time'])); ?> – <?php echo date('H:i', strtotime($cls['end_time'])); ?></div>
        <p class="card-text"><?php echo htmlspecialchars($cls['description']); ?></p>
        <div class="d-flex justify-content-between align-items-center">
          <span class="badge text-bg-secondary">Trainer: <?php echo htmlspecialchars($cls['first_name'].' '.$cls['last_name']); ?></span>
          <button class="btn btn-sm btn-primary" disabled>Book (demo)</button>
        </div>
      </div></div></div>
    <?php endforeach; ?>
    <?php if(empty($rows)): ?><p class="text-muted">No classes yet. Import database/seed.sql.</p><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
