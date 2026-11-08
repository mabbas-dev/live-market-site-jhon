<?php
/**
 * LIVETVMARKET - Admin: Store Categories Management
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/store.php';

$curAdminPage = 'store_categories';
$successMsg = '';
$errorMsg = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Add Category
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $icon = trim($_POST['icon'] ?? 'tv');
        $sort = (int)($_POST['sort_order'] ?? 0);

        if (empty($slug) && !empty($name)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        }

        if (empty($name) || empty($slug)) {
            $errorMsg = "Category name and slug are required.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO `store_categories` (`name`, `slug`, `icon`, `sort_order`, `status`) VALUES (?, ?, ?, ?, 'active')");
                $stmt->execute([$name, $slug, $icon, $sort]);
                $successMsg = "Category added successfully!";
            } catch (Exception $e) {
                $errorMsg = "Error adding category: " . $e->getMessage();
            }
        }
    }

    // 2. Edit Category
    if ($action === 'edit') {
        $catId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $icon = trim($_POST['icon'] ?? 'tv');
        $sort = (int)($_POST['sort_order'] ?? 0);

        if (empty($name) || empty($slug)) {
            $errorMsg = "Category name and slug are required.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE `store_categories` SET `name` = ?, `slug` = ?, `icon` = ?, `sort_order` = ? WHERE `id` = ?");
                $stmt->execute([$name, $slug, $icon, $sort, $catId]);
                $successMsg = "Category updated successfully!";
            } catch (Exception $e) {
                $errorMsg = "Error updating category: " . $e->getMessage();
            }
        }
    }

    // 3. Delete Category
    if ($action === 'delete') {
        $catId = (int)($_POST['category_id'] ?? 0);
        try {
            $pdo->prepare("UPDATE `store_products` SET `category_id` = 0 WHERE `category_id` = ?")->execute([$catId]);
            $pdo->prepare("DELETE FROM `store_categories` WHERE `id` = ?")->execute([$catId]);
            $successMsg = "Category deleted successfully!";
        } catch (Exception $e) {
            $errorMsg = "Error deleting category: " . $e->getMessage();
        }
    }
}

// Fetch categories with product counts
$sql = "SELECT c.*, COUNT(p.id) AS products_count 
        FROM `store_categories` c 
        LEFT JOIN `store_products` p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.sort_order ASC, c.name ASC";
$categories = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/header.php';
?>

  <div class="space-y-6">

    <!-- Top Action Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="font-heading font-black text-xl text-white">Digital Store Categories</h2>
        <p class="text-xs text-slate-400">Organize your digital services (Streaming, Music, AI & Tools, VPN, etc.)</p>
      </div>

      <button id="open-add-cat-btn" class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-cyan-500/20 transition-all cursor-pointer">
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Add Category</span>
      </button>
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

    <!-- Categories List Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-800/60 text-slate-400 font-mono text-[11px] uppercase tracking-wider border-b border-slate-800">
            <tr>
              <th class="p-4">Category Name</th>
              <th class="p-4">Slug</th>
              <th class="p-4">Icon Name</th>
              <th class="p-4 text-center">Products Linked</th>
              <th class="p-4 text-center">Sort Order</th>
              <th class="p-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800 text-slate-300">
            <?php if (empty($categories)): ?>
            <tr>
              <td colspan="6" class="p-8 text-center text-slate-500">
                No categories found. Click "Add Category" above to create your first category.
              </td>
            </tr>
            <?php else: ?>
            <?php foreach ($categories as $cat): ?>
            <tr class="hover:bg-slate-800/30 transition-colors">
              <td class="p-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-cyan-400">
                    <i data-lucide="<?= htmlspecialchars($cat['icon'] ?: 'tv') ?>" class="w-4 h-4"></i>
                  </div>
                  <span class="font-bold text-white text-sm"><?= htmlspecialchars($cat['name']) ?></span>
                </div>
              </td>
              <td class="p-4 font-mono text-slate-400 text-[11px]">
                <?= htmlspecialchars($cat['slug']) ?>
              </td>
              <td class="p-4 font-mono text-slate-400 text-[11px]">
                <?= htmlspecialchars($cat['icon']) ?>
              </td>
              <td class="p-4 text-center">
                <span class="px-2.5 py-0.5 rounded-full font-mono text-[11px] font-bold bg-slate-800 text-cyan-300 border border-slate-700">
                  <?= (int)$cat['products_count'] ?> products
                </span>
              </td>
              <td class="p-4 text-center font-mono text-slate-400">
                <?= (int)$cat['sort_order'] ?>
              </td>
              <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button 
                    type="button" 
                    class="edit-cat-btn p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-400 transition-colors cursor-pointer"
                    data-id="<?= $cat['id'] ?>"
                    data-name="<?= htmlspecialchars($cat['name']) ?>"
                    data-slug="<?= htmlspecialchars($cat['slug']) ?>"
                    data-icon="<?= htmlspecialchars($cat['icon']) ?>"
                    data-sort="<?= $cat['sort_order'] ?>"
                    title="Edit Category"
                  >
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                  </button>

                  <form method="POST" onsubmit="return confirm('Delete this category? Linked products will become uncategorized.');" class="inline-block">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                    <button type="submit" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-500/20 text-rose-400 transition-colors cursor-pointer" title="Delete Category">
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

  <!-- ADD / EDIT MODAL -->
  <div id="cat-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
      <button id="close-cat-modal" class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-lg">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>

      <h3 id="modal-heading" class="font-heading font-black text-lg text-white mb-4">Add Category</h3>

      <form method="POST" id="cat-form" class="space-y-4">
        <input type="hidden" name="action" id="cat-form-action" value="add">
        <input type="hidden" name="category_id" id="cat-form-id" value="0">

        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1">Category Name *</label>
          <input type="text" name="name" id="cat-name-input" required placeholder="e.g. Video Streaming" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1">Category Slug *</label>
          <input type="text" name="slug" id="cat-slug-input" required placeholder="e.g. video-streaming" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">Icon Name (Lucide)</label>
            <input type="text" name="icon" id="cat-icon-input" value="tv" placeholder="tv, sparkles, headphones" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">Sort Order</label>
            <input type="number" name="sort_order" id="cat-sort-input" value="0" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
          </div>
        </div>

        <div class="pt-2 flex items-center justify-end gap-2">
          <button type="button" id="cancel-cat-btn" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700">Cancel</button>
          <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold uppercase tracking-wider">Save Category</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('cat-modal');
      const openAddBtn = document.getElementById('open-add-cat-btn');
      const closeBtn = document.getElementById('close-cat-modal');
      const cancelBtn = document.getElementById('cancel-cat-btn');

      const modalHeading = document.getElementById('modal-heading');
      const formAction = document.getElementById('cat-form-action');
      const formId = document.getElementById('cat-form-id');
      const nameInput = document.getElementById('cat-name-input');
      const slugInput = document.getElementById('cat-slug-input');
      const iconInput = document.getElementById('cat-icon-input');
      const sortInput = document.getElementById('cat-sort-input');

      // Open Add
      if (openAddBtn) {
        openAddBtn.addEventListener('click', function() {
          modalHeading.textContent = 'Add Category';
          formAction.value = 'add';
          formId.value = '0';
          nameInput.value = '';
          slugInput.value = '';
          iconInput.value = 'tv';
          sortInput.value = '0';
          modal.classList.remove('hidden');
        });
      }

      // Open Edit
      document.querySelectorAll('.edit-cat-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          modalHeading.textContent = 'Edit Category';
          formAction.value = 'edit';
          formId.value = this.getAttribute('data-id');
          nameInput.value = this.getAttribute('data-name');
          slugInput.value = this.getAttribute('data-slug');
          iconInput.value = this.getAttribute('data-icon') || 'tv';
          sortInput.value = this.getAttribute('data-sort') || '0';
          modal.classList.remove('hidden');
        });
      });

      // Close modal
      const closeModal = () => modal.classList.add('hidden');
      if (closeBtn) closeBtn.addEventListener('click', closeModal);
      if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
      modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
      });

      // Auto slug
      nameInput.addEventListener('input', function() {
        if (formAction.value === 'add') {
          slugInput.value = this.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        }
      });
    });
  </script>

<?php include __DIR__ . '/footer.php'; ?>

