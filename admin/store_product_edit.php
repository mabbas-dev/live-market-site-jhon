<?php
/**
 * LIVETVMARKET - Admin: Add / Edit Digital Store Product
 * Includes dynamic variations manager, image file upload, and SEO slugs.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/store.php';

$curAdminPage = 'store_product_edit';
$errorMsg = '';
$successMsg = '';

$prodId = (int)($_GET['id'] ?? 0);
$isEditing = ($prodId > 0);

$product = [
    'id'             => 0,
    'name'           => '',
    'slug'           => '',
    'category_id'    => 0,
    'badge'          => 'Instant Delivery',
    'starting_price' => '2.99',
    'status'         => 'in_stock',
    'is_featured'    => 0,
    'image'          => '',
    'short_desc'     => '',
    'features'       => '',
    'description'    => '',
    'sort_order'     => 0
];
$variations = [];

if ($isEditing) {
    $existing = getStoreProductById($prodId);
    if ($existing) {
        $product = $existing;
        $variations = $product['variations'] ?? [];
    } else {
        header('Location: store_products.php');
        exit;
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $slug          = trim($_POST['slug'] ?? '');
    $categoryId    = (int)($_POST['category_id'] ?? 0);
    $badge         = trim($_POST['badge'] ?? '');
    $startingPrice = floatval($_POST['starting_price'] ?? 0);
    $status        = trim($_POST['status'] ?? 'in_stock');
    $isFeatured    = isset($_POST['is_featured']) ? 1 : 0;
    $imageUrl      = trim($_POST['image_url'] ?? '');
    $shortDesc     = trim($_POST['short_desc'] ?? '');
    $features      = trim($_POST['features'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $sortOrder     = (int)($_POST['sort_order'] ?? 0);

    // Auto slug if empty
    if (empty($slug) && !empty($name)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }

    if (empty($name) || empty($slug)) {
        $errorMsg = "Product Name and URL Slug are required.";
    } else {
        // Handle file upload
        $imagePath = $product['image'];
        if (!empty($imageUrl)) {
            $imagePath = $imageUrl;
        }

        if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'])) {
                $fileName = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
                    $imagePath = 'uploads/products/' . $fileName;
                }
            } else {
                $errorMsg = "Invalid image file format. Supported: JPG, PNG, WEBP, SVG.";
            }
        }

        if (empty($errorMsg)) {
            try {
                if ($isEditing) {
                    $stmt = $pdo->prepare("UPDATE `store_products` SET 
                        `category_id` = ?, 
                        `name` = ?, 
                        `slug` = ?, 
                        `badge` = ?, 
                        `starting_price` = ?, 
                        `status` = ?, 
                        `is_featured` = ?, 
                        `image` = ?, 
                        `short_desc` = ?, 
                        `features` = ?, 
                        `description` = ?, 
                        `sort_order` = ? 
                        WHERE `id` = ?");
                    $stmt->execute([
                        $categoryId, $name, $slug, $badge, $startingPrice,
                        $status, $isFeatured, $imagePath, $shortDesc,
                        $features, $description, $sortOrder, $prodId
                    ]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO `store_products` (
                        `category_id`, `name`, `slug`, `badge`, `starting_price`, 
                        `status`, `is_featured`, `image`, `short_desc`, 
                        `features`, `description`, `sort_order`
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $categoryId, $name, $slug, $badge, $startingPrice,
                        $status, $isFeatured, $imagePath, $shortDesc,
                        $features, $description, $sortOrder
                    ]);
                    $prodId = (int)$pdo->lastInsertId();
                    $isEditing = true;
                }

                // Handle Variations Sync
                $varNames      = $_POST['var_name'] ?? [];
                $varDurations  = $_POST['var_duration'] ?? [];
                $varPrices     = $_POST['var_price'] ?? [];
                $varOrigPrices = $_POST['var_orig_price'] ?? [];
                $varPerMonths  = $_POST['var_per_month'] ?? [];
                $varStatuses   = $_POST['var_status'] ?? [];

                // Delete existing variations for clean insert
                $pdo->prepare("DELETE FROM `store_product_variations` WHERE `product_id` = ?")->execute([$prodId]);

                $varInsert = $pdo->prepare("INSERT INTO `store_product_variations` (
                    `product_id`, `name`, `duration_label`, `price`, 
                    `original_price`, `per_month_display`, `status`, `sort_order`
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                $minVarPrice = 999999;
                for ($i = 0; $i < count($varNames); $i++) {
                    $vName = trim($varNames[$i] ?? '');
                    if (empty($vName)) continue;

                    $vDur    = trim($varDurations[$i] ?? '1 Month');
                    $vPrice  = floatval($varPrices[$i] ?? 0);
                    $vOrig   = !empty($varOrigPrices[$i]) ? floatval($varOrigPrices[$i]) : null;
                    $vPerM   = trim($varPerMonths[$i] ?? '');
                    $vStatus = trim($varStatuses[$i] ?? 'in_stock');

                    if ($vPrice < $minVarPrice) $minVarPrice = $vPrice;

                    $varInsert->execute([$prodId, $vName, $vDur, $vPrice, $vOrig, $vPerM, $vStatus, $i]);
                }

                // Update starting price if variations were added
                if ($minVarPrice < 999999 && $startingPrice <= 0) {
                    $pdo->prepare("UPDATE `store_products` SET `starting_price` = ? WHERE `id` = ?")->execute([$minVarPrice, $prodId]);
                }

                $successMsg = "Product saved successfully!";
                $product = getStoreProductById($prodId);
                $variations = $product['variations'] ?? [];
            } catch (Exception $e) {
                $errorMsg = "Database Error: " . $e->getMessage();
            }
        }
    }
}

$categories = getStoreCategories(false);

include __DIR__ . '/header.php';
?>

  <div class="space-y-6 max-w-5xl">

    <!-- Top Action Header -->
    <div class="flex items-center justify-between">
      <div>
        <a href="store_products.php" class="text-xs text-slate-400 hover:text-cyan-400 flex items-center gap-1 mb-1">
          <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Products List
        </a>
        <h2 class="font-heading font-black text-xl text-white">
          <?= $isEditing ? ('Edit Product: ' . htmlspecialchars($product['name'])) : 'Add New Digital Service' ?>
        </h2>
      </div>

      <?php if ($isEditing && !empty($product['slug'])): ?>
      <a href="../product/<?= urlencode($product['slug']) ?>" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-400 text-xs font-bold flex items-center gap-1.5 transition-colors">
        <span>Preview on Live Site</span>
        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
      </a>
      <?php endif; ?>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($successMsg)): ?>
    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold flex items-center gap-2">
      <i data-lucide="check-circle" class="w-4 h-4"></i>
      <span><?= htmlspecialchars($successMsg) ?></span>
    </div>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold flex items-center gap-2">
      <i data-lucide="alert-circle" class="w-4 h-4"></i>
      <span><?= htmlspecialchars($errorMsg) ?></span>
    </div>
    <?php endif; ?>

    <!-- Main Edit Form -->
    <form method="POST" enctype="multipart/form-data" class="space-y-6">

      <!-- Section 1: Basic Information -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <h3 class="font-heading font-bold text-sm text-white flex items-center gap-2 border-b border-slate-800 pb-3">
          <i data-lucide="info" class="w-4 h-4 text-cyan-400"></i>
          <span>1. Basic Product Information</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">Product Title *</label>
            <input type="text" name="name" id="prod-name-input" required value="<?= htmlspecialchars($product['name']) ?>" placeholder="e.g. Netflix Premium (4K Ultra HD)" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">URL Slug * (Clean Link)</label>
            <div class="flex items-center">
              <span class="px-3 py-2.5 bg-slate-800/80 border border-r-0 border-slate-700 text-slate-500 text-xs rounded-l-xl font-mono">/product/</span>
              <input type="text" name="slug" id="prod-slug-input" required value="<?= htmlspecialchars($product['slug']) ?>" placeholder="netflix-premium" class="w-full px-3 py-2.5 rounded-r-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">Category</label>
            <select name="category_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
              <option value="0">-- Select Category --</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">Badge Tag</label>
            <input type="text" name="badge" value="<?= htmlspecialchars($product['badge']) ?>" placeholder="e.g. Best Seller ★, Save 70%" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">Stock Status</label>
            <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
              <option value="in_stock" <?= ($product['status'] === 'in_stock') ? 'selected' : '' ?>>In Stock (Available)</option>
              <option value="out_of_stock" <?= ($product['status'] === 'out_of_stock') ? 'selected' : '' ?>>Out of Stock (Disabled)</option>
              <option value="draft" <?= ($product['status'] === 'draft') ? 'selected' : '' ?>>Draft (Hidden)</option>
            </select>
          </div>
        </div>

        <div class="flex items-center gap-6 pt-2">
          <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-300">
            <input type="checkbox" name="is_featured" value="1" <?= !empty($product['is_featured']) ? 'checked' : '' ?> class="w-4 h-4 rounded text-cyan-500 bg-slate-800 border-slate-700">
            <span>Show as Featured Product on Homepage</span>
          </label>
        </div>
      </div>

      <!-- Section 2: Product Image -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <h3 class="font-heading font-bold text-sm text-white flex items-center gap-2 border-b border-slate-800 pb-3">
          <i data-lucide="image" class="w-4 h-4 text-cyan-400"></i>
          <span>2. Product Image & Media</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
          <div class="md:col-span-3">
            <div class="w-full h-36 rounded-xl bg-slate-800 border border-slate-700 p-2 flex items-center justify-center overflow-hidden">
              <img id="image-preview" src="<?= !empty($product['image']) ? (str_starts_with($product['image'], 'http') ? htmlspecialchars($product['image']) : '../' . htmlspecialchars($product['image'])) : '../assets/images/placeholder-product.png' ?>" alt="Preview" class="max-h-full max-w-full object-contain">
            </div>
            <p class="text-[10px] text-slate-500 text-center mt-1">Current Preview</p>
          </div>

          <div class="md:col-span-9 space-y-3">
            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Option A: Upload Image File</label>
              <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-cyan-400 hover:file:bg-slate-700 cursor-pointer">
              <p class="text-[11px] text-slate-500 mt-1">Accepts PNG, JPG, WEBP, SVG (Square logo or icon works best, min 300x300).</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Option B: External Image URL</label>
              <input type="url" name="image_url" id="image-url-input" value="<?= str_starts_with($product['image'], 'http') ? htmlspecialchars($product['image']) : '' ?>" placeholder="https://example.com/netflix-logo.png" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
            </div>
          </div>
        </div>
      </div>

      <!-- Section 3: Product Variations & Pricing -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h3 class="font-heading font-bold text-sm text-white flex items-center gap-2">
            <i data-lucide="layers" class="w-4 h-4 text-emerald-400"></i>
            <span>3. Plan Durations & Variations (Pricing)</span>
          </h3>

          <button type="button" id="add-var-btn" class="px-3 py-1.5 rounded-xl bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30 text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer border border-emerald-500/30">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            <span>Add Variation</span>
          </button>
        </div>

        <p class="text-xs text-slate-400">
          Create multiple durations or profile tiers for this service (e.g. 1 Month, 3 Months, 6 Months, 12 Months). Mark any variation Out of Stock independently.
        </p>

        <div id="variations-container" class="space-y-3">
          <?php if (empty($variations)): ?>
          <!-- Default first variation row -->
          <div class="var-row p-4 rounded-xl bg-slate-800/60 border border-slate-700 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-3">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Variation Title</label>
              <input type="text" name="var_name[]" value="1 Month (1 Screen UHD Profile)" required placeholder="e.g. 1 Month (1 Screen UHD Profile)" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Duration Label</label>
              <input type="text" name="var_duration[]" value="1 Month" placeholder="1 Month" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Price ($USD) *</label>
              <input type="number" step="0.01" name="var_price[]" value="3.99" required class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-mono">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Orig Price ($USD)</label>
              <input type="number" step="0.01" name="var_orig_price[]" value="15.99" placeholder="15.99" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-mono">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Stock Status</label>
              <select name="var_status[]" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
                <option value="in_stock" selected>In Stock</option>
                <option value="out_of_stock">Out of Stock</option>
              </select>
            </div>
            <div class="md:col-span-1 text-right">
              <button type="button" class="remove-var-btn p-2 rounded-lg text-slate-400 hover:text-rose-400 transition-colors" title="Remove">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
              </button>
            </div>
          </div>
          <?php else: ?>
          <?php foreach ($variations as $idx => $v): ?>
          <div class="var-row p-4 rounded-xl bg-slate-800/60 border border-slate-700 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-3">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Variation Title</label>
              <input type="text" name="var_name[]" value="<?= htmlspecialchars($v['name']) ?>" required placeholder="e.g. 1 Month" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Duration Label</label>
              <input type="text" name="var_duration[]" value="<?= htmlspecialchars($v['duration_label']) ?>" placeholder="1 Month" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Price ($USD) *</label>
              <input type="number" step="0.01" name="var_price[]" value="<?= number_format((float)$v['price'], 2, '.', '') ?>" required class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-mono">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Orig Price ($USD)</label>
              <input type="number" step="0.01" name="var_orig_price[]" value="<?= !empty($v['original_price']) ? number_format((float)$v['original_price'], 2, '.', '') : '' ?>" placeholder="15.99" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-mono">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Stock Status</label>
              <select name="var_status[]" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
                <option value="in_stock" <?= ($v['status'] === 'in_stock') ? 'selected' : '' ?>>In Stock</option>
                <option value="out_of_stock" <?= ($v['status'] === 'out_of_stock') ? 'selected' : '' ?>>Out of Stock</option>
              </select>
            </div>
            <div class="md:col-span-1 text-right">
              <button type="button" class="remove-var-btn p-2 rounded-lg text-slate-400 hover:text-rose-400 transition-colors" title="Remove">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
              </button>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Section 4: Descriptions & Features -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <h3 class="font-heading font-bold text-sm text-white flex items-center gap-2 border-b border-slate-800 pb-3">
          <i data-lucide="file-text" class="w-4 h-4 text-purple-400"></i>
          <span>4. Descriptions & Warranty Details</span>
        </h3>

        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1">Short Summary (Shown on product card & checkout)</label>
          <textarea name="short_desc" rows="2" placeholder="Brief summary of the subscription..." class="w-full px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500"><?= htmlspecialchars($product['short_desc']) ?></textarea>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1">Key Features List (One per line)</label>
          <textarea name="features" rows="4" placeholder="Ultra HD 4K + HDR Streaming&#10;Personal Profile with PIN Lock&#10;Works on Smart TV, Firestick, PC, Phone&#10;100% Full Replacement Guarantee" class="w-full px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500"><?= htmlspecialchars($product['features']) ?></textarea>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1">Full Product Description & Delivery Details (HTML supported)</label>
          <textarea name="description" rows="8" placeholder="Detailed product description, setup instructions, terms, and warranty..." class="w-full px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
      </div>

      <!-- Submit Bar -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <a href="store_products.php" class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-colors">
          Cancel
        </a>
        <button type="submit" class="px-8 py-3 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-cyan-500/25 transition-all flex items-center gap-2 cursor-pointer">
          <i data-lucide="save" class="w-4 h-4"></i>
          <span>Save Digital Product</span>
        </button>
      </div>

    </form>

  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Auto slug from name
      const nameInput = document.getElementById('prod-name-input');
      const slugInput = document.getElementById('prod-slug-input');
      if (nameInput && slugInput && !slugInput.value) {
        nameInput.addEventListener('input', function() {
          slugInput.value = this.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        });
      }

      // External URL image preview
      const imgUrlInput = document.getElementById('image-url-input');
      const imgPreview = document.getElementById('image-preview');
      if (imgUrlInput && imgPreview) {
        imgUrlInput.addEventListener('input', function() {
          if (this.value) {
            imgPreview.src = this.value;
          }
        });
      }

      // Dynamic variations
      const container = document.getElementById('variations-container');
      const addBtn = document.getElementById('add-var-btn');

      addBtn.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'var-row p-4 rounded-xl bg-slate-800/60 border border-slate-700 grid grid-cols-1 md:grid-cols-12 gap-3 items-end';
        row.innerHTML = `
          <div class="md:col-span-3">
            <label class="block text-[10px] font-bold text-slate-400 mb-1">Variation Title</label>
            <input type="text" name="var_name[]" required placeholder="e.g. 3 Months" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
          </div>
          <div class="md:col-span-2">
            <label class="block text-[10px] font-bold text-slate-400 mb-1">Duration Label</label>
            <input type="text" name="var_duration[]" value="3 Months" placeholder="3 Months" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
          </div>
          <div class="md:col-span-2">
            <label class="block text-[10px] font-bold text-slate-400 mb-1">Price ($USD) *</label>
            <input type="number" step="0.01" name="var_price[]" value="9.99" required class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-mono">
          </div>
          <div class="md:col-span-2">
            <label class="block text-[10px] font-bold text-slate-400 mb-1">Orig Price ($USD)</label>
            <input type="number" step="0.01" name="var_orig_price[]" value="44.99" placeholder="44.99" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-mono">
          </div>
          <div class="md:col-span-2">
            <label class="block text-[10px] font-bold text-slate-400 mb-1">Stock Status</label>
            <select name="var_status[]" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
              <option value="in_stock" selected>In Stock</option>
              <option value="out_of_stock">Out of Stock</option>
            </select>
          </div>
          <div class="md:col-span-1 text-right">
            <button type="button" class="remove-var-btn p-2 rounded-lg text-slate-400 hover:text-rose-400 transition-colors" title="Remove">
              <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
          </div>
        `;
        container.appendChild(row);
        if (window.lucide) lucide.createIcons();
      });

      // Remove row
      container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-var-btn')) {
          const rows = container.querySelectorAll('.var-row');
          if (rows.length > 1) {
            e.target.closest('.var-row').remove();
          } else {
            alert('A product must have at least one variation.');
          }
        }
      });
    });
  </script>

<?php include __DIR__ . '/footer.php'; ?>
