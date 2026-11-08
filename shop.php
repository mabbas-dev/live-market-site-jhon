<?php
/**
 * LIVETVMARKET - Dedicated Shop & Pricing Packages Page
 * Full pricing overview, connection calculator, feature comparison table, and instant ordering.
 */

require_once __DIR__ . '/config/config.php';

$pageTitle = 'Shop Premium 4K IPTV Plans - ' . SITE_NAME;
$pageDesc = 'Choose your ' . SITE_NAME . ' 4K IPTV subscription package. Full access to ' . TOTAL_CHANNELS . ' live channels and ' . TOTAL_VOD . ' VOD cinema titles with Anti-Freeze™ 9.9. Instant activation.';
$pageCanonical = SITE_URL . '/shop';
$activePage = 'shop';

$dbPlans = getPricingPlans();
$plansForJs = [];
foreach ($dbPlans as $p) {
    $pCode = $p['plan_code'] ?: ('plan_' . $p['id']);
    $plansForJs[$pCode] = [
        'baseUSD' => (float)$p['price_usd'],
        'months' => (int)$p['months'],
        'name' => $p['name']
    ];
}

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/top-banner.php';
include __DIR__ . '/includes/navbar.php';
?>

  <script>
    window.IPTV_PLANS = <?= json_encode($plansForJs, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  </script>

  <main class="bg-white py-14 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Shop Header -->
      <div class="text-center max-w-3xl mx-auto mb-14" data-aos="fade-up" data-aos-duration="700">
        <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3.5 py-1.5 rounded-full border border-cyan-200 shadow-sm">
          Transparent Streaming Store
        </span>
        <h1 class="mt-4 font-heading font-black text-4xl sm:text-5xl lg:text-6xl text-slate-900 tracking-tight">
          Choose Your Streaming Package
        </h1>
        <p class="mt-4 text-slate-600 text-sm sm:text-lg leading-relaxed">
          All subscription plans include complete access to <?= TOTAL_CHANNELS ?> live channels, <?= TOTAL_VOD ?> 4K movies & series, Anti-Freeze™ 9.9, and automated 60-second activation.
        </p>

        <!-- Simultaneous Connections Selector -->
        <div class="mt-8 inline-flex items-center p-1.5 rounded-2xl bg-slate-100 border border-slate-200 shadow-inner" data-aos="zoom-in" data-aos-delay="150">
          <button id="conn-1-btn" class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all bg-cyan-500 text-white shadow-md cursor-pointer">
            1 Device Connection
          </button>
          <button id="conn-2-btn" class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all text-slate-600 hover:text-slate-900 flex items-center gap-1.5 cursor-pointer">
            <span>2 Simultaneous Devices (Multi-Room)</span>
            <span class="bg-amber-100 text-amber-800 border border-amber-200 text-[10px] px-2 py-0.5 rounded font-mono font-bold">Save 55%</span>
          </button>
        </div>
      </div>

      <!-- PRICING CARDS GRID -->
      <div id="pricing" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= min(4, count($dbPlans)) ?> gap-6 items-stretch mb-20">
        <?php foreach ($dbPlans as $idx => $p): 
          $code = $p['plan_code'] ?: ('plan_' . $p['id']);
          $isPopular = !empty($p['is_popular']);
          $featuresList = array_filter(array_map('trim', explode("\n", $p['features'] ?? '')));
          $months = (int)$p['months'];
          $priceVal = (float)$p['price_usd'];
          $perMonthStr = !empty($p['per_month_display']) ? $p['per_month_display'] : ('$' . number_format($priceVal / max(1, $months), 2) . '/mo');
        ?>
        <?php if ($isPopular): ?>
        <!-- Popular Plan Card -->
        <div class="border-beam-glow relative transform lg:-translate-y-2 shadow-2xl shadow-cyan-500/10 flex flex-col justify-between" data-aos="fade-up" data-aos-delay="<?= 100 * ($idx + 1) ?>">
          <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-cyan-600 via-cyan-500 to-blue-600 text-white font-mono font-black text-xs px-4 py-1.5 rounded-full uppercase tracking-wider shadow-lg shadow-cyan-500/35 flex items-center gap-1.5 z-30 whitespace-nowrap border border-white/40">
            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-300"></i>
            <span>★ <?= !empty($p['savings_badge']) ? htmlspecialchars($p['savings_badge']) : 'Most Popular • Best Value' ?></span>
          </div>

          <div class="border-beam-inner p-6 sm:p-7 flex flex-col justify-between tilt-card spotlight-card h-full bg-white rounded-2xl border border-cyan-300">
            <div>
              <div class="flex items-center justify-between mt-2">
                <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-wider"><?= $months >= 12 ? 'VIP Annual' : 'Featured' ?></span>
                <span class="text-[11px] font-bold text-emerald-700 px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200">Best Choice</span>
              </div>

              <h3 class="font-heading font-bold text-2xl text-slate-900 mt-3"><?= htmlspecialchars($p['name']) ?></h3>
              <p class="text-xs text-slate-500 mt-1">Our highest rated plan with maximum stability</p>

              <div class="mt-6 mb-6">
                <div class="flex items-baseline gap-1">
                  <span id="price-<?= htmlspecialchars($code) ?>" class="font-heading font-black text-4xl text-gradient-cyan">$<?= number_format($priceVal, 2) ?></span>
                  <span class="text-xs text-slate-500 font-medium">/ <?= $months ?> month<?= $months > 1 ? 's' : '' ?></span>
                </div>
                <p id="per-month-<?= htmlspecialchars($code) ?>" class="text-xs text-emerald-600 font-bold mt-1"><?= htmlspecialchars($perMonthStr) ?></p>
              </div>

              <ul class="space-y-3 text-xs text-slate-700 border-t border-cyan-100 pt-6">
                <?php foreach ($featuresList as $feat): ?>
                <li class="flex items-center gap-2.5">
                  <i data-lucide="check" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
                  <span><?= htmlspecialchars($feat) ?></span>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>

            <div class="mt-8">
              <button data-select-plan="<?= htmlspecialchars($code) ?>" class="btn-glow-cyan shimmer-active w-full py-3.5 rounded-xl font-bold text-xs uppercase tracking-wide flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-cyan-500/25">
                <span>Select <?= htmlspecialchars($p['name']) ?></span>
                <i data-lucide="zap" class="w-4 h-4"></i>
              </button>
            </div>
          </div>
        </div>
        <?php else: ?>
        <!-- Standard Plan Card -->
        <div class="bg-white rounded-2xl p-6 sm:p-7 flex flex-col justify-between border border-slate-200 shadow-sm hover:shadow-xl hover:border-cyan-300 transition-all tilt-card spotlight-card" data-aos="fade-up" data-aos-delay="<?= 100 * ($idx + 1) ?>">
          <div>
            <div class="flex items-center justify-between">
              <span class="text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">
                <?= $months <= 1 ? 'Starter' : ($months <= 3 ? 'Quarterly' : ($months <= 6 ? 'Semi-Annual' : ($months >= 24 ? 'Ultimate' : 'Standard'))) ?>
              </span>
              <?php if (!empty($p['savings_badge'])): ?>
              <span class="text-[11px] font-bold text-cyan-700 px-2.5 py-0.5 rounded bg-cyan-50 border border-cyan-200"><?= htmlspecialchars($p['savings_badge']) ?></span>
              <?php else: ?>
              <span class="text-[11px] font-semibold text-slate-600 px-2.5 py-0.5 rounded bg-slate-100"><?= $months ?> Mo</span>
              <?php endif; ?>
            </div>

            <h3 class="font-heading font-bold text-2xl text-slate-900 mt-3"><?= htmlspecialchars($p['name']) ?></h3>
            <p class="text-xs text-slate-500 mt-1">Instant delivery with 60-second activation</p>

            <div class="mt-6 mb-6">
              <div class="flex items-baseline gap-1">
                <span id="price-<?= htmlspecialchars($code) ?>" class="font-heading font-black text-4xl text-slate-900">$<?= number_format($priceVal, 2) ?></span>
                <span class="text-xs text-slate-500 font-medium">/ <?= $months ?> month<?= $months > 1 ? 's' : '' ?></span>
              </div>
              <p id="per-month-<?= htmlspecialchars($code) ?>" class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($perMonthStr) ?></p>
            </div>

            <ul class="space-y-3 text-xs text-slate-600 border-t border-slate-100 pt-6">
              <?php foreach ($featuresList as $feat): ?>
              <li class="flex items-center gap-2.5">
                <i data-lucide="check" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
                <span><?= htmlspecialchars($feat) ?></span>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <div class="mt-8">
            <button data-select-plan="<?= htmlspecialchars($code) ?>" class="w-full py-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs border border-slate-200 transition-all cursor-pointer">
              Select <?= htmlspecialchars($p['name']) ?>
            </button>
          </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <!-- Trust Pillars & Payment Gateways -->
      <section class="p-8 sm:p-12 rounded-3xl bg-slate-50 border border-slate-200" data-aos="fade-up">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-cyan-100 text-cyan-700 flex items-center justify-center flex-shrink-0 shadow-sm">
              <i data-lucide="zap" class="w-6 h-6"></i>
            </div>
            <div>
              <h3 class="font-bold text-slate-900 text-sm mb-1">Instant 60s Activation</h3>
              <p class="text-xs text-slate-600 leading-relaxed">
                As soon as your checkout is completed, our automated provisioning system generates and delivers your playlist to your inbox.
              </p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 shadow-sm">
              <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <div>
              <h3 class="font-bold text-slate-900 text-sm mb-1">7-Day Risk Free SLA</h3>
              <p class="text-xs text-slate-600 leading-relaxed">
                If our 24/7 technical team cannot solve an issue to your satisfaction, simply message us within 7 days for an unconditional refund.
              </p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0 shadow-sm">
              <i data-lucide="lock" class="w-6 h-6"></i>
            </div>
            <div>
              <h3 class="font-bold text-slate-900 text-sm mb-1">256-Bit SSL Checkout</h3>
              <p class="text-xs text-slate-600 leading-relaxed">
                We accept all major credit cards, PayPal, Apple Pay, Google Pay, and Crypto (USDT, BTC) with full encryption.
              </p>
            </div>
          </div>
        </div>
      </section>

    </div>
  </main>

<?php
include __DIR__ . '/includes/modals.php';
include __DIR__ . '/includes/footer.php';
?>

