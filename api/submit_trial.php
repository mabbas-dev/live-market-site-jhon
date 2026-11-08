<?php
/**
 * LIVETVMARKET - API: Submit 24h Trial Request
 * Handles both Free Trial leads (SMTP + Pushover) and Paid Test Line orders (Payment Gateway Bridge).
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
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

$fullName = trim($input['full_name'] ?? $input['name'] ?? '');
$email    = trim($input['email'] ?? '');
$whatsapp = trim($input['whatsapp'] ?? '');
$device   = 'All Devices';
$app      = '';

if (empty($fullName) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($whatsapp)) {
    echo json_encode(['success' => false, 'message' => 'Full Name, a valid Email address, and WhatsApp Number are all required.']);
    exit;
}

$trialMode     = strtolower(getSetting('trial_mode', 'free'));
$trialPrice    = floatval(getSetting('trial_price', '1.99'));
$trialDuration = getSetting('trial_duration_hours', '24');

try {
    if ($trialMode === 'paid') {
        // ─── PAID TEST LINE MODE: Create Order and Redirect to Gateway ────────
        $planName  = "Paid {$trialDuration}h Test Line";
        $currency  = 'USD';
        $country   = 'US';
        $deviceStr = 'All Devices';

        // Insert into orders table
        $stmt = $pdo->prepare("INSERT INTO `orders` (`customer_name`, `email`, `whatsapp`, `plan_name`, `connections`, `amount`, `currency`, `country`, `device`, `payment_channel`, `status`) VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?, 'Credit Card', 'New')");
        $stmt->execute([$fullName, $email, $whatsapp, $planName, $trialPrice, $currency, $country, $deviceStr]);
        $orderId = (int)$pdo->lastInsertId();

        // Generate signed payment bridge URL (notification & trial record are dispatched after payment in callback.php)
        $redirectUrl = createBridgeCheckoutUrl([
            'id'            => $orderId,
            'amount'        => $trialPrice,
            'currency'      => $currency,
            'customer_name' => $fullName,
            'email'         => $email,
            'whatsapp'      => $whatsapp
        ]);

        echo json_encode([
            'success'      => true,
            'is_paid'      => true,
            'order_id'     => $orderId,
            'redirect_url' => $redirectUrl,
            'message'      => 'Test line order created! Redirecting to secure payment checkout...'
        ]);
    } else {
        // ─── FREE TRIAL MODE: Record Lead & Send Instant Notifications ────────
        $stmt = $pdo->prepare("INSERT INTO `trial_leads` (`full_name`, `email`, `whatsapp`, `device`, `app`, `status`) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$fullName, $email, $whatsapp, $device, $app]);
        $leadId = (int)$pdo->lastInsertId();

        // Deliver Customer Email, Admin Alert Email & Pushover Push to Admin
        notifyTrialLead($leadId);

        echo json_encode([
            'success' => true,
            'is_paid' => false,
            'lead_id' => $leadId,
            'message' => 'Your 24h free trial request has been registered successfully! Check your email and WhatsApp for details.'
        ]);
    }
} catch (Exception $e) {
    error_log("[TRIAL SUBMISSION ERROR] " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A system error occurred. Please try again or contact WhatsApp support.']);
}
