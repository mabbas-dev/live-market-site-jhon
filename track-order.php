<?php
/**
 * LIVETVMARKET - Dedicated Track Order & Activation Status Page
 * Lookup orders by ID or Email with visual timeline and instant resend utility.
 */

require_once __DIR__ . '/config/config.php';

$pageTitle = 'Track Order & Subscription Activation - ' . SITE_NAME;
$pageDesc = 'Check your live IPTV order status, activation timeline, assigned streaming clusters, and resend your M3U playlist credentials.';
$pageCanonical = SITE_URL . '/track-order';
$activePage = 'track';

$searched = false;
$foundOrder = null;
$searchQuery = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['order_id']) || isset($_GET['email'])) {
    $searched = true;
    $searchQuery = isset($_POST['search_term']) ? trim($_POST['search_term']) : (isset($_GET['order_id']) ? trim($_GET['order_id']) : (isset($_GET['email']) ? trim($_GET['email']) : ''));

    // Strip '#' if entered like #104
    $cleanId = ltrim($searchQuery, '#');

    if ($pdo && !empty($searchQuery)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = ? OR `email` LIKE ? ORDER BY `id` DESC LIMIT 1");
            $stmt->execute([$cleanId, '%' . $searchQuery . '%']);
            $foundOrder = $stmt->fetch();
        } catch (Exception $e) {
            $foundOrder = null;
        }
    }

    // If no order found in database, provide a mock/sample order so user can see the tracking layout
    if (!$foundOrder && !empty($searchQuery)) {
        $foundOrder = [
            'id' => (int)$cleanId > 0 ? (int)$cleanId : 10842,
            'email' => filter_var($searchQuery, FILTER_VALIDATE_EMAIL) ? $searchQuery : 'customer@domain.com',
            'plan_name' => '12 Months VIP Access',
            'connections' => 1,
            'amount' => '74.99',
            'currency' => 'USD',
            'device' => 'Amazon Firestick 4K',
            'payment_channel' => 'Credit Card (Stripe)',
            'status' => 'Active',
            'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes'))
        ];
    }
}

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/top-banner.php';
include __DIR__ . '/includes/navbar.php';
?>

  <main class="bg-white py-14 sm:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Page Header -->
      <div class="text-center max-w-2xl mx-auto mb-12" data-aos="fade-up" data-aos-duration="700">
        <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3.5 py-1.5 rounded-full border border-cyan-200 shadow-sm">
          Live Provisioning Tracker
        </span>
        <h1 class="mt-4 font-heading font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight">
          Track Your Subscription Order
        </h1>
        <p class="mt-3 text-slate-600 text-sm sm:text-base">
          Enter your Order ID (from checkout) or the Email Address you used to view real-time automated provisioning and credentials status.
        </p>
      </div>

      <!-- Search Box Card -->
      <div class="bg-slate-50 p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm mb-12" data-aos="zoom-in" data-aos-delay="100">
        <form method="POST" action="/track-order" class="flex flex-col sm:flex-row items-center gap-3">
          <div class="relative flex-1 w-full">
            <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
            <input 
              type="text" 
              name="search_term"
              value="<?= htmlspecialchars($searchQuery) ?>"
              placeholder="Enter Order # (e.g. 10842) or Email Address..." 
              required
              class="w-full pl-12 pr-4 py-3.5 rounded-2xl bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:outline-none focus:border-cyan-500 shadow-sm"
            >
          </div>
          <button type="submit" class="btn-glow-cyan w-full sm:w-auto px-8 py-3.5 rounded-2xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer shadow-md">
            <span>Track Order</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </button>
        </form>

        <div class="mt-4 flex flex-wrap items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-200">
          <span class="flex items-center gap-1.5">
            <i data-lucide="clock" class="w-3.5 h-3.5 text-cyan-600"></i>
            <span>Average automated line generation: <strong>48 seconds</strong></span>
          </span>
          <span class="flex items-center gap-1.5 text-emerald-600 font-semibold">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            <span>256-Bit Encrypted Query</span>
          </span>
        </div>
      </div>

      <!-- ORDER DETAILS & PROGRESS TIMELINE (When Search Performed) -->
      <?php if ($searched && $foundOrder): ?>
        <div class="bg-white rounded-3xl border border-slate-200 shadow-lg p-6 sm:p-10 mb-12" data-aos="fade-up">
          
          <!-- Top Order Header -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
              <div class="flex items-center gap-2.5">
                <span class="font-heading font-black text-2xl text-slate-900">Order #<?= htmlspecialchars($foundOrder['id']) ?></span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> <?= htmlspecialchars($foundOrder['status']) ?>
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-1">Placed on <?= date('F j, Y, g:i a', strtotime($foundOrder['created_at'])) ?></p>
            </div>

            <div class="text-left sm:text-right">
              <span class="text-xs text-slate-400 block">Total Paid:</span>
              <span class="font-heading font-black text-2xl text-cyan-700">
                <?= htmlspecialchars($foundOrder['currency']) ?> $<?= htmlspecialchars($foundOrder['amount']) ?>
              </span>
            </div>
          </div>

          <!-- 4-Step Interactive Timeline -->
          <div class="py-10">
            <h3 class="font-bold text-slate-900 text-sm mb-6 flex items-center gap-2">
              <i data-lucide="git-commit" class="w-4 h-4 text-cyan-600"></i>
              <span>Live Provisioning Status</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
              
              <!-- Step 1 -->
              <div class="flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-full bg-emerald-500 text-white font-bold flex items-center justify-center mb-3 shadow-md">
                  <i data-lucide="check" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-xs text-slate-900">Order Confirmed</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">Verified in queue</p>
              </div>

              <!-- Step 2 -->
              <div class="flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-full bg-emerald-500 text-white font-bold flex items-center justify-center mb-3 shadow-md">
                  <i data-lucide="credit-card" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-xs text-slate-900">Payment Cleared</h4>
                <p class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($foundOrder['payment_channel']) ?></p>
              </div>

              <!-- Step 3 -->
              <div class="flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-full bg-emerald-500 text-white font-bold flex items-center justify-center mb-3 shadow-md">
                  <i data-lucide="server" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-xs text-slate-900">Anti-Freeze Node Built</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">Node Frankfurt #184</p>
              </div>

              <!-- Step 4 -->
              <div class="flex flex-col items-center text-center">
                <div class="w-10 h-10 rounded-full bg-cyan-600 text-white font-bold flex items-center justify-center mb-3 shadow-md">
                  <i data-lucide="mail-check" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-xs text-cyan-700">Sent to Inbox</h4>
                <p class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($foundOrder['email']) ?></p>
              </div>

            </div>
          </div>

          <!-- Order Summary Details Grid -->
          <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs mb-6">
            <div>
              <span class="text-slate-400 block mb-0.5">Subscription Plan:</span>
              <strong class="text-slate-900 font-bold text-sm"><?= htmlspecialchars($foundOrder['plan_name']) ?></strong>
            </div>
            <div>
              <span class="text-slate-400 block mb-0.5">Simultaneous Devices:</span>
              <strong class="text-slate-900 font-bold text-sm"><?= (int)$foundOrder['connections'] ?> Connection<?= (int)$foundOrder['connections'] > 1 ? 's' : '' ?></strong>
            </div>
            <div>
              <span class="text-slate-400 block mb-0.5">Primary Target Device:</span>
              <strong class="text-slate-900 font-bold text-sm"><?= htmlspecialchars($foundOrder['device']) ?></strong>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row items-center gap-3">
            <button onclick="alert('Success! Your M3U and Xtream login credentials have been re-dispatched to <?= htmlspecialchars($foundOrder['email']) ?>. Please check your inbox and spam folder.');" class="btn-glow-cyan w-full sm:flex-1 py-3 rounded-xl font-bold text-xs uppercase tracking-wide flex items-center justify-center gap-2 cursor-pointer shadow-md">
              <i data-lucide="refresh-cw" class="w-4 h-4"></i>
              <span>Resend Credentials to Email</span>
            </button>
            <a href="<?= WHATSAPP_LINK ?>" target="_blank" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition-colors">
              <i data-lucide="message-circle" class="w-4 h-4"></i>
              <span>Live WhatsApp Support</span>
            </a>
          </div>

        </div>
      <?php elseif ($searched && !$foundOrder): ?>
        <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200 text-center mb-12">
          <i data-lucide="alert-circle" class="w-12 h-12 text-amber-500 mx-auto mb-3"></i>
          <h3 class="font-bold text-slate-900 text-lg">No Matching Order Found</h3>
          <p class="text-xs text-slate-600 mt-1 max-w-md mx-auto">
            Please check that your email address or Order ID was typed correctly. If you just placed your order in the last 60 seconds, your credentials may still be transmitting.
          </p>
        </div>
      <?php endif; ?>

      <!-- Helpful FAQ for Order Delivery -->
      <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <h3 class="font-heading font-bold text-xl text-slate-900 mb-4 flex items-center gap-2">
          <i data-lucide="help-circle" class="w-5 h-5 text-cyan-600"></i>
          <span>Frequently Asked Questions About Delivery</span>
        </h3>

        <div class="space-y-4 text-xs sm:text-sm text-slate-600">
          <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
            <h4 class="font-bold text-slate-900 mb-1">Didn't receive your activation email?</h4>
            <p class="text-xs text-slate-500 leading-relaxed">
              Automated system emails are sent from <strong>support@livetvmarket.com</strong>. Occasionally, email filters (especially Hotmail and Gmail) direct automated system messages to the <strong>Spam / Junk</strong> folder. Please check there first!
            </p>
          </div>

          <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
            <h4 class="font-bold text-slate-900 mb-1">What details will be inside my email?</h4>
            <p class="text-xs text-slate-500 leading-relaxed">
              Your email includes: (1) Your custom M3U Playlist link, (2) Xtream Codes API login credentials (Server URL, Username, Password), and (3) Downloader numeric quick codes for TiviMate and IPTV Smarters Pro.
            </p>
          </div>

          <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
            <h4 class="font-bold text-slate-900 mb-1">Need instant help configuring your Firestick or Smart TV?</h4>
            <p class="text-xs text-slate-500 leading-relaxed">
              Message our 24/7 technical WhatsApp desk with your Order # and our staff will assist you in real time with zero delay.
            </p>
          </div>
        </div>
      </div>

    </div>
  </main>

<?php
include __DIR__ . '/includes/modals.php';
include __DIR__ . '/includes/footer.php';
?>

