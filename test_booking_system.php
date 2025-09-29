<?php
/**
 * Test Booking Management System
 */

require_once __DIR__ . '/config/config.php';

echo "Testing Booking Management System\n";
echo "==================================\n\n";

try {
    // Check bookings table structure
    $stmt = $pdo->query('DESCRIBE bookings');
    echo "Bookings table structure:\n";
    while ($row = $stmt->fetch()) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }

    echo "\n\nSample bookings with status:\n";
    $stmt = $pdo->query('SELECT id, member_id, class_id, status, booked_at FROM bookings LIMIT 5');
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']}, Member: {$row['member_id']}, Class: {$row['class_id']}, Status: {$row['status']}, Booked: {$row['booked_at']}\n";
    }

    // Check booking statistics
    echo "\n\nBooking Statistics:\n";
    $stmt = $pdo->query("
        SELECT 
            status,
            COUNT(*) as count,
            COUNT(CASE WHEN DATE(booked_at) = CURDATE() THEN 1 END) as today_count
        FROM bookings 
        GROUP BY status
    ");
    while ($row = $stmt->fetch()) {
        echo "Status: {$row['status']}, Total: {$row['count']}, Today: {$row['today_count']}\n";
    }

    // Test notification service
    echo "\n\nTesting Notification Service:\n";
    require_once __DIR__ . '/app/services/BookingNotificationService.php';
    $notificationService = new BookingNotificationService($pdo);
    echo "Notification service initialized successfully!\n";

    // Check if admin booking page exists
    if (file_exists(__DIR__ . '/public/admin_bookings.php')) {
        echo "Admin booking management page exists ✓\n";
    } else {
        echo "Admin booking management page missing ✗\n";
    }

    // Check if my_bookings page exists
    if (file_exists(__DIR__ . '/public/my_bookings.php')) {
        echo "User booking status page exists ✓\n";
    } else {
        echo "User booking status page missing ✗\n";
    }

    echo "\n✅ Booking Management System test completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>