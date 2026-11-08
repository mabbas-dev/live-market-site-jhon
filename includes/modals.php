  <!-- =========================================================================
       MODALS & FLOATING WIDGETS
       ========================================================================= -->

  <!-- OFFICIAL DOMAIN VERIFICATION & 100% LEGAL CERTIFICATE MODAL -->
  <div id="domain-verification-modal" data-backdrop="static" class="modal-overlay fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-content w-full max-w-lg rounded-2xl p-6 sm:p-8 bg-white border border-emerald-200 shadow-2xl relative" onclick="event.stopPropagation();">
      
      <button type="button" id="close-verification-modal" data-close-modal onclick="closeVerificationModal()" class="absolute top-5 right-5 p-2 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 hover:bg-slate-200 transition-all cursor-pointer z-30" aria-label="Close Verification Modal">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>

      <!-- Badge Icon -->
      <div class="flex items-center gap-3.5 mb-5">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-500 flex items-center justify-center shadow-sm flex-shrink-0">
          <i data-lucide="shield-check" class="w-7 h-7"></i>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <span class="text-[11px] font-mono font-bold text-emerald-600 uppercase tracking-widest bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
              Official Verified Gateway
            </span>
          </div>
          <h3 class="font-heading font-black text-xl sm:text-2xl text-slate-900 mt-1">
            <?= SITE_NAME ?> Official Portal
          </h3>
        </div>
      </div>

      <!-- Verified Domain Card -->
      <div class="p-3.5 rounded-xl bg-gray-50 border border-emerald-200 mb-5 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
          <div>
            <p class="text-[11px] text-slate-500 font-medium">Verified Official Domain:</p>
            <p class="text-sm font-mono font-bold text-slate-900 tracking-wide"><?= SITE_URL ?></p>
          </div>
        </div>
        <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-600 text-xs font-bold border border-emerald-200 flex items-center gap-1">
          <i data-lucide="check" class="w-3.5 h-3.5"></i> Authenticated
        </span>
      </div>

      <!-- Trust Pillars -->
      <div class="space-y-3 mb-6 text-xs text-slate-600">
        <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200">
          <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i data-lucide="award" class="w-4 h-4"></i>
          </div>
          <div>
            <h4 class="font-bold text-slate-900 text-xs mb-0.5">100% Authorized & Legal Streams</h4>
            <p class="text-[11px] text-slate-500 leading-relaxed">
              All <?= TOTAL_CHANNELS ?> live channels and VOD media are legally syndicated through certified transmission partners and CDN distribution agreements.
            </p>
          </div>
        </div>

        <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200">
          <div class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-500 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i data-lucide="lock" class="w-4 h-4"></i>
          </div>
          <div>
            <h4 class="font-bold text-slate-900 text-xs mb-0.5">Zero ISP Throttling & 256-Bit SSL Protection</h4>
            <p class="text-[11px] text-slate-500 leading-relaxed">
              End-to-end encrypted streams ensure complete privacy and immunity from local ISP speed restrictions during peak live matches.
            </p>
          </div>
        </div>

        <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200">
          <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-500 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i data-lucide="zap" class="w-4 h-4"></i>
          </div>
          <div>
            <h4 class="font-bold text-slate-900 text-xs mb-0.5">Anti-Freeze™ 9.9 Cluster Guarantee</h4>
            <p class="text-[11px] text-slate-500 leading-relaxed">
              Powered by <?= ACTIVE_CLUSTERS ?> worldwide cloud edge servers for seamless 4K 60FPS streaming without stutter or lag.
            </p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <button type="button" id="accept-verification-btn" onclick="closeVerificationModal()" class="btn-glow-cyan w-full sm:flex-1 py-3 rounded-xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer">
          <span>Enter Store & Explore</span>
          <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>
        <?php if (TRIAL_IS_ACTIVE): ?>
        <button data-open-trial class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-slate-700 font-bold text-xs border border-gray-200 transition-colors" onclick="document.getElementById('domain-verification-modal').classList.remove('active');">
          <?= (TRIAL_MODE === 'paid') ? ('Paid Test Line') : 'Free 24h Trial' ?>
        </button>
        <?php endif; ?>
      </div>

      <!-- Remember Checkbox -->
      <div class="mt-4 flex items-center justify-between text-[11px] text-slate-500 pt-3 border-t border-gray-200">
        <label class="flex items-center gap-2 cursor-pointer select-none">
          <input type="checkbox" id="remember-verification-check" class="accent-emerald-500 rounded">
          <span>Don't show this verification again</span>
        </label>
        <span class="text-emerald-500 font-semibold flex items-center gap-1">
          <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Verified Secure
        </span>
      </div>

    </div>
  </div>

  <!-- FLOATING SITE VISIT VERIFICATION POPUP (Placed in bottom-right corner, non-intrusive) -->
  <aside id="floating-verification-popup" class="fixed bottom-6 right-4 sm:right-6 z-50 max-w-sm rounded-2xl p-4 bg-white border border-emerald-200 shadow-xl transform translate-y-0 opacity-100 transition-all duration-300">
    <div class="flex items-start gap-3">
      <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-500 border border-emerald-200 flex items-center justify-center flex-shrink-0 mt-0.5">
        <i data-lucide="shield-check" class="w-5 h-5"></i>
      </div>
      <div class="flex-1">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-mono font-bold text-emerald-600 uppercase tracking-wider bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
            Official Domain
          </span>
          <button type="button" id="dismiss-floating-verification" onclick="dismissFloatingVerification()" class="text-slate-400 hover:text-slate-700 transition-colors p-1" aria-label="Dismiss verification popup">
            <i data-lucide="x" class="w-4 h-4"></i>
          </button>
        </div>
        <h4 class="font-bold text-slate-900 text-xs mt-1">100% Authorized & Legal Service</h4>
        <p class="text-[11px] text-slate-600 mt-1 leading-snug">
          You are visiting the verified portal: <strong class="text-slate-900 font-mono"><?= htmlspecialchars(SITE_DOMAIN) ?></strong>. All channel feeds & VOD are certified through licensed transmission partners.
        </p>
        <div class="mt-3 flex items-center gap-2">
          <button type="button" id="floating-view-cert-btn" onclick="openVerificationModal()" class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-500 hover:text-white text-emerald-600 border border-emerald-200 text-[10px] font-bold transition-all flex items-center gap-1 cursor-pointer">
            <i data-lucide="award" class="w-3.5 h-3.5"></i>
            <span>View Certificate</span>
          </button>
          <button type="button" id="floating-gotit-btn" onclick="dismissFloatingVerification()" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-600 hover:text-slate-900 text-[10px] font-semibold transition-all cursor-pointer">
            Got it
          </button>
        </div>
      </div>
    </div>
  </aside>

  <!-- FREE 24H TRIAL MODAL (Ready to connect to backend) -->
  <div id="trial-modal" data-backdrop="static" class="modal-overlay fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-content w-full max-w-lg rounded-2xl p-6 sm:p-8 bg-white border border-cyan-200 shadow-2xl relative" onclick="event.stopPropagation();">
      
      <button type="button" data-close-modal class="absolute top-5 right-5 p-2 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 hover:bg-slate-200 transition-all cursor-pointer z-30" aria-label="Close">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>

      <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-500 flex items-center justify-center">
          <i data-lucide="play" class="w-5 h-5"></i>
        </div>
        <div>
          <h3 class="font-heading font-bold text-xl text-slate-900">
            <?= (TRIAL_MODE === 'paid') ? ('Paid ' . htmlspecialchars(TRIAL_DURATION_HOURS) . 'h Test Line') : 'Get Your Free 24h Trial' ?>
          </h3>
          <p class="text-xs text-slate-500">
            <?= (TRIAL_MODE === 'paid') ? ('Test fee: $' . number_format((float)TRIAL_PRICE, 2) . ' USD • Instant automated line') : 'No credit card required • Instant automated line' ?>
          </p>
        </div>
      </div>

      <!-- Trial Form -->
      <form id="trial-form" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Your Full Name <span class="text-rose-500">*</span></label>
          <input required id="trial-name" name="full_name" type="text" placeholder="e.g. John Doe" class="w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-300 text-slate-900 text-xs focus:outline-none focus:border-cyan-400 placeholder:text-slate-400">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Your Email Address (For Credentials) <span class="text-rose-500">*</span></label>
          <input required id="trial-email" name="email" type="email" placeholder="john@example.com" class="w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-300 text-slate-900 text-xs focus:outline-none focus:border-cyan-400 placeholder:text-slate-400">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp Number (with Country Code) <span class="text-rose-500">*</span></label>
          <input required id="trial-whatsapp" name="whatsapp" type="tel" placeholder="+1 555 019 2831" class="w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-300 text-slate-900 text-xs focus:outline-none focus:border-cyan-400 placeholder:text-slate-400">
          <p class="text-[10px] text-slate-400 mt-1">Required for instant line delivery and priority setup assistance.</p>
        </div>

        <button type="submit" class="btn-glow-cyan w-full py-3.5 rounded-xl font-bold text-xs uppercase tracking-wider mt-4 cursor-pointer">
          <?= (TRIAL_MODE === 'paid') ? ('Proceed to Test Line ($' . number_format((float)TRIAL_PRICE, 2) . ')') : 'Generate Free Test Line Now' ?>
        </button>

        <p class="text-[10px] text-center text-slate-500">
          🔒 We respect your privacy. No spam ever. Trial expires automatically in <?= htmlspecialchars(TRIAL_DURATION_HOURS) ?> hours.
        </p>
      </form>

      <!-- Trial Success State -->
      <div id="trial-success-box" class="hidden text-center py-6 space-y-4">
        <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto text-2xl border border-emerald-200">
          ✓
        </div>
        <h4 class="font-heading font-black text-2xl text-slate-900">Your Test Line is Ready!</h4>
        <p class="text-xs text-slate-600">
          We have generated your 24-hour test playlist. Check your email inbox & spam folder for your M3U link and Xtream codes credentials.
        </p>
        <button data-close-modal class="btn-glow-cyan px-6 py-2.5 rounded-xl text-xs font-bold">
          Close Window
        </button>
      </div>

    </div>
  </div>

  <!-- CHECKOUT / ORDER MODAL (Compact No-Scroll Design) -->
  <div id="checkout-modal" data-backdrop="static" class="modal-overlay fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="modal-content w-full max-w-md rounded-2xl p-5 bg-white border border-cyan-200 shadow-2xl relative" onclick="event.stopPropagation();">
      
      <button type="button" data-close-modal class="absolute top-4 right-4 p-1.5 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 hover:bg-slate-200 transition-all cursor-pointer z-30" aria-label="Close">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>

      <div class="flex items-center gap-2.5 mb-3">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-cyan-500 to-blue-500 text-white flex items-center justify-center shadow-md shadow-cyan-500/20 flex-shrink-0">
          <i data-lucide="shopping-cart" class="w-4 h-4"></i>
        </div>
        <div>
          <h3 class="font-heading font-bold text-base text-slate-900 leading-tight">Complete Your Order</h3>
          <p class="text-[11px] text-slate-400">Instant Automated Line Delivery via Email &amp; WhatsApp</p>
        </div>
      </div>

      <!-- Checkout Form -->
      <form id="checkout-form" class="space-y-2.5">
        
        <!-- Location & Active Currency Auto-Detection Bar -->
        <?php
        $modalCurrencies = getCurrencies(true);
        $visitorCountry = detectVisitorCountry();
        $visitorCountryName = getCountryName($visitorCountry);
        ?>
        <input type="hidden" id="checkout-detected-country" name="country" value="<?= htmlspecialchars($visitorCountry) ?>">

        <!-- Compact Plan, Price & Currency Strip -->
        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
              <div class="font-bold text-slate-900 text-xs truncate" id="checkout-plan-name">12 Months VIP Access</div>
              <div class="text-[10px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                <span id="checkout-connections-count" class="text-cyan-700 font-semibold">1 Connection</span>
                <span>•</span>
                <span id="checkout-country-display" class="font-mono text-slate-600">📍 <?= htmlspecialchars($visitorCountry) ?></span>
              </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
              <select id="checkout-currency-select" class="px-2 py-1 rounded-lg bg-white border border-slate-300 text-slate-900 font-bold text-[11px] shadow-sm focus:outline-none focus:border-cyan-400 cursor-pointer">
                <?php foreach ($modalCurrencies as $mcur): ?>
                  <option value="<?= htmlspecialchars($mcur['code']) ?>">
                    <?= htmlspecialchars($mcur['code']) ?> (<?= htmlspecialchars($mcur['symbol']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>

              <div class="text-right">
                <span id="checkout-total-amount" class="font-heading font-black text-lg text-cyan-600 leading-none block">$74.99</span>
                <span id="checkout-plan-price" class="hidden">$74.99</span>
              </div>
            </div>
          </div>

          <!-- Dynamic Coupon Discount Row -->
          <div id="checkout-discount-row" class="hidden mt-1.5 pt-1.5 border-t border-slate-200/80 flex items-center justify-between text-[11px] text-emerald-600 font-medium">
            <span>Coupon Discount Applied:</span>
            <span id="checkout-discount-amount" class="font-bold">-$44.99</span>
          </div>
        </div>

        <!-- Inline Compact Promo Code Input -->
        <div class="flex items-center gap-1.5">
          <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
              <i data-lucide="tag" class="w-3 h-3"></i>
            </span>
            <input 
              id="coupon-code-input"
              type="text" 
              placeholder="Promo voucher (e.g. <?= FLASH_PROMO_CODE ?>)" 
              class="w-full pl-7 pr-2.5 py-1.5 rounded-lg bg-gray-50 border border-gray-300 text-slate-900 text-xs uppercase font-mono tracking-wider focus:outline-none focus:border-cyan-500 focus:bg-white placeholder:text-slate-400"
            >
          </div>
          <button id="apply-coupon-btn" type="button" class="px-3.5 py-1.5 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs transition-all shadow-sm cursor-pointer flex-shrink-0">
            Apply
          </button>
        </div>
        <div id="coupon-feedback" class="text-[10px]"></div>

        <!-- Full Name Field -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-700 mb-0.5">Your Full Name <span class="text-rose-500">*</span></label>
          <input required id="checkout-name" name="name" type="text" placeholder="e.g. John Doe" class="w-full px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-300 text-slate-900 text-xs focus:outline-none focus:border-cyan-400 placeholder:text-slate-400">
        </div>

        <!-- 2-Column Grid: Email & WhatsApp Side by Side -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-0.5">Activation Email <span class="text-rose-500">*</span></label>
            <input required id="checkout-email" name="email" type="email" placeholder="you@domain.com" class="w-full px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-300 text-slate-900 text-xs focus:outline-none focus:border-cyan-400 placeholder:text-slate-400">
          </div>

          <div>
            <label class="block text-[11px] font-semibold text-slate-700 mb-0.5">WhatsApp Number <span class="text-rose-500">*</span></label>
            <input required id="checkout-whatsapp" name="whatsapp" type="tel" placeholder="+1 555 019 2831" class="w-full px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-300 text-slate-900 text-xs focus:outline-none focus:border-cyan-400 placeholder:text-slate-400">
          </div>
        </div>

        <!-- Payment Method Selection (Compact Pills) -->
        <div>
          <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Method</label>
          <div class="grid grid-cols-3 gap-1.5">
            <!-- Credit Card / Debit Card (Active) -->
            <label class="p-2 rounded-xl bg-cyan-50/70 border-2 border-cyan-500 flex items-center justify-center gap-1.5 text-center cursor-pointer shadow-sm transition-all">
              <input type="radio" name="payment-method" value="Credit Card" checked class="accent-cyan-600 w-3 h-3">
              <span class="text-[11px] font-bold text-slate-900">Card</span>
            </label>

            <!-- PayPal (Coming Soon) -->
            <div class="p-2 rounded-xl bg-gray-50/80 border border-gray-200 flex items-center justify-center text-center opacity-60 cursor-not-allowed relative">
              <span class="text-[8px] font-mono font-bold text-amber-700 bg-amber-100 border border-amber-200 px-1 rounded absolute -top-1.5 left-1/2 -translate-x-1/2 whitespace-nowrap">Soon</span>
              <span class="text-[11px] font-bold text-slate-600">PayPal</span>
            </div>

            <!-- Crypto (Coming Soon) -->
            <div class="p-2 rounded-xl bg-gray-50/80 border border-gray-200 flex items-center justify-center text-center opacity-60 cursor-not-allowed relative">
              <span class="text-[8px] font-mono font-bold text-amber-700 bg-amber-100 border border-amber-200 px-1 rounded absolute -top-1.5 left-1/2 -translate-x-1/2 whitespace-nowrap">Soon</span>
              <span class="text-[11px] font-bold text-slate-600">Crypto</span>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-glow-cyan w-full py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-950 shadow-md cursor-pointer mt-1">
          Complete Secure Order
        </button>

        <!-- Secondary WhatsApp Order Link -->
        <button id="whatsapp-order-btn" type="button" class="w-full py-1 text-center text-emerald-600 hover:text-emerald-700 transition-all text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer">
          <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
          <span>Or Order Directly via WhatsApp Support &rarr;</span>
        </button>

        <p class="text-[9px] text-center text-slate-400">
          🔒 256-bit SSL encrypted • 7-Day Money Back Guarantee included
        </p>
      </form>

      <!-- Checkout Success State -->
      <div id="checkout-success-box" class="hidden text-center py-6 space-y-4">
        <div class="w-14 h-14 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto text-2xl border border-emerald-200">
          ✓
        </div>
        <h4 class="font-heading font-black text-2xl text-slate-900">Order Successfully Created!</h4>
        <div id="checkout-success-details" class="text-xs text-slate-600 text-left p-4 rounded-xl bg-gray-50 border border-gray-200">
          <!-- Populated by JS -->
        </div>
        <button data-close-modal class="btn-glow-cyan px-6 py-2.5 rounded-xl text-xs font-bold">
          Close Window
        </button>
      </div>

    </div>
  </div>

  <!-- FLOATING LIVE PURCHASE SOCIAL PROOF TOAST -->
  <div id="social-proof-toast" class="fixed bottom-6 left-6 z-40 transform translate-y-24 opacity-0 transition-all duration-500 max-w-sm pointer-events-none sm:pointer-events-auto">
    <div class="bg-white p-3.5 rounded-2xl border border-cyan-200 shadow-xl flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-500 flex items-center justify-center flex-shrink-0">
        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
      </div>
      <div>
        <p id="toast-customer-text" class="text-xs text-slate-700 leading-tight">
          Someone from <span class="font-bold text-slate-900">Sydney, Australia</span> just ordered <span class="text-cyan-700 font-bold">6 Months Full Access</span>
        </p>
        <p id="toast-time-text" class="text-[10px] text-slate-400 mt-1 font-medium">Just now • Verified Purchase</p>
      </div>
    </div>
  </div>

  <!-- FLOATING WHATSAPP & LIVE CHAT WIDGET -->
  <div class="fixed bottom-6 right-6 z-40 flex flex-col items-end gap-3">
    
    <!-- Chat Drawer Simulation Box -->
    <div id="floating-chat-box" class="w-80 rounded-2xl bg-white border border-gray-200 shadow-xl hidden overflow-hidden transition-all">
      <div class="p-4 bg-gradient-to-r from-cyan-500 to-blue-600 text-white flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center font-bold text-xs">
            AL
          </div>
          <div>
            <h4 class="font-bold text-xs">Alex - LiveTVMarket VIP</h4>
            <p class="text-[10px] text-cyan-100 flex items-center gap-1">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Online Now (Average reply: 2m)
            </p>
          </div>
        </div>
        <button id="close-chat-box" class="text-white/80 hover:text-white">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>

      <div class="p-4 space-y-3 bg-gray-50 text-xs">
        <div class="p-3 rounded-xl bg-gray-100 text-slate-700 rounded-tl-none">
          👋 Hello! Welcome to <?= SITE_NAME ?>. Do you need help choosing a plan or want a free 24-hour trial to test on your device?
        </div>
        <div class="space-y-1.5 pt-2">
          <?php if (TRIAL_IS_ACTIVE): ?>
          <button data-open-trial class="w-full py-2 px-3 text-left rounded-lg bg-white border border-gray-200 text-cyan-600 hover:bg-gray-50 text-[11px] font-semibold transition-colors">
            ⚡ <?= (TRIAL_MODE === 'paid') ? ('Request a ' . htmlspecialchars(TRIAL_DURATION_HOURS) . 'h Test Line') : 'Request a Free 24h Test Line' ?>
          </button>
          <?php endif; ?>
          <a href="/shop" class="block w-full py-2 px-3 text-left rounded-lg bg-white border border-gray-200 text-slate-600 hover:bg-gray-50 text-[11px] font-semibold transition-colors">
            🏷️ View Special <?= FLASH_PROMO_DISCOUNT ?> Discount Plans
          </a>
          <a href="<?= WHATSAPP_LINK ?>" target="_blank" class="block w-full py-2 px-3 text-center rounded-lg bg-emerald-500 text-white hover:bg-emerald-600 text-[11px] font-bold transition-colors">
            Chat on Official WhatsApp Desk →
          </a>
        </div>
      </div>
    </div>

    <!-- Floating Chat Trigger Button -->
    <button id="floating-chat-btn" aria-label="Open Live Chat" class="btn-glow-cyan w-14 h-14 rounded-full flex items-center justify-center text-white shadow-2xl relative group">
      <i data-lucide="message-square" class="w-6 h-6"></i>
      <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white"></span>
    </button>
  </div>