<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

if (!is_admin()) {
    http_response_code(403);
    echo 'Access denied';
    exit();
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo 'Feedback ID required';
    exit();
}

try {
    $stmt = $pdo->prepare('
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
        WHERE f.id = ?
    ');
    $stmt->execute([$_GET['id']]);
    $feedback = $stmt->fetch();

    if (!$feedback) {
        http_response_code(404);
        echo 'Feedback not found';
        exit();
    }

    // Return HTML for the modal
    ?>
    <div class="feedback-detail">
        <div class="row">
            <div class="col-md-6">
                <h6>Member Information</h6>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($feedback['user_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($feedback['user_email']); ?></p>
                <p><strong>Feedback Type:</strong> <?php echo ucfirst($feedback['feedback_type']); ?></p>
                <p><strong>Related To:</strong> <?php echo htmlspecialchars($feedback['related_name']); ?></p>
            </div>
            <div class="col-md-6">
                <h6>Feedback Details</h6>
                <p><strong>Date:</strong> <?php echo date('M j, Y H:i', strtotime($feedback['created_at'])); ?></p>
                <?php if ($feedback['rating']): ?>
                <p><strong>Rating:</strong>
                    <div class="rating-stars d-inline">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?php echo $i <= $feedback['rating'] ? '-fill text-warning' : ''; ?>"></i>
                        <?php endfor; ?>
                    </div>
                </p>
                <?php endif; ?>
                <p><strong>Status:</strong>
                    <span class="badge bg-<?php echo empty($feedback['reply_text']) ? 'warning' : 'success'; ?>">
                        <?php echo empty($feedback['reply_text']) ? 'Pending' : 'Responded'; ?>
                    </span>
                </p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Comments</h6>
                <div class="border p-3 rounded bg-light">
                    <?php echo nl2br(htmlspecialchars($feedback['comments'] ?? 'No comments provided')); ?>
                </div>
            </div>
        </div>
        <?php if (!empty($feedback['reply_text'])): ?>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Admin Response</h6>
                <div class="border p-3 rounded bg-success bg-opacity-10">
                    <?php echo nl2br(htmlspecialchars($feedback['reply_text'])); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php

} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
?>