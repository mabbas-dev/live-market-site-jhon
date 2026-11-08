<?php
/**
 * LIVETVMARKET - Admin Customer Orders Manager
 * View and process subscription checkout orders from MySQL DB 'iptv-website'.
 */
$adminTitle = 'Customer Orders';
$adminPage = 'orders';

require_once __DIR__ . '/header.php';

$successMsg = '';
$errorMsg = '';

// Handle Status Updates or Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $orderId = (int)$_POST['id'];

    if ($action === 'set_status') {
        $newStatus = $_POST['status'] ?? 'New';
        try {
            $stmt = $pdo->prepare("UPDATE `orders` SET `status` = ? WHERE `id` = ?");
            $stmt->execute([$newStatus, $orderId]);
            $successMsg = "Order #{$orderId} status set to '{$newStatus}'.";
        } catch (Exception $e) {
            $errorMsg = "Error updating order: " . $e->getMessage();
        }
    } else if ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM `orders` WHERE `id` = ?");
            $stmt->execute([$orderId]);
            $successMsg = "Order #{$orderId} deleted.";
        } catch (Exception $e) {
            $errorMsg = "Error deleting order: " . $e->getMessage();
        }
    }
}

// Search and filter
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM `orders` WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND `status` = ?";
    $params[] = ucfirst($statusFilter);
}

if (!empty($searchQuery)) {
    $sql .= " AND (`customer_name` LIKE ? OR `email` LIKE ? OR `whatsapp` LIKE ? OR `plan_name` LIKE ? OR `coupon_used` LIKE ?)";
    $term = "%{$searchQuery}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY `id` DESC";

$orders = [];
$totalOrderCount = 0;
$totalRevenueSum = 0.00;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    $totalOrderCount = (int)$pdo->query("SELECT COUNT(*) FROM `orders`")->fetchColumn();
    $totalRevenueSum = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM `orders`")->fetchColumn();

    $counts = [
        'all' => $totalOrderCount,
        'paid' => (int)$pdo->query("SELECT COUNT(*) FROM `orders` WHERE `status` = 'Paid'")->fetchColumn(),
        'new' => (int)$pdo->query("SELECT COUNT(*) FROM `orders` WHERE `status` = 'New'")->fetchColumn(),
        'active' => (int)$pdo->query("SELECT COUNT(*) FROM `orders` WHERE `status` = 'Active'")->fetchColumn(),
        'cancelled' => (int)$pdo->query("SELECT COUNT(*) FROM `orders` WHERE `status` = 'Cancelled'")->fetchColumn(),
    ];
} catch (Exception $e) {
    $errorMsg = "Database error: " . $e->getMessage();
}
?>

<div class="max-w-6xl">
  
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-white">Customer Orders Management</h1>
      <p class="text-xs text-slate-400 mt-1">Review subscription checkout transactions, verify payment channels, and manage customer service access.</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs font-mono">
        <span class="text-slate-400">Total Revenue:</span>
        <strong class="text-emerald-400 ml-1 font-bold text-sm">$<?= number_format($totalRevenueSum, 2) ?></strong>
      </div>
    </div>
  </div>

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

  <!-- FILTER & SEARCH BAR -->
  <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 bg-slate-900 p-1 rounded-xl border border-slate-800 text-xs font-semibold self-start sm:self-auto flex-wrap">
      <a href="orders.php?status=all" class="px-3 py-1.5 rounded-lg transition-colors <?= ($statusFilter === 'all') ? 'bg-cyan-500/20 text-cyan-400 font-bold border border-cyan-500/40' : 'text-slate-400 hover:text-white' ?>">
        All Orders (<?= $counts['all'] ?? 0 ?>)
      </a>
      <a href="orders.php?status=paid" class="px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 <?= ($statusFilter === 'paid') ? 'bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/40' : 'text-slate-400 hover:text-white' ?>">
        <span>Paid</span>
        <span class="px-1.5 py-0.2 rounded bg-emerald-500/30 font-mono text-[10px]"><?= $counts['paid'] ?? 0 ?></span>
      </a>
      <a href="orders.php?status=new" class="px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 <?= ($statusFilter === 'new') ? 'bg-cyan-500/20 text-cyan-300 font-bold border border-cyan-500/40' : 'text-slate-400 hover:text-white' ?>">
        <span>New</span>
        <span class="px-1.5 py-0.2 rounded bg-cyan-500/30 font-mono text-[10px]"><?= $counts['new'] ?? 0 ?></span>
      </a>
      <a href="orders.php?status=active" class="px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 <?= ($statusFilter === 'active') ? 'bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/40' : 'text-slate-400 hover:text-white' ?>">
        <span>Active</span>
        <span class="px-1.5 py-0.2 rounded bg-emerald-500/30 font-mono text-[10px]"><?= $counts['active'] ?? 0 ?></span>
      </a>
      <a href="orders.php?status=cancelled" class="px-3 py-1.5 rounded-lg transition-colors <?= ($statusFilter === 'cancelled') ? 'bg-rose-500/20 text-rose-300 font-bold border border-rose-500/40' : 'text-slate-400 hover:text-white' ?>">
        Cancelled (<?= $counts['cancelled'] ?? 0 ?>)
      </a>
    </div>

    <!-- Search Form -->
    <form action="orders.php" method="GET" class="w-full sm:w-80 flex items-center">
      <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
      <div class="relative w-full">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
          <i data-lucide="search" class="w-4 h-4"></i>
        </span>
        <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search name, email, WhatsApp, plan..."
          class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400">
      </div>
    </form>
  </div>

  <!-- ORDERS TABLE -->
  <div class="glass-card rounded-2xl overflow-hidden border border-slate-800">
    <?php if (empty($orders)): ?>
      <div class="py-16 text-center text-slate-400 text-xs">
        <i data-lucide="shopping-cart" class="w-10 h-10 mx-auto mb-3 text-slate-600"></i>
        <p class="font-bold text-white text-sm">No customer orders found</p>
        <p class="mt-1">When users complete checkout on the website, orders will appear here automatically.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-slate-900/90 text-slate-400 font-mono text-[10px] uppercase border-b border-slate-800">
              <th class="p-4">Order ID</th>
              <th class="p-4">Customer Details</th>
              <th class="p-4">Package</th>
              <th class="p-4">Amount</th>
              <th class="p-4">Payment Method</th>
              <th class="p-4">Status</th>
              <th class="p-4">Date</th>
              <th class="p-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            <?php foreach ($orders as $o): ?>
              <tr class="hover:bg-slate-800/40 transition-colors">
                <td class="p-4 font-mono font-bold text-cyan-400">#<?= $o['id'] ?></td>
                <td class="p-4">
                  <?php if (!empty($o['customer_name'])): ?>
                    <div class="font-bold text-white text-sm"><?= htmlspecialchars($o['customer_name']) ?></div>
                  <?php endif; ?>
                  <div class="font-mono text-cyan-300 text-xs"><?= htmlspecialchars($o['email']) ?></div>
                  <?php if (!empty($o['whatsapp'])): ?>
                    <div class="mt-1 flex items-center gap-1 text-[11px] text-emerald-400 font-mono">
                      <i data-lucide="phone" class="w-3 h-3"></i>
                      <span><?= htmlspecialchars($o['whatsapp']) ?></span>
                    </div>
                  <?php endif; ?>
                  <div class="text-[10px] text-slate-500 mt-1">
                    Platform: <?= htmlspecialchars($o['device']) ?>
                    <?php if (!empty($o['country'])): ?>
                      • <span class="text-cyan-400 font-mono font-bold">📍 <?= htmlspecialchars($o['country']) ?></span>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="p-4">
                  <div class="font-semibold text-white"><?= htmlspecialchars($o['plan_name']) ?></div>
                  <span class="text-[10px] text-slate-400 font-mono"><?= $o['connections'] ?> Connection(s)</span>
                </td>
                <td class="p-4">
                  <div class="font-mono font-bold text-emerald-400 text-sm">
                    <?= htmlspecialchars($o['currency']) ?> <?= number_format($o['amount'], ($o['currency'] === 'PKR' ? 0 : 2)) ?>
                  </div>
                  <?php if (!empty($o['coupon_used'])): ?>
                    <span class="px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-300 font-mono text-[9px]">Promo: <?= htmlspecialchars($o['coupon_used']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="p-4 text-slate-300">
                  <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-[11px] font-mono">
                    <?= htmlspecialchars($o['payment_channel']) ?>
                  </span>
                </td>
                <td class="p-4">
                  <?php if ($o['status'] === 'Paid'): ?>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[10px] font-bold flex items-center gap-1 w-max">
                      <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Paid
                    </span>
                  <?php elseif ($o['status'] === 'New'): ?>
                    <span class="px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 text-[10px] font-bold">New</span>
                  <?php elseif ($o['status'] === 'Active'): ?>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[10px] font-bold">Active</span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[10px]"><?= htmlspecialchars($o['status']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="p-4 text-slate-400 text-[11px]">
                  <?= date('M j, Y • H:i', strtotime($o['created_at'])) ?>
                </td>
                <td class="p-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    
                    <?php if (!empty($o['whatsapp'])): 
                      $cleanPhone = preg_replace('/[^0-9]/', '', $o['whatsapp']);
                      $waMsg = urlencode("Hello " . ($o['customer_name'] ?: 'Customer') . ", thank you for your order #" . $o['id'] . " (" . $o['plan_name'] . ") at LIVETVMARKET! Here are your activation credentials and setup details.");
                    ?>
                      <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $waMsg ?>" target="_blank" title="Contact customer on WhatsApp"
                        class="p-2 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 transition-colors">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                      </a>
                    <?php endif; ?>
                    
                    <form action="orders.php" method="POST" class="inline">
                      <input type="hidden" name="action" value="set_status">
                      <input type="hidden" name="id" value="<?= $o['id'] ?>">
                      <?php if ($o['status'] !== 'Active'): ?>
                        <input type="hidden" name="status" value="Active">
                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500 hover:text-slate-950 text-emerald-300 border border-emerald-500/40 font-bold text-[10px] transition-colors" title="Activate account">
                          Activate
                        </button>
                      <?php else: ?>
                        <input type="hidden" name="status" value="Cancelled">
                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 text-[10px] transition-colors" title="Mark as cancelled">
                          Cancel
                        </button>
                      <?php endif; ?>
                    </form>

                    <form action="orders.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete order #<?= $o['id'] ?>?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $o['id'] ?>">
                      <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors" title="Delete order">
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

<?php
require_once __DIR__ . '/footer.php';
?>
