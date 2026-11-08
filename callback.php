<?php
/**
 * LIVETVMARKET - Webhook Callback Receiver
 * Receives verified payment notification from payments.mabbas.dev bridge
 * and updates order status in MySQL database 'iptv-website'.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/payment_bridge.php';
require_once __DIR__ . '/config/notifications.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// Read raw JSON body
$rawPayload = file_get_contents('php://input');
$signature  = $_SERVER['HTTP_X_SIGNATURE'] ?? '';

if (empty($rawPayload)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Empty payload']);
    exit;
}

$data = json_decode($rawPayload, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

// Verify HMAC-SHA256 signature
$expectedSignature = hash_hmac('sha256', $rawPayload, BRIDGE_SECRET_KEY);
if (!hash_equals($expectedSignature, trim($signature))) {
    error_log("[CALLBACK ERROR] Invalid webhook signature from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    exit;
}

$orderId               = intval($data['order_id'] ?? 0);
$customerTransactionId = trim($data['customerTransactionId'] ?? '');
$gatewayOrderId        = trim($data['gatewayOrderId'] ?? '');
$status                = strtolower(trim($data['status'] ?? ''));
$gatewayStatus         = strtolower(trim($data['gatewayStatus'] ?? ''));

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing order_id']);
    exit;
}

try {
    // Determine new status
    $dbStatus = 'New';
    if ($status === 'paid' || $gatewayStatus === 'success') {
        $dbStatus = 'Paid';
    } elseif (in_array($status, ['failed', 'cancelled'], true)) {
        $dbStatus = 'Cancelled';
    }

    $refId = $customerTransactionId ?: $gatewayOrderId;

    // Update order in MySQL
    $stmt = $pdo->prepare("UPDATE `orders` SET `status` = ?, `transaction_id` = ? WHERE `id` = ?");
    $stmt->execute([$dbStatus, $refId, $orderId]);

    error_log("[CALLBACK SUCCESS] Order #{$orderId} status updated to '{$dbStatus}' (TX: {$refId})");

    // Trigger paid order notifications (Pushover alert with cashregister sound + SMTP emails)
    if ($dbStatus === 'Paid') {
        notifyOrderPaid($orderId, $refId);
    }

    http_response_code(200);
    echo json_encode([
        'status'   => 'ok',
        'order_id' => $orderId,
        'state'    => $dbStatus
    ]);
} catch (Exception $e) {
    error_log("[CALLBACK DB ERROR] " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

