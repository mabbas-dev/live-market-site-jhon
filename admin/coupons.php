<?php
/**
 * LIVETVMARKET - Admin Promo Coupons Management
 * Percentage-based coupon engine with code generation, usage tracking,
 * minimum order thresholds, expiry dates, and real-time checkout popup integration.
 */
$adminTitle = 'Promo Coupons Manager';
$curAdminPage = 'coupons';

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/coupons.php';
require_once __DIR__ . '/../config/store.php';

$successMsg = '';
$errorMsg = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $code = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', trim($_POST['code'] ?? '')));
        $discountPercent = (int)($_POST['discount_percent'] ?? 0);
        $validPlans = trim($_POST['valid_plans'] ?? 'all');
        if (empty($validPlans)) $validPlans = 'all';
        $description = trim($_POST['description'] ?? '');
        $minAmount = floatval($_POST['min_amount'] ?? 0.00);
        $usageLimit = intval($_POST['usage_limit'] ?? 0);
        $expiresAt = !empty($_POST['expires_at']) ? trim($_POST['expires_at']) : null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($code)) {
            $errorMsg = "Coupon code cannot be empty.";
        } elseif ($discountPercent <= 0 || $discountPercent > 100) {
            $errorMsg = "Discount percentage must be between 1% and 100%.";
        } else {
            try {
                if ($id > 0) {
                    // Update existing
                    $check = $pdo->prepare("SELECT `id` FROM `coupons` WHERE `code` = ? AND `id` != ?");
                    $check->execute([$code, $id]);
                    if ($check->rowCount() > 0) {
                        $errorMsg = "A coupon with code '{$code}' already exists.";
                    } else {
                        $stmt = $pdo->prepare("UPDATE `coupons` SET `code` = ?, `discount_percent` = ?, `valid_plans` = ?, `description` = ?, `min_amount` = ?, `usage_limit` = ?, `expires_at` = ?, `is_active` = ? WHERE `id` = ?");
                        $stmt->execute([$code, $discountPercent, $validPlans, $description, $minAmount, $usageLimit, $expiresAt, $isActive, $id]);
                        $successMsg = "Coupon '{$code}' updated successfully!";
                    }
                } else {
                    // Create new
                    $check = $pdo->prepare("SELECT `id` FROM `coupons` WHERE `code` = ?");
                    $check->execute([$code]);
                    if ($check->rowCount() > 0) {
                        $errorMsg = "A coupon with code '{$code}' already exists.";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO `coupons` (`code`, `discount_percent`, `valid_plans`, `description`, `min_amount`, `usage_limit`, `expires_at`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$code, $discountPercent, $validPlans, $description, $minAmount, $usageLimit, $expiresAt, $isActive]);
                        $successMsg = "New coupon '{$code}' created with {$discountPercent}% discount!";
                    }
                }
            } catch (Exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo->prepare("UPDATE `coupons` SET `is_active` = IF(`is_active` = 1, 0, 1) WHERE `id` = ?")->execute([$id]);
                $successMsg = "Coupon status updated!";
            } catch (Exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo->prepare("DELETE FROM `coupons` WHERE `id` = ?")->execute([$id]);
                $successMsg = "Coupon deleted successfully!";
            } catch (Exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch coupons & stats
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM `coupons` WHERE 1=1";
$params = [];

if ($statusFilter === 'active') {
    $sql .= " AND `is_active` = 1 AND (`expires_at` IS NULL OR `expires_at` >= CURDATE())";
} elseif ($statusFilter === 'inactive') {
    $sql .= " AND `is_active` = 0";
} elseif ($statusFilter === 'expired') {
    $sql .= " AND `expires_at` IS NOT NULL AND `expires_at` < CURDATE()";
}

if (!empty($searchQuery)) {
    $sql .= " AND (`code` LIKE ? OR `description` LIKE ?)";
    $term = "%{$searchQuery}%";
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY `id` DESC";

$couponsList = [];
$totalCouponsCount = 0;
$activeCouponsCount = 0;
$totalUsedSum = 0;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $couponsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalCouponsCount = (int)$pdo->query("SELECT COUNT(*) FROM `coupons`")->fetchColumn();
    $activeCouponsCount = (int)$pdo->query("SELECT COUNT(*) FROM `coupons` WHERE `is_active` = 1 AND (`expires_at` IS NULL OR `expires_at` >= CURDATE())")->fetchColumn();
    $totalUsedSum = (int)$pdo->query("SELECT COALESCE(SUM(used_count), 0) FROM `coupons`")->fetchColumn();
} catch (Exception $e) {
    $errorMsg = "Database error: " . $e->getMessage();
}

$allPricingPlans = function_exists('getPricingPlans') ? getPricingPlans() : [];
$allStoreProducts = function_exists('getStoreProducts') ? getStoreProducts(null, false, null) : [];
?>

<div class="max-w-6xl">
  
  <!-- PAGE HEADER -->
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
        <span class="p-2 rounded-xl bg-cyan-500/15 text-cyan-400">
          <i data-lucide="ticket-percent" class="w-6 h-6"></i>
        </span>
        <span>Promo Coupons & % Discount Manager</span>
      </h1>
      <p class="text-xs text-slate-400 mt-1">Create and configure percentage-based promo discount codes that instantly apply inside the checkout/payment popup.</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-2">
      <button type="button" onclick="openAddCouponModal()" class="btn-glow-cyan px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 text-white shadow-lg shadow-cyan-500/20 cursor-pointer">
        <i data-lucide="plus-circle" class="w-4 h-4"></i>
        <span>Create New Coupon</span>
      </button>
    </div>
  </div>

  <!-- ALERTS -->
  <?php if (!empty($successMsg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-3">
      <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
      <span><?= htmlspecialchars($successMsg) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($errorMsg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs flex items-center gap-3">
      <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400"></i>
      <span><?= htmlspecialchars($errorMsg) ?></span>
    </div>
  <?php endif; ?>

  <!-- METRIC STATS -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="glass-card rounded-2xl p-5 border border-slate-800">
      <div class="flex items-center justify-between">
        <span class="text-xs font-mono uppercase text-slate-400">Total Coupons</span>
        <span class="p-2 rounded-xl bg-cyan-500/15 text-cyan-400"><i data-lucide="ticket" class="w-4 h-4"></i></span>
      </div>
      <div class="text-2xl font-black text-white mt-2"><?= $totalCouponsCount ?></div>
      <p class="text-[11px] text-slate-500 mt-1">Configured in store database</p>
    </div>

    <div class="glass-card rounded-2xl p-5 border border-slate-800">
      <div class="flex items-center justify-between">
        <span class="text-xs font-mono uppercase text-slate-400">Active & Redeemable</span>
        <span class="p-2 rounded-xl bg-emerald-500/15 text-emerald-400"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
      </div>
      <div class="text-2xl font-black text-emerald-400 mt-2"><?= $activeCouponsCount ?></div>
      <p class="text-[11px] text-slate-500 mt-1">Currently usable by customers</p>
    </div>

    <div class="glass-card rounded-2xl p-5 border border-slate-800">
      <div class="flex items-center justify-between">
        <span class="text-xs font-mono uppercase text-slate-400">Total Redemptions</span>
        <span class="p-2 rounded-xl bg-indigo-500/15 text-indigo-400"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span>
      </div>
      <div class="text-2xl font-black text-indigo-400 mt-2"><?= number_format($totalUsedSum) ?></div>
      <p class="text-[11px] text-slate-500 mt-1">Orders discounted with coupons</p>
    </div>
  </div>

  <!-- FILTER & SEARCH BAR -->
  <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 bg-slate-900 p-1 rounded-xl border border-slate-800 text-xs font-semibold self-start sm:self-auto flex-wrap">
      <a href="coupons.php?status=all" class="px-3 py-1.5 rounded-lg transition-colors <?= ($statusFilter === 'all') ? 'bg-cyan-500/20 text-cyan-400 font-bold border border-cyan-500/40' : 'text-slate-400 hover:text-white' ?>">
        All (<?= $totalCouponsCount ?>)
      </a>
      <a href="coupons.php?status=active" class="px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 <?= ($statusFilter === 'active') ? 'bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/40' : 'text-slate-400 hover:text-white' ?>">
        <span>Active</span>
        <span class="px-1.5 py-0.2 rounded bg-emerald-500/30 font-mono text-[10px]"><?= $activeCouponsCount ?></span>
      </a>
      <a href="coupons.php?status=inactive" class="px-3 py-1.5 rounded-lg transition-colors <?= ($statusFilter === 'inactive') ? 'bg-amber-500/20 text-amber-300 font-bold border border-amber-500/40' : 'text-slate-400 hover:text-white' ?>">
        Inactive
      </a>
      <a href="coupons.php?status=expired" class="px-3 py-1.5 rounded-lg transition-colors <?= ($statusFilter === 'expired') ? 'bg-rose-500/20 text-rose-300 font-bold border border-rose-500/40' : 'text-slate-400 hover:text-white' ?>">
        Expired
      </a>
    </div>

    <!-- Search Form -->
    <form action="coupons.php" method="GET" class="w-full sm:w-80 flex items-center">
      <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
      <div class="relative w-full">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
          <i data-lucide="search" class="w-4 h-4"></i>
        </span>
        <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search code, description..."
          class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400">
      </div>
    </form>
  </div>

  <!-- COUPONS TABLE -->
  <div class="glass-card rounded-2xl overflow-hidden border border-slate-800">
    <?php if (empty($couponsList)): ?>
      <div class="py-16 text-center text-slate-400 text-xs">
        <i data-lucide="ticket-percent" class="w-10 h-10 mx-auto mb-3 text-slate-600"></i>
        <p class="font-bold text-white text-sm">No coupons found</p>
        <p class="mt-1">Click "Create New Coupon" above to add your first promo code.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-slate-900/90 text-slate-400 font-mono text-[10px] uppercase border-b border-slate-800">
              <th class="p-4">Coupon Code</th>
              <th class="p-4">Discount Rate</th>
              <th class="p-4">Applies To</th>
              <th class="p-4">Description & Rules</th>
              <th class="p-4">Redemptions</th>
              <th class="p-4">Expires</th>
              <th class="p-4">Status</th>
              <th class="p-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            <?php foreach ($couponsList as $c): 
              $isExpired = !empty($c['expires_at']) && ($c['expires_at'] < date('Y-m-d'));
              $isLimitReached = ((int)$c['usage_limit'] > 0) && ((int)$c['used_count'] >= (int)$c['usage_limit']);
            ?>
              <tr class="hover:bg-slate-800/40 transition-colors">
                
                <!-- Code -->
                <td class="p-4">
                  <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg bg-cyan-500/15 border border-cyan-500/30 text-cyan-300 font-mono font-bold text-xs tracking-wider">
                      <?= htmlspecialchars($c['code']) ?>
                    </span>
                    <button type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($c['code']) ?>'); alert('Copied coupon: <?= htmlspecialchars($c['code']) ?>');" title="Copy code" class="p-1 text-slate-500 hover:text-cyan-400 transition-colors cursor-pointer">
                      <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    </button>
                  </div>
                </td>

                <!-- Discount Rate -->
                <td class="p-4">
                  <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-mono font-black text-xs">
                    <span><?= (int)$c['discount_percent'] ?>%</span>
                    <span class="text-[9px] uppercase font-bold text-emerald-400">OFF</span>
                  </div>
                </td>

                <!-- Applies To -->
                <td class="p-4">
                  <?php 
                  $vp = trim($c['valid_plans'] ?? 'all');
                  if (empty($vp) || strtolower($vp) === 'all'): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-cyan-500/15 border border-cyan-500/30 text-cyan-300 font-semibold text-[10px]">
                      <i data-lucide="globe" class="w-3 h-3"></i> All Products (Universal)
                    </span>
                  <?php elseif (strtolower($vp) === 'iptv_all'): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-500/20 border border-blue-500/40 text-blue-300 font-semibold text-[10px]">
                      <i data-lucide="tv" class="w-3 h-3"></i> All IPTV Packages Only
                    </span>
                  <?php elseif (strtolower($vp) === 'store_all'): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-semibold text-[10px]">
                      <i data-lucide="sparkles" class="w-3 h-3"></i> All Digital Store Only
                    </span>
                  <?php else: 
                    $pNames = getPlanNamesByCodes($vp);
                  ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-500/20 border border-purple-500/40 text-purple-300 font-semibold text-[10px]" title="Valid only for: <?= htmlspecialchars($pNames) ?>">
                      <i data-lucide="target" class="w-3 h-3"></i> <?= htmlspecialchars($pNames) ?>
                    </span>
                  <?php endif; ?>
                </td>

                <!-- Description & Rules -->
                <td class="p-4">
                  <div class="font-medium text-white text-xs"><?= htmlspecialchars($c['description'] ?: 'General discount voucher') ?></div>
                  <?php if ((float)$c['min_amount'] > 0): ?>
                    <div class="text-[10px] text-amber-400 font-mono mt-0.5">Min Order: $<?= number_format((float)$c['min_amount'], 2) ?></div>
                  <?php endif; ?>
                </td>

                <!-- Redemptions vs Limit -->
                <td class="p-4 font-mono text-xs">
                  <div class="flex items-center gap-1.5">
                    <span class="font-bold text-slate-200"><?= (int)$c['used_count'] ?></span>
                    <span class="text-slate-500">/</span>
                    <span class="text-slate-400"><?= ((int)$c['usage_limit'] > 0) ? (int)$c['usage_limit'] : '∞' ?></span>
                  </div>
                  <?php if ($isLimitReached): ?>
                    <span class="text-[9px] text-rose-400 font-semibold block mt-0.5">Limit reached</span>
                  <?php endif; ?>
                </td>

                <!-- Expiration -->
                <td class="p-4 text-xs font-mono">
                  <?php if (!empty($c['expires_at'])): ?>
                    <span class="<?= $isExpired ? 'text-rose-400 font-bold' : 'text-slate-300' ?>">
                      <?= date('M j, Y', strtotime($c['expires_at'])) ?>
                    </span>
                    <?php if ($isExpired): ?>
                      <span class="text-[9px] px-1.5 py-0.2 rounded bg-rose-500/20 text-rose-300 font-sans block mt-0.5 w-fit">Expired</span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-slate-500 font-sans text-[11px]">Never</span>
                  <?php endif; ?>
                </td>

                <!-- Status -->
                <td class="p-4">
                  <form action="coupons.php" method="POST" class="inline">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <?php if ((int)$c['is_active'] && !$isExpired && !$isLimitReached): ?>
                      <button type="submit" class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[10px] font-bold hover:bg-emerald-500/30 transition-colors cursor-pointer" title="Click to disable">
                        Active
                      </button>
                    <?php else: ?>
                      <button type="submit" class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 border border-slate-700 text-[10px] font-bold hover:bg-slate-700 transition-colors cursor-pointer" title="Click to enable">
                        Disabled
                      </button>
                    <?php endif; ?>
                  </form>
                </td>

                <!-- Actions -->
                <td class="p-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <!-- Edit Button -->
                    <button type="button" onclick='openEditCouponModal(<?= json_encode($c) ?>)' class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-cyan-400 hover:bg-slate-700 transition-colors cursor-pointer" title="Edit Coupon">
                      <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>

                    <!-- Delete Button -->
                    <form action="coupons.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete coupon \'<?= htmlspecialchars($c['code']) ?>\'?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $c['id'] ?>">
                      <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors cursor-pointer" title="Delete Coupon">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                      </button>
                    </form>
                  </div>
                </td>

              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- CREATE / EDIT COUPON MODAL -->
<div id="coupon-modal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md hidden flex items-center justify-center p-4">
  <div class="bg-[#0b1222] border border-slate-800 rounded-2xl w-full max-w-md p-6 sm:p-7 shadow-2xl relative" onclick="event.stopPropagation();">
    
    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-800">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-cyan-500/15 text-cyan-400 flex items-center justify-center">
          <i data-lucide="ticket" class="w-5 h-5"></i>
        </div>
        <h3 id="modal-title" class="font-bold text-white text-base">Create New Coupon</h3>
      </div>
      <button type="button" onclick="closeCouponModal()" class="p-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white transition-colors cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <form action="coupons.php" method="POST" class="space-y-4">
      <input type="hidden" name="action" value="save">
      <input type="hidden" id="coupon-id" name="id" value="0">

      <!-- Code & Discount -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Coupon Code <span class="text-rose-400">*</span></label>
          <input required type="text" id="modal-code" name="code" placeholder="e.g. SUMMER50" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono font-bold uppercase focus:outline-none focus:border-cyan-400">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Discount % <span class="text-rose-400">*</span></label>
          <div class="relative">
            <input required type="number" min="1" max="100" id="modal-discount" name="discount_percent" placeholder="e.g. 60" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono font-bold focus:outline-none focus:border-cyan-400 pr-8">
            <span class="absolute right-3 top-2.5 font-bold text-cyan-400 text-xs">%</span>
          </div>
        </div>
      </div>

      <!-- Applicable Product / Plan Restriction -->
      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
          <span>Applicable Products / Category <span class="text-rose-400">*</span></span>
          <span class="text-[10px] text-cyan-400 font-normal">All or Specific</span>
        </label>
        <select id="modal-valid-plans" name="valid_plans" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-medium focus:outline-none focus:border-cyan-400 cursor-pointer">
          <option value="all">🌐 All Products &amp; Services (Universal Discount - IPTV &amp; Digital Store)</option>
          <option value="iptv_all">📺 All IPTV Streaming Packages Only</option>
          <option value="store_all">⚡ All Digital Store Subscriptions Only (Netflix, Prime, Spotify, etc.)</option>

          <optgroup label="Specific IPTV Packages">
            <?php foreach ($allPricingPlans as $plan): ?>
              <option value="<?= htmlspecialchars($plan['plan_code']) ?>">
                📺 <?= htmlspecialchars($plan['name']) ?> (<?= htmlspecialchars($plan['plan_code']) ?>)
              </option>
            <?php endforeach; ?>
          </optgroup>

          <?php if (!empty($allStoreProducts)): ?>
          <optgroup label="Specific Digital Store Products">
            <?php foreach ($allStoreProducts as $sp): ?>
              <option value="store_<?= $sp['id'] ?>">
                ⚡ <?= htmlspecialchars($sp['name']) ?>
              </option>
            <?php endforeach; ?>
          </optgroup>
          <?php endif; ?>
        </select>
        <span class="text-[10px] text-slate-500 mt-1 block">Choose whether this coupon applies universally or is restricted to IPTV, Digital Store, or a specific product.</span>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Description / Label</label>
        <input type="text" id="modal-desc" name="description" placeholder="e.g. VIP 60% Launch Voucher" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
      </div>

      <!-- Min Order & Usage Limit -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Min Order ($ USD)</label>
          <input type="number" step="0.01" min="0" id="modal-min-amount" name="min_amount" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:outline-none focus:border-cyan-400">
          <span class="text-[10px] text-slate-500 mt-1 block">0 = no minimum required</span>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Usage Limit (Max Uses)</label>
          <input type="number" min="0" id="modal-usage-limit" name="usage_limit" value="0" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:outline-none focus:border-cyan-400">
          <span class="text-[10px] text-slate-500 mt-1 block">0 = unlimited redemptions</span>
        </div>
      </div>

      <!-- Expiry Date -->
      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Expiration Date (Optional)</label>
        <input type="date" id="modal-expires" name="expires_at" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
        <span class="text-[10px] text-slate-500 mt-1 block">Leave empty for a coupon that never expires</span>
      </div>

      <!-- Active Toggle -->
      <div class="pt-2">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" id="modal-active" name="is_active" value="1" checked class="w-4 h-4 rounded text-cyan-500 focus:ring-0 bg-slate-900 border-slate-700">
          <span class="text-xs font-semibold text-slate-300">Enable this coupon immediately (Active)</span>
        </label>
      </div>

      <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-800">
        <button type="button" onclick="closeCouponModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold text-xs transition-colors cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="btn-glow-cyan px-5 py-2.5 rounded-xl text-white font-bold text-xs shadow-lg shadow-cyan-500/20 cursor-pointer">
          Save Coupon
        </button>
      </div>
    </form>

  </div>
</div>

<script>
  function openAddCouponModal() {
    document.getElementById('modal-title').textContent = 'Create New Coupon';
    document.getElementById('coupon-id').value = '0';
    document.getElementById('modal-code').value = '';
    document.getElementById('modal-discount').value = '';
    document.getElementById('modal-valid-plans').value = 'all';
    document.getElementById('modal-desc').value = '';
    document.getElementById('modal-min-amount').value = '0.00';
    document.getElementById('modal-usage-limit').value = '0';
    document.getElementById('modal-expires').value = '';
    document.getElementById('modal-active').checked = true;

    document.getElementById('coupon-modal').classList.remove('hidden');
  }

  function openEditCouponModal(coupon) {
    document.getElementById('modal-title').textContent = 'Edit Coupon: ' + coupon.code;
    document.getElementById('coupon-id').value = coupon.id;
    document.getElementById('modal-code').value = coupon.code;
    document.getElementById('modal-discount').value = coupon.discount_percent;
    document.getElementById('modal-valid-plans').value = coupon.valid_plans || 'all';
    document.getElementById('modal-desc').value = coupon.description || '';
    document.getElementById('modal-min-amount').value = coupon.min_amount || '0.00';
    document.getElementById('modal-usage-limit').value = coupon.usage_limit || '0';
    document.getElementById('modal-expires').value = coupon.expires_at || '';
    document.getElementById('modal-active').checked = (parseInt(coupon.is_active) === 1);

    document.getElementById('coupon-modal').classList.remove('hidden');
  }

  function closeCouponModal() {
    document.getElementById('coupon-modal').classList.add('hidden');
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeCouponModal();
  });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

