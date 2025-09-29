<?php
$pdo = new PDO('mysql:host=localhost;dbname=l9_gym', 'root', '');
echo "Announcements table structure:\n";
$stmt = $pdo->query('DESCRIBE announcements');
while ($row = $stmt->fetch()) {
    echo $row['Field'] . ' - ' . $row['Type'] . "\n";
}
?>