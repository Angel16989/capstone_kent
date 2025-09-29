<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_admin()) {
    header('Location: /Capstone-latest/public/login.php?admin=1&redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$pageTitle = "Content Management";
$pageCSS = "/assets/css/admin.css";

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_page'])) {
            // Update CMS page
            $stmt = $pdo->prepare('
                INSERT INTO cms_pages (page_key, title, content, meta_title, meta_description, last_updated_by)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                content = VALUES(content),
                meta_title = VALUES(meta_title),
                meta_description = VALUES(meta_description),
                last_updated_by = VALUES(last_updated_by)
            ');
            $stmt->execute([
                $_POST['page_key'],
                $_POST['title'],
                $_POST['content'],
                $_POST['meta_title'],
                $_POST['meta_description'],
                $_SESSION['user']['id']
            ]);
            $message = 'Page updated successfully!';
            $messageType = 'success';
        } elseif (isset($_POST['toggle_page'])) {
            // Toggle page active status
            $stmt = $pdo->prepare('UPDATE cms_pages SET is_active = NOT is_active WHERE page_key = ?');
            $stmt->execute([$_POST['page_key']]);
            $message = 'Page status updated!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get all CMS pages
$stmt = $pdo->query('SELECT * FROM cms_pages ORDER BY page_key ASC');
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert to associative array by page_key
$cms_pages = [];
foreach ($pages as $page) {
    $cms_pages[$page['page_key']] = $page;
}

// Define default pages if not in database
$default_pages = [
    'faq' => ['title' => 'Frequently Asked Questions', 'description' => 'Common questions and answers'],
    'about' => ['title' => 'About L9 Fitness', 'description' => 'Information about our gym'],
    'contact' => ['title' => 'Contact Us', 'description' => 'How to reach us'],
    'privacy' => ['title' => 'Privacy Policy', 'description' => 'Our privacy policy'],
    'terms' => ['title' => 'Terms of Service', 'description' => 'Terms and conditions']
];

// Get stats
$total_pages = count($cms_pages);
$active_pages = count(array_filter($cms_pages, function($page) { return $page['is_active']; }));
$last_updated = !empty($cms_pages) ? max(array_column($cms_pages, 'updated_at')) : null;
?>

<?php include __DIR__ . '/../app/views/layouts/header.php'; ?>

<div class="admin-hero py-4">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin.php">Admin Dashboard</a></li>
            <li class="breadcrumb-item active">Content Management</li>
          </ol>
        </nav>
        <h1 class="display-5 fw-bold text-gradient mb-0">Content Management</h1>
        <p class="lead">Edit pages like FAQ, About Us, Contact, Privacy, and Terms</p>
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
          <i class="bi bi-file-earmark-text"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $total_pages; ?></h4>
          <p class="card-description">Total Pages</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-eye"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $active_pages; ?></h4>
          <p class="card-description">Active Pages</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-md-6">
      <div class="admin-card stats-card">
        <div class="card-icon">
          <i class="bi bi-clock"></i>
        </div>
        <div class="card-content">
          <h4 class="card-title"><?php echo $last_updated ? date('M j', strtotime($last_updated)) : 'Never'; ?></h4>
          <p class="card-description">Last Updated</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Pages Grid -->
  <div class="row g-4">
    <?php foreach ($default_pages as $key => $page_info): ?>
    <div class="col-lg-6 col-xl-4">
      <div class="admin-card">
        <div class="card-content">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
              <h5 class="card-title mb-1"><?php echo htmlspecialchars($page_info['title']); ?></h5>
              <p class="text-muted small mb-0"><?php echo htmlspecialchars($page_info['description']); ?></p>
            </div>
            <div class="d-flex gap-1">
              <?php if (isset($cms_pages[$key]) && $cms_pages[$key]['is_active']): ?>
                <span class="badge bg-success">Active</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactive</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm flex-fill" onclick="editPage('<?php echo $key; ?>')">
              <i class="bi bi-pencil"></i> Edit
            </button>
            <form method="POST" class="d-inline">
              <input type="hidden" name="page_key" value="<?php echo $key; ?>">
              <button type="submit" name="toggle_page" class="btn btn-outline-secondary btn-sm"
                      onclick="return confirm('Toggle page status?')">
                <i class="bi bi-toggle-<?php echo (isset($cms_pages[$key]) && $cms_pages[$key]['is_active']) ? 'on' : 'off'; ?>"></i>
              </button>
            </form>
            <a href="/Capstone-latest/public/<?php echo $key; ?>.php" target="_blank" class="btn btn-outline-info btn-sm">
              <i class="bi bi-eye"></i>
            </a>
          </div>

          <?php if (isset($cms_pages[$key])): ?>
            <div class="mt-2 small text-muted">
              Last updated: <?php echo date('M j, Y H:i', strtotime($cms_pages[$key]['updated_at'])); ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Edit Page Modal -->
<div class="modal fade" id="editPageModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Page Content</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="pageForm">
        <div class="modal-body">
          <input type="hidden" name="page_key" id="edit_page_key">

          <div class="row">
            <div class="col-md-8">
              <div class="mb-3">
                <label class="form-label">Page Title</label>
                <input type="text" class="form-control" name="title" id="edit_title" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label class="form-label">Page Key</label>
                <input type="text" class="form-control" id="display_page_key" readonly>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea class="form-control" name="content" id="edit_content" rows="15" required></textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Meta Title (SEO)</label>
                <input type="text" class="form-control" name="meta_title" id="edit_meta_title">
                <div class="form-text">Appears in browser tab and search results</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Meta Description (SEO)</label>
                <textarea class="form-control" name="meta_description" id="edit_meta_description" rows="2"></textarea>
                <div class="form-text">Appears in search result snippets</div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_page" class="btn btn-admin">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editPage(pageKey) {
    // Set page key
    document.getElementById('edit_page_key').value = pageKey;
    document.getElementById('display_page_key').value = pageKey;

    // Load existing content if available
    <?php foreach ($cms_pages as $key => $page): ?>
    if (pageKey === '<?php echo $key; ?>') {
        document.getElementById('edit_title').value = <?php echo json_encode($page['title']); ?>;
        document.getElementById('edit_content').value = <?php echo json_encode($page['content']); ?>;
        document.getElementById('edit_meta_title').value = <?php echo json_encode($page['meta_title'] ?? ''); ?>;
        document.getElementById('edit_meta_description').value = <?php echo json_encode($page['meta_description'] ?? ''); ?>;
    }
    <?php endforeach; ?>

    // Set default content if not exists
    if (document.getElementById('edit_content').value === '') {
        const defaultContent = {
            'faq': '<h2>Frequently Asked Questions</h2><h3>General Questions</h3><p>Our FAQ content will be updated here.</p>',
            'about': '<h2>About L9 Fitness</h2><p>Learn about our mission, values, and what makes L9 Fitness special.</p>',
            'contact': '<h2>Contact Us</h2><p>Get in touch with our team for any questions or concerns.</p>',
            'privacy': '<h2>Privacy Policy</h2><p>How we protect and handle your personal information.</p>',
            'terms': '<h2>Terms of Service</h2><p>The terms and conditions for using our services.</p>'
        };

        if (defaultContent[pageKey]) {
            document.getElementById('edit_content').value = defaultContent[pageKey];
        }

        // Set default title
        const defaultTitles = {
            'faq': 'Frequently Asked Questions',
            'about': 'About L9 Fitness',
            'contact': 'Contact Us',
            'privacy': 'Privacy Policy',
            'terms': 'Terms of Service'
        };

        if (defaultTitles[pageKey] && document.getElementById('edit_title').value === '') {
            document.getElementById('edit_title').value = defaultTitles[pageKey];
        }
    }

    new bootstrap.Modal(document.getElementById('editPageModal')).show();
}
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>