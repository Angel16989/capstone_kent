<?php
/**
 * PayPal Checkout API Handler
 * PHP 8+ Compatible - L9 Fitness Gym
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/paypal_config.php';
require_once __DIR__ . '/../app/helpers/auth.php';

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }
    
    // Check if user is logged in
    if (!is_logged_in()) {
        throw new Exception('Authentication required', 401);
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input', 400);
    }
    
    $action = $input['action'] ?? '';
    $user_id = $_SESSION['user']['id'];
    
    switch ($action) {
        case 'create_order':
            $plan_id = (int)($input['plan_id'] ?? 0);
            
            // Validate plan
            $stmt = $pdo->prepare("SELECT * FROM membership_plans WHERE id = ? AND is_active = 1");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$plan) {
                throw new Exception('Invalid membership plan', 400);
            }
            
            // Create PayPal order
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'reference_id' => 'L9_MEMBERSHIP_' . $plan_id . '_' . $user_id,
                        'description' => $plan['name'] . ' Membership - L9 Fitness Gym',
                        'amount' => [
                            'currency_code' => PAYPAL_CURRENCY,
                            'value' => number_format((float)$plan['price'], 2, '.', '')
                        ],
                        'items' => [
                            [
                                'name' => $plan['name'] . ' Membership',
                                'description' => $plan['description'],
                                'quantity' => '1',
                                'category' => 'DIGITAL_GOODS',
                                'unit_amount' => [
                                    'currency_code' => PAYPAL_CURRENCY,
                                    'value' => number_format((float)$plan['price'], 2, '.', '')
                                ]
                            ]
                        ]
                    ]
                ],
                'application_context' => [
                    'brand_name' => 'L9 Fitness Gym',
                    'locale' => 'en-US',
                    'landing_page' => 'BILLING',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => BASE_URL . 'paypal_success.php',
                    'cancel_url' => BASE_URL . 'checkout.php?plan_id=' . $plan_id . '&cancelled=1'
                ]
            ];
            
            $paypalOrder = createPayPalPayment($orderData);
            
            if (!$paypalOrder) {
                throw new Exception('Failed to create PayPal order', 500);
            }
            
            // Store order in session for verification
            $_SESSION['paypal_order'] = [
                'order_id' => $paypalOrder['id'],
                'plan_id' => $plan_id,
                'amount' => $plan['price'],
                'created_at' => time()
            ];
            
            echo json_encode([
                'success' => true,
                'order_id' => $paypalOrder['id'],
                'approval_url' => $paypalOrder['links'][1]['href'] ?? null
            ]);
            break;
            
        case 'capture_order':
            $order_id = $input['order_id'] ?? '';
            
            if (empty($order_id)) {
                throw new Exception('Order ID required', 400);
            }
            
            // Verify order in session
            if (!isset($_SESSION['paypal_order']) || $_SESSION['paypal_order']['order_id'] !== $order_id) {
                throw new Exception('Invalid order session', 400);
            }
            
            $sessionOrder = $_SESSION['paypal_order'];
            
            // Capture PayPal payment
            $captureResult = capturePayPalPayment($order_id);
            
            if (!$captureResult || $captureResult['status'] !== 'COMPLETED') {
                throw new Exception('Payment capture failed', 500);
            }
            
            // Begin database transaction
            $pdo->beginTransaction();
            
            try {
                $plan_id = $sessionOrder['plan_id'];
                
                // Get plan details
                $stmt = $pdo->prepare("SELECT * FROM membership_plans WHERE id = ?");
                $stmt->execute([$plan_id]);
                $plan = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Check existing membership
                $stmt = $pdo->prepare("SELECT * FROM memberships WHERE member_id = ? AND status = 'active' AND end_date > NOW()");
                $stmt->execute([$user_id]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Extend existing membership
                    $start_date = max(new DateTime(), new DateTime($existing['end_date']));
                } else {
                    $start_date = new DateTime();
                }
                
                $end_date = clone $start_date;
                $end_date->add(new DateInterval('P' . $plan['duration_days'] . 'D'));
                
                // Create or update membership
                if ($existing) {
                    $stmt = $pdo->prepare("UPDATE memberships SET plan_id = ?, end_date = ?, total_fee = ?, status = 'active' WHERE member_id = ? AND status = 'active'");
                    $stmt->execute([$plan_id, $end_date->format('Y-m-d H:i:s'), $plan['price'], $user_id]);
                    $membership_id = $existing['id'];
                } else {
                    $stmt = $pdo->prepare("INSERT INTO memberships (member_id, plan_id, start_date, end_date, total_fee, status) VALUES (?, ?, ?, ?, ?, 'active')");
                    $stmt->execute([$user_id, $plan_id, $start_date->format('Y-m-d H:i:s'), $end_date->format('Y-m-d H:i:s'), $plan['price']]);
                    $membership_id = $pdo->lastInsertId();
                }
                
                // Generate invoice number
                $invoice_no = 'INV-' . date('Y') . '-' . str_pad((string)$membership_id, 6, '0', STR_PAD_LEFT);
                
                // Get PayPal transaction ID
                $paypal_txn_id = $captureResult['purchase_units'][0]['payments']['captures'][0]['id'] ?? $order_id;
                
                // Create payment record
                $stmt = $pdo->prepare("INSERT INTO payments (member_id, membership_id, amount, method, status, txn_ref, invoice_no, paid_at) VALUES (?, ?, ?, 'paypal', 'paid', ?, ?, NOW())");
                $stmt->execute([$user_id, $membership_id, $plan['price'], $paypal_txn_id, $invoice_no]);
                
                $pdo->commit();
                
                // Clear session
                unset($_SESSION['paypal_order']);
                unset($_SESSION['checkout_plan_id']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment successful! Welcome to L9 Fitness!',
                    'invoice_no' => $invoice_no,
                    'redirect_url' => BASE_URL . 'checkout-success.php?invoice=' . $invoice_no
                ]);
                
            } catch (Exception $e) {
                $pdo->rollback();
                throw $e;
            }
            break;
            
        case 'verify_order':
            $order_id = $input['order_id'] ?? '';
            
            if (empty($order_id)) {
                throw new Exception('Order ID required', 400);
            }
            
            // Verify with PayPal
            $orderDetails = verifyPayPalPayment($order_id);
            
            if (!$orderDetails) {
                throw new Exception('Order verification failed', 500);
            }
            
            echo json_encode([
                'success' => true,
                'status' => $orderDetails['status'],
                'order_details' => $orderDetails
            ]);
            break;
            
        default:
            throw new Exception('Invalid action', 400);
    }
    
} catch (Exception $e) {
    http_response_code($e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
    
    // Log error
    error_log('PayPal API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
}
?>