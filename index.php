<?php
/**
 * LIVETVMARKET - Main Homepage & Storefront
 * Built with modular PHP components, pure Tailwind CSS, and a clean luxury white aesthetic.
 * Streamlined multi-page architecture linking to Shop, Track Order, Blogs, Channels, Setup, and Contact.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/blogs_data.php';

$pageTitle = SITE_NAME . ' - Premium 4K IPTV Subscription | ' . TOTAL_CHANNELS . ' Channels & VOD';
$pageDesc = SITE_NAME . ' offers the #1 premium IPTV service with ' . TOTAL_CHANNELS . ' live channels and ' . TOTAL_VOD . ' VOD movies & series. Anti-Freeze™ 9.9 zero-buffering technology, all live sports & PPV in 4K UHD. Instant activation & 24h free trial.';
$pageCanonical = SITE_URL . '/';
$activePage = 'home';

$featuredArticles = array_slice(getBlogArticles(), 0, 3);
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

  <main class="bg-white">
    
    <!-- 1. HERO SECTION -->
    <section class="relative pt-12 pb-20 lg:pt-16 lg:pb-28 overflow-hidden bg-gradient-to-b from-cyan-50/40 via-white to-white">
      <!-- Glow ambient light with soft luminous breathing animations -->
      <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[400px] bg-cyan-200/40 rounded-full blur-[140px] pointer-events-none animate-aurora-1"></div>
      <div class="absolute top-1/3 -left-40 w-[450px] h-[450px] bg-blue-100/50 rounded-full blur-[130px] pointer-events-none animate-aurora-2"></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <!-- Trust badge -->
        <div class="flex justify-center mb-6" data-aos="fade-down" data-aos-duration="700">
          <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 text-xs text-slate-600 shadow-sm hover:border-cyan-300 transition-all">
            <span class="flex text-amber-500 text-sm">★★★★★</span>
            <span class="font-bold text-slate-900"><?= RATING_SCORE ?> Rating</span>
            <span class="text-slate-300">•</span>
            <span class="text-cyan-700 font-semibold"><?= SUBSCRIBER_COUNT ?> Active Subscribers</span>
            <span class="hidden sm:inline text-slate-300">•</span>
            <span class="hidden sm:inline-flex items-center gap-1 text-emerald-600 font-semibold">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> <?= NETWORK_UPTIME ?> Uptime Verified
            </span>
          </div>
        </div>

        <!-- Main Headline -->
        <div class="text-center max-w-4xl mx-auto" data-aos="fade-up" data-aos-duration="800">
          <h1 class="font-heading font-black text-4xl sm:text-5xl lg:text-6xl text-slate-900 tracking-tight leading-[1.14]">
            <?= htmlspecialchars(HERO_HEADLINE) ?> <span class="bg-gradient-to-r from-cyan-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent">True 4K UHD</span>
          </h1>
          <p class="mt-6 text-base sm:text-lg lg:text-xl text-slate-600 font-normal leading-relaxed max-w-3xl mx-auto" data-aos="fade-up" data-aos-delay="150">
            <?= htmlspecialchars(HERO_SUBTITLE) ?>
          </p>

          <!-- Action Buttons -->
          <div class="mt-8 sm:mt-10 flex flex-col sm:flex-row items-center justify-center gap-4" data-aos="zoom-in" data-aos-delay="250" data-aos-duration="700">
            <a href="/shop" class="btn-glow-cyan shimmer-active w-full sm:w-auto px-8 py-4 rounded-xl font-bold text-base flex items-center justify-center gap-2 group shadow-xl shadow-cyan-500/20">
              <span>Explore Shop Packages</span>
              <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1.5 transition-transform"></i>
            </a>
            <?php if (TRIAL_IS_ACTIVE): ?>
            <button data-open-trial class="w-full sm:w-auto px-8 py-4 rounded-xl font-semibold text-base bg-white hover:bg-slate-50 text-slate-800 flex items-center justify-center gap-2 border border-slate-200 hover:border-cyan-400 shadow-sm transition-all cursor-pointer">
              <i data-lucide="play-circle" class="w-5 h-5 text-cyan-600"></i>
              <span><?= (TRIAL_MODE === 'paid') ? ('Paid ' . TRIAL_DURATION_HOURS . 'h Test Line') : 'Get Free 24-Hour Test' ?></span>
            </button>
            <?php endif; ?>
          </div>

          <!-- Feature Bullets Checklist -->
          <div class="mt-8 pt-6 border-t border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-semibold text-slate-600" data-aos="fade-up" data-aos-delay="350">
            <div class="flex items-center justify-center gap-2">
              <i data-lucide="check-circle-2" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
              <span>Instant 60s Activation</span>
            </div>
            <div class="flex items-center justify-center gap-2">
              <i data-lucide="shield-check" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
              <span>100% Anti-ISP Throttling</span>
            </div>
            <div class="flex items-center justify-center gap-2">
              <i data-lucide="calendar" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
              <span>7-Day Catch-Up & EPG</span>
            </div>
            <div class="flex items-center justify-center gap-2">
              <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
              <span>7-Day Money Back SLA</span>
            </div>
          </div>
        </div>

        <!-- HERO SHOWCASE GRAPHIC WITH FLOATING HUD CARDS -->
        <div class="mt-12 sm:mt-16 relative max-w-5xl mx-auto" data-aos="zoom-in-up" data-aos-duration="900" data-aos-delay="200">
          <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-2xl shadow-slate-900/10 group bg-slate-100">
            
            <img src="assets/images/hero-showcase.jpg" alt="<?= SITE_NAME ?> 4K IPTV Streaming Interface on Smart TV and Mobile" class="w-full h-auto object-cover transform group-hover:scale-[1.01] transition-transform duration-700" loading="eager" width="1280" height="720">

            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-transparent to-transparent opacity-80 pointer-events-none"></div>

            <!-- Floating Card Left: Live Server Health -->
            <div class="absolute top-4 left-4 sm:top-6 sm:left-6 bg-white/95 p-3 sm:p-4 rounded-xl border border-slate-200/90 shadow-lg hidden sm:block animate-float-1 backdrop-blur-xl" data-aos="fade-right" data-aos-delay="450">
              <div class="flex items-center gap-3">
                <div class="radar-dot"></div>
                <div>
                  <p class="text-xs font-mono font-bold text-slate-900">Global CDN: <?= ACTIVE_CLUSTERS ?> Nodes</p>
                  <p class="text-[11px] text-cyan-700 font-semibold"><?= NETWORK_UPTIME ?> Network Health</p>
                </div>
              </div>
            </div>

            <!-- Floating Card Right: Bitrate & Quality -->
            <div class="absolute top-4 right-4 sm:top-6 sm:right-6 bg-white/95 p-3 sm:p-4 rounded-xl border border-slate-200/90 shadow-lg hidden sm:block animate-float-2 backdrop-blur-xl" data-aos="fade-left" data-aos-delay="450">
              <div class="flex items-center gap-2.5">
                <span class="px-2 py-0.5 rounded bg-cyan-600 text-white font-black text-xs shadow-sm">4K HDR</span>
                <span class="text-xs font-mono text-slate-700 font-medium">60 FPS • 24 Mbps HEVC</span>
              </div>
            </div>

            <!-- Bottom Floating Bar: Currently Live Match Badge -->
            <div class="absolute bottom-4 left-4 right-4 sm:bottom-6 sm:left-8 sm:right-8 bg-white/95 p-3 sm:p-4 rounded-xl border border-slate-200 shadow-xl flex flex-wrap items-center justify-between gap-3 backdrop-blur-xl" data-aos="fade-up" data-aos-delay="550">
              <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 rounded-md bg-rose-600 text-white font-black text-xs flex items-center gap-1.5 animate-pulse">
                  <span class="w-2 h-2 rounded-full bg-white"></span> LIVE
                </span>
                <div>
                  <h4 class="text-slate-900 text-xs sm:text-sm font-bold">UEFA Champions League & Premier League</h4>
                  <p class="text-[11px] text-slate-500">Broadcasting in English (TNT / Sky) • Spanish (Movistar) • French (Canal+)</p>
                </div>
              </div>
              <a href="/shop" class="px-3.5 py-1.5 rounded-lg bg-cyan-50 text-cyan-700 border border-cyan-200 text-xs font-bold hover:bg-cyan-600 hover:text-white transition-colors">
                Watch in 4K Now →
              </a>
            </div>

          </div>
        </div>

      </div>
    </section>

    <!-- 2. SHOP & SUBSCRIPTION PACKAGES SHOWCASE -->
    <section id="pricing" class="py-20 lg:py-28 relative bg-slate-50 border-y border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-12" data-aos="fade-up" data-aos-duration="700">
          <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3 py-1 rounded-full border border-cyan-200">
            Subscription Plans
          </span>
          <h2 class="mt-4 font-heading font-black text-3xl sm:text-4xl text-slate-900">
            Choose Your <span class="text-cyan-600"><?= SITE_NAME ?></span> Access
          </h2>
          <p class="mt-3 text-slate-600 text-sm sm:text-base">
            Every subscription includes complete access to <?= TOTAL_CHANNELS ?> live channels, <?= TOTAL_VOD ?> VOD, Anti-Freeze™ 9.9, and automated instant activation.
          </p>

          <!-- Simultaneous Connections Selector -->
          <div class="mt-8 inline-flex items-center p-1.5 rounded-2xl bg-white border border-slate-200 shadow-sm" data-aos="zoom-in" data-aos-delay="150">
            <button id="conn-1-btn" class="px-5 py-2 rounded-xl text-xs font-bold transition-all bg-cyan-500 text-white shadow-md cursor-pointer">
              1 Device Connection
            </button>
            <button id="conn-2-btn" class="px-5 py-2 rounded-xl text-xs font-bold transition-all text-slate-600 hover:text-slate-900 flex items-center gap-1.5 cursor-pointer">
              <span>2 Simultaneous Devices (Multi-Room)</span>
              <span class="bg-amber-100 text-amber-800 border border-amber-200 text-[10px] px-1.5 py-0.2 rounded font-mono font-bold">Save 55%</span>
            </button>
          </div>
        </div>

        <!-- PRICING CARDS GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= min(4, count($dbPlans)) ?> gap-6 items-stretch">
          <?php foreach ($dbPlans as $idx => $p): 
            $code = $p['plan_code'] ?: ('plan_' . $p['id']);
            $isPopular = !empty($p['is_popular']);
            $featuresList = array_filter(array_map('trim', explode("\n", $p['features'] ?? '')));
            $months = (int)$p['months'];
            $priceVal = (float)$p['price_usd'];
            $perMonthStr = !empty($p['per_month_display']) ? $p['per_month_display'] : ('$' . number_format($priceVal / max(1, $months), 2) . '/mo');
          ?>
          <?php if ($isPopular): ?>
          <!-- Plan (POPULAR) -->
          <div class="relative bg-white rounded-2xl p-6 sm:p-7 flex flex-col justify-between border-2 border-cyan-500 shadow-xl shadow-cyan-500/15 tilt-card !overflow-visible scale-[1.02] z-20 mt-3 sm:mt-0" data-aos="fade-up" data-aos-delay="<?= 100 * ($idx + 1) ?>">
            
            <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-cyan-600 via-cyan-500 to-blue-600 text-white font-mono font-black text-xs px-4 py-1.5 rounded-full uppercase tracking-wider shadow-lg shadow-cyan-500/35 whitespace-nowrap z-30 border border-white/40 flex items-center gap-1.5">
              <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-300"></i>
              <span>★ <?= !empty($p['savings_badge']) ? htmlspecialchars($p['savings_badge']) : 'Most Popular • Best Value' ?></span>
            </div>

            <div>
              <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-wider"><?= $months >= 12 ? 'Annual VIP' : 'Featured' ?></span>
                <span class="text-[11px] font-bold text-amber-800 px-2 py-0.5 rounded bg-amber-50 border border-amber-200">Best Value</span>
              </div>

              <h3 class="font-heading font-bold text-2xl text-slate-900 mt-3"><?= htmlspecialchars($p['name']) ?></h3>
              <p class="text-xs text-slate-500 mt-1">Complete peace of mind with ultra stability</p>

              <div class="mt-6 mb-6">
                <div class="flex items-baseline gap-1">
                  <span id="price-<?= htmlspecialchars($code) ?>" class="font-heading font-black text-4xl text-cyan-600">$<?= number_format($priceVal, 2) ?></span>
                  <span class="text-xs text-slate-500 font-medium">/ <?= $months ?> month<?= $months > 1 ? 's' : '' ?></span>
                </div>
                <p id="per-month-<?= htmlspecialchars($code) ?>" class="text-xs text-emerald-700 font-bold mt-1"><?= htmlspecialchars($perMonthStr) ?></p>
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
              <button data-select-plan="<?= htmlspecialchars($code) ?>" class="btn-glow-cyan shimmer-active w-full py-3.5 rounded-xl font-bold text-xs uppercase tracking-wide flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-cyan-500/25">
                <span>Select <?= htmlspecialchars($p['name']) ?></span>
                <i data-lucide="zap" class="w-4 h-4"></i>
              </button>
            </div>
          </div>
          <?php else: ?>
          <!-- Standard Plan -->
          <div class="bg-white rounded-2xl p-6 sm:p-7 flex flex-col justify-between border border-slate-200 shadow-sm hover:shadow-xl hover:border-cyan-300 transition-all tilt-card spotlight-card" data-aos="fade-up" data-aos-delay="<?= 100 * ($idx + 1) ?>">
            <div>
              <div class="flex items-center justify-between">
                <span class="text-xs font-mono font-bold text-slate-500 uppercase tracking-wider">
                  <?= $months <= 1 ? 'Starter' : ($months <= 3 ? 'Quarterly' : ($months <= 6 ? 'Semi-Annual' : ($months >= 24 ? 'Ultimate' : 'Standard'))) ?>
                </span>
                <?php if (!empty($p['savings_badge'])): ?>
                <span class="text-[11px] font-bold text-emerald-700 px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200"><?= htmlspecialchars($p['savings_badge']) ?></span>
                <?php else: ?>
                <span class="text-[11px] font-semibold text-slate-600 px-2.5 py-0.5 rounded bg-slate-100"><?= $months ?> Mo</span>
                <?php endif; ?>
              </div>

              <h3 class="font-heading font-bold text-2xl text-slate-900 mt-3"><?= htmlspecialchars($p['name']) ?></h3>
              <p class="text-xs text-slate-500 mt-1">Instant setup with automated 60s activation</p>

              <div class="mt-6 mb-6">
                <div class="flex items-baseline gap-1">
                  <span id="price-<?= htmlspecialchars($code) ?>" class="font-heading font-black text-4xl text-slate-900">$<?= number_format($priceVal, 2) ?></span>
                  <span class="text-xs text-slate-500 font-medium">/ <?= $months ?> month<?= $months > 1 ? 's' : '' ?></span>
                </div>
                <p id="per-month-<?= htmlspecialchars($code) ?>" class="text-xs text-cyan-700 font-semibold mt-1"><?= htmlspecialchars($perMonthStr) ?></p>
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
              <button data-select-plan="<?= htmlspecialchars($code) ?>" class="w-full py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs border border-slate-200 transition-all cursor-pointer">
                Select <?= htmlspecialchars($p['name']) ?>
              </button>
            </div>
          </div>
          <?php endif; ?>
          <?php endforeach; ?>
        </div>

        <!-- Shop page link banner -->
        <div class="mt-10 text-center">
          <a href="/shop" class="inline-flex items-center gap-2 text-sm font-bold text-cyan-600 hover:text-cyan-700 group">
            <span>View All Packages, Details & Multi-Room Options on our dedicated Shop Page</span>
            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
          </a>
        </div>

        <!-- Payment methods & Trust badges -->
        <div class="mt-10 bg-white p-6 rounded-2xl max-w-4xl mx-auto flex flex-wrap items-center justify-between gap-6 border border-slate-200 shadow-sm" data-aos="fade-up" data-aos-delay="200">
          <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
            <i data-lucide="lock" class="w-4 h-4 text-emerald-600"></i>
            <span>256-Bit SSL Encrypted & Secure Checkout</span>
          </div>

          <div class="flex flex-wrap items-center gap-4 text-xs font-bold text-slate-600">
            <span class="px-2.5 py-1 rounded bg-slate-50 border border-slate-200 text-slate-700 shadow-sm">Visa / Mastercard</span>
            <span class="px-2.5 py-1 rounded bg-slate-50 border border-slate-200 text-slate-700 shadow-sm">PayPal</span>
            <span class="px-2.5 py-1 rounded bg-slate-50 border border-slate-200 text-slate-700 shadow-sm">Apple Pay / G Pay</span>
            <span class="px-2.5 py-1 rounded bg-cyan-50 border border-cyan-200 text-cyan-700 font-semibold shadow-sm">Bitcoin / USDT (10% OFF)</span>
          </div>

          <div class="text-xs text-slate-500">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 inline mr-1 text-cyan-600"></i>
            <span>7-Day No Risk Money Back Guarantee</span>
          </div>
        </div>

      </div>
    </section>

    <!-- 3. ANTI-FREEZE ADVANTAGE (FEATURES OVERVIEW) -->
    <section id="features" class="py-20 lg:py-24 relative bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-14" data-aos="fade-up" data-aos-duration="700">
          <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3 py-1 rounded-full border border-cyan-200">
            Next-Gen Technology
          </span>
          <h2 class="mt-3 font-heading font-black text-3xl sm:text-4xl text-slate-900">
            Why <?= SITE_NAME ?> is the #1 Streaming Provider
          </h2>
          <p class="mt-3 text-slate-600 text-sm sm:text-base">
            Engineered from the ground up for zero buffering, ultra-low latency, and crystal-clear picture fidelity on all devices.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          
          <!-- Feature 1 -->
          <div class="bg-white p-7 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-cyan-300 transition-all group" data-aos="fade-up" data-aos-delay="100">
            <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center mb-5 group-hover:scale-105 transition-transform">
              <i data-lucide="snowflake" class="w-6 h-6"></i>
            </div>
            <h3 class="font-heading font-bold text-lg text-slate-900">Anti-Freeze™ 9.9 CDN</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
              Our intelligent edge routing automatically detects network congestion and redirects packets to the fastest cloud cluster within milliseconds.
            </p>
          </div>

          <!-- Feature 2 -->
          <div class="bg-white p-7 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-300 transition-all group" data-aos="fade-up" data-aos-delay="150">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-5 group-hover:scale-105 transition-transform">
              <i data-lucide="tv" class="w-6 h-6"></i>
            </div>
            <h3 class="font-heading font-bold text-lg text-slate-900">True 4K 60FPS Bitrate</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
              Experience live sports with silky-smooth 60 frames-per-second fidelity. No compressed 25fps stuttering or pixelated ball motion.
            </p>
          </div>

          <!-- Feature 3 -->
          <div class="bg-white p-7 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-300 transition-all group" data-aos="fade-up" data-aos-delay="200">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-5 group-hover:scale-105 transition-transform">
              <i data-lucide="globe" class="w-6 h-6"></i>
            </div>
            <h3 class="font-heading font-bold text-lg text-slate-900"><?= TOTAL_CHANNELS ?> Worldwide Channels</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
              Complete local & international coverage across US, UK, Canada, Europe, Latin America, Arabic, and Asian broadcasting networks.
            </p>
          </div>

          <!-- Feature 4 -->
          <div class="bg-white p-7 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-purple-300 transition-all group" data-aos="fade-up" data-aos-delay="250">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-5 group-hover:scale-105 transition-transform">
              <i data-lucide="film" class="w-6 h-6"></i>
            </div>
            <h3 class="font-heading font-bold text-lg text-slate-900"><?= TOTAL_VOD ?> Movies & Boxsets</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
              Updated daily with the latest Hollywood blockbusters, Netflix originals, HBO classics, and Disney+ series with multi-language subtitles.
            </p>
          </div>

          <!-- Feature 5 -->
          <div class="bg-white p-7 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-amber-300 transition-all group" data-aos="fade-up" data-aos-delay="300">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-5 group-hover:scale-105 transition-transform">
              <i data-lucide="zap" class="w-6 h-6"></i>
            </div>
            <h3 class="font-heading font-bold text-lg text-slate-900">Instant 60-Second Setup</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
              Automated provisioning delivers your M3U playlist URL and Xtream API credentials immediately after checkout without manual delays.
            </p>
          </div>

          <!-- Feature 6 -->
          <div class="bg-white p-7 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-teal-300 transition-all group" data-aos="fade-up" data-aos-delay="350">
            <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mb-5 group-hover:scale-105 transition-transform">
              <i data-lucide="headset" class="w-6 h-6"></i>
            </div>
            <h3 class="font-heading font-bold text-lg text-slate-900">24/7 VIP WhatsApp Desk</h3>
            <p class="text-xs text-slate-600 mt-2 leading-relaxed">
              Direct access to live streaming engineers ready to assist with playlist configuration, EPG setups, and device migrations anytime.
            </p>
          </div>

        </div>

      </div>
    </section>

    <!-- 4. CHANNELS & VOD SPOTLIGHT TEASER -->
    <section class="py-20 bg-slate-50 border-y border-slate-200 relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12" data-aos="fade-up" data-aos-duration="700">
          <div>
            <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3 py-1 rounded-full border border-cyan-200">
              Extensive Catalog
            </span>
            <h2 class="mt-3 font-heading font-black text-3xl sm:text-4xl text-slate-900">
              Over <?= TOTAL_CHANNELS ?> Channels & <?= TOTAL_VOD ?> Movies
            </h2>
            <p class="mt-2 text-slate-600 text-sm max-w-2xl">
              From live Premier League and UFC pay-per-views to national news and cinema releases, explore thousands of channels organized by category and country.
            </p>
          </div>

          <div>
            <a href="/channels" class="btn-glow-cyan px-6 py-3 rounded-xl text-xs font-bold inline-flex items-center gap-2 shadow-md">
              <i data-lucide="search" class="w-4 h-4"></i>
              <span>Explore All <?= TOTAL_CHANNELS ?> Channels</span>
            </a>
          </div>
        </div>

        <!-- 4 Spotlight Category Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          
          <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-sm hover:border-cyan-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center mb-4">
              <i data-lucide="trophy" class="w-5 h-5"></i>
            </div>
            <h3 class="font-heading font-bold text-base text-slate-900">Live Sports & PPV</h3>
            <p class="text-xs text-slate-500 mt-1 mb-4">Sky Sports, TNT Sports, ESPN, beIN, DAZN, UFC Fight Pass, NBA League Pass in 4K 60FPS.</p>
            <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
              <span class="px-2 py-0.5 rounded bg-slate-100">Football</span>
              <span class="px-2 py-0.5 rounded bg-slate-100">Boxing</span>
              <span class="px-2 py-0.5 rounded bg-slate-100">UFC</span>
              <span class="px-2 py-0.5 rounded bg-slate-100">Formula 1</span>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-sm hover:border-indigo-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
              <i data-lucide="film" class="w-5 h-5"></i>
            </div>
            <h3 class="font-heading font-bold text-base text-slate-900">Cinema & 4K VOD</h3>
            <p class="text-xs text-slate-500 mt-1 mb-4">Over 120,000 on-demand titles with Netflix, HBO Max, Disney+, and Amazon Prime releases in 4K HDR.</p>
            <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
              <span class="px-2 py-0.5 rounded bg-slate-100">New 2026</span>
              <span class="px-2 py-0.5 rounded bg-slate-100">Dolby Atmos</span>
              <span class="px-2 py-0.5 rounded bg-slate-100">Series</span>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-sm hover:border-emerald-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
              <i data-lucide="tv-2" class="w-5 h-5"></i>
            </div>
            <h3 class="font-heading font-bold text-base text-slate-900">US, UK & CA Networks</h3>
            <p class="text-xs text-slate-500 mt-1 mb-4">BBC, ITV, Channel 4, NBC, CBS, ABC, Fox, CNN, TSN, and Sportsnet with full 7-day catch-up.</p>
            <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
              <span class="px-2 py-0.5 rounded bg-slate-100">Local Affiliates</span>
              <span class="px-2 py-0.5 rounded bg-slate-100">EPG Guide</span>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-sm hover:border-purple-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4">
              <i data-lucide="globe-2" class="w-5 h-5"></i>
            </div>
            <h3 class="font-heading font-bold text-base text-slate-900">Global International</h3>
            <p class="text-xs text-slate-500 mt-1 mb-4">Full coverage for Germany, France, Spain, Italy, Portugal, Latin America, Arabic, Turkey, and Asia.</p>
            <div class="flex flex-wrap gap-1.5 text-[10px] font-semibold text-slate-600">
              <span class="px-2 py-0.5 rounded bg-slate-100">Europe</span>
              <span class="px-2 py-0.5 rounded bg-slate-100">Middle East</span>
              <span class="px-2 py-0.5 rounded bg-slate-100">LatAm</span>
            </div>
          </div>

        </div>

        <div class="mt-10 text-center">
          <a href="/channels" class="text-xs font-bold text-cyan-600 hover:text-cyan-700 inline-flex items-center gap-1.5 group">
            <span>Filter and search all channels by country and category</span>
            <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
          </a>
        </div>

      </div>
    </section>

    <!-- 5. DEVICE COMPATIBILITY SPOTLIGHT -->
    <section class="py-16 bg-slate-50 border-y border-slate-200 relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-10" data-aos="fade-up" data-aos-duration="700">
          <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3 py-1 rounded-full border border-cyan-200">
            Universal Streaming
          </span>
          <h2 class="mt-3 font-heading font-black text-3xl sm:text-4xl text-slate-900">
            Works Flawlessly on 100% of All Devices
          </h2>
          <p class="mt-2 text-slate-600 text-sm">
            Enjoy full 4K HDR quality on whatever screen you own. Quick 3-minute setup with zero technical knowledge required.
          </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
          
          <a href="/setup#firestick" class="p-5 rounded-2xl bg-white border border-slate-200 text-center hover:border-cyan-300 hover:shadow-md transition-all group">
            <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 mx-auto flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i data-lucide="flame" class="w-6 h-6"></i>
            </div>
            <h4 class="font-heading font-bold text-xs text-slate-900">Amazon Firestick</h4>
            <p class="text-[11px] text-slate-500 mt-0.5">4K Max, Cube & Lite</p>
          </a>

          <a href="/setup#android" class="p-5 rounded-2xl bg-white border border-slate-200 text-center hover:border-cyan-300 hover:shadow-md transition-all group">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i data-lucide="smartphone" class="w-6 h-6"></i>
            </div>
            <h4 class="font-heading font-bold text-xs text-slate-900">Android TV / Box</h4>
            <p class="text-[11px] text-slate-500 mt-0.5">Nvidia Shield, Xiaomi</p>
          </a>

          <a href="/setup#smarttv" class="p-5 rounded-2xl bg-white border border-slate-200 text-center hover:border-cyan-300 hover:shadow-md transition-all group">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 mx-auto flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i data-lucide="tv-2" class="w-6 h-6"></i>
            </div>
            <h4 class="font-heading font-bold text-xs text-slate-900">Smart TV</h4>
            <p class="text-[11px] text-slate-500 mt-0.5">Samsung & LG WebOS</p>
          </a>

          <a href="/setup#apple" class="p-5 rounded-2xl bg-white border border-slate-200 text-center hover:border-cyan-300 hover:shadow-md transition-all group">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 mx-auto flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i data-lucide="apple" class="w-6 h-6"></i>
            </div>
            <h4 class="font-heading font-bold text-xs text-slate-900">Apple Devices</h4>
            <p class="text-[11px] text-slate-500 mt-0.5">Apple TV 4K, iPhone, iPad</p>
          </a>

          <a href="/setup#pc" class="p-5 rounded-2xl bg-white border border-slate-200 text-center hover:border-cyan-300 hover:shadow-md transition-all group">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i data-lucide="monitor" class="w-6 h-6"></i>
            </div>
            <h4 class="font-heading font-bold text-xs text-slate-900">Windows & Mac</h4>
            <p class="text-[11px] text-slate-500 mt-0.5">VLC & Web Player</p>
          </a>

          <a href="/setup#mag" class="p-5 rounded-2xl bg-white border border-slate-200 text-center hover:border-cyan-300 hover:shadow-md transition-all group">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 mx-auto flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i data-lucide="box" class="w-6 h-6"></i>
            </div>
            <h4 class="font-heading font-bold text-xs text-slate-900">MAG & Formuler</h4>
            <p class="text-[11px] text-slate-500 mt-0.5">Stalker Portal & MyTVOnline</p>
          </a>

        </div>

        <div class="mt-8 text-center">
          <a href="/setup" class="btn-glow-cyan px-6 py-2.5 rounded-xl text-xs font-bold inline-flex items-center gap-2 shadow-sm">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            <span>View Step-by-Step Installation Tutorials</span>
          </a>
        </div>

      </div>
    </section>

    <!-- 6. CUSTOMER REVIEWS & SOCIAL PROOF -->
    <section class="py-20 relative bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-14" data-aos="fade-up" data-aos-duration="700">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold mb-3">
            <span>Trustpilot Rated Excellent</span>
            <span>★ <?= RATING_SCORE ?></span>
          </div>
          <h2 class="font-heading font-black text-3xl sm:text-4xl text-slate-900">
            Loved by <?= SUBSCRIBER_COUNT ?> Happy Streamers
          </h2>
          <p class="mt-3 text-slate-600 text-sm">
            Read real feedback from verified customers around the globe who cancelled overpriced cable.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          
          <div class="bg-white p-6 rounded-2xl flex flex-col justify-between border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
            <div>
              <div class="flex items-center gap-1 text-amber-500 text-sm mb-3">★★★★★</div>
              <p class="text-xs text-slate-600 leading-relaxed italic">
                "Zero buffering during the Champions League final! I used to have 3 different IPTV providers and they all crashed during big matches. <?= SITE_NAME ?> is bulletproof."
              </p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-cyan-100 text-cyan-700 font-bold text-xs flex items-center justify-center">MD</div>
              <div>
                <h4 class="font-bold text-slate-900 text-xs">Marcus Davies</h4>
                <p class="text-[10px] text-slate-500 flex items-center gap-1">
                  <span class="text-emerald-600 font-medium">✓ Verified Buyer</span> • UK
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl flex flex-col justify-between border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
            <div>
              <div class="flex items-center gap-1 text-amber-500 text-sm mb-3">★★★★★</div>
              <p class="text-xs text-slate-600 leading-relaxed italic">
                "Setup on my 4K Firestick took literally 2 minutes using their downloader code. All USA local channels and NFL Redzone look crystal clear in 60fps."
              </p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">JL</div>
              <div>
                <h4 class="font-bold text-slate-900 text-xs">Jason Lewis</h4>
                <p class="text-[10px] text-slate-500 flex items-center gap-1">
                  <span class="text-emerald-600 font-medium">✓ Verified Buyer</span> • USA
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl flex flex-col justify-between border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
            <div>
              <div class="flex items-center gap-1 text-amber-500 text-sm mb-3">★★★★★</div>
              <p class="text-xs text-slate-600 leading-relaxed italic">
                "Customer support on WhatsApp is extraordinary. Alex answered my question within 2 minutes and helped me configure EPG guide on TiviMate. 10/10 service."
              </p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-purple-100 text-purple-700 font-bold text-xs flex items-center justify-center">SB</div>
              <div>
                <h4 class="font-bold text-slate-900 text-xs">Sophie Bernard</h4>
                <p class="text-[10px] text-slate-500 flex items-center gap-1">
                  <span class="text-emerald-600 font-medium">✓ Verified Buyer</span> • France
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-2xl flex flex-col justify-between border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
            <div>
              <div class="flex items-center gap-1 text-amber-500 text-sm mb-3">★★★★★</div>
              <p class="text-xs text-slate-600 leading-relaxed italic">
                "The VOD library is unbelievable. Every new movie is available in 4K HDR with English and German subtitles. I just renewed for 24 months!"
              </p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">TH</div>
              <div>
                <h4 class="font-bold text-slate-900 text-xs">Thomas Hoffmann</h4>
                <p class="text-[10px] text-slate-500 flex items-center gap-1">
                  <span class="text-emerald-600 font-medium">✓ Verified Buyer</span> • Germany
                </p>
              </div>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- 7. LATEST ARTICLES FROM BLOG -->
    <section class="py-20 bg-slate-50 border-y border-slate-200 relative">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12" data-aos="fade-up" data-aos-duration="700">
          <div>
            <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3 py-1 rounded-full border border-cyan-200">
              Streaming News & Insights
            </span>
            <h2 class="mt-3 font-heading font-black text-3xl sm:text-4xl text-slate-900">
              Latest Guides from Our Technical Team
            </h2>
            <p class="mt-2 text-slate-600 text-sm max-w-2xl">
              Stay ahead with expert tutorials, cord-cutting advice, and device optimization tips written by our streaming engineers.
            </p>
          </div>

          <div>
            <a href="/blog" class="btn-glow-cyan px-6 py-2.5 rounded-xl text-xs font-bold inline-flex items-center gap-2 shadow-sm">
              <i data-lucide="newspaper" class="w-4 h-4"></i>
              <span>View All Articles & Guides</span>
            </a>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <?php foreach ($featuredArticles as $art): ?>
            <article class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-lg hover:border-cyan-300 transition-all flex flex-col justify-between group">
              <div>
                <?php if (!empty($art['thumbnail_url'])): ?>
                  <div class="h-44 w-full relative overflow-hidden bg-slate-100">
                    <img src="<?= htmlspecialchars($art['thumbnail_url']) ?>" alt="<?= htmlspecialchars($art['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <span class="absolute bottom-3 left-3 text-[10px] font-mono font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white/95 text-slate-800 shadow-sm">
                      <?= htmlspecialchars($art['category']) ?>
                    </span>
                    <span class="absolute bottom-3 right-3 text-[10px] font-semibold text-white/90 flex items-center gap-1 bg-black/40 px-2 py-0.5 rounded backdrop-blur-sm">
                      <i data-lucide="clock" class="w-3 h-3"></i> <?= htmlspecialchars($art['read_time']) ?>
                    </span>
                  </div>
                <?php else: ?>
                  <div class="h-44 bg-gradient-to-tr <?= $art['gradient'] ?> relative p-5 flex flex-col justify-between overflow-hidden">
                    <div class="flex items-center justify-between relative z-10">
                      <span class="text-[10px] font-mono font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white/95 text-slate-800 shadow-sm">
                        <?= htmlspecialchars($art['category']) ?>
                      </span>
                      <span class="text-[10px] font-semibold text-white/90 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3"></i> <?= htmlspecialchars($art['read_time']) ?>
                      </span>
                    </div>
                    <div class="relative z-10 text-white">
                      <i data-lucide="<?= htmlspecialchars($art['icon']) ?>" class="w-7 h-7 text-white/90 mb-1"></i>
                      <p class="text-[11px] text-white/80 font-mono"><?= htmlspecialchars($art['author']) ?></p>
                    </div>
                  </div>
                <?php endif; ?>

                <div class="p-5">
                  <p class="text-[11px] text-slate-400 font-medium mb-1.5"><?= htmlspecialchars($art['date']) ?></p>
                  <h3 class="font-heading font-bold text-base text-slate-900 group-hover:text-cyan-600 transition-colors leading-snug">
                    <a href="/blog/<?= urlencode($art['slug']) ?>">
                      <?= htmlspecialchars($art['title']) ?>
                    </a>
                  </h3>
                  <p class="mt-2.5 text-xs text-slate-600 line-clamp-3 leading-relaxed">
                    <?= htmlspecialchars($art['excerpt']) ?>
                  </p>
                </div>
              </div>

              <div class="px-5 pb-5 pt-2 border-t border-slate-100 flex items-center justify-between">
                <a href="/blog/<?= urlencode($art['slug']) ?>" class="text-xs font-bold text-cyan-600 group-hover:text-cyan-700 inline-flex items-center gap-1">
                  <span>Read Article</span>
                  <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform"></i>
                </a>
                <span class="text-[10px] text-slate-400 font-mono">Free Guide</span>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

      </div>
    </section>

    <!-- 8. CONCISE FAQS -->
    <section id="faq" class="py-20 bg-white border-b border-slate-200 relative">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-12" data-aos="fade-up" data-aos-duration="700">
          <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3 py-1 rounded-full border border-cyan-200">
            Got Questions?
          </span>
          <h2 class="mt-3 font-heading font-black text-3xl sm:text-4xl text-slate-900">
            Frequently Asked Questions
          </h2>
          <p class="mt-2 text-slate-600 text-sm">
            Everything you need to know about our IPTV subscription, delivery, and setup.
          </p>
        </div>

        <div class="space-y-3.5">
          
          <!-- FAQ 1 -->
          <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:border-cyan-300 transition-colors">
            <button data-faq-btn aria-expanded="false" class="w-full p-5 text-left flex items-center justify-between text-sm sm:text-base font-bold text-slate-900 hover:text-cyan-600 transition-colors cursor-pointer">
              <span>What is <?= SITE_NAME ?> IPTV and how does it work?</span>
              <i data-lucide="chevron-down" class="faq-icon w-5 h-5 text-slate-400 transition-transform duration-300"></i>
            </button>
            <div class="hidden p-5 pt-0 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100">
              IPTV stands for Internet Protocol Television. Instead of receiving TV signals through an antenna or satellite dish, channels and movies are streamed directly through your existing internet connection. You simply enter your login credentials into an app on your Firestick, Smart TV, or phone to enjoy <?= TOTAL_CHANNELS ?> live channels immediately.
            </div>
          </div>

          <!-- FAQ 2 -->
          <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:border-cyan-300 transition-colors">
            <button data-faq-btn aria-expanded="false" class="w-full p-5 text-left flex items-center justify-between text-sm sm:text-base font-bold text-slate-900 hover:text-cyan-600 transition-colors cursor-pointer">
              <span>How fast will I receive my subscription credentials?</span>
              <i data-lucide="chevron-down" class="faq-icon w-5 h-5 text-slate-400 transition-transform duration-300"></i>
            </button>
            <div class="hidden p-5 pt-0 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100">
              Our activation system is 100% automated! As soon as your order is confirmed, your M3U Playlist URL, Xtream Codes credentials (Username, Password, Server URL), and full installation tutorials are delivered to your email inbox within 60 seconds and accessible via our <a href="/track-order" class="text-cyan-600 underline font-semibold">Track Order page</a>.
            </div>
          </div>

          <!-- FAQ 3 -->
          <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:border-cyan-300 transition-colors">
            <button data-faq-btn aria-expanded="false" class="w-full p-5 text-left flex items-center justify-between text-sm sm:text-base font-bold text-slate-900 hover:text-cyan-600 transition-colors cursor-pointer">
              <span>Do I need a VPN to use <?= SITE_NAME ?>?</span>
              <i data-lucide="chevron-down" class="faq-icon w-5 h-5 text-slate-400 transition-transform duration-300"></i>
            </button>
            <div class="hidden p-5 pt-0 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100">
              No VPN is required because our streams utilize secure TLS encryption that shields your streaming data from ISP throttling. However, if you already have a VPN (such as Surfshark, NordVPN, or ExpressVPN), our servers are 100% VPN-friendly and will never block your connection.
            </div>
          </div>

          <!-- FAQ 4 -->
          <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:border-cyan-300 transition-colors">
            <button data-faq-btn aria-expanded="false" class="w-full p-5 text-left flex items-center justify-between text-sm sm:text-base font-bold text-slate-900 hover:text-cyan-600 transition-colors cursor-pointer">
              <span>What is your money-back guarantee policy?</span>
              <i data-lucide="chevron-down" class="faq-icon w-5 h-5 text-slate-400 transition-transform duration-300"></i>
            </button>
            <div class="hidden p-5 pt-0 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100">
              We offer a 100% risk-free 7-day money-back guarantee. If you experience technical issues that our 24/7 support team cannot resolve to your satisfaction, simply message us within 7 days for a hassle-free full refund.
            </div>
          </div>

        </div>

        <div class="mt-8 text-center">
          <p class="text-xs text-slate-500">
            Have more questions? <a href="/contact" class="text-cyan-600 font-semibold hover:underline">Contact our 24/7 technical helpdesk</a> or chat on WhatsApp.
          </p>
        </div>

      </div>
    </section>

    <!-- 9. BOTTOM CALL TO ACTION -->
    <section class="py-20 relative overflow-hidden bg-gradient-to-b from-white via-cyan-50/50 to-white" data-aos="zoom-in" data-aos-duration="800">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        
        <div class="w-16 h-16 rounded-2xl bg-cyan-100 border border-cyan-200 text-cyan-700 flex items-center justify-center mx-auto mb-6 shadow-md shadow-cyan-500/10">
          <i data-lucide="tv" class="w-8 h-8"></i>
        </div>

        <h2 class="font-heading font-black text-3xl sm:text-5xl text-slate-900 tracking-tight">
          Ready to Upgrade Your TV Experience Today?
        </h2>
        <p class="mt-4 text-slate-600 text-sm sm:text-lg max-w-2xl mx-auto">
          Join over <?= SUBSCRIBER_COUNT ?> satisfied viewers enjoying unlimited 4K sports, movies, and premium live channels with zero buffering.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
          <a href="/shop" class="btn-glow-cyan w-full sm:w-auto px-8 py-4 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-xl shadow-cyan-500/20">
            <span>Explore Shop Packages</span>
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
          </a>
          <?php if (TRIAL_IS_ACTIVE): ?>
          <button data-open-trial class="w-full sm:w-auto px-8 py-4 rounded-xl font-semibold text-sm bg-white hover:bg-slate-50 text-slate-800 flex items-center justify-center gap-2 border border-slate-200 shadow-sm transition-all cursor-pointer">
            <i data-lucide="clock" class="w-4 h-4 text-cyan-600"></i>
            <span><?= (TRIAL_MODE === 'paid') ? ('Paid ' . TRIAL_DURATION_HOURS . 'h Test Line') : 'Claim Free 24h Test Line' ?></span>
          </button>
          <?php endif; ?>
        </div>

        <div class="mt-6 flex items-center justify-center gap-6 text-xs text-slate-500 font-medium">
          <span>⚡ No Satellite Needed</span>
          <span>•</span>
          <span>🔒 No Contract / Cancel Anytime</span>
          <span>•</span>
          <span>💸 7-Day Money Back</span>
        </div>

      </div>
    </section>
  </main>

<?php
include __DIR__ . '/includes/modals.php';
include __DIR__ . '/includes/footer.php';
?>
