<?php 
require_once __DIR__ . '/../config/config.php';
$pageTitle = "Fitness Classes";
$pageCSS = "/assets/css/classes.css";
?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="classes-hero">
  <div class="container text-center py-5">
    <div class="hero-badge mb-3">
      <svg width="20" height="20" fill="currentColor" class="bi bi-heart-pulse-fill">
        <path d="M1.475 9C2.702 10.84 4.779 12.871 8 15.09c3.221-2.219 5.298-4.25 6.525-6.09a4.5 4.5 0 0 0-.187-6.075 3.5 3.5 0 0 0-4.76.025L8 4.705 6.422 2.95a3.5 3.5 0 0 0-4.76-.025A4.5 4.5 0 0 0 1.475 9z"/>
        <path d="M8 6a.5.5 0 0 1 .5.5v1.716l.802.267c.112.037.233.077.349.145.11.063.17.138.192.235a.5.5 0 0 1-.293.605l-.755-.252V7.5A.5.5 0 0 1 8 6z"/>
      </svg>
      Beast Training Zones
    </div>
    <h1 class="display-4 fw-bold mb-3">Warrior<br><span class="text-gradient">Sessions</span></h1>
    <p class="lead mb-4">Join hardcore training sessions designed to forge champions and break limits</p>
  </div>
</div>

<div class="container py-5">
  <!-- Filter tabs -->
  <div class="class-filters mb-5">
    <nav class="nav nav-pills justify-content-center">
      <a class="nav-link active filter-btn" data-filter="all">All Classes</a>
      <a class="nav-link filter-btn" data-filter="strength">Strength</a>
      <a class="nav-link filter-btn" data-filter="cardio">Cardio</a>
      <a class="nav-link filter-btn" data-filter="yoga">Yoga</a>
      <a class="nav-link filter-btn" data-filter="dance">Dance</a>
    </nav>
  </div>

  <?php 
  $stmt = $pdo->query('SELECT c.*, u.first_name, u.last_name FROM classes c JOIN users u ON u.id=c.trainer_id ORDER BY start_time ASC LIMIT 12');
  $classes = $stmt->fetchAll();
  ?>
  
  <div class="row g-4" id="classGrid">
    <?php if(!empty($classes)): ?>
      <?php foreach($classes as $index => $cls): ?>
        <?php
        $class_types = ['strength', 'cardio', 'yoga', 'dance'];
        $class_type = $class_types[array_rand($class_types)];
        
        $icons = [
          'strength' => 'bi-trophy-fill',
          'cardio' => 'bi-lightning-charge-fill', 
          'yoga' => 'bi-flower3',
          'dance' => 'bi-music-note-beamed'
        ];
        
        $colors = [
          'strength' => 'strength-class',
          'cardio' => 'cardio-class',
          'yoga' => 'yoga-class', 
          'dance' => 'dance-class'
        ];
        
        $start_time = new DateTime($cls['start_time']);
        $end_time = new DateTime($cls['end_time']);
        $duration = $start_time->diff($end_time);
        $duration_minutes = ($duration->h * 60) + $duration->i;
        
        $spots_left = rand(3, 15);
        $capacity = $cls['capacity'] ?? 20;
        ?>
        
        <div class="col-lg-4 col-md-6 class-item" data-type="<?php echo $class_type; ?>">
          <div class="class-card <?php echo $colors[$class_type]; ?>">
            <div class="class-header">
              <div class="class-type-badge">
                <i class="bi <?php echo $icons[$class_type]; ?>"></i>
                <?php echo ucfirst($class_type); ?>
              </div>
              
              <?php if($spots_left <= 5): ?>
                <div class="urgency-badge">Almost Full!</div>
              <?php endif; ?>
            </div>

            <div class="class-body">
              <div class="class-icon">
                <i class="bi <?php echo $icons[$class_type]; ?>"></i>
              </div>
              
              <h3 class="class-title"><?php echo htmlspecialchars($cls['title']); ?></h3>
              
              <div class="class-meta">
                <div class="meta-item">
                  <i class="bi bi-calendar3"></i>
                  <?php echo $start_time->format('M d, Y'); ?>
                </div>
                <div class="meta-item">
                  <i class="bi bi-clock"></i>
                  <?php echo $start_time->format('g:i A'); ?> - <?php echo $end_time->format('g:i A'); ?>
                </div>
                <div class="meta-item">
                  <i class="bi bi-stopwatch"></i>
                  <?php echo $duration_minutes; ?> minutes
                </div>
                <div class="meta-item">
                  <i class="bi bi-geo-alt"></i>
                  <?php echo htmlspecialchars($cls['location'] ?? 'Main Studio'); ?>
                </div>
              </div>

              <p class="class-description"><?php echo htmlspecialchars($cls['description'] ?? 'An amazing fitness class that will challenge and inspire you.'); ?></p>

              <div class="trainer-info">
                <div class="trainer-avatar">
                  <?php echo strtoupper(substr($cls['first_name'], 0, 1) . substr($cls['last_name'], 0, 1)); ?>
                </div>
                <div class="trainer-details">
                  <div class="trainer-name"><?php echo htmlspecialchars($cls['first_name'] . ' ' . $cls['last_name']); ?></div>
                  <div class="trainer-title">Certified Trainer</div>
                </div>
              </div>

              <div class="class-capacity">
                <div class="capacity-bar">
                  <div class="capacity-fill" style="width: <?php echo (($capacity - $spots_left) / $capacity) * 100; ?>%"></div>
                </div>
                <div class="capacity-text">
                  <span class="spots-left"><?php echo $spots_left; ?> spots left</span>
                  <span class="total-capacity"><?php echo $capacity; ?> max</span>
                </div>
              </div>

              <button class="btn-book-class">
                <span class="btn-text">Book Class</span>
                <i class="bi bi-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <div class="empty-state">
          <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
          <h3>No Classes Scheduled</h3>
          <p class="text-muted">Check back soon for upcoming fitness classes.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Class Types Section -->
<div class="class-types-section">
  <div class="container py-5">
    <div class="row text-center mb-5">
      <div class="col-12">
        <h2 class="display-5 fw-bold mb-3">Class Categories</h2>
        <p class="lead">Find the perfect class type that matches your fitness goals</p>
      </div>
    </div>
    
    <div class="row g-4">
      <div class="col-lg-3 col-md-6">
        <div class="category-card strength-category">
          <div class="category-icon">
            <i class="bi bi-trophy-fill"></i>
          </div>
          <h4>Strength Training</h4>
          <p>Build muscle, increase power, and sculpt your physique with our strength-focused classes</p>
          <ul class="category-features">
            <li>Weight lifting techniques</li>
            <li>Functional movements</li>
            <li>Progressive overload</li>
          </ul>
        </div>
      </div>
      
      <div class="col-lg-3 col-md-6">
        <div class="category-card cardio-category">
          <div class="category-icon">
            <i class="bi bi-lightning-charge-fill"></i>
          </div>
          <h4>Cardio Blast</h4>
          <p>High-energy classes that boost endurance and burn calories effectively</p>
          <ul class="category-features">
            <li>HIIT workouts</li>
            <li>Circuit training</li>
            <li>Heart rate zones</li>
          </ul>
        </div>
      </div>
      
      <div class="col-lg-3 col-md-6">
        <div class="category-card yoga-category">
          <div class="category-icon">
            <i class="bi bi-flower3"></i>
          </div>
          <h4>Yoga & Mindfulness</h4>
          <p>Connect mind and body through flowing movements and breathing techniques</p>
          <ul class="category-features">
            <li>Flexibility & balance</li>
            <li>Stress reduction</li>
            <li>Mindful movement</li>
          </ul>
        </div>
      </div>
      
      <div class="col-lg-3 col-md-6">
        <div class="category-card dance-category">
          <div class="category-icon">
            <i class="bi bi-music-note-beamed"></i>
          </div>
          <h4>Dance Fitness</h4>
          <p>Fun, rhythmic workouts that combine dance moves with fitness training</p>
          <ul class="category-features">
            <li>Choreographed routines</li>
            <li>Rhythm & coordination</li>
            <li>Full-body workout</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Class filter functionality
document.addEventListener('DOMContentLoaded', function() {
  const filterBtns = document.querySelectorAll('.filter-btn');
  const classItems = document.querySelectorAll('.class-item');
  
  filterBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      
      // Update active button
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      
      const filter = btn.dataset.filter;
      
      // Filter classes
      classItems.forEach(item => {
        if (filter === 'all' || item.dataset.type === filter) {
          item.style.display = 'block';
          item.style.animation = 'fadeInUp 0.5s ease';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
  
  // Add click handlers to book buttons
  document.querySelectorAll('.btn-book-class').forEach(btn => {
    btn.addEventListener('click', function() {
      this.innerHTML = '<span class="btn-text">Booked!</span><i class="bi bi-check-circle-fill"></i>';
      this.classList.add('booked');
      setTimeout(() => {
        this.innerHTML = '<span class="btn-text">Book Class</span><i class="bi bi-arrow-right"></i>';
        this.classList.remove('booked');
      }, 2000);
    });
  });
});
</script>

<style>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
