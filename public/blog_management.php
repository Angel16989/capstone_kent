<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_admin()) {
    header('Location: /Capstone-latest/public/login.php?admin=1&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$pageTitle = "Blog & News Management";
$pageCSS = "/assets/css/admin.css";

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create_post'])) {
            // Create new blog post
            $stmt = $pdo->prepare('INSERT INTO blog_posts (title, content, excerpt, author_id, status, tags) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $_POST['title'],
                $_POST['content'],
                $_POST['excerpt'],
                $_SESSION['user']['id'],
                $_POST['status'],
                $_POST['tags']
            ]);
            $message = 'Blog post created successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['update_post'])) {
            // Update existing post
            $stmt = $pdo->prepare('UPDATE blog_posts SET title = ?, content = ?, excerpt = ?, status = ?, tags = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([
                $_POST['title'],
                $_POST['content'],
                $_POST['excerpt'],
                $_POST['status'],
                $_POST['tags'],
                $_POST['post_id']
            ]);
            $message = 'Blog post updated successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['delete_post'])) {
            // Delete post
            $stmt = $pdo->prepare('DELETE FROM blog_posts WHERE id = ?');
            $stmt->execute([$_POST['post_id']]);
            $message = 'Blog post deleted successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['create_notification'])) {
            // Create notification
            $stmt = $pdo->prepare('INSERT INTO notifications (title, message, type, target_audience, created_by, expires_at) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $_POST['notification_title'],
                $_POST['notification_message'],
                $_POST['notification_type'],
                $_POST['target_audience'],
                $_SESSION['user']['id'],
                !empty($_POST['expires_at']) ? $_POST['expires_at'] : null
            ]);
            $message = 'Notification created successfully!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get blog posts
$stmt = $pdo->query('SELECT bp.*, CONCAT(u.first_name, " ", u.last_name) as author_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id ORDER BY bp.created_at DESC');
$blog_posts = $stmt->fetchAll();

// Get notifications
$stmt = $pdo->query('SELECT n.*, CONCAT(u.first_name, " ", u.last_name) as creator_name FROM notifications n LEFT JOIN users u ON n.created_by = u.id ORDER BY n.created_at DESC LIMIT 10');
$notifications = $stmt->fetchAll();

// Get blog stats
$stmt = $pdo->query('SELECT COUNT(*) as total_posts FROM blog_posts');
$total_posts = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as published_posts FROM blog_posts WHERE status = "published"');
$published_posts = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) as total_notifications FROM notifications WHERE is_active = 1');
$total_notifications = $stmt->fetchColumn();
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="admin-hero py-4">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Blog & News Management</li>
          </ol>
        </nav>
        <h1 class="display-5 fw-bold text-gradient mb-0">Blog & News Management</h1>
        <p class="lead">Create and manage blog posts, news updates, and in-app notifications</p>
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
    <div class="col-lg-4 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-newspaper"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $total_posts; ?></h4>
          <p class="card-description">Total Blog Posts</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-check-circle"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $published_posts; ?></h4>
          <p class="card-description">Published Posts</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-bell"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $total_notifications; ?></h4>
          <p class="card-description">Active Notifications</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Create New Post Modal -->
  <div class="modal fade" id="createPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create New Blog Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Excerpt</label>
              <textarea class="form-control" name="excerpt" rows="2"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Content</label>
              <textarea class="form-control" name="content" rows="10" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Tags</label>
              <input type="text" class="form-control" name="tags" placeholder="fitness, nutrition, health">
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-control" name="status">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="create_post" class="btn btn-admin">Create Post</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Create Notification Modal -->
  <div class="modal fade" id="createNotificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create In-App Notification</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input type="text" class="form-control" name="notification_title" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Message</label>
              <textarea class="form-control" name="notification_message" rows="4" required></textarea>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Type</label>
                  <select class="form-control" name="notification_type">
                    <option value="info">Info</option>
                    <option value="success">Success</option>
                    <option value="warning">Warning</option>
                    <option value="error">Error</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Target Audience</label>
                  <select class="form-control" name="target_audience">
                    <option value="all">All Users</option>
                    <option value="members">Members Only</option>
                    <option value="trainers">Trainers Only</option>
                    <option value="admins">Admins Only</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Expires At (Optional)</label>
              <input type="datetime-local" class="form-control" name="expires_at">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="create_notification" class="btn btn-admin">Send Notification</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex gap-2">
        <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#createPostModal">
          <i class="bi bi-plus-circle"></i> Create Blog Post
        </button>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createNotificationModal">
          <i class="bi bi-bell"></i> Send Notification
        </button>
      </div>
    </div>
  </div>

  <!-- Blog Posts Table -->
  <div class="row mb-5">
    <div class="col-12">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">Blog Posts</h4>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Author</th>
                  <th>Status</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($blog_posts)): ?>
                  <?php foreach ($blog_posts as $post): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                    <td><?php echo htmlspecialchars($post['author_name'] ?? 'Unknown'); ?></td>
                    <td>
                      <span class="badge bg-<?php echo $post['status'] === 'published' ? 'success' : 'secondary'; ?>">
                        <?php echo ucfirst($post['status']); ?>
                      </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="editPost(<?php echo $post['id']; ?>)">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deletePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['title']); ?>')">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center py-5">
                      <i class="bi bi-newspaper fs-1 text-muted"></i>
                      <p class="text-muted">No blog posts yet</p>
                      <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#createPostModal">Create Your First Post</button>
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

  <!-- Recent Notifications -->
  <div class="row">
    <div class="col-12">
      <div class="admin-card">
        <div class="card-content">
          <h4 class="card-title mb-4">Recent Notifications</h4>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Type</th>
                  <th>Target</th>
                  <th>Status</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($notifications)): ?>
                  <?php foreach ($notifications as $notification): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($notification['title']); ?></td>
                    <td>
                      <span class="badge bg-<?php
                        echo $notification['type'] === 'success' ? 'success' :
                             ($notification['type'] === 'warning' ? 'warning' :
                             ($notification['type'] === 'error' ? 'danger' : 'info'));
                      ?>">
                        <?php echo ucfirst($notification['type']); ?>
                      </span>
                    </td>
                    <td><?php echo ucfirst($notification['target_audience']); ?></td>
                    <td>
                      <span class="badge bg-<?php echo $notification['is_active'] ? 'success' : 'secondary'; ?>">
                        <?php echo $notification['is_active'] ? 'Active' : 'Inactive'; ?>
                      </span>
                    </td>
                    <td><?php echo date('M j, Y H:i', strtotime($notification['created_at'])); ?></td>
                  </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center py-5">
                      <i class="bi bi-bell fs-1 text-muted"></i>
                      <p class="text-muted">No notifications sent yet</p>
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

<script>
function editPost(postId) {
    // Implement edit functionality
    alert('Edit functionality coming soon for post ID: ' + postId);
}

function deletePost(postId, title) {
    if (confirm('Are you sure you want to delete "' + title + '"?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="post_id" value="${postId}">
            <input type="hidden" name="delete_post" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>