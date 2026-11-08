<?php
/**
 * LIVETVMARKET - Admin Legal & Policy Pages Manager
 * Manage Terms & Conditions, Privacy Policy, Refund Policy, DMCA, and Copyright notice content.
 */
$adminTitle = 'Legal & Policy Pages';
$adminPage  = 'legal';

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/db.php';

$successMsg = '';
$errorMsg   = '';

$slugs = ['terms', 'privacy', 'refund-policy', 'dmca', 'copyright'];
$currentTab = $_GET['tab'] ?? 'terms';
if (!in_array($currentTab, $slugs, true)) {
    $currentTab = 'terms';
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $postedSlug = $_POST['slug'] ?? '';

    if ($action === 'save_page' && in_array($postedSlug, $slugs, true)) {
        $title           = trim($_POST['title'] ?? '');
        $badge           = trim($_POST['badge'] ?? 'Legal Agreement');
        $icon            = trim($_POST['icon'] ?? 'shield');
        $lastUpdated     = trim($_POST['last_updated'] ?? date('F Y'));
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $content         = trim($_POST['content'] ?? '');
        $isActive        = isset($_POST['is_active']) ? 1 : 0;

        if (empty($title) || empty($content)) {
            $errorMsg = 'Page Title and Content cannot be empty.';
        } else {
            $res = updateLegalPage($postedSlug, [
                'title'            => $title,
                'badge'            => $badge,
                'icon'             => $icon,
                'last_updated'     => $lastUpdated,
                'meta_description' => $metaDescription,
                'content'          => $content,
                'is_active'        => $isActive
            ]);

            if ($res) {
                $successMsg = "Legal document '{$title}' saved successfully!";
                $currentTab = $postedSlug;
            } else {
                $errorMsg = 'Error saving legal document to database.';
            }
        }
    } elseif ($action === 'reset_default' && in_array($postedSlug, $slugs, true)) {
        $defaultsFile = __DIR__ . '/../config/legal_content.php';
        if (file_exists($defaultsFile)) {
            $defaults = require $defaultsFile;
            if (isset($defaults[$postedSlug])) {
                $def = $defaults[$postedSlug];
                updateLegalPage($postedSlug, [
                    'title'            => $def['title'],
                    'badge'            => $def['badge'],
                    'icon'             => $def['icon'],
                    'last_updated'     => $def['last_updated'],
                    'meta_description' => $def['meta_description'],
                    'content'          => $def['content'],
                    'is_active'        => 1
                ]);
                $successMsg = "Reset '{$def['title']}' to default production content!";
                $currentTab = $postedSlug;
            }
        }
    }
}

// Fetch current page data from DB
$currentPage = getLegalPage($currentTab);
if (!$currentPage) {
    // If not in DB, fallback to default content file
    $defaultsFile = __DIR__ . '/../config/legal_content.php';
    if (file_exists($defaultsFile)) {
        $defaults = require $defaultsFile;
        $currentPage = $defaults[$currentTab] ?? null;
    }
}

$tabMeta = [
    'terms' => [
        'name'  => 'Terms & Conditions',
        'badge' => 'Agreement',
        'icon'  => 'file-text',
        'url'   => '/terms'
    ],
    'privacy' => [
        'name'  => 'Privacy Policy',
        'badge' => 'Zero-Logs',
        'icon'  => 'shield-check',
        'url'   => '/privacy'
    ],
    'refund-policy' => [
        'name'  => 'Refund Policy',
        'badge' => '7-Day SLA',
        'icon'  => 'refresh-cw',
        'url'   => '/refund-policy'
    ],
    'dmca' => [
        'name'  => 'DMCA Policy',
        'badge' => 'IP Notice',
        'icon'  => 'scale',
        'url'   => '/dmca'
    ],
    'copyright' => [
        'name'  => 'Copyright Notice',
        'badge' => 'Trademarks',
        'icon'  => 'copyright',
        'url'   => '/copyright'
    ],
];
?>

<div class="max-w-6xl">

  <!-- PAGE HEADER -->
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="px-2.5 py-0.5 rounded-full bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 font-mono text-[10px] font-bold uppercase">
          Dynamic Content Manager
        </span>
      </div>
      <h1 class="text-2xl font-black text-white">Legal & Compliance Pages</h1>
      <p class="text-xs text-slate-400 mt-1">Control your Terms of Service, Privacy Policy, Refund SLA, DMCA compliance, and Copyright notices.</p>
    </div>

    <div class="flex items-center gap-2">
      <a href="<?= htmlspecialchars($tabMeta[$currentTab]['url']) ?>" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs flex items-center gap-2 border border-slate-700 transition-all">
        <i data-lucide="external-link" class="w-4 h-4 text-cyan-400"></i>
        <span>View Live Page</span>
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

  <!-- PAGE SELECTOR TABS -->
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 mb-8">
    <?php foreach ($tabMeta as $slugKey => $meta): 
      $isActiveTab = ($slugKey === $currentTab);
    ?>
      <a href="legal.php?tab=<?= $slugKey ?>" 
         class="p-3.5 rounded-2xl transition-all border <?= $isActiveTab ? 'bg-cyan-500/15 border-cyan-500/40 text-white shadow-lg shadow-cyan-500/10' : 'bg-slate-900/60 hover:bg-slate-800/80 border-slate-800 text-slate-400 hover:text-white' ?>">
        <div class="flex items-center justify-between mb-2">
          <div class="w-7 h-7 rounded-lg flex items-center justify-center <?= $isActiveTab ? 'bg-cyan-500/30 text-cyan-300' : 'bg-slate-800 text-slate-500' ?>">
            <i data-lucide="<?= $meta['icon'] ?>" class="w-4 h-4"></i>
          </div>
          <span class="text-[9px] font-mono uppercase px-1.5 py-0.5 rounded <?= $isActiveTab ? 'bg-cyan-500/20 text-cyan-300 font-bold' : 'bg-slate-800 text-slate-500' ?>">
            <?= $meta['badge'] ?>
          </span>
        </div>
        <div class="font-bold text-xs leading-tight <?= $isActiveTab ? 'text-white' : 'text-slate-300' ?>">
          <?= htmlspecialchars($meta['name']) ?>
        </div>
        <div class="text-[10px] font-mono text-slate-500 mt-1 truncate">
          <?= htmlspecialchars($meta['url']) ?>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- EDITOR CARD -->
  <div class="glass-card rounded-2xl p-6 sm:p-8 border border-slate-800">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 mb-6 border-b border-slate-800">
      <div>
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
          <i data-lucide="<?= htmlspecialchars($currentPage['icon'] ?? 'file-text') ?>" class="w-5 h-5 text-cyan-400"></i>
          Editing: <span class="text-cyan-300"><?= htmlspecialchars($currentPage['title'] ?? 'Legal Document') ?></span>
        </h2>
        <p class="text-xs text-slate-400 mt-1">Changes are published immediately to <code class="text-cyan-400 font-mono"><?= htmlspecialchars($tabMeta[$currentTab]['url']) ?></code>.</p>
      </div>

      <div class="flex items-center gap-2">
        <form method="POST" onsubmit="return confirm('Reset this legal page back to its default production template? Any manual edits will be overwritten.');">
          <input type="hidden" name="action" value="reset_default">
          <input type="hidden" name="slug" value="<?= htmlspecialchars($currentTab) ?>">
          <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs font-semibold border border-slate-700 transition-colors flex items-center gap-1.5 cursor-pointer">
            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
            <span>Reset to Default</span>
          </button>
        </form>
      </div>
    </div>

    <form method="POST" action="legal.php?tab=<?= htmlspecialchars($currentTab) ?>" class="space-y-6">
      <input type="hidden" name="action" value="save_page">
      <input type="hidden" name="slug" value="<?= htmlspecialchars($currentTab) ?>">

      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Page Title -->
        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Document Title</label>
          <input type="text" name="title" required value="<?= htmlspecialchars($currentPage['title'] ?? '') ?>"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
        </div>

        <!-- Badge Label -->
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Hero Badge Text</label>
          <input type="text" name="badge" value="<?= htmlspecialchars($currentPage['badge'] ?? 'Legal Agreement') ?>"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
        </div>

        <!-- Icon Picker -->
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Lucide Icon</label>
          <select name="icon" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
            <?php 
            $icons = ['file-text', 'shield-check', 'refresh-cw', 'scale', 'copyright', 'shield', 'lock', 'alert-circle', 'check-circle'];
            $curIcon = $currentPage['icon'] ?? 'shield';
            foreach ($icons as $ic): ?>
              <option value="<?= $ic ?>" <?= ($curIcon === $ic) ? 'selected' : '' ?>><?= $ic ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Last Updated -->
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Last Updated Display</label>
          <input type="text" name="last_updated" value="<?= htmlspecialchars($currentPage['last_updated'] ?? date('F Y')) ?>"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
        </div>

        <!-- Published Status -->
        <div class="flex items-center gap-3 pt-6">
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="is_active" value="1" <?= (!isset($currentPage['is_active']) || $currentPage['is_active'] == 1) ? 'checked' : '' ?> class="sr-only peer">
            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500"></div>
          </label>
          <span class="text-xs font-semibold text-slate-300">Page Published & Active</span>
        </div>

      </div>

      <!-- Meta Description -->
      <div>
        <label class="block text-xs font-semibold text-slate-300 mb-1.5">SEO Meta Description</label>
        <textarea name="meta_description" rows="2" placeholder="Brief summary of this legal document for Google search results..."
          class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400"><?= htmlspecialchars($currentPage['meta_description'] ?? '') ?></textarea>
      </div>

      <!-- HTML Content Editor with Shortcuts Toolbar -->
      <div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
          <label class="block text-xs font-semibold text-slate-300">Document Body Content (HTML &amp; Tailwind Formatted)</label>
          
          <!-- Helper Quick Insert Toolbar -->
          <div class="flex flex-wrap items-center gap-1.5 text-[10px] font-mono">
            <button type="button" onclick="insertTag('h3')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">+ Heading</button>
            <button type="button" onclick="insertTag('p')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">+ Paragraph</button>
            <button type="button" onclick="insertTag('ul')" class="px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">+ Bullet List</button>
            <button type="button" onclick="insertTag('alert')" class="px-2 py-1 rounded bg-cyan-950 text-cyan-300 border border-cyan-800">+ Highlight Box</button>
          </div>
        </div>

        <textarea id="page-content-editor" name="content" rows="18" required
          class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-200 text-xs font-mono leading-relaxed focus:outline-none focus:border-cyan-400 placeholder-slate-600"><?= htmlspecialchars($currentPage['content'] ?? '') ?></textarea>
        <p class="text-[11px] text-slate-500 mt-1.5">Supports standard HTML markup, headings, paragraphs, styled callout boxes, and Tailwind CSS classes.</p>
      </div>

      <!-- Form Actions -->
      <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <a href="<?= htmlspecialchars($tabMeta[$currentTab]['url']) ?>" target="_blank" class="text-xs text-cyan-400 hover:underline flex items-center gap-1.5">
          <i data-lucide="eye" class="w-4 h-4"></i>
          <span>Open live page in new tab</span>
        </a>

        <button type="submit" class="btn-glow-cyan w-full sm:w-auto px-8 py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-950 shadow-lg cursor-pointer">
          Save Legal Page
        </button>
      </div>

    </form>

  </div>

</div>

<script>
function insertTag(type) {
  const ta = document.getElementById('page-content-editor');
  if (!ta) return;
  const start = ta.selectionStart;
  const end = ta.selectionEnd;
  const text = ta.value;

  let snippet = '';
  if (type === 'h3') {
    snippet = '\n  <section class="space-y-3">\n    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">\n      <span class="w-7 h-7 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs font-mono font-bold">09</span>\n      New Section Title\n    </h3>\n    <p class="text-sm text-slate-600 leading-relaxed">\n      Section description text goes here.\n    </p>\n  </section>\n';
  } else if (type === 'p') {
    snippet = '<p class="text-sm text-slate-600 leading-relaxed">\n  Enter paragraph text here.\n</p>\n';
  } else if (type === 'ul') {
    snippet = '<ul class="list-disc pl-5 text-sm text-slate-600 space-y-1.5">\n  <li>List item one</li>\n  <li>List item two</li>\n</ul>\n';
  } else if (type === 'alert') {
    snippet = '<div class="p-4 rounded-xl bg-cyan-50 border border-cyan-200 text-slate-800 text-xs leading-relaxed">\n  <strong>Notice:</strong> Highlighted alert message goes here.\n</div>\n';
  }

  ta.value = text.substring(0, start) + snippet + text.substring(end);
  ta.focus();
  ta.selectionStart = ta.selectionEnd = start + snippet.length;
}
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
