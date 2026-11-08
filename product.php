<?php
/**
 * LIVETVMARKET - Digital Service Product Details Page
 * Inspired by modern subscription marketplaces (clean white Tailwind theme).
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/store.php';

// If store is disabled globally, redirect to shop
if (!isStoreEnabled()) {
    header('Location: /shop');
    exit;
}

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: /shop');
    exit;
}

$product = getStoreProductBySlug($slug);
if (!$product) {
    header('Location: /shop');
    exit;
}

$variations = $product['variations'] ?? [];
$isOutOfStock = ($product['status'] === 'out_of_stock');

// Find default variation (first in-stock variation or first variation)
$defaultVar = null;
foreach ($variations as $v) {
    if ($v['status'] === 'in_stock') {
        $defaultVar = $v;
        break;
    }
}
if (!$defaultVar && !empty($variations)) {
    $defaultVar = $variations[0];
}

$activePrice = $defaultVar ? (float)$defaultVar['price'] : (float)$product['starting_price'];
$activeOrig = $defaultVar && !empty($defaultVar['original_price']) ? (float)$defaultVar['original_price'] : 0;
$activePerMonth = $defaultVar['per_month_display'] ?? '';

$pageTitle = htmlspecialchars($product['name']) . " — Instant Digital Delivery | " . SITE_NAME;
$metaDescription = !empty($product['short_desc']) ? htmlspecialchars(strip_tags($product['short_desc'])) : ("Get " . htmlspecialchars($product['name']) . " with instant delivery and 100% replacement warranty.");

// Get related products in same category
$relatedProducts = [];
if (!empty($product['category_id'])) {
    $allInCat = getStoreProducts((int)$product['category_id'], false, 'in_stock');
    foreach ($allInCat as $rp) {
        if ($rp['id'] != $product['id']) {
            $relatedProducts[] = $rp;
        }
        if (count($relatedProducts) >= 4) break;
    }
}
if (empty($relatedProducts)) {
    $relatedProducts = array_slice(getStoreProducts(null, true, 'in_stock'), 0, 4);
}

$pageDesc = $metaDescription;
$pageCanonical = SITE_URL . '/product/' . urlencode($product['slug']);
$activePage = 'shop';

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/top-banner.php';
include __DIR__ . '/includes/navbar.php';
?>

  <main class="min-h-screen pt-28 pb-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Breadcrumbs -->
      <nav class="flex items-center gap-2 text-xs text-slate-500 mb-8 overflow-x-auto whitespace-nowrap">
        <a href="/" class="hover:text-cyan-600 transition-colors">Home</a>
        <span>/</span>
        <a href="/shop" class="hover:text-cyan-600 transition-colors">Shop</a>
        <span>/</span>
        <span class="text-slate-400"><?= htmlspecialchars($product['category_name'] ?: 'Digital Services') ?></span>
        <span>/</span>
        <span class="text-slate-900 font-bold truncate max-w-xs"><?= htmlspecialchars($product['name']) ?></span>
      </nav>

      <!-- PRODUCT HERO TWO-COLUMN CARD -->
      <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10 mb-16">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
          
          <!-- LEFT COLUMN: Product Image & Highlights -->
          <div class="lg:col-span-5">
            <div class="relative bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden shadow-inner flex items-center justify-center p-6 min-h-[300px] sm:min-h-[380px]">
              
              <?php if (!empty($product['badge'])): ?>
              <span class="absolute top-4 left-4 z-10 px-3 py-1 rounded-full bg-cyan-600 text-white font-mono font-bold text-[11px] shadow-md shadow-cyan-600/30">
                <?= htmlspecialchars($product['badge']) ?>
              </span>
              <?php endif; ?>

              <?php if ($isOutOfStock): ?>
              <span class="absolute top-4 right-4 z-10 px-3 py-1 rounded-full bg-rose-600 text-white font-mono font-bold text-[11px] shadow-md">
                Out of Stock
              </span>
              <?php else: ?>
              <span class="absolute top-4 right-4 z-10 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-[11px]">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Instant Delivery
              </span>
              <?php endif; ?>

              <?php 
                $imgSrc = !empty($product['image']) ? htmlspecialchars($product['image']) : 'assets/images/placeholder-product.png';
              ?>
              <img 
                src="<?= $imgSrc ?>" 
                alt="<?= htmlspecialchars($product['name']) ?>" 
                class="w-full max-h-[300px] object-contain rounded-xl hover:scale-105 transition-transform duration-300"
                loading="eager"
              >
            </div>

            <!-- Trust Mini Grid -->
            <div class="grid grid-cols-3 gap-3 mt-4 text-center">
              <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <i data-lucide="zap" class="w-4 h-4 text-cyan-600 mx-auto mb-1"></i>
                <div class="text-[11px] font-bold text-slate-900">Instant Delivery</div>
                <div class="text-[10px] text-slate-500">5-15 mins via WhatsApp</div>
              </div>
              <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 mx-auto mb-1"></i>
                <div class="text-[11px] font-bold text-slate-900">100% Warranty</div>
                <div class="text-[10px] text-slate-500">Full term replacement</div>
              </div>
              <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <i data-lucide="globe" class="w-4 h-4 text-indigo-600 mx-auto mb-1"></i>
                <div class="text-[11px] font-bold text-slate-900">Global Access</div>
                <div class="text-[10px] text-slate-500">No VPN needed</div>
              </div>
            </div>
          </div>

          <!-- RIGHT COLUMN: Details & Variation Selector -->
          <div class="lg:col-span-7 flex flex-col justify-between">
            <div>
              
              <!-- Category & Reviews -->
              <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-2.5 py-1 rounded-md border border-cyan-200">
                  <?= htmlspecialchars($product['category_name'] ?: 'Digital Subscription') ?>
                </span>
                
                <div class="flex items-center gap-1.5 text-xs text-slate-600">
                  <div class="flex text-amber-400">
                    <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                    <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                    <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                    <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                    <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                  </div>
                  <span class="font-bold text-slate-800">4.9/5</span>
                  <span class="text-slate-400">(340+ verified buyers)</span>
                </div>
              </div>

              <!-- Title -->
              <h1 class="font-heading font-black text-2xl sm:text-3xl lg:text-4xl text-slate-900 leading-tight">
                <?= htmlspecialchars($product['name']) ?>
              </h1>

              <!-- Short Description -->
              <?php if (!empty($product['short_desc'])): ?>
              <p class="text-sm text-slate-600 mt-3 leading-relaxed">
                <?= nl2br(htmlspecialchars($product['short_desc'])) ?>
              </p>
              <?php endif; ?>

              <!-- DYNAMIC PRICE DISPLAY STRIP -->
              <div class="mt-6 p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                  <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Total Package Price</div>
                  <div class="flex items-baseline gap-2">
                    <span id="display-price" class="font-heading font-black text-3xl sm:text-4xl text-cyan-600">
                      $<?= number_format($activePrice, 2) ?>
                    </span>
                    <span id="display-orig-price" class="text-sm text-slate-400 line-through <?= ($activeOrig > $activePrice) ? '' : 'hidden' ?>">
                      $<?= number_format($activeOrig, 2) ?>
                    </span>
                    <span id="display-savings" class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200 <?= ($activeOrig > $activePrice) ? '' : 'hidden' ?>">
                      Save <?= round((($activeOrig - $activePrice) / max(1, $activeOrig)) * 100) ?>%
                    </span>
                  </div>
                </div>

                <div class="text-right">
                  <span id="display-status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold <?= $isOutOfStock ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                    <span class="w-2 h-2 rounded-full <?= $isOutOfStock ? 'bg-rose-500' : 'bg-emerald-500 animate-pulse' ?>"></span>
                    <span id="display-status-text"><?= $isOutOfStock ? 'Out of Stock' : 'In Stock • Ready to Activate' ?></span>
                  </span>
                  <div id="display-per-month" class="text-xs text-slate-500 font-medium mt-1">
                    <?= htmlspecialchars($activePerMonth) ?>
                  </div>
                </div>
              </div>

              <!-- VARIATIONS SELECTOR (Pills / Cards) -->
              <?php if (!empty($variations)): ?>
              <div class="mt-6">
                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">
                  Select Package Duration / Plan:
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="variations-grid">
                  <?php foreach ($variations as $idx => $v): 
                    $vPrice = (float)$v['price'];
                    $vOrig = (float)$v['original_price'];
                    $vOutOfStock = ($v['status'] === 'out_of_stock' || $isOutOfStock);
                    $isSelected = ($defaultVar && $defaultVar['id'] == $v['id']);
                  ?>
                  <button 
                    type="button" 
                    class="var-pill text-left p-3.5 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between <?= $isSelected ? 'border-cyan-500 bg-cyan-50/40 text-cyan-950 font-bold shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700' ?> <?= $vOutOfStock ? 'opacity-60 bg-slate-50 cursor-not-allowed border-dashed' : '' ?>"
                    data-var-id="<?= (int)$v['id'] ?>"
                    data-var-name="<?= htmlspecialchars($v['name']) ?>"
                    data-price="<?= number_format($vPrice, 2, '.', '') ?>"
                    data-orig-price="<?= number_format($vOrig, 2, '.', '') ?>"
                    data-per-month="<?= htmlspecialchars($v['per_month_display'] ?? '') ?>"
                    data-status="<?= $v['status'] ?>"
                    <?= $vOutOfStock ? 'disabled' : '' ?>
                  >
                    <div>
                      <div class="text-xs font-bold"><?= htmlspecialchars($v['name']) ?></div>
                      <div class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($v['duration_label']) ?></div>
                    </div>

                    <div class="text-right">
                      <div class="font-heading font-black text-sm text-slate-900">$<?= number_format($vPrice, 2) ?></div>
                      <?php if ($vOutOfStock): ?>
                        <span class="text-[10px] text-rose-600 font-bold">Sold Out</span>
                      <?php elseif ($vOrig > $vPrice): ?>
                        <span class="text-[10px] text-slate-400 line-through">$<?= number_format($vOrig, 2) ?></span>
                      <?php endif; ?>
                    </div>
                  </button>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endif; ?>

              <!-- Key Features Highlights -->
              <?php if (!empty($product['features'])): 
                $feats = array_filter(array_map('trim', explode("\n", $product['features'])));
              ?>
              <div class="mt-6 border-t border-slate-100 pt-5">
                <div class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2.5">What is Included:</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-600">
                  <?php foreach ($feats as $f): ?>
                  <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
                    <span><?= htmlspecialchars($f) ?></span>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endif; ?>

            </div>

            <!-- ACTION BUTTONS -->
            <div class="mt-8 border-t border-slate-100 pt-6">
              <div class="flex flex-col sm:flex-row gap-3">
                <button 
                  id="store-order-btn" 
                  class="btn-glow-cyan shimmer-active flex-1 py-4 px-6 rounded-xl font-bold text-sm uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-cyan-500/25 cursor-pointer <?= $isOutOfStock ? 'opacity-50 pointer-events-none' : '' ?>"
                >
                  <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                  <span id="order-btn-text">Order Now ($<?= number_format($activePrice, 2) ?>)</span>
                </button>

                <?php 
                  $waNum = getSetting('whatsapp_number', '+447456000000');
                  $waNumClean = preg_replace('/[^0-9]/', '', $waNum);
                  $initialWaMsg = urlencode("Hello LIVETVMARKET! I would like to order {$product['name']} - " . ($defaultVar['name'] ?? '') . " ($" . number_format($activePrice, 2) . "). Please guide me with setup!");
                ?>
                <a 
                  id="store-whatsapp-btn"
                  href="https://wa.me/<?= $waNumClean ?>?text=<?= $initialWaMsg ?>" 
                  target="_blank" 
                  rel="noopener"
                  class="py-4 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm flex items-center justify-center gap-2 shadow-md shadow-emerald-600/20 transition-all cursor-pointer"
                >
                  <i data-lucide="message-circle" class="w-4 h-4"></i>
                  <span>Buy via WhatsApp</span>
                </a>
              </div>

              <!-- Payment icons strip -->
              <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-[11px] text-slate-500">
                <div class="flex items-center gap-2">
                  <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                  <span>256-Bit SSL Encrypted Checkout</span>
                </div>
                <div class="flex items-center gap-2 font-semibold text-slate-600">
                  <span>Visa</span> • <span>Mastercard</span> • <span>PayPal</span> • <span>Apple Pay</span> • <span>Crypto</span>
                </div>
              </div>
            </div>

          </div>

        </div>
      </div>

      <!-- LOWER DETAILS TABS / CONTENT -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-16">
        
        <div class="lg:col-span-8 space-y-8">
          <!-- Full Description -->
          <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
            <h2 class="font-heading font-black text-xl text-slate-900 mb-4 flex items-center gap-2">
              <i data-lucide="info" class="w-5 h-5 text-cyan-600"></i>
              <span>Product Description & Instructions</span>
            </h2>
            <div class="prose prose-slate max-w-none text-xs sm:text-sm text-slate-600 leading-relaxed space-y-3">
              <?= $product['description'] ?: ('<p>' . nl2br(htmlspecialchars($product['short_desc'])) . '</p>') ?>
            </div>
          </div>

          <!-- How It Works Step-by-Step -->
          <div class="bg-slate-50 rounded-3xl border border-slate-200 p-6 sm:p-8">
            <h3 class="font-heading font-bold text-lg text-slate-900 mb-6 flex items-center gap-2">
              <i data-lucide="workflow" class="w-5 h-5 text-indigo-600"></i>
              <span>How Digital Delivery Works</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs text-slate-600">
              <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative">
                <div class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-700 font-mono font-black text-xs flex items-center justify-center mb-3">1</div>
                <h4 class="font-bold text-slate-900 text-sm mb-1">Select & Checkout</h4>
                <p>Choose your duration and checkout securely using your Credit/Debit Card, PayPal, or Crypto.</p>
              </div>

              <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 font-mono font-black text-xs flex items-center justify-center mb-3">2</div>
                <h4 class="font-bold text-slate-900 text-sm mb-1">Automated Processing</h4>
                <p>Your order is instantly generated and your subscription credentials/invitation are prepared.</p>
              </div>

              <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative">
                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 font-mono font-black text-xs flex items-center justify-center mb-3">3</div>
                <h4 class="font-bold text-slate-900 text-sm mb-1">WhatsApp & Email Delivery</h4>
                <p>Receive your dedicated profile login, PIN, and setup steps within 5 to 15 minutes.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar FAQs & Warranty Card -->
        <div class="lg:col-span-4 space-y-6">
          <div class="bg-slate-50 rounded-3xl border border-slate-200 p-6">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-3">
              <i data-lucide="shield-alert" class="w-5 h-5"></i>
            </div>
            <h4 class="font-bold text-slate-900 text-sm mb-1">100% Replacement Warranty</h4>
            <p class="text-xs text-slate-600 leading-relaxed">
              Every digital subscription from <?= SITE_NAME ?> is backed by our full-term warranty. If your access experiences any disruption, our 24/7 WhatsApp desk provides an immediate replacement profile.
            </p>
          </div>

          <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
            <h4 class="font-bold text-slate-900 text-sm mb-3">Frequently Asked Questions</h4>
            <div class="space-y-3 text-xs text-slate-600">
              <div>
                <strong class="text-slate-800 block mb-0.5">Do I need a VPN to watch?</strong>
                <span>No, our subscriptions work natively worldwide on any standard home WiFi or cellular network.</span>
              </div>
              <div class="border-t border-slate-100 pt-2.5">
                <strong class="text-slate-800 block mb-0.5">Can I use it on multiple devices?</strong>
                <span>You can log in on multiple personal devices, though active simultaneous playback is limited to 1 screen per profile.</span>
              </div>
              <div class="border-t border-slate-100 pt-2.5">
                <strong class="text-slate-800 block mb-0.5">How fast is delivery?</strong>
                <span>Most orders are processed within 5 to 15 minutes after payment confirmation.</span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- RELATED DIGITAL SERVICES -->
      <?php if (!empty($relatedProducts)): ?>
      <div class="mt-16 pt-10 border-t border-slate-200">
        <div class="flex items-center justify-between mb-8">
          <div>
            <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-2.5 py-1 rounded-md border border-cyan-200">
              Explore More
            </span>
            <h3 class="font-heading font-black text-2xl text-slate-900 mt-2">
              Related Digital Subscriptions
            </h3>
          </div>
          <a href="/shop" class="text-xs font-bold text-cyan-600 hover:text-cyan-700 flex items-center gap-1">
            <span>Browse All</span> <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <?php foreach ($relatedProducts as $rp): ?>
          <div class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col justify-between shadow-sm hover:shadow-md hover:border-cyan-300 transition-all group">
            <div>
              <div class="h-36 bg-slate-50 rounded-xl flex items-center justify-center p-3 mb-4 overflow-hidden">
                <img src="<?= htmlspecialchars($rp['image'] ?: 'assets/images/placeholder-product.png') ?>" alt="<?= htmlspecialchars($rp['name']) ?>" class="max-h-full object-contain group-hover:scale-105 transition-transform">
              </div>
              <span class="text-[10px] font-mono font-bold text-cyan-700 uppercase bg-cyan-50 px-2 py-0.5 rounded border border-cyan-200">
                <?= htmlspecialchars($rp['category_name'] ?: 'Streaming') ?>
              </span>
              <h4 class="font-heading font-bold text-sm text-slate-900 mt-2 line-clamp-1">
                <?= htmlspecialchars($rp['name']) ?>
              </h4>
              <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">
                <?= htmlspecialchars($rp['short_desc']) ?>
              </p>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
              <div>
                <span class="text-[10px] text-slate-400 block">Starts from</span>
                <span class="font-heading font-black text-base text-cyan-600">$<?= number_format($rp['min_price'], 2) ?></span>
              </div>
              <a href="/product/<?= urlencode($rp['slug']) ?>" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-cyan-50 hover:text-cyan-700 text-slate-800 text-xs font-bold transition-colors">
                View Plan &rarr;
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </main>

  <!-- STORE ORDER POPUP MODAL (Compact single-screen design) -->
  <div id="store-order-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-5 border border-slate-200 shadow-2xl relative animate-scaleUp">
      
      <!-- Close Button -->
      <button id="close-store-modal-btn" class="absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>

      <!-- Modal Header -->
      <div class="mb-4">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold">
            <i data-lucide="sparkles" class="w-4 h-4"></i>
          </div>
          <div>
            <h3 class="font-heading font-black text-lg text-slate-900">Digital Order Checkout</h3>
            <p class="text-xs text-slate-500">Instant activation & WhatsApp credential delivery</p>
          </div>
        </div>
      </div>

      <!-- Order Summary Strip -->
      <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 mb-4 flex items-center justify-between text-xs">
        <div>
          <div class="font-bold text-slate-900" id="modal-product-name"><?= htmlspecialchars($product['name']) ?></div>
          <div class="text-[11px] text-slate-500" id="modal-var-name"><?= htmlspecialchars($defaultVar['name'] ?? '') ?></div>
        </div>
        <div class="text-right">
          <div class="font-heading font-black text-base text-cyan-600" id="modal-price">$<?= number_format($activePrice, 2) ?></div>
          <div class="text-[10px] text-emerald-600 font-bold">100% Warranty</div>
        </div>
      </div>

      <!-- Checkout Form -->
      <form id="store-checkout-form" class="space-y-3.5">
        <input type="hidden" name="product_id" id="form-product-id" value="<?= (int)$product['id'] ?>">
        <input type="hidden" name="variation_id" id="form-var-id" value="<?= (int)($defaultVar['id'] ?? 0) ?>">
        <input type="hidden" name="amount" id="form-amount" value="<?= number_format($activePrice, 2, '.', '') ?>">

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
          <input type="text" name="customer_name" required placeholder="John Doe" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 bg-white">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Delivery Email</label>
            <input type="email" name="email" required placeholder="john@gmail.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 bg-white">
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">WhatsApp Number</label>
            <input type="tel" name="whatsapp" required placeholder="+1 555 019 2831" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 bg-white">
          </div>
        </div>

        <!-- Payment Method Selection -->
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Payment Method</label>
          <div class="grid grid-cols-3 gap-2">
            <!-- Credit Card / Debit Card (Active) -->
            <label class="p-2.5 rounded-xl border-2 border-cyan-500 bg-cyan-50/60 text-center cursor-pointer transition-all flex flex-col items-center justify-center shadow-sm">
              <input type="radio" name="payment_channel" value="Credit Card" checked class="hidden">
              <span class="text-xs font-bold text-slate-900">Card</span>
              <span class="text-[10px] text-cyan-700 font-semibold">Instant</span>
            </label>

            <!-- PayPal (Coming Soon) -->
            <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-center opacity-60 cursor-not-allowed relative flex flex-col items-center justify-center">
              <span class="text-[8px] font-mono font-bold text-amber-700 bg-amber-100 border border-amber-200 px-1.5 rounded absolute -top-1.5 left-1/2 -translate-x-1/2 whitespace-nowrap">Soon</span>
              <span class="text-xs font-bold text-slate-600">PayPal</span>
              <span class="text-[10px] text-slate-400">Fast</span>
            </div>

            <!-- Crypto (Coming Soon) -->
            <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-center opacity-60 cursor-not-allowed relative flex flex-col items-center justify-center">
              <span class="text-[8px] font-mono font-bold text-amber-700 bg-amber-100 border border-amber-200 px-1.5 rounded absolute -top-1.5 left-1/2 -translate-x-1/2 whitespace-nowrap">Soon</span>
              <span class="text-xs font-bold text-slate-600">Crypto</span>
              <span class="text-[10px] text-slate-400">Fast</span>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="submit-store-order-btn" class="w-full py-3.5 rounded-xl btn-glow-cyan text-slate-950 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer shadow-lg shadow-cyan-500/25 mt-2">
          <span>Proceed to Payment</span>
          <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>

        <p class="text-[10px] text-slate-400 text-center leading-normal">
          🔒 Secure 256-bit encrypted checkout. Credentials delivered via WhatsApp & Email upon payment.
        </p>
      </form>

    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const varPills = document.querySelectorAll('.var-pill');
      const displayPrice = document.getElementById('display-price');
      const displayOrig = document.getElementById('display-orig-price');
      const displaySavings = document.getElementById('display-savings');
      const displayPerMonth = document.getElementById('display-per-month');
      const displayStatusBadge = document.getElementById('display-status-badge');
      const displayStatusText = document.getElementById('display-status-text');
      const orderBtnText = document.getElementById('order-btn-text');
      const waBtn = document.getElementById('store-whatsapp-btn');
      
      const modal = document.getElementById('store-order-modal');
      const openModalBtn = document.getElementById('store-order-btn');
      const closeModalBtn = document.getElementById('close-store-modal-btn');
      const modalProdName = document.getElementById('modal-product-name');
      const modalVarName = document.getElementById('modal-var-name');
      const modalPrice = document.getElementById('modal-price');
      const formVarId = document.getElementById('form-var-id');
      const formAmount = document.getElementById('form-amount');

      let currentVar = {
        id: <?= (int)($defaultVar['id'] ?? 0) ?>,
        name: <?= json_encode($defaultVar['name'] ?? '') ?>,
        price: '<?= number_format($activePrice, 2, '.', '') ?>'
      };

      const waPhone = '<?= $waNumClean ?>';
      const prodName = <?= json_encode($product['name']) ?>;

      // Click on Variation Pill
      varPills.forEach(pill => {
        pill.addEventListener('click', function() {
          if (this.disabled) return;

          varPills.forEach(p => {
            p.classList.remove('border-cyan-500', 'bg-cyan-50/40', 'text-cyan-950', 'font-bold', 'shadow-sm');
            p.classList.add('border-slate-200', 'bg-white', 'text-slate-700');
          });
          this.classList.remove('border-slate-200', 'bg-white', 'text-slate-700');
          this.classList.add('border-cyan-500', 'bg-cyan-50/40', 'text-cyan-950', 'font-bold', 'shadow-sm');

          const price = parseFloat(this.getAttribute('data-price') || 0);
          const orig = parseFloat(this.getAttribute('data-orig-price') || 0);
          const perMonth = this.getAttribute('data-per-month') || '';
          const varId = this.getAttribute('data-var-id');
          const varName = this.getAttribute('data-var-name');

          currentVar = { id: varId, name: varName, price: price.toFixed(2) };

          displayPrice.textContent = '$' + price.toFixed(2);
          orderBtnText.textContent = `Order Now ($${price.toFixed(2)})`;

          if (orig > price) {
            displayOrig.textContent = '$' + orig.toFixed(2);
            displayOrig.classList.remove('hidden');
            const pct = Math.round(((orig - price) / orig) * 100);
            displaySavings.textContent = `Save ${pct}%`;
            displaySavings.classList.remove('hidden');
          } else {
            displayOrig.classList.add('hidden');
            displaySavings.classList.add('hidden');
          }

          displayPerMonth.textContent = perMonth;

          // Update WhatsApp Link
          const msg = encodeURIComponent(`Hello LIVETVMARKET! I would like to order ${prodName} - ${varName} ($${price.toFixed(2)}). Please guide me with setup!`);
          waBtn.href = `https://wa.me/${waPhone}?text=${msg}`;
        });
      });

      // Open Modal
      if (openModalBtn) {
        openModalBtn.addEventListener('click', function() {
          modalVarName.textContent = currentVar.name;
          modalPrice.textContent = '$' + currentVar.price;
          formVarId.value = currentVar.id;
          formAmount.value = currentVar.price;
          modal.classList.remove('hidden');
        });
      }

      // Close Modal
      if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
          modal.classList.add('hidden');
        });
      }
      modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.classList.add('hidden');
      });

      // Form submission
      const checkoutForm = document.getElementById('store-checkout-form');
      const submitBtn = document.getElementById('submit-store-order-btn');

      checkoutForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full mr-2"></span> Processing...`;

        const formData = new FormData(checkoutForm);

        try {
          const resp = await fetch('/api/submit_store_order.php', {
            method: 'POST',
            body: formData
          });
          const result = await resp.json();

          if (result.success) {
            if (result.redirect_url) {
              window.location.href = result.redirect_url;
            } else {
              window.location.href = `/success.php?order_id=${result.order_id}&type=store`;
            }
          } else {
            alert(result.message || 'Error processing order. Please contact WhatsApp support.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<span>Proceed to Payment</span> <i data-lucide="arrow-right" class="w-4 h-4"></i>`;
            if (window.lucide) lucide.createIcons();
          }
        } catch (err) {
          console.error(err);
          alert('Network or server error. Please try again or chat on WhatsApp.');
          submitBtn.disabled = false;
          submitBtn.innerHTML = `<span>Proceed to Payment</span> <i data-lucide="arrow-right" class="w-4 h-4"></i>`;
          if (window.lucide) lucide.createIcons();
        }
      });
    });
  </script>

<?php
include __DIR__ . '/includes/modals.php';
include __DIR__ . '/includes/footer.php';
?>

