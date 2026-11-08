<?php
/**
 * LIVETVMARKET - Admin Device Setup Guides Manager
 * Full control over all device tutorials (Firestick, Android, Smart TV, Apple TV, PC, MAG, etc.),
 * installation steps, Lucide icons, color accents, sorting, and page hero/help banners.
 */
$adminTitle = 'Device Setup Guides Manager';
$curAdminPage = 'setup_guides';

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/setup_guides.php';

$successMsg = '';
$errorMsg = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_guide') {
        $id = (int)($_POST['id'] ?? 0);
        $slug = strtolower(preg_replace('/[^a-z0-9_-]/', '', trim($_POST['device_slug'] ?? '')));
        $name = trim($_POST['device_name'] ?? '');
        $navTitle = trim($_POST['nav_title'] ?? '');
        $setupTime = trim($_POST['setup_time'] ?? 'Total Setup Time: ~3 minutes');
        $icon = trim($_POST['icon'] ?? 'tv');
        $iconColor = trim($_POST['icon_color'] ?? 'cyan');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Process dynamic steps
        $stepTitles = $_POST['step_title'] ?? [];
        $stepDescs = $_POST['step_desc'] ?? [];
        $steps = [];

        for ($i = 0; $i < count($stepTitles); $i++) {
            $t = trim($stepTitles[$i] ?? '');
            $d = trim($stepDescs[$i] ?? '');
            if (!empty($t) || !empty($d)) {
                $steps[] = [
                    'title' => $t,
                    'desc' => $d
                ];
            }
        }
        $stepsJson = json_encode($steps);

        if (empty($slug)) {
            $errorMsg = "Device URL Anchor Slug cannot be empty.";
        } elseif (empty($name)) {
            $errorMsg = "Device Headline Name cannot be empty.";
        } else {
            try {
                if ($id > 0) {
                    // Check duplicate slug on other guides
                    $chk = $pdo->prepare("SELECT `id` FROM `setup_guides` WHERE `device_slug` = ? AND `id` != ?");
                    $chk->execute([$slug, $id]);
                    if ($chk->rowCount() > 0) {
                        $errorMsg = "A guide with slug '{$slug}' already exists. Slugs must be unique.";
                    } else {
                        $stmt = $pdo->prepare("UPDATE `setup_guides` SET `device_slug` = ?, `device_name` = ?, `nav_title` = ?, `setup_time` = ?, `icon` = ?, `icon_color` = ?, `steps_json` = ?, `sort_order` = ?, `is_active` = ? WHERE `id` = ?");
                        $stmt->execute([$slug, $name, $navTitle, $setupTime, $icon, $iconColor, $stepsJson, $sortOrder, $isActive, $id]);
                        $successMsg = "Guide for '{$name}' updated successfully!";
                    }
                } else {
                    // Check duplicate slug
                    $chk = $pdo->prepare("SELECT `id` FROM `setup_guides` WHERE `device_slug` = ?");
                    $chk->execute([$slug]);
                    if ($chk->rowCount() > 0) {
                        $errorMsg = "A guide with slug '{$slug}' already exists. Slugs must be unique.";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO `setup_guides` (`device_slug`, `device_name`, `nav_title`, `setup_time`, `icon`, `icon_color`, `steps_json`, `sort_order`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$slug, $name, $navTitle, $setupTime, $icon, $iconColor, $stepsJson, $sortOrder, $isActive]);
                        $successMsg = "New guide for '{$name}' created successfully!";
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
                $pdo->prepare("UPDATE `setup_guides` SET `is_active` = IF(`is_active` = 1, 0, 1) WHERE `id` = ?")->execute([$id]);
                $successMsg = "Guide active status updated!";
            } catch (Exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_guide') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo->prepare("DELETE FROM `setup_guides` WHERE `id` = ?")->execute([$id]);
                $successMsg = "Device setup guide deleted successfully!";
            } catch (Exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'save_page_settings') {
        try {
            updateSetting('setup_page_badge', trim($_POST['setup_page_badge'] ?? '3-Minute Tutorials'));
            updateSetting('setup_page_title', trim($_POST['setup_page_title'] ?? 'Device Setup & App Installation Guides'));
            updateSetting('setup_page_subtitle', trim($_POST['setup_page_subtitle'] ?? ''));
            updateSetting('setup_help_title', trim($_POST['setup_help_title'] ?? 'Need Live Setup Assistance?'));
            updateSetting('setup_help_subtitle', trim($_POST['setup_help_subtitle'] ?? ''));
            updateSetting('setup_help_btn_text', trim($_POST['setup_help_btn_text'] ?? 'Chat With Technician'));
            updateSetting('setup_help_btn_link', trim($_POST['setup_help_btn_link'] ?? ''));
            $successMsg = "Setup Page Header & Help Banner settings saved successfully!";
        } catch (Exception $e) {
            $errorMsg = "Error updating settings: " . $e->getMessage();
        }
    }
}

// Fetch guides & settings
$allGuides = getSetupGuides(false);
$setupPageSettings = getSetupPageSettings();
$activeCount = count(array_filter($allGuides, fn($g) => (int)$g['is_active'] === 1));

// Supported Lucide Icons & Colors
$availableIcons = ['flame', 'smartphone', 'tv', 'tv-2', 'apple', 'monitor', 'box', 'play-circle', 'wifi', 'cast', 'layers', 'hard-drive', 'laptop', 'tablet', 'gamepad-2', 'help-circle'];
$availableColors = ['amber', 'emerald', 'cyan', 'indigo', 'rose', 'purple', 'blue'];
?>

<div class="max-w-6xl">

  <!-- PAGE HEADER -->
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
        <span class="p-2 rounded-xl bg-emerald-500/15 text-emerald-400">
          <i data-lucide="book-open" class="w-6 h-6"></i>
        </span>
        <span>Device Setup & Installation Guides Manager</span>
      </h1>
      <p class="text-xs text-slate-400 mt-1">Full control over all device tutorials (Firestick, Android, Smart TV, Apple TV, PC, MAG, etc.), step-by-step instructions, and page banners on <strong class="text-slate-300">/setup</strong>.</p>
    </div>

    <div class="flex items-center gap-2.5">
      <a href="/setup" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold text-xs border border-slate-700 transition-colors flex items-center gap-2">
        <i data-lucide="external-link" class="w-4 h-4 text-cyan-400"></i>
        <span>View Live /setup Page</span>
      </a>
      <button type="button" onclick="openAddGuideModal()" class="btn-glow-cyan px-4 py-2.5 rounded-xl font-bold text-xs flex items-center gap-2 text-white shadow-lg shadow-cyan-500/20 cursor-pointer">
        <i data-lucide="plus-circle" class="w-4 h-4"></i>
        <span>Add New Device Guide</span>
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

  <!-- METRIC STATS -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="glass-card p-4 rounded-2xl border border-slate-800">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs text-slate-400 font-semibold">Total Device Guides</span>
        <span class="p-2 rounded-lg bg-cyan-500/10 text-cyan-400"><i data-lucide="tv" class="w-4 h-4"></i></span>
      </div>
      <div class="text-2xl font-black text-white font-mono"><?= count($allGuides) ?></div>
      <span class="text-[10px] text-slate-500">Configured in system</span>
    </div>

    <div class="glass-card p-4 rounded-2xl border border-slate-800">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs text-slate-400 font-semibold">Active & Published</span>
        <span class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400"><i data-lucide="check" class="w-4 h-4"></i></span>
      </div>
      <div class="text-2xl font-black text-emerald-400 font-mono"><?= $activeCount ?> Active</div>
      <span class="text-[10px] text-slate-500">Live on /setup page</span>
    </div>

    <div class="glass-card p-4 rounded-2xl border border-slate-800">
      <div class="flex items-center justify-between mb-2">
        <span class="text-xs text-slate-400 font-semibold">Assistance Banner</span>
        <span class="p-2 rounded-lg bg-purple-500/10 text-purple-400"><i data-lucide="message-circle" class="w-4 h-4"></i></span>
      </div>
      <div class="text-2xl font-black text-white font-mono">24/7 Desk</div>
      <span class="text-[10px] text-slate-500">WhatsApp live technician link</span>
    </div>
  </div>

  <!-- SECTION 1: DEVICE GUIDES LIST -->
  <div class="mb-10">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-base font-bold text-white flex items-center gap-2">
          <span>Active Device Tutorials</span>
          <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 text-[10px] font-mono"><?= count($allGuides) ?> Total</span>
        </h2>
        <p class="text-xs text-slate-400">Re-order via sort order, edit steps, customize colors and Lucide icons.</p>
      </div>
    </div>

    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
      <?php if (empty($allGuides)): ?>
        <div class="p-12 text-center text-slate-500 text-xs">
          <i data-lucide="book-open" class="w-10 h-10 mx-auto mb-3 text-slate-600"></i>
          <p class="font-bold text-white text-sm">No device setup guides found.</p>
          <p class="mt-1">Click "Add New Device Guide" to create one.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="bg-slate-900/90 text-slate-400 font-mono text-[10px] uppercase border-b border-slate-800">
                <th class="p-4 w-12 text-center">Order</th>
                <th class="p-4">Device Guide</th>
                <th class="p-4">Quick Nav Anchor</th>
                <th class="p-4">Setup Time</th>
                <th class="p-4 text-center">Steps Count</th>
                <th class="p-4 text-center">Status</th>
                <th class="p-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <?php foreach ($allGuides as $g): 
                $colors = getDeviceColorClasses($g['icon_color']);
              ?>
                <tr class="hover:bg-slate-800/40 transition-colors">
                  
                  <!-- Order -->
                  <td class="p-4 text-center font-mono font-bold text-slate-400">
                    <?= (int)$g['sort_order'] ?>
                  </td>

                  <!-- Device Guide & Icon -->
                  <td class="p-4">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-xl <?= $colors['bg'] ?> <?= $colors['text'] ?> border <?= $colors['border'] ?> flex items-center justify-center flex-shrink-0">
                        <i data-lucide="<?= htmlspecialchars($g['icon']) ?>" class="w-4 h-4"></i>
                      </div>
                      <div>
                        <div class="font-bold text-white text-xs"><?= htmlspecialchars($g['device_name']) ?></div>
                        <div class="text-[10px] font-mono text-cyan-400 mt-0.5">#<?= htmlspecialchars($g['device_slug']) ?></div>
                      </div>
                    </div>
                  </td>

                  <!-- Quick Nav Anchor -->
                  <td class="p-4">
                    <span class="px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-700 text-slate-300 font-semibold text-[11px]">
                      <?= htmlspecialchars($g['nav_title']) ?>
                    </span>
                  </td>

                  <!-- Setup Time -->
                  <td class="p-4 text-slate-400 font-mono text-[11px]">
                    <?= htmlspecialchars($g['setup_time']) ?>
                  </td>

                  <!-- Steps Count -->
                  <td class="p-4 text-center">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-cyan-500/15 text-cyan-300 border border-cyan-500/30 text-[10px] font-mono font-bold">
                      <i data-lucide="list-ordered" class="w-3 h-3"></i> <?= count($g['steps']) ?> Steps
                    </span>
                  </td>

                  <!-- Status -->
                  <td class="p-4 text-center">
                    <form action="setup_guides.php" method="POST" class="inline">
                      <input type="hidden" name="action" value="toggle_status">
                      <input type="hidden" name="id" value="<?= $g['id'] ?>">
                      <?php if ((int)$g['is_active']): ?>
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
                      <button type="button" onclick='openEditGuideModal(<?= json_encode($g) ?>)' class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-cyan-400 hover:bg-slate-700 transition-colors cursor-pointer" title="Edit Guide & Steps">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                      </button>

                      <!-- Delete Button -->
                      <form action="setup_guides.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete \'<?= htmlspecialchars(addslashes($g['device_name'])) ?>\'?');">
                        <input type="hidden" name="action" value="delete_guide">
                        <input type="hidden" name="id" value="<?= $g['id'] ?>">
                        <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors cursor-pointer" title="Delete Guide">
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

  <!-- SECTION 2: PAGE HEADER & HELP ASSISTANCE BANNER SETTINGS -->
  <div class="glass-card rounded-2xl border border-slate-800 p-6 sm:p-7">
    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
      <div class="w-9 h-9 rounded-xl bg-cyan-500/15 text-cyan-400 flex items-center justify-center">
        <i data-lucide="sliders" class="w-5 h-5"></i>
      </div>
      <div>
        <h3 class="font-bold text-white text-base">Setup Page Header & WhatsApp Help Banner</h3>
        <p class="text-xs text-slate-400 mt-0.5">Control the top hero title, badge, introduction, and the bottom live assistance banner.</p>
      </div>
    </div>

    <form action="setup_guides.php" method="POST" class="space-y-5">
      <input type="hidden" name="action" value="save_page_settings">

      <!-- Hero Section -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Page Hero Badge Text</label>
          <input type="text" name="setup_page_badge" value="<?= htmlspecialchars($setupPageSettings['badge']) ?>" placeholder="e.g. 3-Minute Tutorials" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Page Main Headline</label>
          <input type="text" name="setup_page_title" value="<?= htmlspecialchars($setupPageSettings['title']) ?>" placeholder="e.g. Device Setup & App Installation Guides" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Page Hero Subtitle / Description</label>
        <textarea name="setup_page_subtitle" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400"><?= htmlspecialchars($setupPageSettings['subtitle']) ?></textarea>
      </div>

      <div class="pt-4 border-t border-slate-800/80">
        <h4 class="text-xs font-bold uppercase tracking-wider text-cyan-400 mb-4 flex items-center gap-1.5">
          <i data-lucide="message-square" class="w-3.5 h-3.5"></i> Live Assistance Banner (Bottom of Page)
        </h4>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Help Banner Title</label>
            <input type="text" name="setup_help_title" value="<?= htmlspecialchars($setupPageSettings['help_title']) ?>" placeholder="e.g. Need Live Setup Assistance?" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Button Text</label>
            <input type="text" name="setup_help_btn_text" value="<?= htmlspecialchars($setupPageSettings['help_btn_text']) ?>" placeholder="e.g. Chat With Technician" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Help Banner Description</label>
            <input type="text" name="setup_help_subtitle" value="<?= htmlspecialchars($setupPageSettings['help_subtitle']) ?>" placeholder="e.g. Our technicians are available 24/7 on WhatsApp..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Custom Button URL (Optional)</label>
            <input type="text" name="setup_help_btn_link" value="<?= htmlspecialchars($setupPageSettings['help_btn_link']) ?>" placeholder="Leave empty to use main WhatsApp link" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
            <span class="text-[10px] text-slate-500 mt-1 block">Default: <?= htmlspecialchars(WHATSAPP_LINK) ?></span>
          </div>
        </div>
      </div>

      <div class="pt-4 flex items-center justify-end">
        <button type="submit" class="btn-glow-cyan px-6 py-2.5 rounded-xl text-white font-bold text-xs shadow-lg shadow-cyan-500/20 cursor-pointer">
          Save Setup Page Settings
        </button>
      </div>
    </form>
  </div>

</div>

<!-- CREATE / EDIT DEVICE GUIDE MODAL -->
<div id="guide-modal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md hidden flex items-center justify-center p-4">
  <div class="bg-[#0b1222] border border-slate-800 rounded-2xl w-full max-w-2xl p-6 sm:p-7 shadow-2xl relative max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation();">
    
    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-800">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center">
          <i data-lucide="tv" class="w-5 h-5"></i>
        </div>
        <h3 id="modal-title" class="font-bold text-white text-base">Add New Device Guide</h3>
      </div>
      <button type="button" onclick="closeGuideModal()" class="p-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white transition-colors cursor-pointer">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <form action="setup_guides.php" method="POST" class="space-y-4">
      <input type="hidden" name="action" value="save_guide">
      <input type="hidden" id="guide-id" name="id" value="0">

      <!-- Slug & Device Name -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Anchor Slug <span class="text-rose-400">*</span></label>
          <input required type="text" id="modal-slug" name="device_slug" placeholder="e.g. firestick or roku" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono lowercase focus:outline-none focus:border-cyan-400">
          <span class="text-[10px] text-slate-500 mt-1 block">Used in URL anchor /setup#slug</span>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Quick Nav Title <span class="text-rose-400">*</span></label>
          <input required type="text" id="modal-nav" name="nav_title" placeholder="e.g. 🔥 Amazon Firestick" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-medium focus:outline-none focus:border-cyan-400">
          <span class="text-[10px] text-slate-500 mt-1 block">Button label at top of page</span>
        </div>
      </div>

      <!-- Main Headline Name -->
      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Device Main Headline Name <span class="text-rose-400">*</span></label>
        <input required type="text" id="modal-name" name="device_name" placeholder="e.g. Amazon Firestick / Fire TV Cube (Recommended)" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-medium focus:outline-none focus:border-cyan-400">
      </div>

      <!-- Setup Time & Sort Order -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Setup Time Subtitle</label>
          <input type="text" id="modal-time" name="setup_time" placeholder="e.g. Total Setup Time: ~3 minutes" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Sort Order</label>
          <input type="number" id="modal-sort" name="sort_order" value="1" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:outline-none focus:border-cyan-400">
        </div>
      </div>

      <!-- Icon & Color -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Lucide Icon</label>
          <select id="modal-icon" name="icon" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400 cursor-pointer">
            <?php foreach ($availableIcons as $ico): ?>
              <option value="<?= $ico ?>"><?= $ico ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Icon Theme Accent</label>
          <select id="modal-color" name="icon_color" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400 cursor-pointer">
            <?php foreach ($availableColors as $col): ?>
              <option value="<?= $col ?>"><?= ucfirst($col) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- DYNAMIC STEPS BUILDER -->
      <div class="pt-3 border-t border-slate-800">
        <div class="flex items-center justify-between mb-2.5">
          <label class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
            <i data-lucide="list-ordered" class="w-4 h-4 text-cyan-400"></i> Step-by-Step Instructions
          </label>
          <button type="button" onclick="addStepRow('', '')" class="px-3 py-1.5 rounded-lg bg-cyan-500/15 hover:bg-cyan-500/25 text-cyan-300 border border-cyan-500/30 text-[11px] font-bold flex items-center gap-1 cursor-pointer transition-colors">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Step
          </button>
        </div>

        <div id="steps-container" class="space-y-3">
          <!-- Populated dynamically via JS -->
        </div>
      </div>

      <!-- Active Toggle -->
      <div class="pt-2">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" id="modal-active" name="is_active" value="1" checked class="w-4 h-4 rounded text-cyan-500 focus:ring-0 bg-slate-900 border-slate-700">
          <span class="text-xs font-semibold text-slate-300">Publish this device guide immediately (Active)</span>
        </label>
      </div>

      <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-800">
        <button type="button" onclick="closeGuideModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold text-xs transition-colors cursor-pointer">
          Cancel
        </button>
        <button type="submit" class="btn-glow-cyan px-5 py-2.5 rounded-xl text-white font-bold text-xs shadow-lg shadow-cyan-500/20 cursor-pointer">
          Save Device Guide
        </button>
      </div>
    </form>

  </div>
</div>

<script>
  function createStepElement(title = '', desc = '') {
    const div = document.createElement('div');
    div.className = 'step-row bg-slate-900/90 p-3 rounded-xl border border-slate-800 space-y-2 relative';
    div.innerHTML = `
      <div class="flex items-center justify-between gap-2">
        <input required type="text" name="step_title[]" value="${escapeHtml(title)}" placeholder="e.g. Step 1: Install App" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-700 text-white text-xs font-bold focus:outline-none focus:border-cyan-400">
        <button type="button" onclick="this.closest('.step-row').remove()" class="p-1.5 rounded text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 cursor-pointer flex-shrink-0" title="Remove Step">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>
      <textarea required name="step_desc[]" rows="2" placeholder="Step instructions & download codes (HTML supported e.g. <span> or <strong>)..." class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400 leading-relaxed">${escapeHtml(desc)}</textarea>
    `;
    return div;
  }

  function addStepRow(title = '', desc = '') {
    const container = document.getElementById('steps-container');
    const el = createStepElement(title, desc);
    container.appendChild(el);
    if (window.lucide) lucide.createIcons();
  }

  function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }

  function openAddGuideModal() {
    document.getElementById('modal-title').textContent = 'Add New Device Guide';
    document.getElementById('guide-id').value = '0';
    document.getElementById('modal-slug').value = '';
    document.getElementById('modal-name').value = '';
    document.getElementById('modal-nav').value = '';
    document.getElementById('modal-time').value = 'Total Setup Time: ~3 minutes';
    document.getElementById('modal-sort').value = '10';
    document.getElementById('modal-icon').value = 'tv';
    document.getElementById('modal-color').value = 'cyan';
    document.getElementById('modal-active').checked = true;

    const container = document.getElementById('steps-container');
    container.innerHTML = '';
    addStepRow('Step 1: Download & Install App', 'Search and download the recommended IPTV player on your device.');
    addStepRow('Step 2: Enter Xtream Credentials', 'Open the app, choose Xtream Codes API, and input your server link, username, and password.');

    document.getElementById('guide-modal').classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
  }

  function openEditGuideModal(guide) {
    document.getElementById('modal-title').textContent = 'Edit Guide: ' + guide.device_name;
    document.getElementById('guide-id').value = guide.id;
    document.getElementById('modal-slug').value = guide.device_slug;
    document.getElementById('modal-name').value = guide.device_name;
    document.getElementById('modal-nav').value = guide.nav_title;
    document.getElementById('modal-time').value = guide.setup_time || '';
    document.getElementById('modal-sort').value = guide.sort_order || '0';
    document.getElementById('modal-icon').value = guide.icon || 'tv';
    document.getElementById('modal-color').value = guide.icon_color || 'cyan';
    document.getElementById('modal-active').checked = (parseInt(guide.is_active) === 1);

    const container = document.getElementById('steps-container');
    container.innerHTML = '';
    const steps = guide.steps || [];
    if (steps.length > 0) {
      steps.forEach(s => {
        container.appendChild(createStepElement(s.title || '', s.desc || ''));
      });
    } else {
      addStepRow('Step 1: Download App', '');
    }

    document.getElementById('guide-modal').classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
  }

  function closeGuideModal() {
    document.getElementById('guide-modal').classList.add('hidden');
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeGuideModal();
  });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

