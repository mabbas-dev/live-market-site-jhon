<?php
/**
 * LIVETVMARKET - Admin: Add / Edit Digital Store Product
 * Includes dynamic variations manager, image file upload, full SEO suite,
 * real-time Google SERP preview (desktop/mobile), social share card preview,
 * and dynamic FAQ repeater for Google FAQPage Schema.
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
    'id'               => 0,
    'name'             => '',
    'slug'             => '',
    'category_id'      => 0,
    'badge'            => 'Instant Delivery',
    'starting_price'   => '2.99',
    'status'           => 'in_stock',
    'is_featured'      => 0,
    'image'            => '',
    'short_desc'       => '',
    'features'         => '',
    'description'      => '',
    'sort_order'       => 0,
    // SEO Fields
    'meta_title'       => '',
    'meta_description' => '',
    'meta_keywords'    => '',
    'og_image'         => '',
    'canonical_url'    => '',
    'schema_type'      => 'Product',
    'rating_value'     => '4.9',
    'rating_count'     => 184,
    'faq_json'         => ''
];
$variations = [];

if ($isEditing) {
    $existing = getStoreProductById($prodId);
    if ($existing) {
        $product = array_merge($product, $existing);
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

    // SEO Data
    $metaTitle     = trim($_POST['meta_title'] ?? '');
    $metaDesc      = trim($_POST['meta_description'] ?? '');
    $metaKeywords  = trim($_POST['meta_keywords'] ?? '');
    $ogImageUrl    = trim($_POST['og_image_url'] ?? '');
    $canonicalUrl  = trim($_POST['canonical_url'] ?? '');
    $schemaType    = trim($_POST['schema_type'] ?? 'Product');
    $ratingValue   = floatval($_POST['rating_value'] ?? 4.9);
    $ratingCount   = (int)($_POST['rating_count'] ?? 184);

    // Auto slug if empty
    if (empty($slug) && !empty($name)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }

    if (empty($name) || empty($slug)) {
        $errorMsg = "Product Name and URL Slug are required.";
    } else {
        $uploadDir = __DIR__ . '/../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Handle Main Product Image
        $imagePath = $product['image'];
        if (!empty($imageUrl)) {
            $imagePath = $imageUrl;
        }
        if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'])) {
                $fileName = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $fileName)) {
                    $imagePath = 'uploads/products/' . $fileName;
                }
            } else {
                $errorMsg = "Invalid product image file format. Supported: JPG, PNG, WEBP, SVG.";
            }
        }

        // Handle Social Share (OG) Image
        $ogImagePath = $product['og_image'];
        if (!empty($ogImageUrl)) {
            $ogImagePath = $ogImageUrl;
        }
        if (!empty($_FILES['og_image_file']['name']) && $_FILES['og_image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['og_image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
                $ogFileName = 'og_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['og_image_file']['tmp_name'], $uploadDir . $ogFileName)) {
                    $ogImagePath = 'uploads/products/' . $ogFileName;
                }
            }
        }

        // Handle Dynamic FAQs
        $faqQuestions = $_POST['faq_question'] ?? [];
        $faqAnswers   = $_POST['faq_answer'] ?? [];
        $faqs = [];
        for ($i = 0; $i < count($faqQuestions); $i++) {
            $q = trim($faqQuestions[$i] ?? '');
            $a = trim($faqAnswers[$i] ?? '');
            if (!empty($q) && !empty($a)) {
                $faqs[] = ['question' => $q, 'answer' => $a];
            }
        }
        $faqJson = !empty($faqs) ? json_encode($faqs, JSON_UNESCAPED_UNICODE) : null;

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
                        `sort_order` = ?,
                        `meta_title` = ?,
                        `meta_description` = ?,
                        `meta_keywords` = ?,
                        `og_image` = ?,
                        `canonical_url` = ?,
                        `schema_type` = ?,
                        `rating_value` = ?,
                        `rating_count` = ?,
                        `faq_json` = ?
                        WHERE `id` = ?");
                    $stmt->execute([
                        $categoryId, $name, $slug, $badge, $startingPrice,
                        $status, $isFeatured, $imagePath, $shortDesc,
                        $features, $description, $sortOrder,
                        $metaTitle, $metaDesc, $metaKeywords,
                        $ogImagePath, $canonicalUrl, $schemaType,
                        $ratingValue, $ratingCount, $faqJson,
                        $prodId
                    ]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO `store_products` (
                        `category_id`, `name`, `slug`, `badge`, `starting_price`, 
                        `status`, `is_featured`, `image`, `short_desc`, 
                        `features`, `description`, `sort_order`,
                        `meta_title`, `meta_description`, `meta_keywords`,
                        `og_image`, `canonical_url`, `schema_type`,
                        `rating_value`, `rating_count`, `faq_json`
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $categoryId, $name, $slug, $badge, $startingPrice,
                        $status, $isFeatured, $imagePath, $shortDesc,
                        $features, $description, $sortOrder,
                        $metaTitle, $metaDesc, $metaKeywords,
                        $ogImagePath, $canonicalUrl, $schemaType,
                        $ratingValue, $ratingCount, $faqJson
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

                if ($minVarPrice < 999999 && $startingPrice <= 0) {
                    $pdo->prepare("UPDATE `store_products` SET `starting_price` = ? WHERE `id` = ?")->execute([$minVarPrice, $prodId]);
                }

                $successMsg = "Product & SEO data saved successfully!";
                $product = getStoreProductById($prodId);
                $variations = $product['variations'] ?? [];
            } catch (Exception $e) {
                $errorMsg = "Database Error: " . $e->getMessage();
            }
        }
    }
}

$categories = getStoreCategories(false);

// Parse FAQs array for form rendering
$currentFaqs = [];
if (!empty($product['faq_json'])) {
    $parsed = json_decode($product['faq_json'], true);
    if (is_array($parsed)) {
        $currentFaqs = $parsed;
    }
}

include __DIR__ . '/header.php';
?>

  <div class="space-y-6 max-w-5xl pb-16">

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
      <div class="flex items-center gap-2">
        <a href="../product/<?= urlencode($product['slug']) ?>" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-400 text-xs font-bold flex items-center gap-1.5 transition-colors border border-cyan-500/30">
          <span>View on Live Site</span>
          <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
        </a>
      </div>
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
            <select name="status" id="prod-status-select" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
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

      <!-- Section 2: Product Image & Media -->
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
              <input type="file" name="image_file" id="image-file-input" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-cyan-400 hover:file:bg-slate-700 cursor-pointer">
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
          Create multiple durations or profile tiers for this service (e.g. 1 Month, 3 Months, 6 Months, 12 Months).
        </p>

        <div id="variations-container" class="space-y-3">
          <?php if (empty($variations)): ?>
          <div class="var-row p-4 rounded-xl bg-slate-800/60 border border-slate-700 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <div class="md:col-span-3">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Variation Title</label>
              <input type="text" name="var_name[]" value="1 Month (1 Screen UHD Profile)" required placeholder="e.g. 1 Month" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Duration Label</label>
              <input type="text" name="var_duration[]" value="1 Month" placeholder="1 Month" class="w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
            </div>
            <div class="md:col-span-2">
              <label class="block text-[10px] font-bold text-slate-400 mb-1">Price ($USD) *</label>
              <input type="number" step="0.01" name="var_price[]" value="3.99" required class="var-price-input w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-mono">
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
              <input type="number" step="0.01" name="var_price[]" value="<?= number_format((float)$v['price'], 2, '.', '') ?>" required class="var-price-input w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-mono">
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
          <span>4. Descriptions & Key Features</span>
        </h3>

        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1">Short Summary (Shown on product card & checkout)</label>
          <textarea name="short_desc" id="prod-short-desc" rows="2" placeholder="Brief summary of the subscription..." class="w-full px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500"><?= htmlspecialchars($product['short_desc']) ?></textarea>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1">Key Features List (One per line)</label>
          <textarea name="features" rows="4" placeholder="Ultra HD 4K + HDR Streaming&#10;Personal Profile with PIN Lock&#10;Works on Smart TV, Firestick, PC, Phone&#10;100% Full Replacement Guarantee" class="w-full px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500"><?= htmlspecialchars($product['features']) ?></textarea>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1">Full Product Description & Delivery Details (HTML supported)</label>
          <textarea name="description" rows="6" placeholder="Detailed product description, setup instructions, terms, and warranty..." class="w-full px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
      </div>

      <!-- ================================================================= -->
      <!-- SECTION 5: ADVANCED SEO & GOOGLE SERP PREVIEW -->
      <!-- ================================================================= -->
      <div class="bg-slate-900 border border-cyan-500/30 rounded-2xl p-6 space-y-6 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
          <div>
            <h3 class="font-heading font-bold text-base text-white flex items-center gap-2">
              <i data-lucide="search" class="w-5 h-5 text-cyan-400"></i>
              <span>5. Advanced SEO & Real-Time Google SERP Preview</span>
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">
              Customize title tags, meta descriptions, and rich snippet schemas for high ranking on Google & Bing.
            </p>
          </div>

          <!-- Preview Mode Toggle -->
          <div class="flex items-center gap-1 p-1 bg-slate-800 rounded-xl border border-slate-700 text-xs self-start">
            <button type="button" id="serp-desktop-tab" class="px-3 py-1.5 rounded-lg bg-cyan-500 text-slate-950 font-bold transition-all flex items-center gap-1.5">
              <i data-lucide="monitor" class="w-3.5 h-3.5"></i>
              <span>Google Desktop</span>
            </button>
            <button type="button" id="serp-mobile-tab" class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white font-semibold transition-all flex items-center gap-1.5">
              <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
              <span>Google Mobile</span>
            </button>
            <button type="button" id="serp-social-tab" class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-white font-semibold transition-all flex items-center gap-1.5">
              <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
              <span>Social Card</span>
            </button>
          </div>
        </div>

        <!-- LIVE PREVIEW CONTAINER -->
        <div class="p-4 sm:p-5 rounded-2xl bg-slate-950 border border-slate-800 shadow-inner">
          <div class="text-[10px] font-mono uppercase tracking-wider text-slate-500 mb-3 flex items-center justify-between">
            <span class="flex items-center gap-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
              <span>Live Search Engine Snippet Simulator</span>
            </span>
            <span class="text-slate-400" id="preview-mode-label">Desktop SERP</span>
          </div>

          <!-- GOOGLE DESKTOP PREVIEW BOX -->
          <div id="google-desktop-view" class="bg-white rounded-xl p-4 sm:p-5 border border-slate-200 text-left max-w-2xl font-sans">
            <div class="flex items-center gap-2 mb-1.5">
              <div class="w-6 h-6 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-black text-cyan-600">
                LTV
              </div>
              <div class="text-xs text-slate-700 leading-tight">
                <span class="font-bold text-slate-800">LIVETVMARKET</span>
                <span class="text-slate-400 text-[11px] block truncate">https://livetvmarket.com › product › <span id="preview-slug-text"><?= htmlspecialchars($product['slug'] ?: 'product-name') ?></span></span>
              </div>
            </div>

            <!-- Clickable Blue Title -->
            <h4 id="preview-title-text" class="text-lg text-[#1a0dab] hover:underline cursor-pointer font-normal leading-snug line-clamp-1 mb-1 font-sans">
              <?= htmlspecialchars($product['meta_title'] ?: ($product['name'] . ' — Instant Digital Delivery | LIVETVMARKET')) ?>
            </h4>

            <!-- Rich Results: Star Rating & In Stock -->
            <div class="flex items-center gap-2 text-xs text-slate-600 mb-1.5 font-sans">
              <div class="flex items-center text-amber-500 text-[13px] tracking-tighter">
                ★★★★★
              </div>
              <span class="font-bold text-slate-700"><span id="preview-rating-text"><?= $product['rating_value'] ?: '4.9' ?></span></span>
              <span class="text-slate-400">(<span id="preview-count-text"><?= $product['rating_count'] ?: '184' ?></span> reviews)</span>
              <span class="text-slate-400">·</span>
              <span class="text-emerald-700 font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                <span id="preview-stock-text">In stock</span>
              </span>
              <span class="text-slate-400">·</span>
              <span class="font-mono text-slate-700 font-bold" id="preview-price-text">$<?= number_format((float)$product['starting_price'], 2) ?></span>
            </div>

            <!-- Snippet Description -->
            <p id="preview-desc-text" class="text-xs text-[#4d5156] leading-relaxed line-clamp-2">
              <?= htmlspecialchars($product['meta_description'] ?: ($product['short_desc'] ?: 'Get instant access key with automated email & WhatsApp delivery, 100% full replacement guarantee, and 24/7 dedicated support at LIVETVMARKET.')) ?>
            </p>
          </div>

          <!-- GOOGLE MOBILE PREVIEW BOX (Hidden by default) -->
          <div id="google-mobile-view" class="hidden bg-white rounded-2xl p-4 border border-slate-200 text-left max-w-sm mx-auto shadow-md font-sans">
            <div class="flex items-center gap-2 mb-2">
              <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-cyan-600">
                LTV
              </div>
              <div>
                <div class="text-xs font-bold text-slate-900 leading-tight">LIVETVMARKET</div>
                <div class="text-[10px] text-slate-500 truncate max-w-[200px]">livetvmarket.com/product/<span class="preview-slug-mobile"><?= htmlspecialchars($product['slug'] ?: 'product') ?></span></div>
              </div>
            </div>

            <h4 class="text-base text-[#1a0dab] font-normal leading-snug line-clamp-2 mb-1 preview-title-mobile font-sans">
              <?= htmlspecialchars($product['meta_title'] ?: ($product['name'] . ' — Instant Delivery | LIVETVMARKET')) ?>
            </h4>

            <div class="flex items-center gap-1.5 text-[11px] text-slate-600 mb-1.5">
              <span class="text-amber-500">★★★★★</span>
              <span class="font-bold text-slate-800"><?= $product['rating_value'] ?: '4.9' ?></span>
              <span class="text-slate-400">(<?= $product['rating_count'] ?: '184' ?>)</span>
              <span class="text-slate-300">•</span>
              <span class="text-emerald-600 font-bold">In stock</span>
            </div>

            <p class="text-xs text-[#4d5156] leading-relaxed line-clamp-3 preview-desc-mobile">
              <?= htmlspecialchars($product['meta_description'] ?: ($product['short_desc'] ?: 'Get instant access key with automated email delivery, 100% replacement guarantee.')) ?>
            </p>
          </div>

          <!-- SOCIAL MEDIA SHARE CARD PREVIEW (Hidden by default) -->
          <div id="social-card-view" class="hidden bg-slate-900 rounded-2xl border border-slate-700 overflow-hidden text-left max-w-md mx-auto shadow-lg">
            <div class="w-full h-44 bg-slate-800 relative flex items-center justify-center overflow-hidden">
              <img id="preview-social-img" src="<?= !empty($product['og_image']) ? (str_starts_with($product['og_image'], 'http') ? htmlspecialchars($product['og_image']) : '../' . htmlspecialchars($product['og_image'])) : (!empty($product['image']) ? (str_starts_with($product['image'], 'http') ? htmlspecialchars($product['image']) : '../' . htmlspecialchars($product['image'])) : '../assets/images/hero-showcase.jpg') ?>" alt="Social Preview" class="w-full h-full object-cover">
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
              <span class="absolute top-2 right-2 px-2 py-0.5 rounded bg-black/60 text-[10px] text-white font-mono uppercase">Social Card</span>
            </div>
            <div class="p-3.5 bg-slate-800/90 border-t border-slate-700">
              <div class="text-[10px] uppercase font-mono tracking-wider text-cyan-400 font-bold">livetvmarket.com</div>
              <h5 class="text-xs font-bold text-white truncate mt-0.5" id="preview-social-title">
                <?= htmlspecialchars($product['meta_title'] ?: ($product['name'] . ' — Buy Online')) ?>
              </h5>
              <p class="text-[11px] text-slate-300 line-clamp-2 mt-0.5" id="preview-social-desc">
                <?= htmlspecialchars($product['meta_description'] ?: ($product['short_desc'] ?: 'Instant digital activation with 100% replacement guarantee.')) ?>
              </p>
            </div>
          </div>
        </div>

        <!-- SEO INPUT FIELDS -->
        <div class="space-y-4 pt-2">

          <!-- Meta Title -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-bold text-slate-300">
                SEO Meta Title <span class="text-cyan-400 font-normal">(Optimal: 50 – 60 characters)</span>
              </label>
              <span id="title-char-counter" class="text-xs font-mono font-bold text-slate-400">0 / 60</span>
            </div>
            <input type="text" name="meta_title" id="meta-title-input" value="<?= htmlspecialchars($product['meta_title']) ?>" placeholder="e.g. <?= htmlspecialchars($product['name'] ?: 'Product Name') ?> — Instant Digital Delivery | LIVETVMARKET" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-1.5 overflow-hidden">
              <div id="title-progress-bar" class="h-full bg-emerald-500 transition-all duration-300" style="width: 0%"></div>
            </div>
          </div>

          <!-- Meta Description -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-bold text-slate-300">
                SEO Meta Description <span class="text-cyan-400 font-normal">(Optimal: 120 – 160 characters)</span>
              </label>
              <span id="desc-char-counter" class="text-xs font-mono font-bold text-slate-400">0 / 160</span>
            </div>
            <textarea name="meta_description" id="meta-desc-input" rows="3" placeholder="Write a compelling summary including primary keyword, benefits (instant delivery, warranty), and call to action..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500"><?= htmlspecialchars($product['meta_description']) ?></textarea>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-1.5 overflow-hidden">
              <div id="desc-progress-bar" class="h-full bg-emerald-500 transition-all duration-300" style="width: 0%"></div>
            </div>
          </div>

          <!-- Keywords & Canonical -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Meta Keywords (Comma-separated)</label>
              <input type="text" name="meta_keywords" value="<?= htmlspecialchars($product['meta_keywords']) ?>" placeholder="netflix 4k, cheap netflix subscription, buy netflix profile" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Custom Canonical URL (Leave blank for default)</label>
              <input type="url" name="canonical_url" value="<?= htmlspecialchars($product['canonical_url']) ?>" placeholder="https://livetvmarket.com/product/<?= htmlspecialchars($product['slug'] ?: 'product-name') ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
            </div>
          </div>

          <!-- Structured Data Schema Controls -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Schema Type</label>
              <select name="schema_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white">
                <option value="Product" <?= ($product['schema_type'] === 'Product') ? 'selected' : '' ?>>Product (Default - Google Shopping / E-commerce)</option>
                <option value="Service" <?= ($product['schema_type'] === 'Service') ? 'selected' : '' ?>>Service</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Star Rating (Schema Aggregate)</label>
              <input type="number" step="0.1" min="1.0" max="5.0" name="rating_value" id="schema-rating-input" value="<?= htmlspecialchars($product['rating_value'] ?: '4.9') ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono">
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-300 mb-1">Review Count (Schema Count)</label>
              <input type="number" min="1" name="rating_count" id="schema-count-input" value="<?= htmlspecialchars($product['rating_count'] ?: '184') ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono">
            </div>
          </div>

          <!-- Social Share (OG) Image Customization -->
          <div class="pt-2 border-t border-slate-800">
            <label class="block text-xs font-bold text-slate-300 mb-1">Custom Social Share (OG) Image (Optional)</label>
            <p class="text-[11px] text-slate-400 mb-2">If left blank, Google and social platforms will automatically use the main product image.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <input type="file" name="og_image_file" id="og-image-file" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-cyan-400 hover:file:bg-slate-700 cursor-pointer">
              <input type="url" name="og_image_url" id="og-image-url" value="<?= str_starts_with($product['og_image'], 'http') ? htmlspecialchars($product['og_image']) : '' ?>" placeholder="Or paste external image URL..." class="w-full px-3.5 py-2 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
            </div>
          </div>

        </div>
      </div>

      <!-- ================================================================= -->
      <!-- SECTION 6: PRODUCT FAQS (GOOGLE FAQPAGE SCHEMA REPEATER) -->
      <!-- ================================================================= -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <div>
            <h3 class="font-heading font-bold text-sm text-white flex items-center gap-2">
              <i data-lucide="help-circle" class="w-4 h-4 text-amber-400"></i>
              <span>6. Product FAQs (Powers Interactive Accordion & Google FAQ Rich Snippets)</span>
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">
              Each question and answer entered here is automatically converted to Google FAQPage Schema.
            </p>
          </div>

          <button type="button" id="add-faq-btn" class="px-3 py-1.5 rounded-xl bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer border border-amber-500/30">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            <span>Add FAQ Question</span>
          </button>
        </div>

        <div id="faqs-container" class="space-y-3">
          <?php if (empty($currentFaqs)): ?>
          <!-- Sample FAQ 1 -->
          <div class="faq-row p-4 rounded-xl bg-slate-800/60 border border-slate-700 space-y-2 relative">
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-mono uppercase font-bold text-amber-400 flex items-center gap-1">
                <i data-lucide="message-circle-question" class="w-3 h-3"></i> FAQ Item
              </span>
              <button type="button" class="remove-faq-btn text-slate-400 hover:text-rose-400 p-1 transition-colors" title="Remove FAQ">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
              </button>
            </div>
            <div>
              <input type="text" name="faq_question[]" value="How fast is delivery after ordering?" placeholder="Question..." class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">
            </div>
            <div>
              <textarea name="faq_answer[]" rows="2" placeholder="Answer..." class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white">Your credentials and setup guide are dispatched automatically to your WhatsApp and Email within 5 to 15 minutes of payment.</textarea>
            </div>
          </div>
          <?php else: ?>
          <?php foreach ($currentFaqs as $fq): ?>
          <div class="faq-row p-4 rounded-xl bg-slate-800/60 border border-slate-700 space-y-2 relative">
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-mono uppercase font-bold text-amber-400 flex items-center gap-1">
                <i data-lucide="message-circle-question" class="w-3 h-3"></i> FAQ Item
              </span>
              <button type="button" class="remove-faq-btn text-slate-400 hover:text-rose-400 p-1 transition-colors" title="Remove FAQ">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
              </button>
            </div>
            <div>
              <input type="text" name="faq_question[]" value="<?= htmlspecialchars($fq['question']) ?>" placeholder="Question..." class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-bold">
            </div>
            <div>
              <textarea name="faq_answer[]" rows="2" placeholder="Answer..." class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white"><?= htmlspecialchars($fq['answer']) ?></textarea>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Submit Bar -->
      <div class="flex items-center justify-between pt-2">
        <a href="store_products.php" class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-colors">
          Cancel
        </a>
        <button type="submit" class="px-8 py-3 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-cyan-500/25 transition-all flex items-center gap-2 cursor-pointer">
          <i data-lucide="save" class="w-4 h-4"></i>
          <span>Save Digital Product & SEO</span>
        </button>
      </div>

    </form>

  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // 1. Elements
      const nameInput     = document.getElementById('prod-name-input');
      const slugInput     = document.getElementById('prod-slug-input');
      const metaTitle     = document.getElementById('meta-title-input');
      const metaDesc      = document.getElementById('meta-desc-input');
      const shortDesc     = document.getElementById('prod-short-desc');
      const ratingInput   = document.getElementById('schema-rating-input');
      const countInput    = document.getElementById('schema-count-input');
      const statusSelect  = document.getElementById('prod-status-select');

      // Previews
      const prevTitle     = document.getElementById('preview-title-text');
      const prevSlug      = document.getElementById('preview-slug-text');
      const prevDesc      = document.getElementById('preview-desc-text');
      const prevRating    = document.getElementById('preview-rating-text');
      const prevCount     = document.getElementById('preview-count-text');
      const prevStock     = document.getElementById('preview-stock-text');
      const prevPrice     = document.getElementById('preview-price-text');
      const prevSocialTtl = document.getElementById('preview-social-title');
      const prevSocialDsc = document.getElementById('preview-social-desc');

      // Counters & Progress
      const titleCounter  = document.getElementById('title-char-counter');
      const titleProgress = document.getElementById('title-progress-bar');
      const descCounter   = document.getElementById('desc-char-counter');
      const descProgress  = document.getElementById('desc-progress-bar');

      // Tabs
      const tabDesktop    = document.getElementById('serp-desktop-tab');
      const tabMobile     = document.getElementById('serp-mobile-tab');
      const tabSocial     = document.getElementById('serp-social-tab');
      const viewDesktop   = document.getElementById('google-desktop-view');
      const viewMobile    = document.getElementById('google-mobile-view');
      const viewSocial    = document.getElementById('social-card-view');
      const modeLabel     = document.getElementById('preview-mode-label');

      // Tab Switcher
      function activateTab(activeBtn, activeView, label) {
        [tabDesktop, tabMobile, tabSocial].forEach(b => {
          b.className = 'px-3 py-1.5 rounded-lg text-slate-400 hover:text-white font-semibold transition-all flex items-center gap-1.5';
        });
        activeBtn.className = 'px-3 py-1.5 rounded-lg bg-cyan-500 text-slate-950 font-bold transition-all flex items-center gap-1.5';

        viewDesktop.classList.add('hidden');
        viewMobile.classList.add('hidden');
        viewSocial.classList.add('hidden');
        activeView.classList.remove('hidden');
        modeLabel.textContent = label;
      }

      tabDesktop.addEventListener('click', () => activateTab(tabDesktop, viewDesktop, 'Desktop SERP'));
      tabMobile.addEventListener('click', () => activateTab(tabMobile, viewMobile, 'Mobile SERP'));
      tabSocial.addEventListener('click', () => activateTab(tabSocial, viewSocial, 'Social Share Card'));

      // Live SERP Updater
      function updateSerp() {
        const prodName = nameInput.value.trim() || 'Product Name';
        const prodSlug = slugInput.value.trim() || 'product-slug';

        // Title
        const customTitle = metaTitle.value.trim();
        const displayTitle = customTitle || (prodName + ' — Instant Digital Delivery | LIVETVMARKET');
        prevTitle.textContent = displayTitle;
        if (prevSocialTtl) prevSocialTtl.textContent = displayTitle;
        document.querySelectorAll('.preview-title-mobile').forEach(el => el.textContent = displayTitle);

        // Title Counter & Bar
        const tLen = customTitle.length;
        titleCounter.textContent = `${tLen} / 60`;
        const tPct = Math.min(100, Math.round((tLen / 60) * 100));
        titleProgress.style.width = tPct + '%';
        if (tLen >= 45 && tLen <= 65) {
          titleProgress.className = 'h-full bg-emerald-500 transition-all duration-300';
          titleCounter.className = 'text-xs font-mono font-bold text-emerald-400';
        } else if (tLen > 65) {
          titleProgress.className = 'h-full bg-rose-500 transition-all duration-300';
          titleCounter.className = 'text-xs font-mono font-bold text-rose-400';
        } else {
          titleProgress.className = 'h-full bg-amber-500 transition-all duration-300';
          titleCounter.className = 'text-xs font-mono font-bold text-slate-400';
        }

        // Slug
        prevSlug.textContent = prodSlug;
        document.querySelectorAll('.preview-slug-mobile').forEach(el => el.textContent = prodSlug);

        // Description
        const customDesc = metaDesc.value.trim();
        const sDesc = shortDesc.value.trim();
        const displayDesc = customDesc || sDesc || ('Get instant access key for ' + prodName + ' with automated delivery, 100% full replacement guarantee, and 24/7 dedicated support.');
        prevDesc.textContent = displayDesc;
        if (prevSocialDsc) prevSocialDsc.textContent = displayDesc;
        document.querySelectorAll('.preview-desc-mobile').forEach(el => el.textContent = displayDesc);

        // Desc Counter & Bar
        const dLen = customDesc.length;
        descCounter.textContent = `${dLen} / 160`;
        const dPct = Math.min(100, Math.round((dLen / 160) * 100));
        descProgress.style.width = dPct + '%';
        if (dLen >= 120 && dLen <= 165) {
          descProgress.className = 'h-full bg-emerald-500 transition-all duration-300';
          descCounter.className = 'text-xs font-mono font-bold text-emerald-400';
        } else if (dLen > 165) {
          descProgress.className = 'h-full bg-rose-500 transition-all duration-300';
          descCounter.className = 'text-xs font-mono font-bold text-rose-400';
        } else {
          descProgress.className = 'h-full bg-amber-500 transition-all duration-300';
          descCounter.className = 'text-xs font-mono font-bold text-slate-400';
        }

        // Rating & Count
        if (ratingInput && prevRating) prevRating.textContent = ratingInput.value || '4.9';
        if (countInput && prevCount) prevCount.textContent = countInput.value || '184';

        // Stock status
        if (statusSelect && prevStock) {
          const isOut = statusSelect.value === 'out_of_stock';
          prevStock.textContent = isOut ? 'Out of stock' : 'In stock';
          prevStock.parentElement.className = isOut ? 'text-rose-600 font-semibold flex items-center gap-1' : 'text-emerald-700 font-semibold flex items-center gap-1';
        }

        // Lowest variation price
        const priceInputs = document.querySelectorAll('.var-price-input');
        if (priceInputs.length > 0 && prevPrice) {
          let lowest = 999999;
          priceInputs.forEach(inp => {
            const val = parseFloat(inp.value);
            if (!isNaN(val) && val < lowest) lowest = val;
          });
          if (lowest < 999999) {
            prevPrice.textContent = '$' + lowest.toFixed(2);
          }
        }
      }

      // Auto slug from name
      if (nameInput && slugInput) {
        nameInput.addEventListener('input', function() {
          if (!slugInput.dataset.manual) {
            slugInput.value = this.value
              .toLowerCase()
              .trim()
              .replace(/[^a-z0-9]+/g, '-')
              .replace(/^-+|-+$/g, '');
          }
          updateSerp();
        });
        slugInput.addEventListener('input', function() {
          this.dataset.manual = 'true';
          updateSerp();
        });
      }

      metaTitle.addEventListener('input', updateSerp);
      metaDesc.addEventListener('input', updateSerp);
      if (shortDesc) shortDesc.addEventListener('input', updateSerp);
      if (ratingInput) ratingInput.addEventListener('input', updateSerp);
      if (countInput) countInput.addEventListener('input', updateSerp);
      if (statusSelect) statusSelect.addEventListener('change', updateSerp);

      // External URL image preview
      const imgUrlInput = document.getElementById('image-url-input');
      const imgPreview  = document.getElementById('image-preview');
      const socialImg   = document.getElementById('preview-social-img');
      if (imgUrlInput && imgPreview) {
        imgUrlInput.addEventListener('input', function() {
          if (this.value) {
            imgPreview.src = this.value;
            if (socialImg) socialImg.src = this.value;
          }
        });
      }

      // Variations Repeater
      const container = document.getElementById('variations-container');
      const addBtn = document.getElementById('add-var-btn');
      if (addBtn && container) {
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
              <input type="number" step="0.01" name="var_price[]" value="9.99" required class="var-price-input w-full px-2.5 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-mono">
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
          row.querySelector('.var-price-input').addEventListener('input', updateSerp);
          updateSerp();
        });

        container.addEventListener('click', function(e) {
          if (e.target.closest('.remove-var-btn')) {
            const rows = container.querySelectorAll('.var-row');
            if (rows.length > 1) {
              e.target.closest('.var-row').remove();
              updateSerp();
            } else {
              alert('A product must have at least one variation.');
            }
          }
        });

        document.querySelectorAll('.var-price-input').forEach(inp => {
          inp.addEventListener('input', updateSerp);
        });
      }

      // FAQs Repeater
      const faqContainer = document.getElementById('faqs-container');
      const addFaqBtn    = document.getElementById('add-faq-btn');
      if (addFaqBtn && faqContainer) {
        addFaqBtn.addEventListener('click', function() {
          const row = document.createElement('div');
          row.className = 'faq-row p-4 rounded-xl bg-slate-800/60 border border-slate-700 space-y-2 relative';
          row.innerHTML = `
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-mono uppercase font-bold text-amber-400 flex items-center gap-1">
                <i data-lucide="message-circle-question" class="w-3 h-3"></i> FAQ Item
              </span>
              <button type="button" class="remove-faq-btn text-slate-400 hover:text-rose-400 p-1 transition-colors" title="Remove FAQ">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
              </button>
            </div>
            <div>
              <input type="text" name="faq_question[]" placeholder="Question..." class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-bold">
            </div>
            <div>
              <textarea name="faq_answer[]" rows="2" placeholder="Answer..." class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white"></textarea>
            </div>
          `;
          faqContainer.appendChild(row);
          if (window.lucide) lucide.createIcons();
        });

        faqContainer.addEventListener('click', function(e) {
          if (e.target.closest('.remove-faq-btn')) {
            e.target.closest('.faq-row').remove();
          }
        });
      }

      // Initialize SERP preview on page load
      updateSerp();
    });
  </script>

<?php include __DIR__ . '/footer.php'; ?>
