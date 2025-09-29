<?php
$pdo = new PDO('mysql:host=localhost;dbname=l9_gym', 'root', '');
echo "Payment history records:\n";
$stmt = $pdo->query('SELECT * FROM payment_history ORDER BY id DESC LIMIT 5');
while ($row = $stmt->fetch()) {
    echo "ID: {$row['id']}, Member: {$row['member_id']}, Amount: {$row['amount']}\n";
}
?>