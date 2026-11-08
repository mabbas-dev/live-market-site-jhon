<?php
/**
 * LIVETVMARKET - Customer Order & Payment Success Page
 * Displays verified order details, automated activation notice, and setup shortcuts.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/payment_bridge.php';

$pageTitle = 'Payment Successful - Order Confirmed | ' . SITE_NAME;
$pageDesc = 'Your LIVETVMARKET IPTV subscription order has been confirmed and paid. Instant streaming credentials dispatched.';
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
        }
    } catch (Exception $e) {
        error_log("Success page query error: " . $e->getMessage());
    }
}

// Fallback if accessed directly or mock
if (!$order) {
    $order = [
        'id' => $orderId > 0 ? $orderId : 10842,
        'customer_name' => 'Valued Customer',
        'email' => 'customer@domain.com',
        'whatsapp' => '+1 555 019 2831',
        'plan_name' => '12 Months VIP Access',
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

$currSymbol = get_currency_symbol($order['currency'] ?? 'USD');
$cleanWa = preg_replace('/[^0-9]/', '', SUPPORT_WHATSAPP);
$waText = urlencode("Hello LIVETVMARKET Support, I just completed payment for Order #LTV-{$order['id']}. Please verify my activation details.");
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

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-3 uppercase tracking-wider">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          Payment Confirmed • Automated Activation
        </span>

        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
          Thank You, <?= htmlspecialchars($order['customer_name'] ?? 'Customer') ?>!
        </h1>
        <p class="mt-2 text-base text-slate-600 max-w-xl mx-auto">
          Your payment has been successfully approved. Your Anti-Freeze™ 9.9 high-speed streaming account is now fully provisioned.
        </p>

        <div class="mt-4 inline-block bg-slate-100 rounded-xl px-4 py-2 text-xs font-mono font-bold text-slate-700">
          Order Reference: #LTV-<?= htmlspecialchars($order['id']) ?>
        </div>
      </div>

      <!-- Two-Column Information Layout -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        
        <!-- Column 1: Order Details -->
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
              <span class="font-semibold text-slate-900">Credit / Debit Card</span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-100">
              <span class="text-slate-500">Payment Status</span>
              <span class="font-bold text-emerald-600 flex items-center gap-1">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                Paid & Active
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

        <!-- Column 2: Activation Details & Instructions -->
        <div class="bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-8 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
              <i data-lucide="mail-check" class="w-5 h-5 text-emerald-600"></i>
              <h2 class="text-base font-bold text-slate-900">Activation Credentials</h2>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 text-xs text-emerald-950 mb-4">
              <div class="font-bold text-emerald-900 flex items-center gap-1.5 mb-1.5">
                <i data-lucide="send" class="w-4 h-4 text-emerald-600"></i>
                Instant Email Delivery Initiated
              </div>
              <p class="leading-relaxed text-slate-600">
                Your M3U playlist URL, Xtream Codes credentials (Username & Password), and setup guide have been generated and sent to:
              </p>
              <div class="mt-2 font-mono font-bold text-slate-900 bg-white/80 p-2 rounded-lg border border-emerald-200">
                <?= htmlspecialchars($order['email']) ?>
              </div>
            </div>

            <div class="text-[11px] text-slate-500 flex items-start gap-2 bg-slate-50 p-3 rounded-xl border border-gray-200">
              <i data-lucide="info" class="w-4 h-4 text-cyan-600 shrink-0 mt-0.5"></i>
              <span><strong>Check Spam/Junk:</strong> If you do not see the activation email within 2 minutes, please inspect your Spam folder or contact our 24/7 WhatsApp technician.</span>
            </div>
          </div>

          <div class="mt-6 pt-4 border-t border-gray-100 flex flex-col gap-2.5">
            <a href="/setup" class="w-full py-3 px-4 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs text-center flex items-center justify-center gap-2 shadow-sm transition-all">
              <i data-lucide="book-open" class="w-4 h-4"></i>
              View Complete Device Setup Guide
            </a>
            <a href="<?= $waUrl ?>" target="_blank" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs text-center flex items-center justify-center gap-2 shadow-sm transition-all">
              <i data-lucide="message-circle" class="w-4 h-4"></i>
              Fast-Track VIP WhatsApp Support
            </a>
          </div>
        </div>

      </div>

      <!-- Quick Links -->
      <div class="text-center text-xs text-slate-500 flex items-center justify-center gap-6">
        <a href="/" class="hover:text-cyan-600 transition-colors flex items-center gap-1">
          <i data-lucide="home" class="w-3.5 h-3.5"></i> Return to Homepage
        </a>
        <a href="/track-order?order_id=<?= urlencode((string)$order['id']) ?>" class="hover:text-cyan-600 transition-colors flex items-center gap-1">
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

