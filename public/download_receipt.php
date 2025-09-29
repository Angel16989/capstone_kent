<?php
session_start();
require_once '../config/config.php';
require_once '../app/helpers/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$receipt_id = $_GET['id'] ?? null;
if (!$receipt_id) {
    header('Location: profile.php');
    exit();
}

$user_id = $_SESSION['user']['id'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=l9_gym", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get receipt details
    $stmt = $pdo->prepare("
        SELECT pr.*, p.amount, p.payment_date, p.transaction_id, p.payment_method,
               m.plan_name, m.duration_months,
               u.first_name, u.last_name, u.email
        FROM payment_receipts pr
        LEFT JOIN payments p ON pr.payment_id = p.id
        LEFT JOIN memberships m ON p.membership_id = m.id
        LEFT JOIN users u ON pr.user_id = u.id
        WHERE pr.id = ? AND pr.user_id = ?
    ");
    $stmt->execute([$receipt_id, $user_id]);
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$receipt) {
        header('Location: profile.php');
        exit();
    }
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="L9_Fitness_Receipt_' . $receipt['id'] . '.pdf"');
    
    // Simple HTML to PDF conversion (you might want to use a proper PDF library)
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; border-bottom: 2px solid #ff6b35; padding-bottom: 20px; margin-bottom: 20px; }
            .company-name { color: #ff6b35; font-size: 24px; font-weight: bold; }
            .receipt-info { margin: 20px 0; }
            .customer-info { background: #f5f5f5; padding: 15px; border-radius: 5px; }
            .payment-details { margin: 20px 0; }
            .total { font-size: 18px; font-weight: bold; color: #ff6b35; }
            .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="company-name">L9 FITNESS</div>
            <p>Premium Fitness & Training Center</p>
        </div>
        
        <div class="receipt-info">
            <h2>Payment Receipt</h2>
            <p><strong>Receipt #:</strong> ' . htmlspecialchars($receipt['receipt_number']) . '</p>
            <p><strong>Date:</strong> ' . date('F j, Y', strtotime($receipt['payment_date'])) . '</p>
            <p><strong>Transaction ID:</strong> ' . htmlspecialchars($receipt['transaction_id']) . '</p>
        </div>
        
        <div class="customer-info">
            <h3>Customer Information</h3>
            <p><strong>Name:</strong> ' . htmlspecialchars($receipt['first_name'] . ' ' . $receipt['last_name']) . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($receipt['email']) . '</p>
        </div>
        
        <div class="payment-details">
            <h3>Payment Details</h3>
            <table width="100%" style="border-collapse: collapse;">
                <tr>
                    <td style="border-bottom: 1px solid #ddd; padding: 10px 0;"><strong>Membership Plan:</strong></td>
                    <td style="border-bottom: 1px solid #ddd; padding: 10px 0;">' . htmlspecialchars($receipt['plan_name']) . '</td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #ddd; padding: 10px 0;"><strong>Duration:</strong></td>
                    <td style="border-bottom: 1px solid #ddd; padding: 10px 0;">' . $receipt['duration_months'] . ' months</td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid #ddd; padding: 10px 0;"><strong>Payment Method:</strong></td>
                    <td style="border-bottom: 1px solid #ddd; padding: 10px 0;">' . ucfirst($receipt['payment_method']) . '</td>
                </tr>
                <tr>
                    <td style="padding: 15px 0;" class="total"><strong>Total Amount:</strong></td>
                    <td style="padding: 15px 0;" class="total"><strong>$' . number_format($receipt['amount'], 2) . ' AUD</strong></td>
                </tr>
            </table>
        </div>
        
        <div class="footer">
            <p>Thank you for choosing L9 Fitness!</p>
            <p>For support, contact us at support@l9fitness.com</p>
            <p>This is a computer-generated receipt.</p>
        </div>
    </body>
    </html>';
    
    // For now, we'll output the HTML. In production, you'd want to use a PDF library like TCPDF or DOMPDF
    header('Content-Type: text/html');
    echo $html;
    
} catch (Exception $e) {
    echo "Error generating receipt: " . $e->getMessage();
}
?>