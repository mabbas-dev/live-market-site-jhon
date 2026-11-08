<?php
/**
 * LIVETVMARKET - Admin: Digital Store Products Management
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/store.php';

$curAdminPage = 'store_products';
$successMsg = '';
$errorMsg = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Toggle Store Enable/Disable
    if ($action === 'toggle_store') {
        $newState = (getSetting('store_enabled', '1') === '1') ? '0' : '1';
        updateSetting('store_enabled', $newState);
        $successMsg = ($newState === '1') ? "Digital Store has been ENABLED across the website." : "Digital Store has been DISABLED across the website.";
    }

    // 2. Toggle Stock
    if ($action === 'toggle_stock') {
        $prodId = (int)($_POST['product_id'] ?? 0);
        $curStatus = $_POST['cur_status'] ?? 'in_stock';
        $newStatus = ($curStatus === 'in_stock') ? 'out_of_stock' : 'in_stock';
        $pdo->prepare("UPDATE `store_products` SET `status` = ? WHERE `id` = ?")->execute([$newStatus, $prodId]);
        $successMsg = "Product stock status updated to: " . strtoupper($newStatus);
    }

    // 3. Toggle Featured
    if ($action === 'toggle_featured') {
        $prodId = (int)($_POST['product_id'] ?? 0);
        $curFeat = (int)($_POST['cur_featured'] ?? 0);
        $newFeat = ($curFeat === 1) ? 0 : 1;
        $pdo->prepare("UPDATE `store_products` SET `is_featured` = ? WHERE `id` = ?")->execute([$newFeat, $prodId]);
        $successMsg = "Featured status updated.";
    }

    // 4. Delete Product
    if ($action === 'delete_product') {
        $prodId = (int)($_POST['product_id'] ?? 0);
        $pdo->prepare("DELETE FROM `store_product_variations` WHERE `product_id` = ?")->execute([$prodId]);
        $pdo->prepare("DELETE FROM `store_products` WHERE `id` = ?")->execute([$prodId]);
        $successMsg = "Product and its variations were deleted successfully.";
    }
}

$isStoreOn = isStoreEnabled();
$categories = getStoreCategories(false);

// Filter params
$selectedCat = (int)($_GET['category_id'] ?? 0);
$searchQ = trim($_GET['q'] ?? '');

$sql = "SELECT p.*, c.name AS category_name 
        FROM `store_products` p 
        LEFT JOIN `store_categories` c ON p.category_id = c.id 
        WHERE 1=1";
$params = [];

if ($selectedCat > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $selectedCat;
}
if (!empty($searchQ)) {
    $sql .= " AND (p.name LIKE ? OR p.slug LIKE ?)";
    $params[] = "%{$searchQ}%";
    $params[] = "%{$searchQ}%";
}

$sql .= " ORDER BY p.sort_order ASC, p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load variations count and price range for each product
foreach ($products as &$p) {
    $p['variations'] = getProductVariations((int)$p['id']);
}
unset($p);

include __DIR__ . '/header.php';
?>

  <div class="space-y-6">

    <!-- Top Bar with Store Enable Status -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl border <?= $isStoreOn ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-rose-500/10 border-rose-500/30 text-rose-300' ?>">
      <div class="flex items-center gap-3.5">
        <div class="w-10 h-10 rounded-xl <?= $isStoreOn ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' ?> flex items-center justify-center font-bold">
          <i data-lucide="<?= $isStoreOn ? 'shopping-bag' : 'store' ?>" class="w-5 h-5"></i>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="font-heading font-black text-base text-white">STORE ENABLE STATUS:</h2>
            <span class="px-2.5 py-0.5 rounded-full font-mono text-xs font-bold <?= $isStoreOn ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40' ?>">
              <?= $isStoreOn ? '● ACTIVE (ONLINE)' : '○ DISABLED (OFFLINE)' ?>
            </span>
          </div>
          <p class="text-xs text-slate-400 mt-0.5">
            <?= $isStoreOn ? 'Store is visible on Homepage and Shop page. Direct product URLs are live.' : 'Digital store products and URLs are completely hidden from frontend visitors.' ?>
          </p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <form method="POST">
          <input type="hidden" name="action" value="toggle_store">
          <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer <?= $isStoreOn ? 'bg-rose-600 hover:bg-rose-500 text-white shadow-md' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-md' ?>">
            <?= $isStoreOn ? 'Turn Store OFF' : 'Turn Store ON' ?>
          </button>
        </form>

        <a href="store_product_edit.php" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20 transition-all">
          <i data-lucide="plus" class="w-4 h-4"></i>
          <span>Add New Product</span>
        </a>
      </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($successMsg)): ?>
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-2">
      <i data-lucide="check-circle" class="w-4 h-4"></i>
      <span><?= htmlspecialchars($successMsg) ?></span>
    </div>
    <?php endif; ?>

    <!-- Filter & Search Bar -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-2">
        <i data-lucide="package" class="w-4 h-4 text-cyan-400"></i>
        <h3 class="font-heading font-bold text-sm text-white">Digital Services Products (<?= count($products) ?>)</h3>
      </div>

      <form method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
        <select name="category_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-slate-200 focus:outline-none focus:border-cyan-500">
          <option value="0">All Categories</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= ($selectedCat == $cat['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>

        <div class="relative flex-1 md:w-56">
          <input type="text" name="q" value="<?= htmlspecialchars($searchQ) ?>" placeholder="Search product..." class="w-full px-3 py-2 pl-8 rounded-xl bg-slate-800 border border-slate-700 text-xs text-slate-200 focus:outline-none focus:border-cyan-500">
          <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
        </div>

        <?php if ($selectedCat > 0 || !empty($searchQ)): ?>
        <a href="store_products.php" class="px-2.5 py-2 text-xs text-slate-400 hover:text-white">Clear</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Products Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-800/60 text-slate-400 font-mono text-[11px] uppercase tracking-wider border-b border-slate-800">
            <tr>
              <th class="p-4">Product</th>
              <th class="p-4">Category</th>
              <th class="p-4">Variations & Pricing</th>
              <th class="p-4 text-center">Stock Status</th>
              <th class="p-4 text-center">Featured</th>
              <th class="p-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800 text-slate-300">
            <?php if (empty($products)): ?>
            <tr>
              <td colspan="6" class="p-8 text-center text-slate-500">
                No digital products found. Click "Add New Product" to create your first digital subscription service.
              </td>
            </tr>
            <?php else: ?>
            <?php foreach ($products as $prod): 
              $isOut = ($prod['status'] === 'out_of_stock');
              $vars = $prod['variations'] ?? [];
              $prices = array_column($vars, 'price');
              $minP = !empty($prices) ? min($prices) : (float)$prod['starting_price'];
              $maxP = !empty($prices) ? max($prices) : (float)$prod['starting_price'];
            ?>
            <tr class="hover:bg-slate-800/30 transition-colors">
              <!-- Product Image & Name -->
              <td class="p-4">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 p-1 flex-shrink-0 flex items-center justify-center overflow-hidden">
                    <img src="<?= htmlspecialchars($prod['image'] ?: '../assets/images/placeholder-product.png') ?>" alt="" class="max-h-full max-w-full object-contain">
                  </div>
                  <div>
                    <div class="font-bold text-white text-sm flex items-center gap-2">
                      <span><?= htmlspecialchars($prod['name']) ?></span>
                      <?php if (!empty($prod['badge'])): ?>
                      <span class="px-2 py-0.2 rounded font-mono text-[10px] font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30"><?= htmlspecialchars($prod['badge']) ?></span>
                      <?php endif; ?>
                    </div>
                    <div class="text-[11px] text-slate-500 font-mono flex items-center gap-2 mt-0.5">
                      <span>/product/<?= htmlspecialchars($prod['slug']) ?></span>
                      <a href="../product/<?= urlencode($prod['slug']) ?>" target="_blank" class="text-cyan-400 hover:underline">View ↗</a>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Category -->
              <td class="p-4">
                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                  <?= htmlspecialchars($prod['category_name'] ?: 'Uncategorized') ?>
                </span>
              </td>

              <!-- Variations & Price Range -->
              <td class="p-4 font-mono">
                <div class="font-bold text-white">
                  $<?= number_format($minP, 2) ?><?= ($maxP > $minP) ? (' — $' . number_format($maxP, 2)) : '' ?>
                </div>
                <div class="text-[11px] text-slate-400 font-sans">
                  <?= count($vars) ?> duration plan<?= count($vars) !== 1 ? 's' : '' ?>
                </div>
              </td>

              <!-- Stock Status & Quick Toggle -->
              <td class="p-4 text-center">
                <form method="POST" class="inline-block">
                  <input type="hidden" name="action" value="toggle_stock">
                  <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                  <input type="hidden" name="cur_status" value="<?= $prod['status'] ?>">
                  <button type="submit" class="px-2.5 py-1 rounded-full text-[11px] font-bold transition-all cursor-pointer flex items-center gap-1.5 <?= $isOut ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40 hover:bg-rose-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-500/30' ?>" title="Click to toggle Stock Status">
                    <span class="w-1.5 h-1.5 rounded-full <?= $isOut ? 'bg-rose-500' : 'bg-emerald-500' ?>"></span>
                    <span><?= $isOut ? 'Out of Stock' : 'In Stock' ?></span>
                  </button>
                </form>
              </td>

              <!-- Featured Flag -->
              <td class="p-4 text-center">
                <form method="POST" class="inline-block">
                  <input type="hidden" name="action" value="toggle_featured">
                  <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                  <input type="hidden" name="cur_featured" value="<?= (int)$prod['is_featured'] ?>">
                  <button type="submit" class="font-bold text-xs cursor-pointer <?= !empty($prod['is_featured']) ? 'text-amber-400' : 'text-slate-600 hover:text-slate-400' ?>" title="Click to toggle homepage featured">
                    <?= !empty($prod['is_featured']) ? '★ Featured' : '☆ Standard' ?>
                  </button>
                </form>
              </td>

              <!-- Actions -->
              <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <a href="store_product_edit.php?id=<?= $prod['id'] ?>" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-400 transition-colors" title="Edit Product">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                  </a>

                  <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product and all its variations?');" class="inline-block">
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                    <button type="submit" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-500/20 text-rose-400 transition-colors cursor-pointer" title="Delete Product">
                      <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

<?php include __DIR__ . '/footer.php'; ?>

