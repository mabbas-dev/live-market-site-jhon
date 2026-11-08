<?php
/**
 * LIVETVMARKET - API: Submit Digital Store Product Order
 * Handles orders for Netflix, Prime, Spotify, ChatGPT, etc.
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/store.php';
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
$paymentChannel = trim($input['payment_channel'] ?? 'Credit Card');

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

    $amount = $variation ? (float)$variation['price'] : (float)$product['starting_price'];
    $varName = $variation ? $variation['name'] : 'Standard Plan';
    $planTitle = "{$product['name']} ({$varName})";
    $currency = 'USD';
    $country = 'US';
    $device = 'All Devices';

    // Insert into orders table
    $stmt = $pdo->prepare("INSERT INTO `orders` (`customer_name`, `email`, `whatsapp`, `plan_name`, `order_type`, `product_id`, `variation_name`, `connections`, `amount`, `currency`, `country`, `device`, `payment_channel`, `status`) VALUES (?, ?, ?, ?, 'store', ?, ?, 1, ?, ?, ?, ?, ?, 'New')");
    $stmt->execute([$customerName, $email, $whatsapp, $planTitle, $productId, $varName, $amount, $currency, $country, $device, $paymentChannel]);
    $orderId = (int)$pdo->lastInsertId();

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
    } else {
        // Trigger notification for manual orders
        notifyNewOrder($orderId);
    }

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

