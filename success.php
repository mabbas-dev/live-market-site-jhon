<?php
/**
 * LIVETVMARKET - Customer Order & Payment Success Page
 * Displays verified order details, automated activation notice, and setup shortcuts.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/payment_bridge.php';
require_once __DIR__ . '/config/notifications.php';

$pageTitle = 'Payment Successful - Order Confirmed | ' . SITE_NAME;
$pageDesc = 'Your subscription order has been confirmed and paid. Instant credentials dispatched.';
$pageCanonical = SITE_URL . '/success';
$activePage = 'orders';

$orderId = intval($_GET['order_id'] ?? $_GET['orderId'] ?? $_GET['id'] ?? 0);
$txid = trim($_GET['txid'] ?? $_GET['transactionId'] ?? '');

$order = null;

if ($pdo && $orderId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        // If order found and currently 'New', mark as 'Paid'
        if ($order && ($order['status'] === 'New' || empty($order['status']))) {
            $upd = $pdo->prepare("UPDATE `orders` SET `status` = 'Paid'" . (!empty($txid) ? ", `transaction_id` = ?" : "") . " WHERE `id` = ?");
            if (!empty($txid)) {
                $upd->execute([$txid, $orderId]);
            } else {
                $upd->execute([$orderId]);
            }
            $order['status'] = 'Paid';

            // Send instant Pushover & email credentials
            notifyOrderPaid($orderId, $txid);
        }
    } catch (Exception $e) {
        error_log("Success page query error: " . $e->getMessage());
    }
}

$isStore = false;
if ($order && !empty($order['order_type']) && $order['order_type'] === 'store') {
    $isStore = true;
} elseif (isset($_GET['type']) && $_GET['type'] === 'store') {
    $isStore = true;
}

// Fallback if accessed directly or mock
if (!$order) {
    if ($isStore) {
        $order = [
            'id' => $orderId > 0 ? $orderId : 10842,
            'customer_name' => 'Valued Customer',
            'email' => 'customer@domain.com',
            'whatsapp' => '+1 555 019 2831',
            'plan_name' => 'Netflix Premium (1 Month)',
            'order_type' => 'store',
            'variation_name' => '1 Month (Private Profile)',
            'connections' => 1,
            'amount' => 4.99,
            'currency' => 'USD',
            'country' => 'US',
            'device' => 'All Devices',
            'payment_channel' => 'Credit / Debit Card',
            'status' => 'Paid',
            'created_at' => date('Y-m-d H:i:s')
        ];
    } else {
        $order = [
            'id' => $orderId > 0 ? $orderId : 10842,
            'customer_name' => 'Valued Customer',
            'email' => 'customer@domain.com',
            'whatsapp' => '+1 555 019 2831',
            'plan_name' => '12 Months VIP Access',
            'order_type' => 'iptv',
            'connections' => 1,
            'amount' => 74.99,
            'currency' => 'USD',
            'country' => 'US',
            'device' => 'Amazon Firestick / Android TV',
            'payment_channel' => 'Credit / Debit Card',
            'status' => 'Paid',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}

$currSymbol = get_currency_symbol($order['currency'] ?? 'USD');
$cleanWa = preg_replace('/[^0-9]/', '', SUPPORT_WHATSAPP);

if ($isStore) {
    $waText = urlencode("Hello LIVETVMARKET Support, I completed payment for Order #LTV-{$order['id']} ({$order['plan_name']}). Please send my private profile credentials and PIN on WhatsApp.");
} else {
    $waText = urlencode("Hello LIVETVMARKET Support, I completed payment for Order #LTV-{$order['id']} ({$order['plan_name']}). Please connect with me to send my credentials on WhatsApp.");
}
$waUrl = "https://wa.me/{$cleanWa}?text={$waText}";

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/top-banner.php';
include __DIR__ . '/includes/navbar.php';
?>

  <main class="bg-slate-50 py-12 sm:py-20 min-h-[85vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Top Success Card -->
      <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xl overflow-hidden p-8 sm:p-12 mb-8 text-center relative">
        <div class="w-20 h-20 bg-emerald-50 border-2 border-emerald-500/30 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/10">
          <i data-lucide="check" class="w-10 h-10 text-emerald-600 stroke-[3]"></i>
        </div>

        <?php if ($isStore): ?>
          <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-3 uppercase tracking-wider">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Payment Approved • Digital Subscription Order
          </span>

          <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Thank You, <?= htmlspecialchars($order['customer_name'] ?? 'Customer') ?>!
          </h1>
          <p class="mt-2 text-base text-slate-600 max-w-xl mx-auto">
            Your payment has been successfully approved. Your order for <strong><?= htmlspecialchars($order['plan_name']) ?></strong> is confirmed. Our support team is preparing your private profile &amp; activation details.
          </p>
        <?php else: ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-3 uppercase tracking-wider">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Payment Approved • Agent Line Configuration
          </span>

          <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
            Thank You, <?= htmlspecialchars($order['customer_name'] ?? 'Customer') ?>!
          </h1>
          <p class="mt-2 text-base text-slate-600 max-w-xl mx-auto">
            Your payment has been successfully approved. Our technical support agent is currently preparing your subscription and will connect with you on WhatsApp shortly.
          </p>
        <?php endif; ?>

        <div class="mt-4 inline-block bg-slate-100 rounded-xl px-4 py-2 text-xs font-mono font-bold text-slate-700">
          Order Reference: #LTV-<?= htmlspecialchars($order['id']) ?>
        </div>
      </div>

      <!-- Two-Column Information Layout -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        
        <?php if ($isStore): ?>
          <!-- Column 1: Digital Subscription Summary -->
          <div class="bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-8 shadow-sm">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
              <i data-lucide="receipt" class="w-5 h-5 text-cyan-600"></i>
              <h2 class="text-base font-bold text-slate-900">Subscription Summary</h2>
            </div>

            <div class="space-y-3 text-xs">
              <div class="flex justify-between py-1.5 border-b border-gray-100">
                <span class="text-slate-500">Service Plan</span>
                <span class="font-bold text-slate-900"><?= htmlspecialchars($order['plan_name']) ?></span>
              </div>
              <?php if (!empty($order['variation_name'])): ?>
              <div class="flex justify-between py-1.5 border-b border-gray-100">
                <span class="text-slate-500">Plan Duration</span>
                <span class="font-semibold text-slate-900"><?= htmlspecialchars($order['variation_name']) ?></span>
              </div>
              <?php endif; ?>
              <div class="flex justify-between py-1.5 border-b border-gray-100">
                <span class="text-slate-500">Delivery Email</span>
                <span class="font-semibold text-slate-900"><?= htmlspecialchars($order['email']) ?></span>
              </div>
              <div class="flex justify-between py-1.5 border-b border-gray-100">
                <span class="text-slate-500">WhatsApp Number</span>
                <span class="font-semibold text-slate-900"><?= htmlspecialchars($order['whatsapp']) ?></span>
              </div>
              <div class="flex justify-between py-1.5 border-b border-gray-100">
                <span class="text-slate-500">Payment Channel</span>
                <span class="font-semibold text-slate-900"><?= htmlspecialchars($order['payment_channel'] ?? 'Credit / Debit Card') ?></span>
              </div>
              <div class="flex justify-between py-1.5 border-b border-gray-100">
                <span class="text-slate-500">Payment Status</span>
                <span class="font-bold text-emerald-600 flex items-center gap-1">
                  <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                  Paid &amp; Approved
                </span>
              </div>
              <div class="flex justify-between pt-2 text-sm font-extrabold text-slate-900">
                <span>Total Paid</span>
                <span class="text-cyan-700 font-mono text-base">
                  <?= htmlspecialchars($currSymbol) ?> <?= number_format((float)$order['amount'], 2) ?> <?= htmlspecialchars($order['currency']) ?>
                </span>
              </div>
            </div>
          </div>

          <!-- Column 2: Digital Delivery Details -->
          <div class="bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-8 shadow-sm flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                <i data-lucide="sparkles" class="w-5 h-5 text-cyan-600"></i>
                <h2 class="text-base font-bold text-slate-900">Profile &amp; Credentials Delivery</h2>
              </div>

              <div class="p-4 rounded-2xl bg-cyan-50/70 border border-cyan-200/80 text-xs text-cyan-950 mb-4">
                <div class="font-bold text-cyan-950 flex items-center gap-1.5 mb-1.5">
                  <i data-lucide="send" class="w-4 h-4 text-cyan-600"></i>
                  Support Agent Preparing Your Subscription
                </div>
                <p class="leading-relaxed text-slate-600">
                  Our support agent is currently preparing your dedicated account and private profile. <strong>Please wait for our agent to connect with you directly on your provided WhatsApp number</strong> (<?= htmlspecialchars($order['whatsapp']) ?>) and Email.
                </p>
                <div class="mt-2.5 space-y-1 text-slate-800 font-mono text-[11px] bg-white/90 p-2.5 rounded-lg border border-cyan-100">
                  <div><span class="text-slate-500 font-sans">WhatsApp:</span> <strong><?= htmlspecialchars($order['whatsapp']) ?></strong></div>
                  <div><span class="text-slate-500 font-sans">Email:</span> <strong><?= htmlspecialchars($order['email']) ?></strong></div>
                </div>
              </div>

              <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1.5">
                <div class="flex items-center gap-1.5 text-emerald-700 font-bold">
                  <i data-lucide="shield-check" class="w-4 h-4"></i>
                  <span>100% Replacement Warranty Included</span>
                </div>
                <p class="text-slate-500 leading-normal">
                  Every digital service is backed by a full replacement warranty for your complete subscription period. If you ever need help or an update, our team is available 24/7 on WhatsApp.
                </p>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 flex flex-col gap-2.5">
              <a href="<?= $waUrl ?>" target="_blank" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs text-center flex items-center justify-center gap-2 shadow-sm transition-all">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                Connect on WhatsApp for Instant Setup &amp; PIN
              </a>
              <a href="/shop" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs text-center flex items-center justify-center gap-2 transition-all">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                Browse More Subscriptions
              </a>
            </div>
          </div>

        <?php else: ?>
          <!-- Column 1: IPTV Order Details -->
          <div class="bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-8 shadow-sm">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
              <i data-lucide="receipt" class="w-5 h-5 text-cyan-600"></i>
              <h2 class="text-base font-bold text-slate-900">Subscription Summary</h2>
            </div>

            <div class="space-y-3 text-xs">
              <div class="flex justify-between py-1.5 border-b border-gray-100">
                <span class="text-slate-500">Plan Purchased</span>
                <span class="font-bold text-slate-900"><?= htmlspecialchars($order['plan_name']) ?></span>
              </div>
              <div class="flex justify-between py-1.5 border-b border-gray-100">
                <span class="text-slate-500">Device Connections</span>
                <span class="font-semibold text-slate-900"><?= htmlspecialchars($order['connections']) ?> Connection(s)</span>
              </div>
              <div class="flex justify-between py-1.5 border-b border-gray-100">
                <span class="text-slate-500">Target Device</span>
                <span class="font-semibold text-slate-900"><?= htmlspecialchars($order['device'] ?? 'Smart TV / Firestick') ?></span>
              </div>
              <div class="flex justify-between py-1.5 border-b border-gray-100">
                <span class="text-slate-500">Payment Channel</span>
                <span class="font-semibold text-slate-900"><?= htmlspecialchars($order['payment_channel'] ?? 'Credit / Debit Card') ?></span>
              </div>
              <div class="flex justify-between py-1.5 border-b border-gray-100">
                <span class="text-slate-500">Payment Status</span>
                <span class="font-bold text-emerald-600 flex items-center gap-1">
                  <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                  Paid &amp; Active
                </span>
              </div>
              <div class="flex justify-between pt-2 text-sm font-extrabold text-slate-900">
                <span>Total Paid</span>
                <span class="text-cyan-700 font-mono text-base">
                  <?= htmlspecialchars($currSymbol) ?> <?= number_format((float)$order['amount'], 2) ?> <?= htmlspecialchars($order['currency']) ?>
                </span>
              </div>
            </div>
          </div>

          <!-- Column 2: IPTV Activation Details & Instructions -->
          <div class="bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-8 shadow-sm flex flex-col justify-between">
            <div>
              <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
                <i data-lucide="user-check" class="w-5 h-5 text-cyan-600"></i>
                <h2 class="text-base font-bold text-slate-900">Agent Account Preparation</h2>
              </div>

              <div class="p-4 rounded-2xl bg-cyan-50/70 border border-cyan-200/80 text-xs text-cyan-950 mb-4">
                <div class="font-bold text-cyan-950 flex items-center gap-1.5 mb-1.5">
                  <i data-lucide="send" class="w-4 h-4 text-cyan-600"></i>
                  Technical Agent Assigned to Your Order
                </div>
                <p class="leading-relaxed text-slate-600">
                  Our technical agent is preparing and verifying your dedicated account line. <strong>Please wait for our support agent to connect with you directly on your provided WhatsApp number</strong> (<?= htmlspecialchars($order['whatsapp']) ?>) and Email.
                </p>
                <div class="mt-2.5 space-y-1 text-slate-800 font-mono text-[11px] bg-white/90 p-2.5 rounded-lg border border-cyan-100">
                  <div><span class="text-slate-500 font-sans">WhatsApp:</span> <strong><?= htmlspecialchars($order['whatsapp']) ?></strong></div>
                  <div><span class="text-slate-500 font-sans">Email:</span> <strong><?= htmlspecialchars($order['email']) ?></strong></div>
                </div>
              </div>

              <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 space-y-1.5">
                <div class="flex items-center gap-1.5 text-emerald-700 font-bold">
                  <i data-lucide="zap" class="w-4 h-4"></i>
                  <span>Need Instant Setup Right Away?</span>
                </div>
                <p class="text-slate-500 leading-normal">
                  Your credentials will be sent directly on WhatsApp. If you want instant activation without any wait, connect with our 24/7 technical desk below.
                </p>
              </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 flex flex-col gap-2.5">
              <a href="<?= $waUrl ?>" target="_blank" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs text-center flex items-center justify-center gap-2 shadow-sm transition-all">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                Connect with Agent on WhatsApp for Instant Setup
              </a>
              <a href="/setup" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs text-center flex items-center justify-center gap-2 transition-all">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                View Step-by-Step Setup Guides
              </a>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Quick Links -->
      <div class="text-center text-xs text-slate-500 flex items-center justify-center gap-6">
        <a href="/" class="hover:text-cyan-600 transition-colors flex items-center gap-1">
          <i data-lucide="home" class="w-3.5 h-3.5"></i> Return to Homepage
        </a>
        <a href="/track-order?order_id=<?= urlencode((string)$order['id']) ?><?= $isStore ? '&type=store' : '' ?>" class="hover:text-cyan-600 transition-colors flex items-center gap-1">
          <i data-lucide="truck" class="w-3.5 h-3.5"></i> Track Activation Status
        </a>
        <a href="/contact" class="hover:text-cyan-600 transition-colors flex items-center gap-1">
          <i data-lucide="life-buoy" class="w-3.5 h-3.5"></i> Contact Support
        </a>
      </div>

    </div>
  </main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/modals.php';
?>
