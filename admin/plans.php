<?php
/**
 * LIVETVMARKET - Admin Subscription Plans & Trial Manager
 * Full control over pricing plans (Add, Edit, Delete, Re-order) and Free vs Paid Trial settings.
 */
$adminTitle = 'Plans & Trial Control';
$adminPage = 'plans';

require_once __DIR__ . '/header.php';

$successMsg = '';
$errorMsg = '';

// Handle Plan Add / Edit / Delete / Trial Mode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $months = (int)($_POST['months'] ?? 1);
        $price = (float)($_POST['price_usd'] ?? 14.99);
        $perMonth = trim($_POST['per_month_display'] ?? '');
        $savingsBadge = trim($_POST['savings_badge'] ?? '');
        $isPopular = isset($_POST['is_popular']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 10);
        $features = trim($_POST['features'] ?? '');

        try {
            if ($isPopular === 1) {
                $pdo->exec("UPDATE `pricing_plans` SET `is_popular` = 0");
            }

            $stmt = $pdo->prepare("UPDATE `pricing_plans` SET `name` = ?, `months` = ?, `price_usd` = ?, `per_month_display` = ?, `savings_badge` = ?, `is_popular` = ?, `sort_order` = ?, `features` = ? WHERE `id` = ?");
            $stmt->execute([$name, $months, $price, $perMonth, $savingsBadge, $isPopular, $sortOrder, $features, $id]);
            $successMsg = "Plan '{$name}' was updated successfully in database!";
        } catch (Exception $e) {
            $errorMsg = "Error updating plan: " . $e->getMessage();
        }
    } else if ($action === 'create') {
        $planCode = strtolower(trim($_POST['plan_code'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $months = (int)($_POST['months'] ?? 1);
        $price = (float)($_POST['price_usd'] ?? 14.99);
        $perMonth = trim($_POST['per_month_display'] ?? '');
        $savingsBadge = trim($_POST['savings_badge'] ?? '');
        $isPopular = isset($_POST['is_popular']) ? 1 : 0;
        $sortOrder = (int)($_POST['sort_order'] ?? 99);
        $features = trim($_POST['features'] ?? '');

        if (empty($planCode) || empty($name)) {
            $errorMsg = "Please provide both a unique Plan Code (e.g. '6m') and a Plan Name.";
        } else {
            try {
                if ($isPopular === 1) {
                    $pdo->exec("UPDATE `pricing_plans` SET `is_popular` = 0");
                }
                $stmt = $pdo->prepare("INSERT INTO `pricing_plans` (`plan_code`, `name`, `months`, `price_usd`, `per_month_display`, `savings_badge`, `is_popular`, `features`, `sort_order`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$planCode, $name, $months, $price, $perMonth, $savingsBadge, $isPopular, $features, $sortOrder]);
                $successMsg = "New plan '{$name}' added to database successfully!";
            } catch (Exception $e) {
                $errorMsg = "Error creating plan: " . $e->getMessage();
            }
        }
    } else if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM `pricing_plans` WHERE `id` = ?");
            $stmt->execute([$id]);
            $successMsg = "Plan deleted successfully!";
        } catch (Exception $e) {
            $errorMsg = "Error deleting plan: " . $e->getMessage();
        }
    } else if ($action === 'update_trial') {
        $trialMode = trim($_POST['trial_mode'] ?? 'free');
        $trialPrice = trim($_POST['trial_price'] ?? '1.99');
        $trialDuration = trim($_POST['trial_duration_hours'] ?? '24');

        try {
            updateSetting('trial_mode', $trialMode);
            updateSetting('trial_price', $trialPrice);
            updateSetting('trial_duration_hours', $trialDuration);
            $successMsg = "Trial settings updated successfully! Storefront will now use {$trialMode} trial configuration.";
        } catch (Exception $e) {
            $errorMsg = "Error updating trial settings: " . $e->getMessage();
        }
    }
}

// Fetch all active plans
$plans = [];
try {
    $stmt = $pdo->query("SELECT * FROM `pricing_plans` ORDER BY `sort_order` ASC, `months` ASC");
    $plans = $stmt->fetchAll();
} catch (Exception $e) {
    $errorMsg = "Database error: " . $e->getMessage();
}

$currentTrialMode = getSetting('trial_mode', 'free');
$currentTrialPrice = getSetting('trial_price', '1.99');
$currentTrialDuration = getSetting('trial_duration_hours', '24');
?>

<div class="max-w-6xl">
  
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-white">Pricing Plans & Trial Control</h1>
      <p class="text-xs text-slate-400 mt-1">Configure subscription packages, prices, add new plans, and switch between Free Trial vs Paid Trial ($X.XX).</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="#new-plan-card" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-all shadow-md">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add New Plan</span>
      </a>
    </div>
  </div>

  <?php if (!empty($successMsg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-3">
      <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400 flex-shrink-0"></i>
      <span><?= htmlspecialchars($successMsg) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($errorMsg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs flex items-center gap-3">
      <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400 flex-shrink-0"></i>
      <span><?= htmlspecialchars($errorMsg) ?></span>
    </div>
  <?php endif; ?>

  <!-- TRIAL CONFIGURATION CARD (Free vs Paid Test Lines) -->
  <div class="glass-card rounded-2xl p-6 border border-cyan-500/30 mb-8 shadow-xl bg-slate-900/60 relative overflow-hidden">
    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-800">
      <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
        <i data-lucide="zap" class="w-5 h-5"></i>
      </div>
      <div>
        <h2 class="text-base font-bold text-white">Trial Mode Control (Free vs Paid)</h2>
        <p class="text-xs text-slate-400">Control how trials operate across the storefront, header, modals, and checkout.</p>
      </div>
    </div>

    <form action="plans.php" method="POST" class="space-y-4">
      <input type="hidden" name="action" value="update_trial">

      <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Trial Type Mode</label>
          <select name="trial_mode" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none">
            <option value="free" <?= ($currentTrialMode === 'free') ? 'selected' : '' ?>>🎁 Free Trial (Zero Cost - Leads Collection)</option>
            <option value="paid" <?= ($currentTrialMode === 'paid') ? 'selected' : '' ?>>💳 Paid Trial (Paid Test Line via Checkout)</option>
            <option value="disabled" <?= ($currentTrialMode === 'disabled') ? 'selected' : '' ?>>⛔ Disabled (Hide Trial Options)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Paid Trial Price (USD $)</label>
          <div class="relative">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">$</span>
            <input type="number" step="0.01" name="trial_price" value="<?= htmlspecialchars($currentTrialPrice) ?>" placeholder="1.99"
              class="w-full pl-8 pr-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono">
          </div>
          <p class="text-[10px] text-slate-500 mt-1">Applies when mode is set to "Paid Trial".</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Duration (Hours)</label>
          <input type="number" name="trial_duration_hours" value="<?= htmlspecialchars($currentTrialDuration) ?>" placeholder="24"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono">
        </div>
      </div>

      <div class="pt-2 flex justify-end">
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-2 transition-all shadow-md">
          <i data-lucide="save" class="w-4 h-4"></i>
          <span>Save Trial Settings</span>
        </button>
      </div>
    </form>
  </div>

  <!-- EXISTING PLANS LIST -->
  <div class="mb-6 flex items-center justify-between">
    <h2 class="text-lg font-bold text-white flex items-center gap-2">
      <i data-lucide="package" class="w-5 h-5 text-cyan-400"></i>
      <span>Active Subscription Packages (<?= count($plans) ?>)</span>
    </h2>
    <span class="text-xs text-slate-400">Drag/re-order via "Sort Order" number</span>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">
    <?php foreach ($plans as $p): ?>
      <div class="glass-card rounded-2xl p-6 border <?= $p['is_popular'] ? 'border-cyan-400/60 shadow-xl shadow-cyan-950/40' : 'border-slate-800' ?> relative flex flex-col justify-between">
        
        <?php if ($p['is_popular']): ?>
          <div class="absolute -top-3 right-6 px-3 py-0.5 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 text-[10px] font-black uppercase tracking-wider text-slate-950 font-mono shadow-md">
            ★ Most Popular Choice
          </div>
        <?php endif; ?>

        <form action="plans.php" method="POST" class="space-y-4">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= $p['id'] ?>">

          <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <div>
              <span class="text-[10px] font-mono text-cyan-400 uppercase tracking-wider font-bold">Code: <?= htmlspecialchars($p['plan_code']) ?></span>
              <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($p['name']) ?></h3>
            </div>
            <div class="text-right">
              <span class="text-xl font-black text-white font-mono">$<?= number_format($p['price_usd'], 2) ?></span>
              <div class="text-[11px] text-slate-400"><?= htmlspecialchars($p['per_month_display']) ?></div>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Plan Display Name</label>
              <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" required
                class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none">
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Duration (Months)</label>
              <input type="number" name="months" value="<?= (int)$p['months'] ?>" min="1" max="60" required
                class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono">
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Price (USD $)</label>
              <input type="number" step="0.01" name="price_usd" value="<?= number_format($p['price_usd'], 2, '.', '') ?>" required
                class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono">
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Per Month Text</label>
              <input type="text" name="per_month_display" value="<?= htmlspecialchars($p['per_month_display']) ?>" required
                class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono">
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Savings Badge</label>
              <input type="text" name="savings_badge" value="<?= htmlspecialchars($p['savings_badge'] ?? '') ?>" placeholder="e.g. Save 60%"
                class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none">
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Display Sort Order</label>
              <input type="number" name="sort_order" value="<?= (int)($p['sort_order'] ?? 10) ?>"
                class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono">
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Features (One per line)</label>
            <textarea name="features" rows="5" required
              class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono text-[11px] leading-relaxed"><?= htmlspecialchars($p['features']) ?></textarea>
          </div>

          <div class="flex items-center justify-between pt-2 border-t border-slate-800">
            <label class="flex items-center gap-2 cursor-pointer select-none text-xs text-slate-300">
              <input type="checkbox" name="is_popular" value="1" <?= $p['is_popular'] ? 'checked' : '' ?> class="accent-cyan-400 rounded">
              <span>Set as "Most Popular"</span>
            </label>

            <div class="flex items-center gap-2">
              <button type="submit" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-all shadow-md">
                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                <span>Save</span>
              </button>
            </div>
          </div>
        </form>

        <!-- Delete Plan Form -->
        <div class="pt-3 mt-3 border-t border-slate-800/80 flex justify-end">
          <form action="plans.php" method="POST" onsubmit="return confirm('Are you sure you want to delete plan \'<?= htmlspecialchars($p['name']) ?>\'? This cannot be undone.');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button type="submit" class="text-rose-400 hover:text-rose-300 text-[11px] font-semibold flex items-center gap-1 transition-colors">
              <i data-lucide="trash-2" class="w-3 h-3"></i> Delete Plan
            </button>
          </form>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <!-- ADD NEW PLAN FORM CARD -->
  <div id="new-plan-card" class="glass-card rounded-2xl p-6 sm:p-8 border border-slate-800 bg-slate-900/80 shadow-2xl">
    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
      <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
        <i data-lucide="plus-circle" class="w-5 h-5"></i>
      </div>
      <div>
        <h2 class="text-lg font-bold text-white">Create New Subscription Plan</h2>
        <p class="text-xs text-slate-400">Add a custom plan (e.g. 6 Months, Lifetime, Special Match Pass) to appear on the storefront.</p>
      </div>
    </div>

    <form action="plans.php" method="POST" class="space-y-4">
      <input type="hidden" name="action" value="create">

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Plan Code <span class="text-rose-400">*</span></label>
          <input type="text" name="plan_code" placeholder="e.g. 6m or lifetime" required
            class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono">
          <p class="text-[10px] text-slate-500 mt-1">Unique alphanumeric identifier (e.g. 6m)</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Plan Name <span class="text-rose-400">*</span></label>
          <input type="text" name="name" placeholder="e.g. 6 Months Pass" required
            class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Duration (Months) <span class="text-rose-400">*</span></label>
          <input type="number" name="months" value="6" min="1" max="120" required
            class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Price in USD ($) <span class="text-rose-400">*</span></label>
          <input type="number" step="0.01" name="price_usd" placeholder="49.99" required
            class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Per Month Display Text <span class="text-rose-400">*</span></label>
          <input type="text" name="per_month_display" placeholder="e.g. $8.33/mo" required
            class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Savings Badge (Optional)</label>
          <input type="text" name="savings_badge" placeholder="e.g. Save 40%"
            class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Display Sort Order</label>
          <input type="number" name="sort_order" value="10"
            class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1">Features (One per line) <span class="text-rose-400">*</span></label>
        <textarea name="features" rows="5" required placeholder="25,000+ Live Channels&#10;120,000+ VOD Movies & Shows&#10;All PPV in 4K UHD&#10;Anti-Freeze 9.9 Cluster"
          class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none font-mono text-[11px] leading-relaxed"></textarea>
      </div>

      <div class="flex items-center justify-between pt-4 border-t border-slate-800">
        <label class="flex items-center gap-2 cursor-pointer select-none text-xs text-slate-300">
          <input type="checkbox" name="is_popular" value="1" class="accent-cyan-400 rounded">
          <span>Make this the "Most Popular" highlighted plan</span>
        </label>

        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-all shadow-md">
          <i data-lucide="plus" class="w-4 h-4"></i>
          <span>Create & Publish Plan</span>
        </button>
      </div>
    </form>
  </div>

</div>

<?php
require_once __DIR__ . '/footer.php';
?>
