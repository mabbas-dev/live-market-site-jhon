<?php
/**
 * LIVETVMARKET - Admin Dashboard
 * Real-time statistics, quick shortcuts, recent leads & orders.
 */
$adminTitle = 'Dashboard Overview';
$adminPage = 'dashboard';

require_once __DIR__ . '/header.php';

// Fetch Statistics from MySQL DB 'iptv-website'
$totalOrders = 0;
$totalRevenue = 0.00;
$totalTrials = 0;
$pendingTrials = 0;
$activePlansCount = 0;

$recentTrials = [];
$recentOrders = [];

try {
    // Orders stats
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `orders`");
    $totalOrders = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM `orders` WHERE `status` = 'Paid'");
    $paidOrders = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM `orders` WHERE `status` = 'Paid'");
    $totalRevenue = (float)$stmt->fetchColumn();

    // Trial stats
    $stmt = $pdo->query("SELECT COUNT(*) FROM `trial_leads`");
    $totalTrials = (int)$stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM `trial_leads` WHERE `status` = 'Pending'");
    $pendingTrials = (int)$stmt->fetchColumn();

    // Plans stats
    $stmt = $pdo->query("SELECT COUNT(*) FROM `pricing_plans`");
    $activePlansCount = (int)$stmt->fetchColumn();

    // Recent 5 Trials
    $stmt = $pdo->query("SELECT * FROM `trial_leads` ORDER BY `id` DESC LIMIT 5");
    $recentTrials = $stmt->fetchAll();

    // Recent 5 Orders
    $stmt = $pdo->query("SELECT * FROM `orders` ORDER BY `id` DESC LIMIT 5");
    $recentOrders = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Dashboard query error: " . $e->getMessage());
}
?>

<!-- WELCOME BANNER -->
<div class="mb-8 p-6 rounded-2xl bg-gradient-to-r from-cyan-950/60 via-slate-900/80 to-blue-950/60 border border-cyan-500/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-mono text-[11px] font-bold border border-emerald-500/30 flex items-center gap-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
        SYSTEM OPERATIONAL • ALL SERVICES LIVE
      </span>
    </div>
    <h1 class="text-2xl font-black text-white">Welcome back, <?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></h1>
    <p class="text-xs text-slate-300 mt-1">Control all content, pricing plans, banners, and incoming leads across <strong class="text-white"><?= htmlspecialchars(SITE_DOMAIN) ?></strong>.</p>
  </div>
  <div class="flex items-center gap-2.5 flex-shrink-0">
    <a href="settings.php" class="btn-glow-cyan px-4 py-2.5 rounded-xl text-xs font-bold text-slate-950 hover:text-white flex items-center gap-1.5">
      <i data-lucide="sliders" class="w-4 h-4"></i>
      <span>Customize Website</span>
    </a>
    <a href="plans.php" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-700 transition-all flex items-center gap-1.5">
      <i data-lucide="tags" class="w-4 h-4 text-cyan-400"></i>
      <span>Edit Plans</span>
    </a>
  </div>
</div>

<!-- KEY METRIC CARDS GRID -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
  
  <!-- Metric 1: Total Revenue -->
  <div class="glass-card p-5 rounded-2xl relative overflow-hidden">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-medium text-slate-400">Confirmed Paid Revenue</span>
      <div class="w-9 h-9 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center">
        <i data-lucide="badge-dollar-sign" class="w-5 h-5"></i>
      </div>
    </div>
    <div class="text-2xl font-black text-white font-mono">$<?= number_format($totalRevenue, 2) ?></div>
    <div class="mt-2 text-[11px] text-slate-400 flex items-center gap-1">
      <span class="text-emerald-400 font-semibold"><?= $paidOrders ?> Paid</span>
      <span>• <?= $totalOrders ?> Total Orders Placed</span>
    </div>
  </div>

  <!-- Metric 2: Free 24h Trial Leads -->
  <div class="glass-card p-5 rounded-2xl relative overflow-hidden">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-medium text-slate-400">Free 24h Trial Leads</span>
      <div class="w-9 h-9 rounded-xl bg-purple-500/15 border border-purple-500/30 text-purple-400 flex items-center justify-center">
        <i data-lucide="play-circle" class="w-5 h-5"></i>
      </div>
    </div>
    <div class="text-2xl font-black text-white font-mono"><?= $totalTrials ?></div>
    <div class="mt-2 text-[11px] text-slate-400 flex items-center gap-1.5">
      <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 font-semibold font-mono"><?= $pendingTrials ?> Pending</span>
      <span>ready for activation</span>
    </div>
  </div>

  <!-- Metric 3: Active Subscription Plans -->
  <div class="glass-card p-5 rounded-2xl relative overflow-hidden">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-medium text-slate-400">Subscription Plans</span>
      <div class="w-9 h-9 rounded-xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 flex items-center justify-center">
        <i data-lucide="tags" class="w-5 h-5"></i>
      </div>
    </div>
    <div class="text-2xl font-black text-white font-mono"><?= $activePlansCount ?> Active</div>
    <div class="mt-2 text-[11px] text-slate-400 flex items-center gap-1">
      <span class="text-cyan-400 font-semibold"><?= TOTAL_CHANNELS ?></span>
      <span>Channels & VOD Included</span>
    </div>
  </div>

  <!-- Metric 4: CDN Clusters & Uptime -->
  <div class="glass-card p-5 rounded-2xl relative overflow-hidden">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-medium text-slate-400">Edge Streaming Nodes</span>
      <div class="w-9 h-9 rounded-xl bg-blue-500/15 border border-blue-500/30 text-blue-400 flex items-center justify-center">
        <i data-lucide="server" class="w-5 h-5"></i>
      </div>
    </div>
    <div class="text-2xl font-black text-white font-mono"><?= htmlspecialchars(ACTIVE_CLUSTERS) ?> Nodes</div>
    <div class="mt-2 text-[11px] text-emerald-400 flex items-center gap-1 font-semibold">
      <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
      <?= htmlspecialchars(NETWORK_UPTIME) ?> High-Availability
    </div>
  </div>

</div>

<!-- TWO-COLUMN TABLES: RECENT TRIALS & RECENT ORDERS -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
  
  <!-- Recent Free Trial Requests -->
  <div class="glass-card rounded-2xl p-6 flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
        <div class="flex items-center gap-2">
          <i data-lucide="user-check" class="w-5 h-5 text-purple-400"></i>
          <h3 class="font-bold text-white text-sm">Recent Free Trial Requests</h3>
        </div>
        <a href="trials.php" class="text-xs text-cyan-400 hover:text-white font-semibold transition-colors">
          View All (<?= $totalTrials ?>) &rarr;
        </a>
      </div>

      <?php if (empty($recentTrials)): ?>
        <div class="py-12 text-center text-slate-400 text-xs">
          <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-500"></i>
          No trial requests recorded yet. When users submit the trial form, they will appear here!
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-slate-500 border-b border-slate-800/80 font-mono text-[10px] uppercase">
                <th class="pb-2.5">User</th>
                <th class="pb-2.5">Device</th>
                <th class="pb-2.5">Status</th>
                <th class="pb-2.5 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <?php foreach ($recentTrials as $t): ?>
                <tr class="hover:bg-slate-800/30 transition-colors">
                  <td class="py-3">
                    <div class="font-bold text-white"><?= htmlspecialchars($t['full_name']) ?></div>
                    <div class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($t['email']) ?></div>
                  </td>
                  <td class="py-3 text-slate-300">
                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-[10px]"><?= htmlspecialchars($t['device']) ?></span>
                  </td>
                  <td class="py-3">
                    <?php if ($t['status'] === 'Pending'): ?>
                      <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-bold">Pending</span>
                    <?php elseif ($t['status'] === 'Sent'): ?>
                      <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold">Sent</span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded-full bg-slate-700 text-slate-300 text-[10px]"><?= htmlspecialchars($t['status']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 text-right">
                    <a href="trials.php" class="text-cyan-400 hover:text-white font-bold text-[11px]">Manage &rarr;</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recent Customer Orders -->
  <div class="glass-card rounded-2xl p-6 flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
        <div class="flex items-center gap-2">
          <i data-lucide="shopping-bag" class="w-5 h-5 text-blue-400"></i>
          <h3 class="font-bold text-white text-sm">Recent Customer Orders</h3>
        </div>
        <a href="orders.php" class="text-xs text-cyan-400 hover:text-white font-semibold transition-colors">
          View All (<?= $totalOrders ?>) &rarr;
        </a>
      </div>

      <?php if (empty($recentOrders)): ?>
        <div class="py-12 text-center text-slate-400 text-xs">
          <i data-lucide="shopping-cart" class="w-8 h-8 mx-auto mb-2 text-slate-500"></i>
          No orders placed yet. Orders submitted via the instant checkout modal will display here!
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="text-slate-500 border-b border-slate-800/80 font-mono text-[10px] uppercase">
                <th class="pb-2.5">Customer</th>
                <th class="pb-2.5">Plan</th>
                <th class="pb-2.5">Amount</th>
                <th class="pb-2.5 text-right">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <?php foreach ($recentOrders as $o): ?>
                <tr class="hover:bg-slate-800/30 transition-colors">
                  <td class="py-3">
                    <div class="font-mono text-white text-[11px]"><?= htmlspecialchars($o['email']) ?></div>
                    <div class="text-[10px] text-slate-500"><?= date('M j, Y - H:i', strtotime($o['created_at'])) ?></div>
                  </td>
                  <td class="py-3 text-slate-300">
                    <div class="font-semibold text-white"><?= htmlspecialchars($o['plan_name']) ?></div>
                    <div class="text-[10px] text-slate-400"><?= $o['connections'] ?> Connection(s)</div>
                  </td>
                  <td class="py-3 font-mono font-bold text-emerald-400">
                    $<?= number_format($o['amount'], 2) ?> <?= htmlspecialchars($o['currency']) ?>
                  </td>
                  <td class="py-3 text-right">
                    <?php if ($o['status'] === 'Paid' || $o['status'] === 'Active'): ?>
                      <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold">
                        <?= htmlspecialchars($o['status']) ?>
                      </span>
                    <?php else: ?>
                      <span class="px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-[10px] font-bold">
                        <?= htmlspecialchars($o['status']) ?>
                      </span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- QUICK CUSTOMIZATION CARDS (Direct Links) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
  
  <a href="settings.php" class="p-5 rounded-2xl glass-card hover:border-cyan-400/50 transition-all group">
    <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
      <i data-lucide="sliders" class="w-5 h-5"></i>
    </div>
    <h4 class="font-bold text-white text-sm mb-1 group-hover:text-cyan-300 transition-colors">Site & Brand Settings</h4>
    <p class="text-xs text-slate-400 leading-relaxed">
      Edit domain name, top legal banner statement, promo codes, discount %, WhatsApp contact, and SEO meta tags.
    </p>
  </a>

  <a href="plans.php" class="p-5 rounded-2xl glass-card hover:border-cyan-400/50 transition-all group">
    <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
      <i data-lucide="credit-card" class="w-5 h-5"></i>
    </div>
    <h4 class="font-bold text-white text-sm mb-1 group-hover:text-cyan-300 transition-colors">Pricing & Subscription Plans</h4>
    <p class="text-xs text-slate-400 leading-relaxed">
      Modify plan names, monthly/annual pricing, savings badges, feature bullet points, and set the "Most Popular" plan.
    </p>
  </a>

  <a href="setup_guides.php" class="p-5 rounded-2xl glass-card hover:border-cyan-400/50 transition-all group">
    <div class="w-10 h-10 rounded-xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
      <i data-lucide="book-open" class="w-5 h-5"></i>
    </div>
    <h4 class="font-bold text-white text-sm mb-1 group-hover:text-cyan-300 transition-colors">Device Setup Tutorials</h4>
    <p class="text-xs text-slate-400 leading-relaxed">
      Add & edit installation guides for Firestick, Android, Smart TV, Apple, PC, MAG with step-by-step instructions.
    </p>
  </a>

  <a href="trials.php" class="p-5 rounded-2xl glass-card hover:border-cyan-400/50 transition-all group">
    <div class="w-10 h-10 rounded-xl bg-purple-500/15 border border-purple-500/30 text-purple-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
      <i data-lucide="send" class="w-5 h-5"></i>
    </div>
    <h4 class="font-bold text-white text-sm mb-1 group-hover:text-cyan-300 transition-colors">Lead Management & WhatsApp</h4>
    <p class="text-xs text-slate-400 leading-relaxed">
      Access Free 24h trial requests, one-click copy customer credentials, send M3U lines, or direct WhatsApp message.
    </p>
  </a>

</div>

<?php
require_once __DIR__ . '/footer.php';
?>
