<?php
/**
 * LIVETVMARKET - API: Submit Digital Store Product Order
 * Handles orders for Netflix, Prime, Spotify, ChatGPT, etc.
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/store.php';
require_once __DIR__ . '/../config/coupons.php';
require_once __DIR__ . '/../config/payment_bridge.php';
require_once __DIR__ . '/../config/notifications.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isStoreEnabled()) {
    echo json_encode(['success' => false, 'message' => 'The digital store is currently undergoing maintenance.']);
    exit;
}

// Read raw JSON or form-encoded POST
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$productId    = (int)($input['product_id'] ?? 0);
$variationId  = (int)($input['variation_id'] ?? 0);
$customerName = trim($input['customer_name'] ?? $input['name'] ?? '');
$email        = trim($input['email'] ?? '');
$whatsapp     = trim($input['whatsapp'] ?? '');
$paymentChannelInput = trim($input['payment_channel'] ?? 'Credit Card');
if (stripos($paymentChannelInput, 'PayPal') !== false || stripos($paymentChannelInput, 'Crypto') !== false) {
    echo json_encode([
        'success' => false,
        'message' => 'PayPal and Crypto payments are currently coming soon. Please checkout securely via Credit / Debit Card.'
    ]);
    exit;
}
$paymentChannel = 'Credit Card';

if (empty($customerName) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($whatsapp)) {
    echo json_encode(['success' => false, 'message' => 'Full Name, a valid Delivery Email address, and WhatsApp Number are required.']);
    exit;
}

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid digital subscription service.']);
    exit;
}

try {
    $product = getStoreProductById($productId);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Selected product was not found.']);
        exit;
    }

    if ($product['status'] === 'out_of_stock') {
        echo json_encode(['success' => false, 'message' => 'Sorry, this product is currently out of stock.']);
        exit;
    }

    // Find variation
    $variation = null;
    $variations = $product['variations'] ?? [];
    if ($variationId > 0) {
        foreach ($variations as $v) {
            if ($v['id'] == $variationId) {
                $variation = $v;
                break;
            }
        }
    }
    if (!$variation && !empty($variations)) {
        $variation = $variations[0];
    }

    if ($variation && $variation['status'] === 'out_of_stock') {
        echo json_encode(['success' => false, 'message' => 'The selected plan variation is currently out of stock. Please choose another option.']);
        exit;
    }

    $baseAmount = $variation ? (float)$variation['price'] : (float)$product['starting_price'];
    $varName = $variation ? $variation['name'] : 'Standard Plan';
    $planTitle = "{$product['name']} ({$varName})";
    $currency = strtoupper(trim($input['currency'] ?? 'USD'));
    $country = strtoupper(trim($input['country'] ?? 'US'));
    $couponUsed = strtoupper(trim($input['coupon_used'] ?? ''));
    $device = 'All Devices';

    // Currency rate conversion
    $rate = 1.0;
    $allCurrencies = function_exists('getCurrencies') ? getCurrencies(true) : [];
    foreach ($allCurrencies as $ac) {
        if (strtoupper($ac['code']) === $currency) {
            $rate = (float)$ac['rate'];
            break;
        }
    }
    $convertedBaseAmount = round($baseAmount * $rate, 2);
    $finalAmount = $convertedBaseAmount;

    // Apply Coupon if supplied
    if (!empty($couponUsed)) {
        $couponRes = validateCouponCode($couponUsed, $convertedBaseAmount, '', 'store', $productId);
        if ($couponRes['valid']) {
            $discountVal = round($convertedBaseAmount * $couponRes['discount_rate'], 2);
            $finalAmount = max(0.01, round($convertedBaseAmount - $discountVal, 2));
            incrementCouponUsage($couponUsed);
        } else {
            $couponUsed = '';
        }
    }

    // Insert into orders table with coupon_used and converted amount
    $stmt = $pdo->prepare("INSERT INTO `orders` (`customer_name`, `email`, `whatsapp`, `plan_name`, `order_type`, `product_id`, `variation_name`, `connections`, `amount`, `currency`, `country`, `device`, `payment_channel`, `coupon_used`, `status`) VALUES (?, ?, ?, ?, 'store', ?, ?, 1, ?, ?, ?, ?, ?, ?, 'New')");
    $stmt->execute([$customerName, $email, $whatsapp, $planTitle, $productId, $varName, $finalAmount, $currency, $country, $device, $paymentChannel, $couponUsed]);
    $orderId = (int)$pdo->lastInsertId();

    // Trigger admin notification email for new store order
    notifyNewOrder($orderId);

    // Create secure signed bridge checkout URL
    $redirectUrl = createBridgeCheckoutUrl([
        'id'            => $orderId,
        'amount'        => $finalAmount,
        'currency'      => $currency,
        'customer_name' => $customerName,
        'email'         => $email,
        'whatsapp'      => $whatsapp
    ]);

    echo json_encode([
        'success'      => true,
        'order_id'     => $orderId,
        'redirect_url' => $redirectUrl,
        'message'      => 'Order created successfully! Redirecting to secure checkout...'
    ]);
} catch (Exception $e) {
    error_log("[SUBMIT STORE ORDER ERROR] " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A system error occurred. Please try again or order directly via WhatsApp.']);
}

