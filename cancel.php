<?php
/**
 * LIVETVMARKET - Customer Payment Incomplete / Cancel Page
 */

require_once __DIR__ . '/config/config.php';

$pageTitle = 'Payment Incomplete | ' . SITE_NAME;
$pageDesc = 'Your checkout was not completed. No charges were made to your card.';
$pageCanonical = SITE_URL . '/cancel';
$activePage = 'orders';

$orderId = intval($_GET['order_id'] ?? $_GET['orderId'] ?? $_GET['id'] ?? 0);

$cleanWa = preg_replace('/[^0-9]/', '', SUPPORT_WHATSAPP);
$waText = urlencode("Hello LIVETVMARKET, I experienced an issue completing my payment for order #" . ($orderId ?: 'new') . ". Can you assist me?");
$waUrl = "https://wa.me/{$cleanWa}?text={$waText}";

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/top-banner.php';
include __DIR__ . '/includes/navbar.php';
?>

  <main class="bg-slate-50 py-14 sm:py-24 min-h-[85vh]">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      
      <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xl p-8 sm:p-12">
        <div class="w-16 h-16 bg-rose-50 border-2 border-rose-500/20 rounded-full flex items-center justify-center mx-auto mb-6 text-rose-600">
          <i data-lucide="x-circle" class="w-8 h-8 stroke-[2.5]"></i>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 mb-3">
          Payment Incomplete
        </span>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
          Checkout Was Cancelled
        </h1>
        <p class="mt-3 text-sm text-slate-600 leading-relaxed">
          Your payment could not be finalized, or you decided to cancel the process. 
          <strong>No funds have been debited from your card.</strong>
        </p>

        <?php if ($orderId > 0): ?>
          <div class="mt-4 inline-block bg-slate-100 rounded-lg px-3 py-1 text-xs font-mono text-slate-600">
            Order Reference: #LTV-<?= htmlspecialchars($orderId) ?>
          </div>
        <?php endif; ?>

        <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col sm:flex-row gap-3 justify-center">
          <a href="/#pricing" class="w-full sm:w-auto py-3 px-6 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition-all">
            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
            Return to Plans & Try Again
          </a>
          <a href="<?= $waUrl ?>" target="_blank" class="w-full sm:w-auto py-3 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition-all">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            Contact WhatsApp Support
          </a>
        </div>
      </div>

      <div class="mt-6 text-xs text-slate-400">
        <a href="/" class="hover:text-cyan-600 transition-colors">Return to Home</a>
      </div>

    </div>
  </main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/modals.php';
?>

