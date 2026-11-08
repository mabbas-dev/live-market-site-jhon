<?php
/**
 * LIVETVMARKET - Admin Customer Orders Manager
 * View and process subscription checkout orders from MySQL DB 'iptv-website'.
 */
$adminTitle = 'Customer Orders';
$adminPage = 'orders';

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/notifications.php';

$successMsg = '';
$errorMsg = '';

// Handle Status Updates, Email Dispatch, or Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $orderId = (int)$_POST['id'];

    if ($action === 'mark_paid') {
        try {
            $stmt = $pdo->prepare("UPDATE `orders` SET `status` = 'Paid' WHERE `id` = ?");
            $stmt->execute([$orderId]);

            // Dispatch instant Pushover notification and email credentials
            notifyOrderPaid($orderId);

            $successMsg = "Order #{$orderId} has been confirmed as PAID! Customer activation email and Pushover mobile alert dispatched.";
        } catch (Exception $e) {
            $errorMsg = "Error updating order: " . $e->getMessage();
        }
    } else if ($action === 'send_email') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = ? LIMIT 1");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if ($order && !empty($order['email'])) {
                $subject = "Streaming Activation Credentials — Order #LTV-{$order['id']}";
                $html = buildCustomerActivationEmail($order);
                $res = sendSMTPEmail($order['email'], $order['customer_name'] ?: 'Customer', $subject, $html);

                if ($res['success']) {
                    $successMsg = "Full activation credentials email sent successfully to '{$order['email']}' via SMTP!";
                } else {
                    $errorMsg = "Email Delivery Error: " . ($res['error'] ?? 'SMTP server rejected message. Check SMTP settings.');
                }
            } else {
                $errorMsg = "Customer has no valid email address on file.";
            }
        } catch (Exception $e) {
            $errorMsg = "Error preparing email: " . $e->getMessage();
        }
    } else if ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM `orders` WHERE `id` = ?");
            $stmt->execute([$orderId]);
            $successMsg = "Order #{$orderId} deleted successfully.";
        } catch (Exception $e) {
            $errorMsg = "Error deleting order: " . $e->getMessage();
        }
    }
}

// Search and filter
$statusFilter = $_GET['status'] ?? 'all';
$typeFilter = $_GET['type'] ?? 'all';
$searchQuery = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM `orders` WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND `status` = ?";
    $params[] = ucfirst($statusFilter);
}

if ($typeFilter === 'store') {
    $sql .= " AND `order_type` = 'store'";
} elseif ($typeFilter === 'iptv') {
    $sql .= " AND (`order_type` IS NULL OR `order_type` != 'store')";
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
    // Confirmed Paid revenue only
    $totalRevenueSum = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM `orders` WHERE `status` = 'Paid'")->fetchColumn();

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
  
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-white">Customer Orders Management</h1>
      <p class="text-xs text-slate-400 mt-1">Review subscription checkout transactions, deliver credentials via WhatsApp & SMTP email, and verify payment channels.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="notifications.php" class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-cyan-500 text-xs font-semibold text-cyan-400 flex items-center gap-1.5 transition-colors">
        <i data-lucide="bell" class="w-3.5 h-3.5"></i>
        <span>Pushover & SMTP Settings</span>
      </a>
    </div>
  </div>

  <!-- 4 HIGH-CLARITY METRIC CARDS -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="glass-card rounded-2xl p-4 border border-slate-800">
      <div class="flex items-center justify-between text-slate-400 text-xs mb-1">
        <span>Total Orders Placed</span>
        <i data-lucide="shopping-bag" class="w-4 h-4 text-cyan-400"></i>
      </div>
      <div class="text-2xl font-black text-white font-mono"><?= $counts['all'] ?></div>
      <div class="text-[10px] text-slate-500 mt-1">All checkout submissions</div>
    </div>

    <div class="glass-card rounded-2xl p-4 border border-emerald-500/30 bg-emerald-500/5">
      <div class="flex items-center justify-between text-emerald-400 text-xs mb-1">
        <span class="font-bold">Paid Customers</span>
        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
      </div>
      <div class="text-2xl font-black text-emerald-400 font-mono"><?= $counts['paid'] ?></div>
      <div class="text-[10px] text-emerald-300/60 mt-1">Confirmed payments</div>
    </div>

    <div class="glass-card rounded-2xl p-4 border border-slate-800">
      <div class="flex items-center justify-between text-slate-400 text-xs mb-1">
        <span>Confirmed Revenue</span>
        <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-emerald-400"></i>
      </div>
      <div class="text-2xl font-black text-white font-mono">$<?= number_format($totalRevenueSum, 2) ?></div>
      <div class="text-[10px] text-slate-500 mt-1">Strictly from paid orders</div>
    </div>

    <div class="glass-card rounded-2xl p-4 border border-slate-800">
      <div class="flex items-center justify-between text-slate-400 text-xs mb-1">
        <span>Pending Payment</span>
        <i data-lucide="clock" class="w-4 h-4 text-amber-400"></i>
      </div>
      <div class="text-2xl font-black text-amber-400 font-mono"><?= $counts['new'] ?></div>
      <div class="text-[10px] text-slate-500 mt-1">Awaiting customer payment</div>
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
                  <?php if (!empty($o['country'])): ?>
                    <div class="text-[10px] text-slate-500 mt-1">
                      <span class="text-cyan-400 font-mono font-bold">📍 <?= htmlspecialchars($o['country']) ?></span>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="p-4">
                  <?php if (!empty($o['order_type']) && $o['order_type'] === 'store'): ?>
                    <span class="px-2 py-0.5 rounded font-mono text-[9px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30 mb-1 inline-block">Digital Service</span>
                  <?php else: ?>
                    <span class="px-2 py-0.5 rounded font-mono text-[9px] font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 mb-1 inline-block">IPTV Plan</span>
                  <?php endif; ?>
                  <div class="font-semibold text-white"><?= htmlspecialchars($o['plan_name']) ?></div>
                  <?php if (empty($o['order_type']) || $o['order_type'] !== 'store'): ?>
                    <span class="text-[10px] text-slate-400 font-mono"><?= $o['connections'] ?> Connection(s)</span>
                  <?php endif; ?>
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
                  <div class="flex items-center justify-end gap-1.5">
                    
                    <?php 
                    if (!empty($o['whatsapp'])): 
                      $cleanPhone = preg_replace('/[^0-9]/', '', $o['whatsapp']);
                      $custName = $o['customer_name'] ?: 'Customer';
                      $simpleMsg = "Hello {$custName}, we received your order #{$o['id']} ({$o['plan_name']}) at LIVETVMARKET. How can we assist you with your setup today?";
                    ?>
                      <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= urlencode($simpleMsg) ?>" target="_blank" title="Contact customer on WhatsApp"
                        class="p-2 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 transition-colors">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                      </a>
                    <?php endif; ?>

                    <!-- Mark as Paid Action (for pending / manual orders) -->
                    <?php if ($o['status'] !== 'Paid'): ?>
                      <form action="orders.php" method="POST" class="inline" onsubmit="return confirm('Confirm order #<?= $o['id'] ?> as Paid? This will trigger credentials delivery and Pushover alert.');">
                        <input type="hidden" name="action" value="mark_paid">
                        <input type="hidden" name="id" value="<?= $o['id'] ?>">
                        <button type="submit" class="p-2 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 transition-colors" title="Confirm Payment (Mark as Paid)">
                          <i data-lucide="check" class="w-4 h-4"></i>
                        </button>
                      </form>
                    <?php endif; ?>

                    <!-- Delete Order -->
                    <form action="orders.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete order #<?= $o['id'] ?>?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $o['id'] ?>">
                      <button type="submit" class="p-2 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors" title="Delete order">
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
