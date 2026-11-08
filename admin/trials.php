<?php
/**
 * LIVETVMARKET - Admin Free Trial Leads Manager
 * View and process visitor 24h trial requests from MySQL DB 'iptv-website'.
 */
$adminTitle = 'Free Trial Leads';
$adminPage = 'trials';

require_once __DIR__ . '/header.php';

$successMsg = '';
$errorMsg = '';

// Handle Status Updates or Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $leadId = (int)$_POST['id'];

    if ($action === 'set_status') {
        $newStatus = $_POST['status'] ?? 'Pending';
        try {
            $stmt = $pdo->prepare("UPDATE `trial_leads` SET `status` = ? WHERE `id` = ?");
            $stmt->execute([$newStatus, $leadId]);
            $successMsg = "Lead #{$leadId} marked as '{$newStatus}'.";
        } catch (Exception $e) {
            $errorMsg = "Error updating lead: " . $e->getMessage();
        }
    } else if ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM `trial_leads` WHERE `id` = ?");
            $stmt->execute([$leadId]);
            $successMsg = "Lead #{$leadId} deleted.";
        } catch (Exception $e) {
            $errorMsg = "Error deleting lead: " . $e->getMessage();
        }
    }
}

// Filter query
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM `trial_leads` WHERE 1=1";
$params = [];

if ($statusFilter !== 'all') {
    $sql .= " AND `status` = ?";
    $params[] = ucfirst($statusFilter);
}

if (!empty($searchQuery)) {
    $sql .= " AND (`full_name` LIKE ? OR `email` LIKE ? OR `whatsapp` LIKE ?)";
    $term = "%{$searchQuery}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY `id` DESC";

$leads = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $leads = $stmt->fetchAll();
} catch (Exception $e) {
    $errorMsg = "Database error: " . $e->getMessage();
}

// Counts for filters
$counts = ['all' => 0, 'pending' => 0, 'sent' => 0];
try {
    $counts['all'] = (int)$pdo->query("SELECT COUNT(*) FROM `trial_leads`")->fetchColumn();
    $counts['pending'] = (int)$pdo->query("SELECT COUNT(*) FROM `trial_leads` WHERE `status` = 'Pending'")->fetchColumn();
    $counts['sent'] = (int)$pdo->query("SELECT COUNT(*) FROM `trial_leads` WHERE `status` = 'Sent'")->fetchColumn();
} catch (Exception $e) {}
?>

<div class="max-w-6xl">
  
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-white">Free Trial Leads Management</h1>
      <p class="text-xs text-slate-400 mt-1">Review visitor requests for 24-hour test lines, contact leads via WhatsApp, and dispatch credentials.</p>
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
    <div class="flex items-center gap-2 bg-slate-900 p-1 rounded-xl border border-slate-800 text-xs font-semibold self-start sm:self-auto">
      <a href="trials.php?status=all" class="px-3 py-1.5 rounded-lg transition-colors <?= ($statusFilter === 'all') ? 'bg-cyan-500/20 text-cyan-400 font-bold border border-cyan-500/40' : 'text-slate-400 hover:text-white' ?>">
        All Leads (<?= $counts['all'] ?>)
      </a>
      <a href="trials.php?status=pending" class="px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 <?= ($statusFilter === 'pending') ? 'bg-amber-500/20 text-amber-300 font-bold border border-amber-500/40' : 'text-slate-400 hover:text-white' ?>">
        <span>Pending</span>
        <span class="px-1.5 py-0.2 rounded bg-amber-500/30 font-mono text-[10px]"><?= $counts['pending'] ?></span>
      </a>
      <a href="trials.php?status=sent" class="px-3 py-1.5 rounded-lg transition-colors <?= ($statusFilter === 'sent') ? 'bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-500/40' : 'text-slate-400 hover:text-white' ?>">
        Sent (<?= $counts['sent'] ?>)
      </a>
    </div>

    <!-- Search Form -->
    <form action="trials.php" method="GET" class="w-full sm:w-72 flex items-center">
      <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
      <div class="relative w-full">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
          <i data-lucide="search" class="w-4 h-4"></i>
        </span>
        <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search name, email, WhatsApp..."
          class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400">
      </div>
    </form>
  </div>

  <!-- LEADS TABLE -->
  <div class="glass-card rounded-2xl overflow-hidden border border-slate-800">
    <?php if (empty($leads)): ?>
      <div class="py-16 text-center text-slate-400 text-xs">
        <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-3 text-slate-600"></i>
        <p class="font-bold text-white text-sm">No trial requests found</p>
        <p class="mt-1">When users submit the 24H Trial form on the site, their entries will show up here.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-slate-900/90 text-slate-400 font-mono text-[10px] uppercase border-b border-slate-800">
              <th class="p-4">ID</th>
              <th class="p-4">Lead Contact Details</th>
              <th class="p-4">Status</th>
              <th class="p-4">Submitted At</th>
              <th class="p-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            <?php foreach ($leads as $l): ?>
              <tr class="hover:bg-slate-800/40 transition-colors">
                <td class="p-4 font-mono font-bold text-slate-500">#<?= $l['id'] ?></td>
                <td class="p-4">
                  <div class="font-bold text-white text-sm"><?= htmlspecialchars($l['full_name']) ?></div>
                  <div class="text-slate-400 font-mono mt-0.5"><?= htmlspecialchars($l['email']) ?></div>
                  <?php if (!empty($l['whatsapp'])): ?>
                    <div class="mt-1 flex items-center gap-1 text-[11px] text-emerald-400">
                      <i data-lucide="phone" class="w-3 h-3"></i>
                      <span><?= htmlspecialchars($l['whatsapp']) ?></span>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="p-4">
                  <?php if ($l['status'] === 'Pending'): ?>
                    <span class="px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40 text-[10px] font-bold inline-flex items-center gap-1">
                      <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                      Pending
                    </span>
                  <?php elseif ($l['status'] === 'Sent'): ?>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[10px] font-bold inline-flex items-center gap-1">
                      <i data-lucide="check" class="w-3 h-3"></i> Sent
                    </span>
                  <?php else: ?>
                    <span class="px-2.5 py-1 rounded-full bg-slate-800 text-slate-400 text-[10px] font-bold"><?= htmlspecialchars($l['status']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="p-4 text-slate-400 text-[11px]">
                  <?= date('M j, Y • H:i', strtotime($l['created_at'])) ?>
                </td>
                <td class="p-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    
                    <?php if (!empty($l['whatsapp'])): 
                      $cleanPhone = preg_replace('/[^0-9]/', '', $l['whatsapp']);
                      $waMsg = urlencode("Hello " . $l['full_name'] . ", here are your LIVETVMARKET 24H Trial credentials! Enjoy your test.");
                    ?>
                      <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $waMsg ?>" target="_blank" title="Contact on WhatsApp"
                        class="p-2 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950 transition-colors">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                      </a>
                    <?php endif; ?>

                    <!-- Mark as Sent / Pending Toggle Form -->
                    <form action="trials.php" method="POST" class="inline">
                      <input type="hidden" name="action" value="set_status">
                      <input type="hidden" name="id" value="<?= $l['id'] ?>">
                      <?php if ($l['status'] === 'Pending'): ?>
                        <input type="hidden" name="status" value="Sent">
                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-cyan-500/20 hover:bg-cyan-500 hover:text-slate-950 text-cyan-300 border border-cyan-500/40 font-bold text-[10px] transition-colors" title="Mark line as sent">
                          Mark Sent
                        </button>
                      <?php else: ?>
                        <input type="hidden" name="status" value="Pending">
                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 text-[10px] transition-colors" title="Revert to pending">
                          Mark Pending
                        </button>
                      <?php endif; ?>
                    </form>

                    <!-- Delete Form -->
                    <form action="trials.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete lead #<?= $l['id'] ?>?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $l['id'] ?>">
                      <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors" title="Delete lead">
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
