<?php
/**
 * LIVETVMARKET - Dedicated Shop & Pricing Packages Page
 * Full pricing overview, connection calculator, interactive top search & category filtering,
 * multi-currency dynamic conversion, and digital store products.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/store.php';

$storeEnabled = isStoreEnabled();
$storeCategories = $storeEnabled ? getStoreCategories(true) : [];
$storeProducts = $storeEnabled ? getStoreProducts(null, false, 'in_stock') : [];

$pageTitle = 'Shop Premium IPTV & Digital Subscriptions — ' . SITE_NAME;
$pageDesc = 'Explore official ' . SITE_NAME . ' 4K IPTV plans (' . TOTAL_CHANNELS . ' live channels) and digital subscription services (Netflix, Prime Video, Spotify). Instant delivery and 100% guarantee.';
$pageKeywords = 'shop iptv, buy iptv subscription, digital streaming keys, netflix premium account, amazon prime video, iptv 4k pricing, iptv plans';
$pageCanonical = SITE_URL . '/shop';
$activePage = 'shop';
$breadcrumbs = [
    ['name' => 'Home', 'url' => SITE_URL . '/'],
    ['name' => 'Shop & Packages', 'url' => SITE_URL . '/shop']
];

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

  <main class="bg-white py-14 sm:py-20 min-h-[85vh]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Shop Header -->
      <div class="text-center max-w-3xl mx-auto mb-10" data-aos="fade-up" data-aos-duration="700">
        <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3.5 py-1.5 rounded-full border border-cyan-200 shadow-sm">
          Transparent Streaming Store
        </span>
        <h1 class="mt-4 font-heading font-black text-4xl sm:text-5xl lg:text-6xl text-slate-900 tracking-tight">
          Choose Your Streaming Package
        </h1>
        <p class="mt-4 text-slate-600 text-sm sm:text-lg leading-relaxed">
          All subscription plans include complete access to <?= TOTAL_CHANNELS ?> live channels, <?= TOTAL_VOD ?> 4K movies &amp; series, Anti-Freeze™ 9.9, and automated 60-second activation.
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

      <!-- INTERACTIVE TOP SEARCH & CATEGORY FILTER BAR -->
      <div class="max-w-3xl mx-auto mb-14" data-aos="zoom-in" data-aos-delay="150">
        <div class="relative">
          <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
            <i data-lucide="search" class="w-5 h-5"></i>
          </span>
          <input 
            type="text" 
            id="shop-search-input" 
            placeholder="Search subscriptions (e.g. Netflix, Prime, 12 Months, IPTV, Spotify, ChatGPT, VPN)..." 
            class="w-full pl-11 pr-10 py-3.5 rounded-2xl bg-slate-50 border border-slate-300 text-slate-900 placeholder:text-slate-400 text-sm focus:outline-none focus:border-cyan-500 focus:bg-white shadow-sm transition-all"
          >
          <button 
            type="button" 
            id="shop-search-clear" 
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 hidden cursor-pointer"
            title="Clear search"
          >
            <i data-lucide="x" class="w-4 h-4"></i>
          </button>
        </div>

        <!-- Category Filter Pills -->
        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
          <button class="shop-filter-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-cyan-600 text-white shadow-sm cursor-pointer" data-filter="all">
            All Subscriptions
          </button>
          <button class="shop-filter-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 cursor-pointer" data-filter="iptv">
            📺 IPTV Packages
          </button>
          <?php foreach ($storeCategories as $sc): ?>
          <button class="shop-filter-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 cursor-pointer" data-filter="<?= htmlspecialchars($sc['slug']) ?>">
            <?= htmlspecialchars($sc['name']) ?>
          </button>
          <?php endforeach; ?>
        </div>

        <!-- Search Results Count & Reset Link -->
        <div class="mt-3 flex items-center justify-between text-xs text-slate-500 px-1">
          <span id="shop-search-count">Showing all streaming packages &amp; digital services</span>
          <button type="button" id="shop-search-reset-link" class="text-cyan-600 hover:text-cyan-700 font-bold hidden cursor-pointer">
            Reset Filters
          </button>
        </div>
      </div>

      <!-- No Matching Results Empty State -->
      <div id="shop-empty-results" class="hidden py-16 text-center bg-slate-50 border border-slate-200 rounded-3xl mb-14">
        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4 border border-slate-200">
          <i data-lucide="search-x" class="w-8 h-8"></i>
        </div>
        <h3 class="font-heading font-black text-xl text-slate-900">No Matching Subscriptions Found</h3>
        <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
          We couldn't find any packages or services matching your query. Try searching for "Netflix", "Prime", "Spotify", "12 Months", or "IPTV".
        </p>
        <button type="button" id="shop-empty-reset-btn" class="mt-5 px-6 py-2.5 rounded-xl btn-glow-cyan text-slate-950 font-bold text-xs uppercase cursor-pointer shadow-md">
          Show All Services
        </button>
      </div>

      <!-- IPTV PRICING CARDS SECTION -->
      <div id="iptv-section-wrapper" class="mb-20">
        <div id="iptv-heading-container" class="mb-6 flex items-center justify-between">
          <div>
            <h2 class="font-heading font-black text-2xl text-slate-900">Live IPTV Streaming Subscriptions</h2>
            <p class="text-xs text-slate-500">Universal access across Smart TV, Firestick, Android, iOS &amp; PC</p>
          </div>
          <span class="text-xs font-mono font-bold text-cyan-700 bg-cyan-50 px-2.5 py-1 rounded-lg border border-cyan-200 hidden sm:inline-block">
            Anti-Freeze™ 9.9
          </span>
        </div>

        <div id="pricing" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= min(4, count($dbPlans)) ?> gap-6 items-stretch">
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
          <div class="shop-item iptv-plan-card border-beam-glow relative transform lg:-translate-y-2 shadow-2xl shadow-cyan-500/10 flex flex-col justify-between" data-title="<?= htmlspecialchars($p['name']) ?>" data-category="iptv" data-desc="<?= htmlspecialchars($p['name'] . ' ' . implode(' ', $featuresList)) ?>" data-aos="fade-up" data-aos-delay="<?= 100 * ($idx + 1) ?>">
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
          <div class="shop-item iptv-plan-card bg-white rounded-2xl p-6 sm:p-7 flex flex-col justify-between border border-slate-200 shadow-sm hover:shadow-xl hover:border-cyan-300 transition-all tilt-card spotlight-card" data-title="<?= htmlspecialchars($p['name']) ?>" data-category="iptv" data-desc="<?= htmlspecialchars($p['name'] . ' ' . implode(' ', $featuresList)) ?>" data-aos="fade-up" data-aos-delay="<?= 100 * ($idx + 1) ?>">
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
      </div>

      <?php if ($storeEnabled && !empty($storeProducts)): ?>
      <!-- DIGITAL STREAMING & APP SERVICES SHOWCASE -->
      <section id="digital-store" class="mb-20 pt-10 border-t border-slate-200" data-aos="fade-up">
        
        <div id="digital-store-heading" class="text-center max-w-3xl mx-auto mb-10">
          <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3.5 py-1.5 rounded-full border border-cyan-200 shadow-sm">
            Digital Subscriptions Marketplace
          </span>
          <h2 class="mt-4 font-heading font-black text-3xl sm:text-4xl text-slate-900 tracking-tight">
            Popular Streaming &amp; AI Subscriptions
          </h2>
          <p class="mt-3 text-slate-600 text-sm sm:text-base">
            Dedicated private profiles and upgrades with automated WhatsApp delivery, zero VPN hassle, and 100% replacement warranty.
          </p>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch" id="store-products-grid">
          <?php foreach ($storeProducts as $sp): 
            $catSlug = $sp['category_slug'] ?: 'general';
            $isOutOfStock = ($sp['status'] === 'out_of_stock');
            $varsCount = count($sp['variations'] ?? []);
          ?>
          <div class="shop-item store-prod-card bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-sm hover:shadow-xl hover:border-cyan-300 transition-all duration-300 group relative" data-title="<?= htmlspecialchars($sp['name']) ?>" data-category="<?= htmlspecialchars($catSlug) ?>" data-desc="<?= htmlspecialchars($sp['name'] . ' ' . $sp['short_desc'] . ' ' . $sp['category_name']) ?>">
            
            <div>
              <!-- Thumbnail Container -->
              <div class="h-44 bg-slate-50 rounded-xl flex items-center justify-center p-4 mb-4 relative overflow-hidden border border-slate-100 group-hover:bg-slate-100/60 transition-colors">
                <?php if (!empty($sp['badge'])): ?>
                <span class="absolute top-2.5 left-2.5 z-10 px-2.5 py-0.5 rounded-full bg-cyan-600 text-white font-mono font-bold text-[10px] shadow-sm">
                  <?= htmlspecialchars($sp['badge']) ?>
                </span>
                <?php endif; ?>

                <?php if ($isOutOfStock): ?>
                <span class="absolute top-2.5 right-2.5 z-10 px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-mono font-bold text-[10px]">
                  Sold Out
                </span>
                <?php else: ?>
                <span class="absolute top-2.5 right-2.5 z-10 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-[10px]">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Instant
                </span>
                <?php endif; ?>

                <img 
                  src="<?= htmlspecialchars($sp['image'] ?: 'assets/images/placeholder-product.png') ?>" 
                  alt="<?= htmlspecialchars($sp['name']) ?>" 
                  class="max-h-full object-contain group-hover:scale-105 transition-transform duration-300"
                  loading="lazy"
                >
              </div>

              <!-- Category & Name -->
              <span class="text-[10px] font-mono font-bold text-cyan-700 uppercase bg-cyan-50 px-2 py-0.5 rounded border border-cyan-200">
                <?= htmlspecialchars($sp['category_name'] ?: 'Digital') ?>
              </span>

              <h3 class="font-heading font-bold text-base text-slate-900 mt-2 line-clamp-1 group-hover:text-cyan-600 transition-colors">
                <a href="/product/<?= urlencode($sp['slug']) ?>"><?= htmlspecialchars($sp['name']) ?></a>
              </h3>

              <p class="text-xs text-slate-500 mt-1.5 line-clamp-2 leading-relaxed">
                <?= htmlspecialchars($sp['short_desc']) ?>
              </p>
            </div>

            <!-- Price & Button -->
            <div class="mt-5 pt-3.5 border-t border-slate-100 flex items-center justify-between">
              <div>
                <span class="text-[10px] text-slate-400 block font-medium">Starts from</span>
                <span class="store-prod-price font-heading font-black text-lg text-slate-900 group-hover:text-cyan-600 transition-colors" data-base-price="<?= number_format($sp['min_price'], 2, '.', '') ?>">
                  $<?= number_format($sp['min_price'], 2) ?>
                </span>
                <?php if ($varsCount > 1): ?>
                <span class="text-[10px] text-slate-400 block"><?= $varsCount ?> options</span>
                <?php endif; ?>
              </div>

              <a 
                href="/product/<?= urlencode($sp['slug']) ?>" 
                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-cyan-600 hover:text-white text-slate-800 text-xs font-bold transition-all flex items-center gap-1 shadow-sm"
              >
                <span>View Plan</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
              </a>
            </div>

          </div>
          <?php endforeach; ?>
        </div>

      </section>

      <script>
        document.addEventListener('DOMContentLoaded', function() {
          const searchInput = document.getElementById('shop-search-input');
          const clearBtn = document.getElementById('shop-search-clear');
          const resetLink = document.getElementById('shop-search-reset-link');
          const emptyResetBtn = document.getElementById('shop-empty-reset-btn');
          const emptyBox = document.getElementById('shop-empty-results');
          const countDisplay = document.getElementById('shop-search-count');
          const filterBtns = document.querySelectorAll('.shop-filter-btn');
          const allItems = document.querySelectorAll('.shop-item');
          const iptvWrapper = document.getElementById('iptv-section-wrapper');
          const storeSection = document.getElementById('digital-store');

          let activeFilter = 'all';

          function filterShop() {
            const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
            let visibleCount = 0;
            let iptvVisible = 0;
            let storeVisible = 0;

            if (clearBtn) {
              if (q.length > 0) clearBtn.classList.remove('hidden');
              else clearBtn.classList.add('hidden');
            }

            if (resetLink) {
              if (q.length > 0 || activeFilter !== 'all') resetLink.classList.remove('hidden');
              else resetLink.classList.add('hidden');
            }

            allItems.forEach(item => {
              const title = (item.getAttribute('data-title') || '').toLowerCase();
              const cat = (item.getAttribute('data-category') || '').toLowerCase();
              const desc = (item.getAttribute('data-desc') || '').toLowerCase();
              const isIptv = item.classList.contains('iptv-plan-card');

              // Category filter check
              let matchesCat = false;
              if (activeFilter === 'all') {
                matchesCat = true;
              } else if (activeFilter === 'iptv') {
                matchesCat = isIptv;
              } else {
                matchesCat = (cat === activeFilter);
              }

              // Text query search check
              let matchesQuery = false;
              if (!q) {
                matchesQuery = true;
              } else {
                matchesQuery = title.includes(q) || cat.includes(q) || desc.includes(q);
              }

              if (matchesCat && matchesQuery) {
                item.style.display = 'flex';
                visibleCount++;
                if (isIptv) iptvVisible++;
                else storeVisible++;
              } else {
                item.style.display = 'none';
              }
            });

            // Adjust sections visibility
            if (iptvWrapper) {
              iptvWrapper.style.display = (iptvVisible > 0) ? 'block' : 'none';
            }
            if (storeSection) {
              storeSection.style.display = (storeVisible > 0) ? 'block' : 'none';
            }

            // Empty state
            if (emptyBox) {
              if (visibleCount === 0) {
                emptyBox.classList.remove('hidden');
              } else {
                emptyBox.classList.add('hidden');
              }
            }

            // Results count feedback
            if (countDisplay) {
              if (q.length > 0) {
                countDisplay.textContent = `Found ${visibleCount} subscription${visibleCount === 1 ? '' : 's'} matching "${q}"`;
              } else if (activeFilter !== 'all') {
                countDisplay.textContent = `Showing ${visibleCount} subscription${visibleCount === 1 ? '' : 's'}`;
              } else {
                countDisplay.textContent = `Showing all ${visibleCount} subscriptions & digital services`;
              }
            }
          }

          // Category filter button click handler
          filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
              activeFilter = this.getAttribute('data-filter');
              filterBtns.forEach(b => {
                b.classList.remove('bg-cyan-600', 'text-white');
                b.classList.add('bg-slate-100', 'text-slate-700', 'hover:bg-slate-200');
              });
              this.classList.remove('bg-slate-100', 'text-slate-700', 'hover:bg-slate-200');
              this.classList.add('bg-cyan-600', 'text-white');
              filterShop();
            });
          });

          if (searchInput) {
            searchInput.addEventListener('input', filterShop);
          }

          function resetFilters() {
            if (searchInput) searchInput.value = '';
            activeFilter = 'all';
            filterBtns.forEach(b => {
              if (b.getAttribute('data-filter') === 'all') {
                b.classList.remove('bg-slate-100', 'text-slate-700');
                b.classList.add('bg-cyan-600', 'text-white');
              } else {
                b.classList.remove('bg-cyan-600', 'text-white');
                b.classList.add('bg-slate-100', 'text-slate-700');
              }
            });
            filterShop();
          }

          if (clearBtn) clearBtn.addEventListener('click', resetFilters);
          if (resetLink) resetLink.addEventListener('click', resetFilters);
          if (emptyResetBtn) emptyResetBtn.addEventListener('click', resetFilters);

          // Digital Store Products Multi-Currency Converter
          function updateStoreCardsCurrency() {
            let activeCurrency = (window.CURRENCY_DATA && window.CURRENCY_DATA.detectedCurrency) ? window.CURRENCY_DATA.detectedCurrency : 'USD';
            const userManual = localStorage.getItem('livetv_user_manual_currency');
            const savedCurr = localStorage.getItem('livetv_selected_currency');
            if (userManual && savedCurr && window.CURRENCY_DATA && window.CURRENCY_DATA.rates && window.CURRENCY_DATA.rates[savedCurr]) {
              activeCurrency = savedCurr;
            }
            const curr = (window.CURRENCY_DATA && window.CURRENCY_DATA.rates && window.CURRENCY_DATA.rates[activeCurrency]) || { symbol: '$', rate: 1.0 };
            const sym = curr.symbol || '$';

            document.querySelectorAll('.store-prod-price').forEach(el => {
              const base = parseFloat(el.getAttribute('data-base-price') || 0);
              const converted = base * parseFloat(curr.rate || 1.0);
              if (activeCurrency === 'PKR' || curr.rate > 50) {
                el.textContent = `${sym} ${Math.round(converted).toLocaleString()}`;
              } else {
                el.textContent = `${sym}${converted.toFixed(2)}`;
              }
            });
          }

          updateStoreCardsCurrency();
          window.addEventListener('storage', updateStoreCardsCurrency);
          window.addEventListener('livetv:currencyUpdated', updateStoreCardsCurrency);
          ['header-currency-select', 'mobile-currency-select'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', () => setTimeout(updateStoreCardsCurrency, 50));
          });
        });
      </script>
      <?php endif; ?>

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
                As soon as your checkout is completed, our automated provisioning system generates and delivers your credentials directly to your WhatsApp and Email.
              </p>
            </div>
          </div>

          <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 shadow-sm">
              <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <div>
              <h3 class="font-bold text-slate-900 text-sm mb-1">100% Replacement Warranty</h3>
              <p class="text-xs text-slate-600 leading-relaxed">
                Every subscription includes unconditional warranty and dedicated WhatsApp support for the entire duration of your plan.
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
                We accept all major credit and debit cards with secure 256-bit encryption through our certified payment gateway.
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
