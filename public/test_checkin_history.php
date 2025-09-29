<!DOCTYPE html>
<html>
<head>
    <title>🔥 Check-in History Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #000000, #1a1a1a);
            color: white;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(255, 68, 68, 0.1);
            border: 2px solid #ff4444;
            border-radius: 15px;
            padding: 30px;
        }
        .status-good { color: #28a745; }
        .status-bad { color: #dc3545; }
        .data-row {
            background: rgba(0,0,0,0.5);
            padding: 15px;
            margin: 10px 0;
            border-radius: 10px;
            border: 1px solid #ff4444;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="text-center mb-4">🔥 Check-in History Debug</h1>
        
        <?php
        // Start session and check if user is logged in
        session_start();
        
        if (!isset($_SESSION['user']['id'])) {
            echo '<div class="alert alert-warning">❌ User not logged in. Please login first.</div>';
            echo '<a href="login.php" class="btn btn-primary">Login</a>';
            exit;
        }
        
        $user_id = $_SESSION['user']['id'];
        echo "<div class='alert alert-info'>✅ User logged in with ID: $user_id</div>";
        
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=l9_gym', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get check-in data exactly like profile.php does
            $stmt = $pdo->prepare("
                SELECT *, 
                       TIMESTAMPDIFF(MINUTE, checkin_time, COALESCE(checkout_time, NOW())) as duration_calc,
                       CASE WHEN checkout_time IS NULL THEN 'active' ELSE 'completed' END as status
                FROM member_checkins 
                WHERE member_id = ? 
                ORDER BY checkin_time DESC 
                LIMIT 50
            ");
            $stmt->execute([$user_id]);
            $check_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<div class='alert alert-success'>✅ Check-in query executed successfully</div>";
            echo "<div class='data-row'><strong>Total check-ins found:</strong> " . count($check_logs) . "</div>";
            
            if (count($check_logs) > 0) {
                echo "<h3>🏋️ Check-in History:</h3>";
                foreach ($check_logs as $log) {
                    echo "<div class='data-row'>";
                    echo "<strong>Date:</strong> " . date('M j, Y', strtotime($log['checkin_time'])) . "<br>";
                    echo "<strong>Check-in:</strong> " . date('g:i A', strtotime($log['checkin_time'])) . "<br>";
                    echo "<strong>Check-out:</strong> " . ($log['checkout_time'] ? date('g:i A', strtotime($log['checkout_time'])) : '<span class="status-good">🔥 Still Active</span>') . "<br>";
                    echo "<strong>Duration:</strong> " . $log['duration_calc'] . " minutes<br>";
                    echo "<strong>Status:</strong> " . ($log['status'] === 'active' ? '<span class="status-good">🔥 Active</span>' : '<span class="status-bad">✅ Complete</span>') . "<br>";
                    echo "<strong>Area:</strong> " . ucfirst(str_replace('_', ' ', $log['facility_area'] ?? 'Gym Floor'));
                    echo "</div>";
                }
            } else {
                echo "<div class='alert alert-warning'>❌ No check-in history found for this user</div>";
            }
            
            // Test direct profile page access
            echo "<h3>📋 Navigation Test:</h3>";
            echo "<div class='data-row'>";
            echo "<a href='profile.php' class='btn btn-primary me-2'>🔗 Go to Profile Page</a>";
            echo "<a href='profile.php#checkins' class='btn btn-success me-2'>🔗 Go to Check-ins Tab</a>";
            echo "<a href='dashboard.php' class='btn btn-warning'>🔗 Back to Dashboard</a>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>❌ Database Error: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
</body>
</html>