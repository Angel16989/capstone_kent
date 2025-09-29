<?php
require_once '../../config/config.php';
require_once '../../app/helpers/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    die('Unauthorized');
}

$user_id = $_SESSION['user']['id'];
$invoice_no = $_GET['invoice'] ?? '';

if (empty($invoice_no)) {
    http_response_code(400);
    die('Invoice number required');
}

try {
    // Get payment details with security check (only user's own invoices)
    $stmt = $pdo->prepare("
        SELECT p.*, u.first_name, u.last_name, u.email, u.address, u.city, u.state, u.postcode,
               mp.name as plan_name, mp.description as plan_description, m.start_date, m.end_date
        FROM payments p
        JOIN users u ON p.member_id = u.id
        LEFT JOIN memberships m ON p.membership_id = m.id
        LEFT JOIN membership_plans mp ON m.plan_id = mp.id
        WHERE p.invoice_no = ? AND p.member_id = ?
    ");
    $stmt->execute([$invoice_no, $user_id]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        http_response_code(404);
        die('Invoice not found');
    }
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="L9_Fitness_Invoice_' . $invoice_no . '.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    
    // Generate PDF content (simplified HTML to PDF)
    // In a real application, you'd use a proper PDF library like TCPDF or FPDF
    
    // For now, we'll create an HTML invoice that can be saved as PDF
    header('Content-Type: text/html');
    header('Content-Disposition: inline; filename="L9_Fitness_Invoice_' . $invoice_no . '.html"');
    
    ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>L9 Fitness Invoice - <?php echo $invoice_no; ?></title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f8f9fa;
        }
        .invoice-container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .invoice-header { 
            background: linear-gradient(135deg, #ff4444, #ff6666);
            color: white; 
            padding: 30px; 
            text-align: center;
        }
        .invoice-header h1 { 
            margin: 0; 
            font-size: 2.5em; 
            font-weight: bold;
        }
        .invoice-header .tagline { 
            font-size: 1.2em; 
            opacity: 0.9; 
            margin-top: 10px;
        }
        .invoice-body { 
            padding: 40px; 
        }
        .invoice-info { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 40px;
        }
        .company-info, .customer-info { 
            flex: 1; 
        }
        .company-info h3, .customer-info h3 { 
            color: #ff4444; 
            border-bottom: 2px solid #ff4444; 
            padding-bottom: 10px;
        }
        .invoice-details { 
            background: #f8f9fa; 
            padding: 30px; 
            border-radius: 10px; 
            margin: 30px 0;
        }
        .detail-row { 
            display: flex; 
            justify-content: space-between; 
            padding: 10px 0; 
            border-bottom: 1px solid #dee2e6;
        }
        .detail-row.total { 
            font-weight: bold; 
            font-size: 1.3em; 
            color: #ff4444; 
            border-bottom: 3px solid #ff4444;
        }
        .payment-status { 
            text-align: center; 
            padding: 20px; 
            margin: 20px 0;
        }
        .status-paid { 
            background: #d4edda; 
            color: #155724; 
            border: 2px solid #c3e6cb;
            border-radius: 10px;
        }
        .invoice-footer { 
            background: #343a40; 
            color: white; 
            padding: 30px; 
            text-align: center;
        }
        .beast-mode { 
            font-weight: bold; 
            color: #ff4444; 
            text-transform: uppercase;
        }
        @media print {
            body { background: white; }
            .invoice-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>🏋️ L9 FITNESS GYM</h1>
            <div class="tagline">UNLEASH THE BEAST! 💪</div>
        </div>
        
        <div class="invoice-body">
            <div class="invoice-info">
                <div class="company-info">
                    <h3>From:</h3>
                    <strong>L9 Fitness Gym</strong><br>
                    123 Beast Mode Boulevard<br>
                    Muscle City, MC 12345<br>
                    Phone: (555) BEAST-MODE<br>
                    Email: admin@l9fitness.com
                </div>
                
                <div class="customer-info">
                    <h3>Bill To:</h3>
                    <strong><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></strong><br>
                    <?php echo htmlspecialchars($payment['email']); ?><br>
                    <?php if ($payment['address']): ?>
                        <?php echo htmlspecialchars($payment['address']); ?><br>
                        <?php echo htmlspecialchars($payment['city'] . ', ' . $payment['state'] . ' ' . $payment['postcode']); ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="invoice-details">
                <div class="detail-row">
                    <span><strong>Invoice Number:</strong></span>
                    <span><?php echo htmlspecialchars($invoice_no); ?></span>
                </div>
                <div class="detail-row">
                    <span><strong>Payment Date:</strong></span>
                    <span><?php echo date('F j, Y', strtotime($payment['paid_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span><strong>Payment Method:</strong></span>
                    <span><?php echo ucfirst($payment['method']); ?></span>
                </div>
                <?php if ($payment['txn_ref']): ?>
                <div class="detail-row">
                    <span><strong>Transaction ID:</strong></span>
                    <span><?php echo htmlspecialchars($payment['txn_ref']); ?></span>
                </div>
                <?php endif; ?>
                
                <hr style="margin: 20px 0;">
                
                <div class="detail-row">
                    <span><strong>Description:</strong></span>
                    <span><?php echo htmlspecialchars($payment['plan_name'] ?? 'L9 Fitness Service'); ?></span>
                </div>
                <?php if ($payment['start_date'] && $payment['end_date']): ?>
                <div class="detail-row">
                    <span><strong>Membership Period:</strong></span>
                    <span><?php echo date('M j, Y', strtotime($payment['start_date'])) . ' - ' . date('M j, Y', strtotime($payment['end_date'])); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="detail-row total">
                    <span>TOTAL AMOUNT:</span>
                    <span>$<?php echo number_format($payment['amount'], 2); ?></span>
                </div>
            </div>
            
            <div class="payment-status status-paid">
                <h3>✅ PAYMENT CONFIRMED</h3>
                <p>Your payment has been successfully processed. Welcome to the <span class="beast-mode">BEAST MODE</span> family!</p>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <p><strong>Thank you for choosing L9 Fitness!</strong></p>
                <p>Questions? Contact us at <strong>support@l9fitness.com</strong> or <strong>(555) BEAST-MODE</strong></p>
            </div>
        </div>
        
        <div class="invoice-footer">
            <p><strong>L9 FITNESS GYM</strong> - Where Warriors Are Forged 🔥</p>
            <p>Visit us: www.l9fitness.com | Follow us: @L9FitnessGym</p>
            <p style="font-size: 0.9em; opacity: 0.8;">This invoice was generated on <?php echo date('F j, Y \a\t g:i A'); ?></p>
        </div>
    </div>
    
    <script>
        // Auto-print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
    <?php
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Error generating invoice: " . $e->getMessage();
}
?>