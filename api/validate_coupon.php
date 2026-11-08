<?php
/**
 * LIVETVMARKET - API: Validate Coupon Code
 * Validates promo coupons in real time against MySQL database for % base discounts.
 * Supports IPTV packages, all Digital Store services, and specific products.
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/coupons.php';

// Support both POST (JSON / form) and GET
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST ?: $_GET;
}

$code      = trim($input['code'] ?? '');
$amount    = floatval($input['amount'] ?? 0.00);
$plan      = trim($input['plan'] ?? $input['plan_id'] ?? $input['plan_code'] ?? '');
$orderType = trim($input['type'] ?? $input['order_type'] ?? 'iptv');
$productId = (int)($input['product_id'] ?? 0);

if (empty($code)) {
    echo json_encode([
        'success' => false,
        'valid'   => false,
        'message' => 'Please enter a coupon code.'
    ]);
    exit;
}

$result = validateCouponCode($code, $amount, $plan, $orderType, $productId);

if ($result['valid']) {
    echo json_encode([
        'success'          => true,
        'valid'            => true,
        'code'             => $result['code'],
        'discount_percent' => $result['discount_percent'],
        'discount_rate'    => $result['discount_rate'],
        'valid_plans'      => $result['valid_plans'] ?? 'all',
        'description'      => $result['description'] ?? '',
        'message'          => $result['message']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'valid'   => false,
        'message' => $result['message']
    ]);
}
