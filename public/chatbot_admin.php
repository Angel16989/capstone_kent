<?php
require_once '../config/db.php';
require_once '../app/helpers/auth.php';

// Ensure user is logged in and is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Get chatbot conversation logs
$conversations = [];
try {
    $stmt = $pdo->query("
        SELECT cl.*, u.name as user_name, u.email as user_email 
        FROM chatbot_logs cl 
        LEFT JOIN users u ON cl.user_id = u.id 
        ORDER BY cl.created_at DESC 
        LIMIT 100
    ");
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching chatbot logs: " . $e->getMessage());
}

// Get conversation statistics
$stats = [];
try {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_conversations,
            COUNT(DISTINCT user_id) as unique_users,
            COUNT(CASE WHEN user_id IS NULL THEN 1 END) as anonymous_chats,
            DATE(created_at) as chat_date,
            COUNT(*) as daily_count
        FROM chatbot_logs 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
        GROUP BY DATE(created_at)
        ORDER BY chat_date DESC
    ");
    $daily_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_conversations,
            COUNT(DISTINCT user_id) as unique_users,
            COUNT(CASE WHEN user_id IS NULL THEN 1 END) as anonymous_chats
        FROM chatbot_logs
    ");
    $overall_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error fetching chatbot stats: " . $e->getMessage());
    $overall_stats = ['total_conversations' => 0, 'unique_users' => 0, 'anonymous_chats' => 0];
    $daily_stats = [];
}

include '../app/views/layouts/header.php';
?>

<style>
.chatbot-admin {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    min-height: 100vh;
    color: #fff;
}

.stats-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 255, 255, 0.3);
}

.stats-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #00ff88;
    text-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
}

.conversation-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    margin-bottom: 15px;
    padding: 15px;
    transition: all 0.3s ease;
}

.conversation-card:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: #00ff88;
}

.user-message {
    background: rgba(0, 123, 255, 0.2);
    border-left: 3px solid #007bff;
    padding: 10px;
    border-radius: 5px;
    margin: 5px 0;
}

.bot-response {
    background: rgba(40, 167, 69, 0.2);
    border-left: 3px solid #28a745;
    padding: 10px;
    border-radius: 5px;
    margin: 5px 0;
}

.timestamp {
    color: #888;
    font-size: 0.9rem;
}

.user-info {
    color: #00ff88;
    font-weight: bold;
}

.anonymous-user {
    color: #ffc107;
    font-style: italic;
}
</style>

<div class="chatbot-admin">
    <div class="container py-5">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="bi bi-robot"></i> Chatbot Admin Dashboard
                </h1>
                <p class="text-center lead">Monitor and analyze chatbot conversations</p>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-5">
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo number_format($overall_stats['total_conversations']); ?></div>
                    <h5>Total Conversations</h5>
                    <i class="bi bi-chat-dots fs-2 text-info"></i>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo number_format($overall_stats['unique_users']); ?></div>
                    <h5>Unique Users</h5>
                    <i class="bi bi-people fs-2 text-success"></i>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo number_format($overall_stats['anonymous_chats']); ?></div>
                    <h5>Anonymous Chats</h5>
                    <i class="bi bi-incognito fs-2 text-warning"></i>
                </div>
            </div>
        </div>

        <!-- Daily Statistics -->
        <?php if (!empty($daily_stats)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="stats-card">
                    <h4 class="mb-4"><i class="bi bi-graph-up"></i> Last 7 Days Activity</h4>
                    <div class="row">
                        <?php foreach ($daily_stats as $day): ?>
                        <div class="col">
                            <div class="text-center">
                                <div class="fw-bold text-info"><?php echo $day['daily_count']; ?></div>
                                <small><?php echo date('M j', strtotime($day['chat_date'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Conversations -->
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4"><i class="bi bi-clock-history"></i> Recent Conversations</h3>
                
                <?php if (empty($conversations)): ?>
                <div class="conversation-card text-center">
                    <p class="mb-0">No conversations yet. The chatbot is ready for action! 🤖</p>
                </div>
                <?php else: ?>
                
                <div style="max-height: 600px; overflow-y: auto;">
                    <?php foreach ($conversations as $conv): ?>
                    <div class="conversation-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <?php if ($conv['user_name']): ?>
                                <span class="user-info">
                                    <i class="bi bi-person-fill"></i> 
                                    <?php echo htmlspecialchars($conv['user_name']); ?>
                                    <small>(<?php echo htmlspecialchars($conv['user_email']); ?>)</small>
                                </span>
                                <?php else: ?>
                                <span class="anonymous-user">
                                    <i class="bi bi-incognito"></i> Anonymous User
                                </span>
                                <?php endif; ?>
                            </div>
                            <small class="timestamp">
                                <?php echo date('M j, Y g:i A', strtotime($conv['created_at'])); ?>
                            </small>
                        </div>
                        
                        <div class="user-message">
                            <strong>User:</strong> <?php echo htmlspecialchars($conv['message']); ?>
                        </div>
                        
                        <?php if ($conv['response']): ?>
                        <div class="bot-response">
                            <strong>Bot:</strong> <?php echo htmlspecialchars($conv['response']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php endif; ?>
            </div>
        </div>

        <!-- Back to Admin -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="admin.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-arrow-left"></i> Back to Admin Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../app/views/layouts/footer.php'; ?>
