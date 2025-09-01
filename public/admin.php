<?php require_once __DIR__ . '/../config/config.php'; require_once __DIR__ . '/../app/helpers/auth.php'; if(!is_admin()) { http_response_code(403); exit('Admins only.'); } ?>
<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<!-- Admin Hero Section -->
<div class="admin-hero py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="text-center mb-5">
          <div class="admin-badge mb-3">
            <i class="bi bi-shield-check"></i>
            Beast Command
          </div>
          <h1 class="display-4 fw-bold text-gradient">Beast Master Control</h1>
          <p class="lead">Command the gym empire. Control the warriors. Dominate everything.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Admin Cards Section -->
<div class="container pb-5">
  <div class="row g-4">
    <!-- User Management -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card users-card">
        <div class="card-icon">
          <i class="bi bi-people-fill"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">User Management</h4>
          <p class="card-description">Manage member accounts, view profiles, and handle user permissions</p>
          <div class="card-stats">
            <span class="stat-number">245</span>
            <span class="stat-label">Active Members</span>
          </div>
        </div>
        <a href="#" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Manage Users
        </a>
      </div>
    </div>
    
    <!-- Membership Plans -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card memberships-card">
        <div class="card-icon">
          <i class="bi bi-credit-card-2-front"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Membership Plans</h4>
          <p class="card-description">Create and modify membership tiers, pricing, and benefits</p>
          <div class="card-stats">
            <span class="stat-number">4</span>
            <span class="stat-label">Active Plans</span>
          </div>
        </div>
        <a href="memberships.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Manage Plans
        </a>
      </div>
    </div>
    
    <!-- Class Management -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card classes-card">
        <div class="card-icon">
          <i class="bi bi-calendar-event"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Class Schedules</h4>
          <p class="card-description">Schedule fitness classes, manage instructors, and track attendance</p>
          <div class="card-stats">
            <span class="stat-number">12</span>
            <span class="stat-label">Weekly Classes</span>
          </div>
        </div>
        <a href="classes.php" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Manage Classes
        </a>
      </div>
    </div>
    
    <!-- Reports & Analytics -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card reports-card">
        <div class="card-icon">
          <i class="bi bi-graph-up"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Reports & Analytics</h4>
          <p class="card-description">View membership trends, revenue reports, and usage statistics</p>
          <div class="card-stats">
            <span class="stat-number">8.5%</span>
            <span class="stat-label">Growth Rate</span>
          </div>
        </div>
        <a href="#" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          View Reports
        </a>
      </div>
    </div>
    
    <!-- System Settings -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card settings-card">
        <div class="card-icon">
          <i class="bi bi-gear-fill"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">System Settings</h4>
          <p class="card-description">Configure gym policies, payment settings, and notification preferences</p>
          <div class="card-stats">
            <span class="stat-number">✓</span>
            <span class="stat-label">All Systems OK</span>
          </div>
        </div>
        <a href="#" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Settings
        </a>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-4 col-md-6">
      <div class="admin-card actions-card">
        <div class="card-icon">
          <i class="bi bi-lightning-charge"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title">Quick Actions</h4>
          <p class="card-description">Perform common administrative tasks and emergency functions</p>
          <div class="card-stats">
            <span class="stat-number">6</span>
            <span class="stat-label">Quick Actions</span>
          </div>
        </div>
        <a href="#" class="btn btn-admin">
          <i class="bi bi-arrow-right"></i>
          Quick Actions
        </a>
      </div>
    </div>
  </div>
  
  <!-- Recent Activity Section -->
  <div class="row mt-5">
    <div class="col-12">
      <div class="admin-activity-section">
        <h3 class="section-title">Recent Administrative Activity</h3>
        <div class="activity-list">
          <div class="activity-item">
            <div class="activity-icon">
              <i class="bi bi-person-plus"></i>
            </div>
            <div class="activity-content">
              <h6>New Member Registration</h6>
              <p>John Smith signed up for Premium membership</p>
              <span class="activity-time">2 hours ago</span>
            </div>
          </div>
          
          <div class="activity-item">
            <div class="activity-icon">
              <i class="bi bi-calendar-check"></i>
            </div>
            <div class="activity-content">
              <h6>Class Schedule Updated</h6>
              <p>Yoga class moved from 6 PM to 7 PM on Wednesdays</p>
              <span class="activity-time">5 hours ago</span>
            </div>
          </div>
          
          <div class="activity-item">
            <div class="activity-icon">
              <i class="bi bi-credit-card"></i>
            </div>
            <div class="activity-content">
              <h6>Payment Processed</h6>
              <p>Monthly membership fees collected for 18 members</p>
              <span class="activity-time">1 day ago</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
