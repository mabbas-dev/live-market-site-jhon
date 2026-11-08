<?php
/**
 * LIVETVMARKET - Admin Site Settings & Customization
 * Controls all text, numbers, banners, and contacts dynamically in MySQL DB 'iptv-website'.
 */
$adminTitle = 'Site Customization & Settings';
$adminPage = 'settings';

require_once __DIR__ . '/header.php';

$successMsg = '';
$errorMsg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'site_name',
        'site_domain',
        'site_url',
        'legal_banner_text',
        'promo_discount',
        'promo_code',
        'hero_headline',
        'hero_subtitle',
        'rating_score',
        'subscriber_count',
        'total_channels',
        'total_vod',
        'active_clusters',
        'network_uptime',
        'contact_email',
        'contact_whatsapp',
        'whatsapp_url',
        'contact_telegram',
        'trial_mode',
        'trial_price',
        'trial_duration_hours',
        'meta_title',
        'meta_description'
    ];

    $updatedCount = 0;
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $val = trim($_POST[$field]);
            if (updateSetting($field, $val)) {
                $updatedCount++;
            }
        }
    }

    // Synchronize flash promo code to coupons database table
    if (!empty($_POST['promo_code'])) {
        require_once __DIR__ . '/../config/coupons.php';
        $pCode = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', trim($_POST['promo_code'])));
        $pDisc = (int)preg_replace('/[^0-9]/', '', trim($_POST['promo_discount'] ?? '60')) ?: 60;
        
        $chk = $pdo->prepare("SELECT `id` FROM `coupons` WHERE `code` = ?");
        $chk->execute([$pCode]);
        if ($chk->rowCount() > 0) {
            $pdo->prepare("UPDATE `coupons` SET `discount_percent` = ?, `is_active` = 1 WHERE `code` = ?")->execute([$pDisc, $pCode]);
        } else {
            $pdo->prepare("INSERT INTO `coupons` (`code`, `discount_percent`, `description`, `is_active`) VALUES (?, ?, 'Storefront Flash Promo Voucher', 1)")->execute([$pCode, $pDisc]);
        }
    }

    $successMsg = "All settings have been successfully updated! Changes are immediately live across the entire storefront.";
}

// Fetch current values
$currentSettings = [];
try {
    $stmt = $pdo->query("SELECT `key_name`, `value` FROM `site_settings`");
    while ($row = $stmt->fetch()) {
        $currentSettings[$row['key_name']] = $row['value'];
    }
} catch (Exception $e) {
    $errorMsg = "Database read error: " . $e->getMessage();
}

function sVal($key, $default = '') {
    global $currentSettings;
    return $currentSettings[$key] ?? $default;
}
?>

<div class="max-w-5xl">
  
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-white">Website Customization Panel</h1>
      <p class="text-xs text-slate-400 mt-1">Manage brand identity, top announcement bar, marketing promo codes, streaming stats, and support channels.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="../index.php" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-300 font-bold text-xs border border-slate-700 flex items-center gap-1.5 transition-colors">
        <i data-lucide="eye" class="w-4 h-4"></i>
        <span>Preview Live Site</span>
      </a>
    </div>
  </div>

  <?php if (!empty($successMsg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-3 shadow-lg shadow-emerald-950/20">
      <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
      </div>
      <div class="flex-1 font-medium"><?= htmlspecialchars($successMsg) ?></div>
    </div>
  <?php endif; ?>

  <?php if (!empty($errorMsg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs flex items-center gap-3">
      <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400"></i>
      <span><?= htmlspecialchars($errorMsg) ?></span>
    </div>
  <?php endif; ?>

  <form action="settings.php" method="POST" class="space-y-8">
    
    <!-- SECTION 1: BRAND IDENTITY -->
    <div class="glass-card rounded-2xl p-6 sm:p-7 border-slate-800">
      <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-800">
        <div class="w-8 h-8 rounded-lg bg-cyan-500/15 text-cyan-400 flex items-center justify-center">
          <i data-lucide="shield" class="w-4 h-4"></i>
        </div>
        <div>
          <h3 class="font-bold text-white text-sm">Brand & Official Identity</h3>
          <p class="text-[11px] text-slate-400">Controls website brand name, official verified domain, and portal URL.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Brand / Site Name</label>
          <input type="text" name="site_name" value="<?= htmlspecialchars(sVal('site_name', 'LIVETVMARKET')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Official Verified Domain</label>
          <input type="text" name="site_domain" value="<?= htmlspecialchars(sVal('site_domain', 'livetvmarket.com')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Official Portal Full URL</label>
          <input type="text" name="site_url" value="<?= htmlspecialchars(sVal('site_url', 'https://livetvmarket.com')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>
      </div>
    </div>

    <!-- SECTION 2: TOP BANNER & PROMO CODES -->
    <div class="glass-card rounded-2xl p-6 sm:p-7 border-slate-800">
      <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-800">
        <div class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center">
          <i data-lucide="award" class="w-4 h-4"></i>
        </div>
        <div>
          <h3 class="font-bold text-white text-sm">Top Announcement Bar & Legal Trust</h3>
          <p class="text-[11px] text-slate-400">Controls the statement in the top compliance bar, promo coupon code and discount percentage.</p>
        </div>
      </div>

      <div class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Top Bar Legal & Compliance Statement</label>
          <input type="text" name="legal_banner_text" value="<?= htmlspecialchars(sVal('legal_banner_text', 'All broadcasts & VOD legally syndicated via certified partners')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400">
          <p class="text-[10px] text-slate-500 mt-1">Displayed next to the emerald 100% AUTHORIZED & LEGAL badge on desktop.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Promo Discount Percentage (Display)</label>
            <input type="text" name="promo_discount" value="<?= htmlspecialchars(sVal('promo_discount', '60%')) ?>" required
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Flash Promo Coupon Code</label>
            <input type="text" name="promo_code" value="<?= htmlspecialchars(sVal('promo_code', 'MARKET60')) ?>" required
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono font-bold uppercase">
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION 3: HERO SECTION CONTENT -->
    <div class="glass-card rounded-2xl p-6 sm:p-7 border-slate-800">
      <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-800">
        <div class="w-8 h-8 rounded-lg bg-indigo-500/15 text-indigo-400 flex items-center justify-center">
          <i data-lucide="sparkles" class="w-4 h-4"></i>
        </div>
        <div>
          <h3 class="font-bold text-white text-sm">Hero Showcase & Trust Social Proof</h3>
          <p class="text-[11px] text-slate-400">Customize main headline text, subtitles, customer star rating score, and subscriber counters.</p>
        </div>
      </div>

      <div class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Hero Headline Lead Text</label>
          <input type="text" name="hero_headline" value="<?= htmlspecialchars(sVal('hero_headline', 'Stream 25,000+ Live Channels & 120,000+ Movies in')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Hero Subtitle Paragraph</label>
          <textarea name="hero_subtitle" rows="3" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 leading-relaxed"><?= htmlspecialchars(sVal('hero_subtitle', 'Experience unbuffered live sports, global entertainment, and cinema blockbusters with our proprietary Anti-Freeze™ 9.9 CDN cluster. Zero lag, zero ISP throttling, instant automated delivery.')) ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Rating Score Display</label>
            <input type="text" name="rating_score" value="<?= htmlspecialchars(sVal('rating_score', '4.9/5')) ?>" required
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Active Subscribers Counter</label>
            <input type="text" name="subscriber_count" value="<?= htmlspecialchars(sVal('subscriber_count', '12,480+')) ?>" required
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION 4: STREAMING CAPACITY & INFRASTRUCTURE STATS -->
    <div class="glass-card rounded-2xl p-6 sm:p-7 border-slate-800">
      <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-800">
        <div class="w-8 h-8 rounded-lg bg-blue-500/15 text-blue-400 flex items-center justify-center">
          <i data-lucide="server" class="w-4 h-4"></i>
        </div>
        <div>
          <h3 class="font-bold text-white text-sm">Streaming Catalog & Server Node Capacity</h3>
          <p class="text-[11px] text-slate-400">Controls channel counts, movie counts, active cluster nodes, and uptime guarantees.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Total Live Channels</label>
          <input type="text" name="total_channels" value="<?= htmlspecialchars(sVal('total_channels', '25,000+')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Total VOD Movies & Shows</label>
          <input type="text" name="total_vod" value="<?= htmlspecialchars(sVal('total_vod', '120,000+')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Global Cluster Nodes</label>
          <input type="text" name="active_clusters" value="<?= htmlspecialchars(sVal('active_clusters', '184')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">System Uptime Guarantee</label>
          <input type="text" name="network_uptime" value="<?= htmlspecialchars(sVal('network_uptime', '99.99%')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>
      </div>
    </div>

    <!-- SECTION: TRIAL SYSTEM SETTINGS (Free vs Paid Test Lines) -->
    <div class="glass-card rounded-2xl p-6 sm:p-7 border-slate-800">
      <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-800">
        <div class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center">
          <i data-lucide="zap" class="w-4 h-4"></i>
        </div>
        <div>
          <h3 class="font-bold text-white text-sm">Trial Configuration (Free vs Paid Test Lines)</h3>
          <p class="text-[11px] text-slate-400">Control whether trial test lines are 100% Free or Paid, and customize the test duration and price.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Trial Mode</label>
          <select name="trial_mode" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
            <option value="free" <?= (sVal('trial_mode', 'free') === 'free') ? 'selected' : '' ?>>🎁 100% Free 24h Trial</option>
            <option value="paid" <?= (sVal('trial_mode', 'free') === 'paid') ? 'selected' : '' ?>>💳 Paid Test Line ($X.XX)</option>
            <option value="disabled" <?= (sVal('trial_mode', 'free') === 'disabled') ? 'selected' : '' ?>>⛔ Disabled</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Paid Trial Price (USD $)</label>
          <input type="number" step="0.01" name="trial_price" value="<?= htmlspecialchars(sVal('trial_price', '1.99')) ?>" placeholder="1.99"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Trial Duration (Hours)</label>
          <input type="number" name="trial_duration_hours" value="<?= htmlspecialchars(sVal('trial_duration_hours', '24')) ?>" placeholder="24"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>
      </div>
    </div>

    <!-- SECTION 5: CONTACT & SUPPORT CHANNELS -->
    <div class="glass-card rounded-2xl p-6 sm:p-7 border-slate-800">
      <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-800">
        <div class="w-8 h-8 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center">
          <i data-lucide="message-square" class="w-4 h-4"></i>
        </div>
        <div>
          <h3 class="font-bold text-white text-sm">Customer Support & Live Chat Contacts</h3>
          <p class="text-[11px] text-slate-400">Controls email and WhatsApp direct chat links used across the site and modals.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tech Support Email</label>
          <input type="email" name="contact_email" value="<?= htmlspecialchars(sVal('contact_email', 'support@livetvmarket.com')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Support WhatsApp Display Number</label>
          <input type="text" name="contact_whatsapp" value="<?= htmlspecialchars(sVal('contact_whatsapp', '+1 555 019 2831')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">WhatsApp Direct Chat URL</label>
          <input type="text" name="whatsapp_url" value="<?= htmlspecialchars(sVal('whatsapp_url', 'https://wa.me/15550192831')) ?>" required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Telegram Support URL</label>
          <input type="text" name="contact_telegram" value="<?= htmlspecialchars(sVal('contact_telegram', 'https://t.me/livetvmarket')) ?>"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
        </div>
      </div>
    </div>

    <!-- SAVE BUTTON BAR -->
    <div class="sticky bottom-4 z-20 p-4 rounded-2xl bg-[#090f20]/95 backdrop-blur-xl border border-cyan-500/40 shadow-2xl flex items-center justify-between gap-4">
      <div class="text-xs text-slate-400 hidden sm:flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <span>Updates save directly into MySQL table <strong class="text-white font-mono">site_settings</strong></span>
      </div>
      <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
        <button type="submit" class="btn-glow-cyan w-full sm:w-auto px-7 py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-950 hover:text-white flex items-center justify-center gap-2 cursor-pointer shadow-lg">
          <i data-lucide="save" class="w-4 h-4"></i>
          <span>Save All Settings</span>
        </button>
      </div>
    </div>

  </form>

</div>

<?php
require_once __DIR__ . '/footer.php';
?>
