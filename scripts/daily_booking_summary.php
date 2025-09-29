<?php
/**
 * Daily Booking Summary Cron Job
 * Run this script daily to send booking summaries to admins
 * 
 * To set up cron job, add this line to your crontab:
 * 0 8 * * * /usr/bin/php /path/to/your/project/scripts/daily_booking_summary.php
 */

// Set up paths
$root_dir = dirname(__DIR__);
require_once $root_dir . '/config/config.php';
require_once $root_dir . '/app/services/BookingNotificationService.php';

// Initialize notification service
$notificationService = new BookingNotificationService($pdo);

// Send daily summary
try {
    $result = $notificationService->sendDailyBookingSummary();
    
    if ($result) {
        echo "Daily booking summary sent successfully at " . date('Y-m-d H:i:s') . "\n";
    } else {
        echo "Failed to send daily booking summary at " . date('Y-m-d H:i:s') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error sending daily booking summary: " . $e->getMessage() . "\n";
}

// Log the execution
$log_file = $root_dir . '/logs/cron_log.txt';
$log_dir = dirname($log_file);

if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

$log_entry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'script' => 'daily_booking_summary.php',
    'status' => isset($result) && $result ? 'success' : 'failed'
];

file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
?>