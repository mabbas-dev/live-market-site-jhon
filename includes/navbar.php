<?php
/**
 * LIVETVMARKET - Luxury Single-Line Unified Streaming Navbar
 * Clean 5-link structure: Home | Shop | Track Order | Blogs | Contact Us
 */
$curPage = isset($activePage) ? $activePage : 'home';

if (!function_exists('getNavLinkClass')) {
    function getNavLinkClass($pageKey, $active) {
        if ($pageKey === $active) {
            return 'text-slate-900 bg-white font-bold border border-gray-200 shadow-sm';
        }
        return 'text-slate-600 hover:text-cyan-600';
    }
}
?>
<!-- UNIFIED SINGLE-LINE LUXURY HEADER (Strictly 1 Row, Zero Wrapping) -->
<header class="sticky-header sticky top-0 z-45 backdrop-blur-2xl bg-white/95 border-b border-gray-200 shadow-sm transition-all duration-300 select-none">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between flex-nowrap h-20 gap-3 sm:gap-4 w-full">
      
      <!-- LEFT: Brand Logo Image -->
      <a href="/" class="flex items-center flex-shrink-0 focus:outline-none group py-1" aria-label="<?= htmlspecialchars(SITE_NAME) ?> Home">
        <img src="assets/images/header-logo.png" alt="<?= htmlspecialchars(SITE_NAME) ?>" class="h-10 sm:h-12 w-auto max-w-[200px] sm:max-w-[250px] object-contain transition-transform group-hover:scale-105">
      </a>

      <!-- CENTER: Desktop Pill Navigation (Strictly: Home | Shop | Track Order | Blogs | Contact Us) -->
      <nav aria-label="Main Navigation" class="hidden lg:flex items-center gap-1 bg-gray-100 border border-gray-200 rounded-xl p-1 text-xs font-semibold shadow-inner whitespace-nowrap flex-shrink-0">
        <a href="/" class="px-3.5 py-1.5 rounded-lg transition-all <?= getNavLinkClass('home', $curPage) ?>">
          Home
        </a>
        <a href="/shop" class="px-3.5 py-1.5 rounded-lg transition-all <?= getNavLinkClass('shop', $curPage) ?>">
          Shop
        </a>
        <a href="/setup" class="px-3.5 py-1.5 rounded-lg transition-all <?= getNavLinkClass('setup', $curPage) ?>">
          Setup Guide
        </a>
        <a href="/track-order" class="px-3.5 py-1.5 rounded-lg transition-all <?= getNavLinkClass('track', $curPage) ?>">
          Track Order
        </a>
        <a href="/blog" class="px-3.5 py-1.5 rounded-lg transition-all <?= getNavLinkClass('blog', $curPage) ?>">
          Blogs
        </a>
        <a href="/contact" class="px-3.5 py-1.5 rounded-lg transition-all <?= getNavLinkClass('contact', $curPage) ?>">
          Contact Us
        </a>
      </nav>

      <!-- RIGHT: Currency + Free Trial + Instant Access CTA (Zero Wrapping Guarantee) -->
      <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0 flex-nowrap">
        
        <!-- Compact Currency Dropdown (Ultra Space-Saving, Desktop only) -->
        <?php $activeNavCurrencies = getCurrencies(true); ?>
        <div class="hidden lg:block relative flex-shrink-0" id="desktop-currency-container">
          <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 transition-all shadow-sm focus-within:ring-2 focus-within:ring-cyan-500/20 focus-within:border-cyan-400">
            <i data-lucide="globe" class="w-3.5 h-3.5 text-cyan-600 flex-shrink-0"></i>
            <select id="header-currency-select" aria-label="Select Currency" class="bg-transparent font-mono font-bold text-xs text-slate-800 focus:outline-none cursor-pointer pr-1">
              <?php foreach ($activeNavCurrencies as $cur): ?>
                <option value="<?= htmlspecialchars($cur['code']) ?>">
                  <?= htmlspecialchars($cur['code']) ?> (<?= htmlspecialchars($cur['symbol']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Free Trial Button (Slightly smaller, dynamic from admin, Desktop only) -->
        <?php if (TRIAL_IS_ACTIVE): ?>
        <button type="button" data-open-trial class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold rounded-lg border border-cyan-200 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 hover:text-cyan-800 transition-all shadow-sm whitespace-nowrap flex-shrink-0 cursor-pointer">
          <i data-lucide="play" class="w-3 h-3 text-cyan-600"></i>
          <span><?= htmlspecialchars(TRIAL_BUTTON_LABEL) ?></span>
        </button>
        <?php endif; ?>

        <!-- High-Impact Instant Access CTA (Compact, responsive on phone & desktop) -->
        <a href="/shop" class="btn-glow-cyan px-2.5 sm:px-4 py-1.5 sm:py-2 text-[11px] sm:text-xs font-bold rounded-lg flex items-center gap-1.5 uppercase tracking-wider whitespace-nowrap flex-shrink-0 shadow-md shadow-cyan-500/20 hover:shadow-cyan-400/35 transition-all">
          <span>Get Instant Access</span>
          <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>

        <!-- Mobile / Tablet Hamburger Toggle Button -->
        <button type="button" id="mobile-menu-btn" aria-label="Open Navigation Menu" class="p-2 sm:p-2.5 rounded-xl bg-gray-100 border border-gray-200 text-slate-600 hover:text-cyan-600 hover:border-cyan-300 transition-all lg:hidden flex-shrink-0 cursor-pointer">
          <i data-lucide="menu" class="w-5 h-5"></i>
        </button>
      </div>

    </div>
  </div>
</header>

<!-- MOBILE & TABLET NAVIGATION DRAWER WITH SILKY SMOOTH SLIDE-OVER ANIMATION -->
<div id="mobile-menu-drawer" class="fixed inset-0 z-50 pointer-events-none opacity-0 transition-opacity duration-300 ease-in-out lg:hidden" aria-hidden="true">
  <!-- Semi-transparent backdrop with blur -->
  <div id="mobile-menu-backdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300"></div>

  <!-- Slide-out Drawer Panel (from right) -->
  <div id="mobile-menu-panel" class="absolute top-0 right-0 bottom-0 w-full max-w-sm bg-white shadow-2xl flex flex-col p-6 overflow-y-auto transform translate-x-full transition-transform duration-300 ease-out border-l border-gray-200">
    
    <!-- Drawer Header -->
    <div class="flex items-center justify-between pb-5 border-b border-gray-100">
      <a href="/" class="flex items-center">
        <img src="assets/images/header-logo.png" alt="<?= htmlspecialchars(SITE_NAME) ?>" class="h-9 w-auto object-contain">
      </a>
      <button type="button" id="close-mobile-menu" aria-label="Close menu" class="p-2.5 rounded-xl bg-gray-100 text-slate-500 hover:text-slate-900 hover:bg-gray-200 transition-all cursor-pointer">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Mobile Currency Selector Dropdown -->
    <div class="mt-5">
      <label for="mobile-currency-select" class="block text-xs text-slate-500 font-semibold mb-1.5">Select Store Currency:</label>
      <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 hover:border-cyan-400 transition-colors">
        <i data-lucide="globe" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
        <select id="mobile-currency-select" class="w-full bg-transparent font-mono font-bold text-xs text-slate-800 focus:outline-none cursor-pointer">
          <?php foreach ($activeNavCurrencies as $cur): ?>
            <option value="<?= htmlspecialchars($cur['code']) ?>">
              <?= htmlspecialchars($cur['code']) ?> (<?= htmlspecialchars($cur['symbol']) ?>) — <?= htmlspecialchars($cur['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Mobile Promo Code Banner -->
    <div class="mt-3.5 p-3 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 flex items-center justify-between text-xs text-amber-800 shadow-sm">
      <div class="flex items-center gap-2">
        <i data-lucide="flame" class="w-4 h-4 text-amber-500 animate-pulse"></i>
        <span class="font-medium"><?= htmlspecialchars(FLASH_PROMO_DISCOUNT) ?> OFF Code:</span>
      </div>
      <strong class="font-mono text-amber-950 font-bold px-2 py-0.5 rounded bg-amber-100 text-xs tracking-wider"><?= htmlspecialchars(FLASH_PROMO_CODE) ?></strong>
    </div>

    <!-- Mobile Navigation Links -->
    <nav class="flex flex-col gap-1.5 mt-5 text-sm font-semibold">
      <a href="/" class="py-2.5 px-3.5 rounded-xl <?= ($curPage === 'home') ? 'bg-cyan-50 text-cyan-700 font-bold border border-cyan-100' : 'text-slate-700 hover:bg-slate-50 hover:text-cyan-600' ?> flex items-center gap-3 transition-colors">
        <i data-lucide="home" class="w-4 h-4 text-cyan-600"></i>
        <span>Home</span>
      </a>
      <a href="/shop" class="py-2.5 px-3.5 rounded-xl <?= ($curPage === 'shop') ? 'bg-cyan-50 text-cyan-700 font-bold border border-cyan-100' : 'text-slate-700 hover:bg-slate-50 hover:text-cyan-600' ?> flex items-center justify-between transition-colors">
        <div class="flex items-center gap-3">
          <i data-lucide="shopping-bag" class="w-4 h-4 text-cyan-600"></i>
          <span>Shop Packages</span>
        </div>
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-cyan-100 text-cyan-800">Save 60%</span>
      </a>
      <a href="/setup" class="py-2.5 px-3.5 rounded-xl <?= ($curPage === 'setup') ? 'bg-cyan-50 text-cyan-700 font-bold border border-cyan-100' : 'text-slate-700 hover:bg-slate-50 hover:text-cyan-600' ?> flex items-center gap-3 transition-colors">
        <i data-lucide="tv" class="w-4 h-4 text-cyan-600"></i>
        <span>Setup Guide</span>
      </a>
      <a href="/channels" class="py-2.5 px-3.5 rounded-xl <?= ($curPage === 'channels') ? 'bg-cyan-50 text-cyan-700 font-bold border border-cyan-100' : 'text-slate-700 hover:bg-slate-50 hover:text-cyan-600' ?> flex items-center gap-3 transition-colors">
        <i data-lucide="list" class="w-4 h-4 text-cyan-600"></i>
        <span>All Channels (25,000+)</span>
      </a>
      <a href="/track-order" class="py-2.5 px-3.5 rounded-xl <?= ($curPage === 'track') ? 'bg-cyan-50 text-cyan-700 font-bold border border-cyan-100' : 'text-slate-700 hover:bg-slate-50 hover:text-cyan-600' ?> flex items-center gap-3 transition-colors">
        <i data-lucide="package-search" class="w-4 h-4 text-cyan-600"></i>
        <span>Track Order</span>
      </a>
      <a href="/blog" class="py-2.5 px-3.5 rounded-xl <?= ($curPage === 'blog') ? 'bg-cyan-50 text-cyan-700 font-bold border border-cyan-100' : 'text-slate-700 hover:bg-slate-50 hover:text-cyan-600' ?> flex items-center gap-3 transition-colors">
        <i data-lucide="newspaper" class="w-4 h-4 text-cyan-600"></i>
        <span>Blogs & Guides</span>
      </a>
      <a href="/contact" class="py-2.5 px-3.5 rounded-xl <?= ($curPage === 'contact') ? 'bg-cyan-50 text-cyan-700 font-bold border border-cyan-100' : 'text-slate-700 hover:bg-slate-50 hover:text-cyan-600' ?> flex items-center gap-3 transition-colors">
        <i data-lucide="message-square" class="w-4 h-4 text-cyan-600"></i>
        <span>Contact Us</span>
      </a>

      <div class="pt-3 border-t border-gray-100 mt-2 space-y-1">
        <button type="button" onclick="if (typeof closeMobileDrawer === 'function') closeMobileDrawer(); openVerificationModal();" class="w-full py-2 px-3 text-emerald-700 hover:bg-emerald-50 rounded-xl text-left flex items-center justify-between text-xs font-semibold transition-colors">
          <span class="flex items-center gap-2">
            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
            <span>Security & Legal Certificate</span>
          </span>
          <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 font-bold text-emerald-800">100% Legal</span>
        </button>
        <a href="admin/login.php" class="py-2 px-3 text-slate-400 hover:text-cyan-600 text-xs flex items-center gap-2 transition-colors">
          <i data-lucide="lock" class="w-4 h-4"></i>
          <span>Admin Portal</span>
        </a>
      </div>
    </nav>

    <!-- Bottom Action CTAs -->
    <div class="mt-auto pt-5 flex flex-col gap-2.5 border-t border-gray-100">
      <?php if (TRIAL_IS_ACTIVE): ?>
      <button type="button" data-open-trial onclick="if (typeof closeMobileDrawer === 'function') closeMobileDrawer();" class="w-full py-3 text-center rounded-xl bg-cyan-50 border border-cyan-200 font-bold text-xs text-cyan-800 hover:bg-cyan-100 transition-all flex items-center justify-center gap-2 shadow-sm cursor-pointer">
        <i data-lucide="play" class="w-3.5 h-3.5 text-cyan-600"></i>
        <span><?= htmlspecialchars(TRIAL_BUTTON_LABEL) ?></span>
      </button>
      <?php endif; ?>
      <a href="/shop" class="btn-glow-cyan w-full py-3.5 text-center rounded-xl font-bold text-xs uppercase tracking-wider text-white shadow-lg shadow-cyan-500/20 flex items-center justify-center gap-2">
        <span>Get Instant Access</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
      </a>
    </div>

  </div>
</div>
