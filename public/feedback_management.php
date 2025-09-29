<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_admin()) {
    header('Location: /Capstone-latest/public/login.php?admin=1&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$pageTitle = "Feedback Management";
$pageCSS = "/assets/css/admin.css";

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['respond_feedback'])) {
            // Respond to feedback by updating reply_text
            $stmt = $pdo->prepare('UPDATE feedback SET reply_text = ? WHERE id = ?');
            $stmt->execute([
                $_POST['admin_response'],
                $_POST['feedback_id']
            ]);
            $message = 'Response sent successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['mark_reviewed'])) {
            // Mark as reviewed by adding a note to reply_text
            $stmt = $pdo->prepare('UPDATE feedback SET reply_text = CONCAT(COALESCE(reply_text, ""), " [Reviewed by admin]") WHERE id = ? AND (reply_text IS NULL OR reply_text = "")');
            $stmt->execute([$_POST['feedback_id']]);
            $message = 'Feedback marked as reviewed!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get feedback data
$stmt = $pdo->query('
    SELECT f.*,
           CONCAT(u.first_name, " ", u.last_name) as user_name,
           u.email as user_email,
           CASE
               WHEN f.class_id IS NOT NULL THEN "class"
               WHEN f.trainer_id IS NOT NULL THEN "trainer"
               ELSE "general"
           END as feedback_type,
           CASE
               WHEN f.class_id IS NOT NULL THEN (SELECT title FROM classes WHERE id = f.class_id)
               WHEN f.trainer_id IS NOT NULL THEN (SELECT CONCAT(first_name, " ", last_name) FROM users WHERE id = f.trainer_id AND role_id = 3)
               ELSE "General Feedback"
           END as related_name
    FROM feedback f
    LEFT JOIN users u ON f.member_id = u.id
    ORDER BY f.created_at DESC
');
$feedback = $stmt->fetchAll();

// Get feedback stats
$stmt = $pdo->query('SELECT COUNT(*) as total FROM feedback');
$total_feedback = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as pending FROM feedback WHERE reply_text IS NULL OR reply_text = ""');
$pending_feedback = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as responded FROM feedback WHERE reply_text IS NOT NULL AND reply_text != ""');
$responded_feedback = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT AVG(rating) as avg_rating FROM feedback WHERE rating > 0');
$avg_rating = $stmt->fetchColumn();

// Get rating distribution
$stmt = $pdo->query('SELECT rating, COUNT(*) as count FROM feedback WHERE rating > 0 GROUP BY rating ORDER BY rating DESC');
$rating_distribution = $stmt->fetchAll();
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="admin-hero py-4">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Feedback Management</li>
          </ol>
        </nav>
        <h1 class="display-5 fw-bold text-gradient mb-0">Feedback Management</h1>
        <p class="lead">Review and respond to member feedback on classes and trainers</p>
      </div>
    </div>
  </div>
</div>

<div class="container pb-5">
  <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($message); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Stats Overview -->
  <div class="row g-4 mb-5">
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-chat-quote"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $total_feedback; ?></h4>
          <p class="card-description">Total Feedback</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-clock"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $pending_feedback; ?></h4>
          <p class="card-description">Pending Review</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $responded_feedback; ?></h4>
          <p class="card-description">Responded</p>
        </div>
      </div>
    </div>
    <div class="col-lg-3 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-star"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $avg_rating ? number_format($avg_rating, 1) : '0.0'; ?></h4>
          <p class="card-description">Average Rating</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Rating Distribution -->
  <?php if (!empty($rating_distribution)): ?>
  <div class="row mb-5">
    <div class="col-12">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">Rating Distribution</h4>
          <div class="row g-3">
            <?php foreach ($rating_distribution as $rating): ?>
            <div class="col-md-2">
              <div class="text-center">
                <div class="rating-stars mb-2">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="bi bi-star<?php echo $i <= $rating['rating'] ? '-fill text-warning' : ''; ?>"></i>
                  <?php endfor; ?>
                </div>
                <div class="fw-bold"><?php echo $rating['count']; ?> reviews</div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Feedback Table -->
  <div class="row">
    <div class="col-12">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">All Feedback</h4>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Type</th>
                  <th>Related To</th>
                  <th>Rating</th>
                  <th>Feedback</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($feedback)): ?>
                  <?php foreach ($feedback as $item): ?>
                  <tr>
                    <td>
                      <div>
                        <strong><?php echo htmlspecialchars($item['user_name']); ?></strong>
                        <br><small class="text-muted"><?php echo htmlspecialchars($item['user_email']); ?></small>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-info"><?php echo ucfirst($item['feedback_type']); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($item['related_name']); ?></td>
                    <td>
                      <?php if ($item['rating']): ?>
                        <div class="rating-stars">
                          <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?php echo $i <= $item['rating'] ? '-fill text-warning' : ''; ?>"></i>
                          <?php endfor; ?>
                        </div>
                      <?php else: ?>
                        No rating
                      <?php endif; ?>
                    </td>
                    <td>
                      <div style="max-width: 300px;">
                        <?php if ($item['comments']): ?>
                          <small class="text-muted"><?php echo htmlspecialchars(substr($item['comments'], 0, 100)) . (strlen($item['comments']) > 100 ? '...' : ''); ?></small>
                        <?php else: ?>
                          <em class="text-muted">No comments</em>
                        <?php endif; ?>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-<?php
                        echo (empty($item['reply_text'])) ? 'warning' : 'success';
                      ?>">
                        <?php echo empty($item['reply_text']) ? 'Pending' : 'Responded'; ?>
                      </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewFeedback(<?php echo $item['id']; ?>)">
                          <i class="bi bi-eye"></i>
                        </button>
                        <?php if (empty($item['reply_text'])): ?>
                          <button class="btn btn-sm btn-outline-success" onclick="respondFeedback(<?php echo $item['id']; ?>)">
                            <i class="bi bi-reply"></i>
                          </button>
                          <button class="btn btn-sm btn-outline-info" onclick="markReviewed(<?php echo $item['id']; ?>)">
                            <i class="bi bi-check"></i>
                          </button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8" class="text-center py-5">
                      <i class="bi bi-chat-quote fs-1 text-muted"></i>
                      <p class="text-muted">No feedback received yet</p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- View Feedback Modal -->
<div class="modal fade" id="viewFeedbackModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Feedback Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="feedbackDetails">
        <!-- Content will be loaded here -->
      </div>
    </div>
  </div>
</div>

<!-- Respond to Feedback Modal -->
<div class="modal fade" id="respondFeedbackModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Respond to Feedback</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="responseForm">
        <div class="modal-body">
          <input type="hidden" name="feedback_id" id="response_feedback_id">
          <div class="mb-3">
            <label class="form-label">Original Feedback</label>
            <div class="border p-3 rounded bg-light" id="originalFeedback">
              <!-- Original feedback will be loaded here -->
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Your Response</label>
            <textarea class="form-control" name="admin_response" rows="4" placeholder="Type your response to the member..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="respond_feedback" class="btn btn-admin">Send Response</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function viewFeedback(feedbackId) {
    // Load feedback details via AJAX (simplified for now)
    fetch(`feedback_details.php?id=${feedbackId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('feedbackDetails').innerHTML = data;
            new bootstrap.Modal(document.getElementById('viewFeedbackModal')).show();
        })
        .catch(error => {
            alert('Error loading feedback details');
        });
}

function respondFeedback(feedbackId) {
    // Load feedback details for response
    fetch(`feedback_details.php?id=${feedbackId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('originalFeedback').innerHTML = data;
            document.getElementById('response_feedback_id').value = feedbackId;
            new bootstrap.Modal(document.getElementById('respondFeedbackModal')).show();
        })
        .catch(error => {
            alert('Error loading feedback details');
        });
}

function markReviewed(feedbackId) {
    if (confirm('Mark this feedback as reviewed?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="feedback_id" value="${feedbackId}">
            <input type="hidden" name="mark_reviewed" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>