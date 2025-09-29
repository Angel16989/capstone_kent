<?php
/**
 * Booking Notification Service
 * Handles email notifications for booking status changes
 */

class BookingNotificationService {
    private $pdo;
    private $config;
    
    public function __construct($pdo, $config = null) {
        $this->pdo = $pdo;
        $this->config = $config ?: [
            'smtp_host' => 'localhost',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'from_email' => 'noreply@l9fitness.com',
            'from_name' => 'L9 Fitness'
        ];
    }
    
    /**
     * Send booking confirmation email
     */
    public function sendBookingConfirmation($booking_id) {
        $booking = $this->getBookingDetails($booking_id);
        if (!$booking) return false;
        
        $subject = "Booking Confirmed - {$booking['class_name']}";
        $message = $this->generateConfirmationEmail($booking);
        
        return $this->sendEmail($booking['email'], $subject, $message);
    }
    
    /**
     * Send booking rejection email
     */
    public function sendBookingRejection($booking_id) {
        $booking = $this->getBookingDetails($booking_id);
        if (!$booking) return false;
        
        $subject = "Booking Update - {$booking['class_name']}";
        $message = $this->generateRejectionEmail($booking);
        
        return $this->sendEmail($booking['email'], $subject, $message);
    }
    
    /**
     * Get booking details with user and class info
     */
    private function getBookingDetails($booking_id) {
        $stmt = $this->pdo->prepare("
            SELECT b.*, c.name as class_name, c.date, c.time, c.instructor, c.description,
                   u.first_name, u.last_name, u.email,
                   admin_u.first_name as admin_first_name, admin_u.last_name as admin_last_name
            FROM bookings b
            JOIN classes c ON b.class_id = c.id
            JOIN users u ON b.member_id = u.id
            LEFT JOIN users admin_u ON (b.confirmed_by = admin_u.id OR b.rejected_by = admin_u.id)
            WHERE b.id = ?
        ");
        $stmt->execute([$booking_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generate confirmation email HTML
     */
    private function generateConfirmationEmail($booking) {
        $class_date = new DateTime($booking['date']);
        $formatted_date = $class_date->format('l, F j, Y');
        $formatted_time = date('g:i A', strtotime($booking['time']));
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Booking Confirmed</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 10px 0; border-bottom: 1px solid #eee; }
                .detail-label { font-weight: bold; color: #666; }
                .detail-value { color: #333; }
                .success-icon { font-size: 48px; color: #28a745; text-align: center; margin: 20px 0; }
                .footer { text-align: center; margin: 30px 0; color: #666; font-size: 14px; }
                .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎉 Booking Confirmed!</h1>
                </div>
                
                <div class='content'>
                    <div class='success-icon'>✅</div>
                    
                    <p>Hi {$booking['first_name']},</p>
                    
                    <p>Great news! Your booking has been confirmed. We're excited to see you in class!</p>
                    
                    <div class='booking-details'>
                        <h3 style='margin-top: 0; color: #28a745;'>Class Details</h3>
                        
                        <div class='detail-row'>
                            <span class='detail-label'>Class:</span>
                            <span class='detail-value'>{$booking['class_name']}</span>
                        </div>
                        
                        <div class='detail-row'>
                            <span class='detail-label'>Date:</span>
                            <span class='detail-value'>{$formatted_date}</span>
                        </div>
                        
                        <div class='detail-row'>
                            <span class='detail-label'>Time:</span>
                            <span class='detail-value'>{$formatted_time}</span>
                        </div>
                        
                        <div class='detail-row'>
                            <span class='detail-label'>Instructor:</span>
                            <span class='detail-value'>{$booking['instructor']}</span>
                        </div>
                        
                        <div class='detail-row' style='border-bottom: none;'>
                            <span class='detail-label'>Status:</span>
                            <span class='detail-value' style='color: #28a745; font-weight: bold;'>CONFIRMED</span>
                        </div>
                    </div>
                    
                    <div style='background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4 style='margin: 0 0 10px 0; color: #155724;'>Important Reminders:</h4>
                        <ul style='margin: 0; padding-left: 20px; color: #155724;'>
                            <li>Please arrive 10-15 minutes early</li>
                            <li>Bring a water bottle and towel</li>
                            <li>Wear comfortable workout attire</li>
                            <li>If you need to cancel, please do so at least 2 hours before class</li>
                        </ul>
                    </div>
                    
                    <div style='text-align: center;'>
                        <a href='" . BASE_URL . "my_bookings.php' class='btn'>View My Bookings</a>
                    </div>
                    
                    <p>Questions? Reply to this email or contact us at the gym.</p>
                    
                    <p>See you in class!<br>
                    <strong>The L9 Fitness Team</strong></p>
                </div>
                
                <div class='footer'>
                    <p>L9 Fitness Center<br>
                    Email: info@l9fitness.com | Phone: (555) 123-4567</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Generate rejection email HTML
     */
    private function generateRejectionEmail($booking) {
        $class_date = new DateTime($booking['date']);
        $formatted_date = $class_date->format('l, F j, Y');
        $formatted_time = date('g:i A', strtotime($booking['time']));
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Booking Update</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 10px 0; border-bottom: 1px solid #eee; }
                .detail-label { font-weight: bold; color: #666; }
                .detail-value { color: #333; }
                .footer { text-align: center; margin: 30px 0; color: #666; font-size: 14px; }
                .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
                .reason-box { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Booking Update</h1>
                </div>
                
                <div class='content'>
                    <p>Hi {$booking['first_name']},</p>
                    
                    <p>We're writing to inform you about an update to your class booking.</p>
                    
                    <div class='booking-details'>
                        <h3 style='margin-top: 0; color: #dc3545;'>Booking Details</h3>
                        
                        <div class='detail-row'>
                            <span class='detail-label'>Class:</span>
                            <span class='detail-value'>{$booking['class_name']}</span>
                        </div>
                        
                        <div class='detail-row'>
                            <span class='detail-label'>Date:</span>
                            <span class='detail-value'>{$formatted_date}</span>
                        </div>
                        
                        <div class='detail-row'>
                            <span class='detail-label'>Time:</span>
                            <span class='detail-value'>{$formatted_time}</span>
                        </div>
                        
                        <div class='detail-row'>
                            <span class='detail-label'>Instructor:</span>
                            <span class='detail-value'>{$booking['instructor']}</span>
                        </div>
                        
                        <div class='detail-row' style='border-bottom: none;'>
                            <span class='detail-label'>Status:</span>
                            <span class='detail-value' style='color: #dc3545; font-weight: bold;'>NOT CONFIRMED</span>
                        </div>
                    </div>";
                    
        if (!empty($booking['rejection_reason'])) {
            $message .= "
                    <div class='reason-box'>
                        <h4 style='margin: 0 0 10px 0; color: #856404;'>Reason:</h4>
                        <p style='margin: 0; color: #856404;'>{$booking['rejection_reason']}</p>
                    </div>";
        }
        
        $message .= "
                    <p>We apologize for any inconvenience. Here are some options:</p>
                    
                    <ul>
                        <li><strong>Book a different class:</strong> Check our schedule for alternative times</li>
                        <li><strong>Join the waitlist:</strong> We'll notify you if a spot opens up</li>
                        <li><strong>Contact us:</strong> Our team can help you find the perfect class</li>
                    </ul>
                    
                    <div style='text-align: center;'>
                        <a href='" . BASE_URL . "classes.php' class='btn'>Browse Classes</a>
                        <a href='" . BASE_URL . "my_bookings.php' class='btn' style='background: #6c757d;'>View My Bookings</a>
                    </div>
                    
                    <p>Thank you for your understanding. We look forward to seeing you in a future class!</p>
                    
                    <p>Best regards,<br>
                    <strong>The L9 Fitness Team</strong></p>
                </div>
                
                <div class='footer'>
                    <p>L9 Fitness Center<br>
                    Email: info@l9fitness.com | Phone: (555) 123-4567</p>
                </div>
            </div>
        </body>
        </html>";
        
        return $message;
    }
    
    /**
     * Send email using PHP mail() function
     * In production, replace with proper SMTP service
     */
    private function sendEmail($to, $subject, $message) {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->config['from_name'] . ' <' . $this->config['from_email'] . '>',
            'Reply-To: ' . $this->config['from_email'],
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Log email for debugging
        $this->logEmail($to, $subject, $message);
        
        // In development, you might want to just log emails instead of sending
        if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
            return true; // Simulate successful send
        }
        
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
    
    /**
     * Log email for debugging purposes
     */
    private function logEmail($to, $subject, $message) {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'subject' => $subject,
            'message_length' => strlen($message)
        ];
        
        $log_file = __DIR__ . '/../../logs/email_log.txt';
        $log_dir = dirname($log_file);
        
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Send daily booking summary to admins
     */
    public function sendDailyBookingSummary() {
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total_bookings,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_count,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count,
                COUNT(CASE WHEN DATE(booked_at) = CURDATE() THEN 1 END) as today_bookings
            FROM bookings
        ");
        
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get admin emails
        $stmt = $this->pdo->query("SELECT email FROM users WHERE role = 'admin'");
        $admin_emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($admin_emails)) return false;
        
        $subject = "Daily Booking Summary - " . date('F j, Y');
        $message = $this->generateDailySummaryEmail($stats);
        
        $success = true;
        foreach ($admin_emails as $email) {
            if (!$this->sendEmail($email, $subject, $message)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Generate daily summary email
     */
    private function generateDailySummaryEmail($stats) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Daily Booking Summary</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0; }
                .stat-card { background: white; padding: 20px; border-radius: 8px; text-align: center; }
                .stat-number { font-size: 24px; font-weight: bold; margin: 10px 0; }
                .pending { color: #ffc107; }
                .confirmed { color: #28a745; }
                .rejected { color: #dc3545; }
                .total { color: #007bff; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📊 Daily Booking Summary</h1>
                    <p>" . date('l, F j, Y') . "</p>
                </div>
                
                <div class='content'>
                    <p>Here's your daily booking overview:</p>
                    
                    <div class='stats-grid'>
                        <div class='stat-card'>
                            <div class='stat-number pending'>{$stats['pending_count']}</div>
                            <div>Pending Bookings</div>
                        </div>
                        
                        <div class='stat-card'>
                            <div class='stat-number confirmed'>{$stats['confirmed_count']}</div>
                            <div>Confirmed Today</div>
                        </div>
                        
                        <div class='stat-card'>
                            <div class='stat-number rejected'>{$stats['rejected_count']}</div>
                            <div>Rejected Today</div>
                        </div>
                        
                        <div class='stat-card'>
                            <div class='stat-number total'>{$stats['today_bookings']}</div>
                            <div>New Bookings Today</div>
                        </div>
                    </div>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . BASE_URL . "admin_bookings.php' style='display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;'>
                            Manage Bookings
                        </a>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}
?>