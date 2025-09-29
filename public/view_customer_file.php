<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

// Check if user is logged in and is a trainer
require_login();
$current_user = current_user();
if (!$current_user || ($current_user['role_id'] !== 3 && $current_user['role_id'] !== 1)) {
    header('Location: login.php');
    exit;
}

$file_id = $_GET['id'] ?? '';
$trainer_id = $current_user['id'];

if (empty($file_id)) {
    die('File ID is required');
}

// Get file info and verify trainer has access
$file_query = "SELECT cf.*, u.first_name, u.last_name, u.email 
               FROM customer_files cf 
               JOIN users u ON cf.customer_id = u.id 
               WHERE cf.id = ? AND cf.assigned_trainer_id = ?";
$file_stmt = $pdo->prepare($file_query);
$file_stmt->execute([$file_id, $trainer_id]);
$file_info = $file_stmt->fetch();

if (!$file_info) {
    die('File not found or access denied');
}

// Update status to reviewed if it was pending
if ($file_info['status'] === 'pending') {
    $update_stmt = $pdo->prepare("UPDATE customer_files SET status = 'reviewed' WHERE id = ?");
    $update_stmt->execute([$file_id]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer File - <?php echo htmlspecialchars($file_info['file_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .file-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }
        .file-content {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 2rem;
            margin: 2rem 0;
            background: #f8f9fa;
        }
        .customer-info {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .suggestion-form {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="file-header">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h1><i class="fas fa-file-alt me-2"></i><?php echo htmlspecialchars($file_info['file_name']); ?></h1>
                    <p class="mb-0">Customer: <?php echo htmlspecialchars($file_info['first_name'] . ' ' . $file_info['last_name']); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="trainer_dashboard.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="row">
            <!-- Customer Information -->
            <div class="col-lg-4 mb-4">
                <div class="customer-info">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user me-2"></i>Customer Information
                    </h5>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($file_info['first_name'] . ' ' . $file_info['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($file_info['email']); ?></p>
                    <p><strong>File Status:</strong> 
                        <span class="badge bg-<?php 
                            echo $file_info['status'] === 'pending' ? 'warning' : 
                                ($file_info['status'] === 'reviewed' ? 'info' : 'success'); 
                        ?>">
                            <?php echo ucfirst($file_info['status']); ?>
                        </span>
                    </p>
                    <p><strong>Forwarded:</strong> <?php echo date('M j, Y g:i A', strtotime($file_info['forwarded_at'])); ?></p>
                    
                    <?php if ($file_info['notes']): ?>
                        <hr>
                        <h6>Admin Notes:</h6>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($file_info['notes'])); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="customer-info mt-3">
                    <h6 class="text-primary mb-3">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-sm" onclick="showSuggestionForm()">
                            <i class="fas fa-lightbulb me-1"></i>Add Suggestion
                        </button>
                        <button class="btn btn-info btn-sm" onclick="markCompleted()">
                            <i class="fas fa-check me-1"></i>Mark Completed
                        </button>
                        <a href="mailto:<?php echo htmlspecialchars($file_info['email']); ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>Email Customer
                        </a>
                    </div>
                </div>
            </div>

            <!-- File Content -->
            <div class="col-lg-8">
                <div class="file-content">
                    <h5 class="mb-3">
                        <i class="fas fa-file me-2"></i>File Content
                    </h5>
                    
                    <?php 
                    $file_path = __DIR__ . '/' . $file_info['file_path'];
                    $file_extension = strtolower(pathinfo($file_info['file_name'], PATHINFO_EXTENSION));
                    ?>

                    <?php if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <!-- Image file -->
                        <div class="text-center">
                            <img src="<?php echo htmlspecialchars($file_info['file_path']); ?>" 
                                 class="img-fluid rounded shadow" 
                                 alt="<?php echo htmlspecialchars($file_info['file_name']); ?>"
                                 style="max-height: 600px;">
                        </div>
                    
                    <?php elseif ($file_extension === 'pdf'): ?>
                        <!-- PDF file -->
                        <div class="text-center">
                            <embed src="<?php echo htmlspecialchars($file_info['file_path']); ?>" 
                                   type="application/pdf" 
                                   width="100%" 
                                   height="600px" 
                                   class="rounded shadow">
                            <p class="mt-3">
                                <a href="<?php echo htmlspecialchars($file_info['file_path']); ?>" 
                                   class="btn btn-primary" 
                                   target="_blank">
                                    <i class="fas fa-download me-1"></i>Download PDF
                                </a>
                            </p>
                        </div>

                    <?php elseif (in_array($file_extension, ['txt', 'csv'])): ?>
                        <!-- Text file -->
                        <div class="bg-light p-3 rounded">
                            <pre class="mb-0"><?php
                                if (file_exists($file_path)) {
                                    echo htmlspecialchars(file_get_contents($file_path));
                                } else {
                                    echo "File content not available for preview.";
                                }
                            ?></pre>
                        </div>

                    <?php else: ?>
                        <!-- Other file types -->
                        <div class="text-center py-5">
                            <i class="fas fa-file fa-5x text-muted mb-3"></i>
                            <h5>File Preview Not Available</h5>
                            <p class="text-muted">This file type cannot be previewed in the browser.</p>
                            <a href="<?php echo htmlspecialchars($file_info['file_path']); ?>" 
                               class="btn btn-primary" 
                               download>
                                <i class="fas fa-download me-1"></i>Download File
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Suggestion Form (Initially Hidden) -->
                <div id="suggestionForm" class="suggestion-form" style="display: none;">
                    <h5 class="text-success mb-3">
                        <i class="fas fa-lightbulb me-2"></i>Add Trainer Suggestion
                    </h5>
                    <form id="addSuggestionForm">
                        <input type="hidden" name="customer_id" value="<?php echo $file_info['customer_id']; ?>">
                        <input type="hidden" name="file_id" value="<?php echo $file_id; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Suggestion Title:</label>
                            <input type="text" class="form-control" name="title" 
                                   placeholder="e.g., Personalized Nutrition Plan" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Detailed Suggestion:</label>
                            <textarea class="form-control" name="suggestion" rows="4" 
                                     placeholder="Provide detailed suggestions based on the customer file..." required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Priority Level:</label>
                            <select class="form-select" name="priority">
                                <option value="low">Low - General Recommendation</option>
                                <option value="medium" selected>Medium - Important Suggestion</option>
                                <option value="high">High - Urgent Attention Needed</option>
                            </select>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane me-1"></i>Send Suggestion
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="hideSuggestionForm()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showSuggestionForm() {
            document.getElementById('suggestionForm').style.display = 'block';
            document.getElementById('suggestionForm').scrollIntoView({ behavior: 'smooth' });
        }

        function hideSuggestionForm() {
            document.getElementById('suggestionForm').style.display = 'none';
        }

        function markCompleted() {
            if (confirm('Mark this file as completed? This will notify the admin that you have finished reviewing it.')) {
                fetch('trainer_actions.php?action=mark_file_completed', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ file_id: <?php echo $file_id; ?> })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('File marked as completed!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        document.getElementById('addSuggestionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('trainer_actions.php?action=add_customer_suggestion', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Suggestion sent successfully!');
                    hideSuggestionForm();
                    this.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    </script>
</body>
</html>