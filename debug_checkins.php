<?php
$pdo = new PDO('mysql:host=localhost;dbname=l9_gym', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== CHECKING MEMBER CHECKINS TABLE ===\n";

// Check if table exists
try {
    $stmt = $pdo->query('DESCRIBE member_checkins');
    echo "member_checkins table structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']}: {$row['Type']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: member_checkins table doesn't exist: " . $e->getMessage() . "\n";
    exit;
}

// Check data in table
$stmt = $pdo->query('SELECT COUNT(*) as total FROM member_checkins');
$count = $stmt->fetchColumn();
echo "\nTotal check-ins in database: $count\n";

if ($count > 0) {
    $stmt = $pdo->query('SELECT * FROM member_checkins ORDER BY checkin_time DESC LIMIT 10');
    echo "\nRecent check-ins:\n";
    while ($checkin = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- Member ID: {$checkin['member_id']}, Check-in: {$checkin['checkin_time']}, Check-out: " . ($checkin['checkout_time'] ?? 'NULL') . "\n";
    }
}

// Check Michael Jackson's user ID
$stmt = $pdo->prepare('SELECT id, first_name, last_name, email FROM users WHERE email = ?');
$stmt->execute(['mj@l9fitness.com']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "\nMichael Jackson's user info:\n";
    echo "- ID: {$user['id']}\n";
    echo "- Name: {$user['first_name']} {$user['last_name']}\n";
    echo "- Email: {$user['email']}\n";
    
    // Check Michael's check-ins specifically
    $stmt = $pdo->prepare('SELECT * FROM member_checkins WHERE member_id = ? ORDER BY checkin_time DESC');
    $stmt->execute([$user['id']]);
    $mj_checkins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nMichael Jackson's check-ins: " . count($mj_checkins) . "\n";
    foreach ($mj_checkins as $checkin) {
        echo "- Check-in: {$checkin['checkin_time']}, Check-out: " . ($checkin['checkout_time'] ?? 'NULL') . "\n";
    }
} else {
    echo "\nMichael Jackson user not found!\n";
}
?>