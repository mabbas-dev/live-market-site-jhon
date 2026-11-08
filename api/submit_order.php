<?php
/**
 * LIVETVMARKET - API: Submit Customer Order
 * Inserts customer subscription order directly into MySQL database 'iptv-website'.
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/coupons.php';
require_once __DIR__ . '/../config/payment_bridge.php';
require_once __DIR__ . '/../config/notifications.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Read raw JSON or form-encoded POST
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$customerName = trim($input['customer_name'] ?? $input['name'] ?? '');
$email = trim($input['email'] ?? '');
$whatsapp = trim($input['whatsapp'] ?? '');
$planName = trim($input['plan_name'] ?? '12 Months VIP Access');
$connections = intval($input['connections'] ?? 1);
$amount = floatval($input['amount'] ?? 74.99);
$currency = strtoupper(trim($input['currency'] ?? 'USD'));
$country = strtoupper(trim($input['country'] ?? 'US'));
$device = 'All Devices';
$paymentChannel = trim($input['payment_channel'] ?? 'Credit Card');
$couponUsed = trim($input['coupon_used'] ?? '');

if (empty($customerName) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($whatsapp)) {
    echo json_encode(['success' => false, 'message' => 'Full Name, a valid Activation Email address, and WhatsApp Number are all required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO `orders` (`customer_name`, `email`, `whatsapp`, `plan_name`, `connections`, `amount`, `currency`, `country`, `device`, `payment_channel`, `coupon_used`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'New')");
    $stmt->execute([$customerName, $email, $whatsapp, $planName, $connections, $amount, $currency, $country, $device, $paymentChannel, $couponUsed]);
    $orderId = $pdo->lastInsertId();

    if (!empty($couponUsed)) {
        incrementCouponUsage($couponUsed);
    }

    // Trigger admin notification email for new order
    notifyNewOrder((int)$orderId);

    $redirectUrl = null;
    // If Credit / Debit Card, create secure signed bridge checkout URL
    if (stripos($paymentChannel, 'Card') !== false || stripos($paymentChannel, 'Credit') !== false) {
        $redirectUrl = createBridgeCheckoutUrl([
            'id'            => $orderId,
            'amount'        => $amount,
            'currency'      => $currency,
            'customer_name' => $customerName,
            'email'         => $email,
            'whatsapp'      => $whatsapp
        ]);
    }

    echo json_encode([
        'success'      => true,
        'message'      => 'Order created successfully!',
        'order_id'     => $orderId,
        'redirect_url' => $redirectUrl,
        'name'         => $customerName,
        'email'        => $email,
        'whatsapp'     => $whatsapp,
        'plan_name'    => $planName
    ]);
} catch (Exception $e) {
    error_log("Error saving customer order: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please contact support.']);
}
