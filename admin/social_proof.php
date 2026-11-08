<?php
/**
 * LIVETVMARKET - Admin Live Purchase & Social Proof Toasts Manager
 * Full control over floating sales notifications that appear on storefront pages.
 * Customize customer names, locations (e.g. Sydney, Australia), purchased plans, and times.
 */
$adminTitle = 'Live Sales Popups Manager';
$curAdminPage = 'social_proof';

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/social_proof.php';
require_once __DIR__ . '/../config/config.php';

$successMsg = '';
$errorMsg = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_toast') {
        $id = (int)($_POST['id'] ?? 0);
        $customerName = trim($_POST['customer_name'] ?? 'Someone');
        if (empty($customerName)) $customerName = 'Someone';
        $location = trim($_POST['location'] ?? '');
        $planName = trim($_POST['plan_name'] ?? '');
        $timeAgo = trim($_POST['time_ago'] ?? 'Just now');
        if (empty($timeAgo)) $timeAgo = 'Just now';
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($location)) {
            $errorMsg = "Customer location (city, country) is required.";
        } elseif (empty($planName)) {
            $errorMsg = "Purchased plan name is required.";
        } else {
            try {
                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE `social_proof_toasts` SET `customer_name` = ?, `location` = ?, `plan_name` = ?, `time_ago` = ?, `sort_order` = ?, `is_active` = ? WHERE `id` = ?");
                    $stmt->execute([$customerName, $location, $planName, $timeAgo, $sortOrder, $isActive, $id]);
                    $successMsg = "Notification updated successfully!";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO `social_proof_toasts` (`customer_name`, `location`, `plan_name`, `time_ago`, `sort_order`, `is_active`) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$customerName, $location, $planName, $timeAgo, $sortOrder, $isActive]);
                    $successMsg = "New notification toast added successfully!";
                }
            } catch (Exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo->prepare("UPDATE `social_proof_toasts` SET `is_active` = IF(`is_active` = 1, 0, 1) WHERE `id` = ?")->execute([$id]);
                $successMsg = "Notification status updated!";
            } catch (Exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_toast') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo->prepare("DELETE FROM `social_proof_toasts` WHERE `id` = ?")->execute([$id]);
                $successMsg = "Notification deleted successfully!";
            } catch (Exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch all toasts
$allToasts = getSocialProofToasts(false);
$activeCount = count(array_filter($allToasts, fn($t) => (int)$t['is_active'] === 1));

// Available subscription plans for quick selection
$allPricingPlans = function_exists('getPricingPlans') ? getPricingPlans() : [];
?>

<div class="max-w-6xl">

  <!-- PAGE HEADER -->
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
        <span class="p-2 rounded-xl bg-cyan-500/15 text-cyan-400">
          <i data-lucide="bell-ring" class="w-6 h-6"></i>
        </span>
        <span>Live Purchase & Social Proof Notifications</span>
      </h1>
      <p class="text-xs text-slate-400 mt-1">Full control over live customer purchase toasts ("Someone from Sydney, Australia just ordered...") that rotate on storefront pages.</p>
    </div>

    <div class="flex items-center gap-2.5">
      <button type="button" onclick="openAddToastModal()" class="btn-glow-cyan px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 text-white shadow-lg shadow-cyan-500/20 cursor-pointer">
        <i data-lucide="plus-circle" class="w-4 h-4"></i>
        <span>Add New Notification</span>
      </button>
    </div>
  </div>

  <!-- FEEDBACK MESSAGES -->
  <?php if (!empty($successMsg)): ?>
    <div class="mb-6 p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2.5">
      <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
      <span><?= htmlspecialchars($successMsg) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($errorMsg)): ?>
    <div class="mb-6 p-4 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2.5">
      <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
      <span><?= htmlspecialchars($errorMsg) ?></span>
    </div>
  <?php endif; ?>

  <!-- LIVE TOAST PREVIEW CARD -->
  <div class="mb-8 p-5 rounded-2xl glass-card border border-slate-800">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <span class="text-[10px] font-mono uppercase tracking-wider text-cyan-400 font-bold block mb-1">Storefront Live Preview</span>
        <h3 class="text-xs font-semibold text-slate-300">Here is how notifications appear to visitors on the bottom-left of the storefront:</h3>
      </div>
      
      <!-- Simulated Toast Widget -->
      <div class="bg-white p-3.5 rounded-2xl border border-cyan-200 shadow-xl flex items-center gap-3 max-w-sm">
        <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-500 flex items-center justify-center flex-shrink-0">
          <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
        </div>
        <div>
          <p class="text-xs text-slate-700 leading-tight">
            Someone from <span class="font-bold text-slate-900">Sydney, Australia</span> just ordered <span class="text-cyan-700 font-bold">6 Months Full Access</span>
          </p>
          <p class="text-[10px] text-slate-400 mt-1 font-medium">Just now • Verified Purchase</p>
        </div>
      </div>
    </div>
  </div>

  <!-- METRIC STATS -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="glass-card p-4 rounded-2xl border border-slate-800">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs text-slate-400 font-semibold">Total Notifications</span>
        <span class="p-2 rounded-lg bg-cyan-500/10 text-cyan-400"><i data-lucide="layers" class="w-4 h-4"></i></span>
      </div>
      <div class="text-2xl font-black text-white font-mono"><?= count($allToasts) ?></div>
      <span class="text-[10px] text-slate-500">In database pool</span>
    </div>

    <div class="glass-card p-4 rounded-2xl border border-slate-800">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs text-slate-400 font-semibold">Active In Rotation</span>
        <span class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400"><i data-lucide="check" class="w-4 h-4"></i></span>
      </div>
      <div class="text-2xl font-black text-emerald-400 font-mono"><?= $activeCount ?> Active</div>
      <span class="text-[10px] text-slate-500">Live rotating on site</span>
    </div>

    <div class="glass-card p-4 rounded-2xl border border-slate-800">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs text-slate-400 font-semibold">Rotation Cycle</span>
        <span class="p-2 rounded-lg bg-purple-500/10 text-purple-400"><i data-lucide="repeat" class="w-4 h-4"></i></span>
      </div>
      <div class="text-2xl font-black text-white font-mono">18s</div>
      <span class="text-[10px] text-slate-500">Display duration: 5 seconds</span>
    </div>
  </div>

  <!-- TOASTS TABLE -->
  <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden mb-10">
    <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
      <div>
        <h2 class="text-base font-bold text-white flex items-center gap-2">
          <span>Social Proof Notification Pool</span>
          <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 text-[10px] font-mono"><?= count($allToasts) ?> Total</span>
        </h2>
        <p class="text-xs text-slate-400 mt-0.5">Add more customer locations and subscription packages to build maximum social trust.</p>
      </div>
    </div>

    <?php if (empty($allToasts)): ?>
      <div class="p-12 text-center text-slate-500 text-xs">
        <i data-lucide="bell-off" class="w-10 h-10 mx-auto mb-3 text-slate-600"></i>
        <p class="font-bold text-white text-sm">No notification toasts found.</p>
        <p class="mt-1">Click "Add New Notification" above to create your first notification.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-slate-900/90 text-slate-400 font-mono text-[10px] uppercase border-b border-slate-800">
              <th class="p-4 w-12 text-center">Order</th>
              <th class="p-4">Customer Name</th>
              <th class="p-4">Location (City, Country)</th>
              <th class="p-4">Package / Plan</th>
              <th class="p-4">Time Display</th>
              <th class="p-4 text-center">Status</th>
              <th class="p-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            <?php foreach ($allToasts as $t): ?>
              <tr class="hover:bg-slate-800/40 transition-colors">
                
                <!-- Order -->
                <td class="p-4 text-center font-mono font-bold text-slate-400">
                  <?= (int)$t['sort_order'] ?>
                </td>

                <!-- Customer Name -->
                <td class="p-4 font-semibold text-white">
                  <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-700 text-slate-300 font-mono">
                    <?= htmlspecialchars($t['customer_name'] ?: 'Someone') ?>
                  </span>
                </td>

                <!-- Location -->
                <td class="p-4 font-bold text-cyan-300">
                  <div class="flex items-center gap-1.5">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-cyan-400 flex-shrink-0"></i>
                    <span><?= htmlspecialchars($t['location']) ?></span>
                  </div>
                </td>

                <!-- Plan Name -->
                <td class="p-4 font-bold text-slate-200">
                  <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 text-[11px]">
                    <?= htmlspecialchars($t['plan_name']) ?>
                  </span>
                </td>

                <!-- Time Ago -->
                <td class="p-4 font-mono text-slate-400">
                  <?= htmlspecialchars($t['time_ago'] ?: 'Just now') ?>
                </td>

                <!-- Status -->
                <td class="p-4 text-center">
                  <form action="social_proof.php" method="POST" class="inline">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <?php if ((int)$t['is_active']): ?>
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
                    <button type="button" onclick='openEditToastModal(<?= json_encode($t) ?>)' class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-cyan-400 hover:bg-slate-700 transition-colors cursor-pointer" title="Edit Notification">
                      <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>

                    <!-- Delete Button -->
                    <form action="social_proof.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete notification for \'<?= htmlspecialchars(addslashes($t['location'])) ?>\'?');">
                      <input type="hidden" name="action" value="delete_toast">
                      <input type="hidden" name="id" value="<?= $t['id'] ?>">
                      <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors cursor-pointer" title="Delete Notification">
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

<!-- CREATE / EDIT TOAST MODAL -->
<div id="toast-modal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md hidden flex items-center justify-center p-4">
  <div class="bg-[#0b1222] border border-slate-800 rounded-2xl w-full max-w-md p-6 sm:p-7 shadow-2xl relative" onclick="event.stopPropagation();">
    
    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-800">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-cyan-500/15 text-cyan-400 flex items-center justify-center">
          <i data-lucide="bell-ring" class="w-5 h-5"></i>
        </div>
        <h3 id="modal-title" class="font-bold text-white text-base">Add New Notification</h3>
      </div>
      <button type="button" onclick="closeToastModal()" class="p-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white transition-colors cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <form action="social_proof.php" method="POST" class="space-y-4">
      <input type="hidden" name="action" value="save_toast">
      <input type="hidden" id="toast-id" name="id" value="0">

      <!-- Customer Name & Location -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Customer Name</label>
          <input type="text" id="modal-customer-name" name="customer_name" placeholder="e.g. Someone or John D." value="Someone" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-medium focus:outline-none focus:border-cyan-400">
          <span class="text-[10px] text-slate-500 mt-1 block">Default: "Someone"</span>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Location (City, Country) <span class="text-rose-400">*</span></label>
          <input required type="text" id="modal-location" name="location" placeholder="e.g. Sydney, Australia" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-medium focus:outline-none focus:border-cyan-400">
          <span class="text-[10px] text-slate-500 mt-1 block">Visible in bold text</span>
        </div>
      </div>

      <!-- Plan Name Selection or Custom -->
      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Package / Plan Name <span class="text-rose-400">*</span></label>
        <div class="space-y-2">
          <select id="modal-plan-select" onchange="if(this.value) document.getElementById('modal-plan-name').value = this.value;" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-medium focus:outline-none focus:border-cyan-400 cursor-pointer">
            <option value="">-- Choose from preset plans or enter below --</option>
            <option value="12 Months VIP 4K Plan">12 Months VIP 4K Plan</option>
            <option value="6 Months Full Access">6 Months Full Access</option>
            <option value="24 Months Ultra Access">24 Months Ultra Access</option>
            <option value="3 Months Access">3 Months Access</option>
            <option value="1 Month Access">1 Month Access</option>
            <option value="Free 24H Trial Activated">Free 24H Trial Activated</option>
            <?php foreach ($allPricingPlans as $plan): ?>
              <option value="<?= htmlspecialchars($plan['name']) ?>"><?= htmlspecialchars($plan['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <input required type="text" id="modal-plan-name" name="plan_name" placeholder="e.g. 12 Months VIP 4K Plan" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-medium focus:outline-none focus:border-cyan-400">
        </div>
      </div>

      <!-- Time Ago & Sort Order -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Time Ago Display</label>
          <input type="text" id="modal-time-ago" name="time_ago" placeholder="e.g. 2 mins ago or Just now" value="Just now" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:outline-none focus:border-cyan-400">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Sort Order</label>
          <input type="number" id="modal-sort-order" name="sort_order" value="1" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:outline-none focus:border-cyan-400">
        </div>
      </div>

      <!-- Active Checkbox -->
      <div class="pt-2">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" id="modal-active" name="is_active" value="1" checked class="w-4 h-4 rounded text-cyan-500 focus:ring-0 bg-slate-900 border-slate-700">
          <span class="text-xs font-semibold text-slate-300">Enable this notification immediately (Active)</span>
        </label>
      </div>

      <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-800">
        <button type="button" onclick="closeToastModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold text-xs transition-colors cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="btn-glow-cyan px-5 py-2.5 rounded-xl text-white font-bold text-xs shadow-lg shadow-cyan-500/20 cursor-pointer">
          Save Notification
        </button>
      </div>
    </form>

  </div>
</div>

<script>
  function openAddToastModal() {
    document.getElementById('modal-title').textContent = 'Add New Notification';
    document.getElementById('toast-id').value = '0';
    document.getElementById('modal-customer-name').value = 'Someone';
    document.getElementById('modal-location').value = '';
    document.getElementById('modal-plan-name').value = '12 Months VIP 4K Plan';
    document.getElementById('modal-time-ago').value = 'Just now';
    document.getElementById('modal-sort-order').value = '10';
    document.getElementById('modal-active').checked = true;

    document.getElementById('toast-modal').classList.remove('hidden');
  }

  function openEditToastModal(toast) {
    document.getElementById('modal-title').textContent = 'Edit Notification: ' + toast.location;
    document.getElementById('toast-id').value = toast.id;
    document.getElementById('modal-customer-name').value = toast.customer_name || 'Someone';
    document.getElementById('modal-location').value = toast.location;
    document.getElementById('modal-plan-name').value = toast.plan_name;
    document.getElementById('modal-time-ago').value = toast.time_ago || 'Just now';
    document.getElementById('modal-sort-order').value = toast.sort_order || '0';
    document.getElementById('modal-active').checked = (parseInt(toast.is_active) === 1);

    document.getElementById('toast-modal').classList.remove('hidden');
  }

  function closeToastModal() {
    document.getElementById('toast-modal').classList.add('hidden');
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeToastModal();
  });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

