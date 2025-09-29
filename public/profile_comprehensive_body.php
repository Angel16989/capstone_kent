<div class="dashboard-enhanced">
    <!-- Profile Header with Photo Upload -->
    <div class="profile-header mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="profile-photo-container">
                        <?php if ($profile_photo): ?>
                            <img src="/uploads/profile_photos/<?= htmlspecialchars($profile_photo) ?>" 
                                 alt="Profile Photo" class="profile-photo-large">
                        <?php else: ?>
                            <div class="profile-photo-placeholder">
                                <i class="fas fa-user fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-primary photo-upload-btn" data-bs-toggle="modal" data-bs-target="#photoUploadModal">
                            <i class="fas fa-camera"></i> Change Photo
                        </button>
                    </div>
                </div>
                <div class="col">
                    <h1 class="profile-name">
                        <?= htmlspecialchars($row['first_name'] ?? '') ?> <?= htmlspecialchars($row['last_name'] ?? '') ?>
                        <span class="membership-badge"><?= htmlspecialchars($membership['plan_name'] ?? 'No Active Plan') ?></span>
                    </h1>
                    <p class="profile-stats">
                        <span class="stat-item">
                            <i class="fas fa-dumbbell"></i> 
                            <?= $stats['total_bookings'] ?? 0 ?> Classes Attended
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-calendar"></i> 
                            Member Since <?= date('M Y', strtotime($row['created_at'] ?? '')) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- In-App Announcements -->
    <?php if (!empty($unread_announcements)): ?>
        <div class="announcements-section mb-4">
            <h3><i class="fas fa-bullhorn"></i> Latest Announcements</h3>
            <?php foreach ($unread_announcements as $announcement): ?>
                <div class="announcement-card priority-<?= $announcement['priority'] ?>" data-announcement-id="<?= $announcement['id'] ?>">
                    <div class="announcement-header">
                        <h5><?= htmlspecialchars($announcement['title']) ?></h5>
                        <button class="btn btn-sm btn-outline-secondary mark-read-btn">Mark as Read</button>
                    </div>
                    <p><?= nl2br(htmlspecialchars($announcement['content'])) ?></p>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> <?= date('M d, Y H:i', strtotime($announcement['created_at'])) ?>
                    </small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Main Navigation Tabs -->
    <div class="profile-nav mb-4">
        <ul class="nav nav-pills nav-fill" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="personal-tab" data-bs-toggle="pill" data-bs-target="#personal" type="button" role="tab">
                    <i class="fas fa-user"></i> Personal Info
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="fitness-tab" data-bs-toggle="pill" data-bs-target="#fitness" type="button" role="tab">
                    <i class="fas fa-dumbbell"></i> Fitness Profile
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="nutrition-tab" data-bs-toggle="pill" data-bs-target="#nutrition" type="button" role="tab">
                    <i class="fas fa-apple-alt"></i> Nutrition
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="progress-tab" data-bs-toggle="pill" data-bs-target="#progress" type="button" role="tab">
                    <i class="fas fa-chart-line"></i> Progress
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="messages-tab" data-bs-toggle="pill" data-bs-target="#messages" type="button" role="tab">
                    <i class="fas fa-comments"></i> Messages
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="activity-tab" data-bs-toggle="pill" data-bs-target="#activity" type="button" role="tab">
                    <i class="fas fa-history"></i> Activity
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="pill" data-bs-target="#settings" type="button" role="tab">
                    <i class="fas fa-cog"></i> Settings
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="profileTabsContent">
        <!-- Personal Information Tab -->
        <div class="tab-pane fade show active" id="personal" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-user"></i> Personal Information</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="first_name" 
                                           value="<?= htmlspecialchars($row['first_name'] ?? '') ?>" readonly>
                                    <label for="first_name">First Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="last_name" 
                                           value="<?= htmlspecialchars($row['last_name'] ?? '') ?>" readonly>
                                    <label for="last_name">Last Name</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" 
                                           value="<?= htmlspecialchars($row['email'] ?? '') ?>" readonly>
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($row['phone'] ?? '') ?>">
                                    <label for="phone">Phone Number</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="date" class="form-control" id="dob" name="dob" 
                                           value="<?= htmlspecialchars($row['dob'] ?? '') ?>">
                                    <label for="dob">Date of Birth</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="gender" name="gender">
                                        <option value="">Prefer not to say</option>
                                        <option value="male" <?= ($row['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= ($row['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                        <option value="other" <?= ($row['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                    <label for="gender">Gender</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="address" name="address" style="height: 100px"><?= htmlspecialchars($row['address'] ?? '') ?></textarea>
                            <label for="address">Address</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" 
                                   value="<?= htmlspecialchars($row['emergency_contact'] ?? '') ?>">
                            <label for="emergency_contact">Emergency Contact</label>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Fitness Profile Tab -->
        <div class="tab-pane fade" id="fitness" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-dumbbell"></i> Fitness Profile</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="fitness-stat-card">
                                <h5>Current Weight</h5>
                                <div class="stat-value"><?= $fitness_profile['current_weight'] ?? 'Not Set' ?> kg</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="fitness-stat-card">
                                <h5>Height</h5>
                                <div class="stat-value"><?= $fitness_profile['height'] ?? 'Not Set' ?> cm</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="fitness-stat-card">
                                <h5>Fitness Level</h5>
                                <div class="stat-value"><?= ucfirst($fitness_profile['fitness_level'] ?? 'Not Set') ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fitness-stat-card">
                                <h5>Primary Goal</h5>
                                <div class="stat-value"><?= ucfirst(str_replace('_', ' ', $fitness_profile['primary_goal'] ?? 'Not Set')) ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="fitness-stat-card">
                                <h5>Activity Level</h5>
                                <div class="stat-value"><?= ucfirst(str_replace('_', ' ', $fitness_profile['activity_level'] ?? 'Not Set')) ?></div>
                            </div>
                        </div>
                    </div>

                    <?php if ($fitness_profile['health_conditions']): ?>
                        <div class="mt-3">
                            <h6>Health Conditions</h6>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($fitness_profile['health_conditions'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#fitnessProfileModal">
                        <i class="fas fa-edit"></i> Update Fitness Profile
                    </button>
                </div>
            </div>
        </div>

        <!-- Nutrition Tab -->
        <div class="tab-pane fade" id="nutrition" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-apple-alt"></i> Nutrition Plan</h4>
                </div>
                <div class="card-body">
                    <?php if ($nutrition_plan): ?>
                        <div class="nutrition-overview">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nutrition-stat">
                                        <h6>Diet Type</h6>
                                        <span class="badge bg-primary"><?= ucfirst($nutrition_plan['diet_type']) ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="nutrition-stat">
                                        <h6>Daily Calories</h6>
                                        <strong><?= number_format($nutrition_plan['daily_calories']) ?></strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="nutrition-stat">
                                        <h6>Protein (g)</h6>
                                        <strong><?= $nutrition_plan['protein_grams'] ?></strong>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="nutrition-stat">
                                        <h6>Carbs (g)</h6>
                                        <strong><?= $nutrition_plan['carbs_grams'] ?></strong>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($nutrition_plan['meal_plan']): ?>
                                <div class="mt-3">
                                    <h6>Meal Plan</h6>
                                    <div class="meal-plan-content">
                                        <?= nl2br(htmlspecialchars($nutrition_plan['meal_plan'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                            <h5>No Nutrition Plan Set</h5>
                            <p class="text-muted">Create a personalized nutrition plan to reach your fitness goals.</p>
                        </div>
                    <?php endif; ?>
                    
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#nutritionPlanModal">
                        <i class="fas fa-plus"></i> <?= $nutrition_plan ? 'Update' : 'Create' ?> Nutrition Plan
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress Tab -->
        <div class="tab-pane fade" id="progress" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-chart-line"></i> Progress Tracking</h4>
                </div>
                <div class="card-body">
                    <div class="progress-section">
                        <h5>Weight Progress</h5>
                        <div id="weightChart" style="height: 300px;"></div>
                        
                        <h5 class="mt-4">Workout Progress</h5>
                        <div id="workoutChart" style="height: 300px;"></div>
                        
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addProgressModal">
                            <i class="fas fa-plus"></i> Log Progress
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Tab -->
        <div class="tab-pane fade" id="messages" role="tabpanel">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-inbox"></i> Messages</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($messages)): ?>
                                <?php foreach ($messages as $msg): ?>
                                    <div class="message-item <?= $msg['is_read'] ? '' : 'unread' ?>">
                                        <div class="message-header">
                                            <strong><?= htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']) ?></strong>
                                            <span class="message-time"><?= date('M d, Y H:i', strtotime($msg['sent_at'])) ?></span>
                                        </div>
                                        <div class="message-subject"><?= htmlspecialchars($msg['subject']) ?></div>
                                        <div class="message-preview"><?= htmlspecialchars(substr($msg['message'], 0, 100)) ?>...</div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                                    <h5>No Messages</h5>
                                    <p class="text-muted">You don't have any messages yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-paper-plane"></i> Send Message</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="action" value="send_message">
                                
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="recipient_id" name="recipient_id" required>
                                        <option value="">Select Recipient</option>
                                        <optgroup label="Trainers">
                                            <?php foreach ($trainers as $trainer): ?>
                                                <option value="<?= $trainer['id'] ?>">
                                                    <?= htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    </select>
                                    <label for="recipient_id">To</label>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                    <label for="subject">Subject</label>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="message" name="message" style="height: 120px" required></textarea>
                                    <label for="message">Message</label>
                                </div>
                                
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="message_type" name="message_type">
                                                <option value="general">General</option>
                                                <option value="class_inquiry">Class Inquiry</option>
                                                <option value="feedback">Feedback</option>
                                                <option value="complaint">Complaint</option>
                                            </select>
                                            <label for="message_type">Type</label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" id="priority" name="priority">
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                            </select>
                                            <label for="priority">Priority</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Tab -->
        <div class="tab-pane fade" id="activity" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-credit-card"></i> Payment History</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($payment_history)): ?>
                                <?php foreach ($payment_history as $payment): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon payment">
                                            <i class="fas fa-dollar-sign"></i>
                                        </div>
                                        <div class="activity-content">
                                            <strong><?= htmlspecialchars($payment['description']) ?></strong>
                                            <div class="activity-meta">
                                                $<?= number_format($payment['amount'], 2) ?> • 
                                                <?= date('M d, Y', strtotime($payment['payment_date'])) ?>
                                            </div>
                                        </div>
                                        <div class="activity-actions">
                                            <button class="btn btn-sm btn-outline-primary" onclick="downloadReceipt(<?= $payment['id'] ?>)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center">No payment history found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-door-open"></i> Gym Check-ins</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($gym_logs)): ?>
                                <?php foreach ($gym_logs as $log): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon checkin">
                                            <i class="fas fa-<?= $log['check_type'] === 'in' ? 'sign-in-alt' : 'sign-out-alt' ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <strong>Check-<?= $log['check_type'] ?></strong>
                                            <div class="activity-meta">
                                                <?= date('M d, Y H:i', strtotime($log['check_time'])) ?>
                                                <?php if ($log['method']): ?>
                                                    • <?= ucfirst($log['method']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center">No check-in history found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($waitlists)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-clock"></i> Waitlists</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($waitlists as $waitlist): ?>
                            <div class="waitlist-item">
                                <div class="waitlist-info">
                                    <strong><?= htmlspecialchars($waitlist['class_title']) ?></strong>
                                    <div class="waitlist-meta">
                                        Position: #<?= $waitlist['position'] ?> • 
                                        Joined: <?= date('M d, Y', strtotime($waitlist['joined_at'])) ?>
                                    </div>
                                </div>
                                <div class="waitlist-status">
                                    <span class="badge bg-warning">Waiting</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Settings Tab -->
        <div class="tab-pane fade" id="settings" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bell"></i> Notification Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="action" value="update_preferences">
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="notifications_email" name="notifications_email" 
                                           <?= ($user_preferences['notifications_email'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifications_email">
                                        Email Notifications
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="notifications_sms" name="notifications_sms" 
                                           <?= ($user_preferences['notifications_sms'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notifications_sms">
                                        SMS Notifications
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="class_reminders" name="class_reminders" 
                                           <?= ($user_preferences['class_reminders'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="class_reminders">
                                        Class Reminders
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="payment_reminders" name="payment_reminders" 
                                           <?= ($user_preferences['payment_reminders'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="payment_reminders">
                                        Payment Reminders
                                    </label>
                                </div>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="marketing_emails" name="marketing_emails" 
                                           <?= ($user_preferences['marketing_emails'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="marketing_emails">
                                        Marketing Emails
                                    </label>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="privacy_level" name="privacy_level">
                                        <option value="public" <?= ($user_preferences['privacy_level'] ?? 'members_only') === 'public' ? 'selected' : '' ?>>Public</option>
                                        <option value="members_only" <?= ($user_preferences['privacy_level'] ?? 'members_only') === 'members_only' ? 'selected' : '' ?>>Members Only</option>
                                        <option value="private" <?= ($user_preferences['privacy_level'] ?? 'members_only') === 'private' ? 'selected' : '' ?>>Private</option>
                                    </select>
                                    <label for="privacy_level">Profile Privacy</label>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <select class="form-select" id="measurement_system" name="measurement_system">
                                        <option value="metric" <?= ($user_preferences['measurement_system'] ?? 'metric') === 'metric' ? 'selected' : '' ?>>Metric (kg, cm)</option>
                                        <option value="imperial" <?= ($user_preferences['measurement_system'] ?? 'metric') === 'imperial' ? 'selected' : '' ?>>Imperial (lbs, ft)</option>
                                    </select>
                                    <label for="measurement_system">Measurement System</label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Preferences
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-lock"></i> Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    <label for="current_password">Current Password</label>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <label for="new_password">New Password</label>
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <label for="confirm_password">Confirm New Password</label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Photo Upload Modal -->
<div class="modal fade" id="photoUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Profile Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="action" value="upload_photo">
                    
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">Choose Photo</label>
                        <input type="file" class="form-control" id="profile_photo" name="profile_photo" 
                               accept="image/*" required>
                        <div class="form-text">Maximum file size: 5MB. Supported formats: JPG, PNG, GIF, WebP</div>
                    </div>
                    
                    <div id="imagePreview" class="text-center" style="display: none;">
                        <img id="previewImg" src="" alt="Preview" style="max-width: 200px; max-height: 200px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Photo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Handle announcement read marking
document.addEventListener('DOMContentLoaded', function() {
    // Handle mark as read buttons
    document.querySelectorAll('.mark-read-btn').forEach(button => {
        button.addEventListener('click', function() {
            const announcementId = this.closest('.announcement-card').dataset.announcementId;
            const formData = new FormData();
            formData.append('action', 'mark_announcement_read');
            formData.append('announcement_id', announcementId);
            formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
            
            fetch('', {
                method: 'POST',
                body: formData
            }).then(() => {
                this.closest('.announcement-card').style.display = 'none';
            });
        });
    });
    
    // Photo upload preview
    document.getElementById('profile_photo').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
});

function downloadReceipt(paymentId) {
    window.open(`/download_receipt.php?id=${paymentId}`, '_blank');
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>