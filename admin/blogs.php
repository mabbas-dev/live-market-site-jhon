<?php
/**
 * LIVETVMARKET - Admin Blog Manager
 * Full control over blog posts: Create, Edit, Delete, File/URL Thumbnail upload,
 * Full Rich WYSIWYG Editor, Live Preview, and Publishing status.
 */
$adminTitle = 'Blog Articles Manager';
$adminPage = 'blogs';

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/blogs_data.php';

$successMsg = '';
$errorMsg = '';

$mode = $_GET['mode'] ?? 'list'; // 'list', 'create', 'edit'
$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['post_action'] ?? '';

    if ($postAction === 'save') {
        $id = (int)($_POST['article_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $category = trim($_POST['category'] ?? 'Setup Guides');
        $categoryColor = trim($_POST['category_color'] ?? 'cyan');
        $author = trim($_POST['author'] ?? 'Alex Rivera');
        $authorRole = trim($_POST['author_role'] ?? 'Senior Streaming Technician');
        $readTime = trim($_POST['read_time'] ?? '4 min read');
        $publishDate = trim($_POST['date'] ?? date('F j, Y'));
        $content = trim($_POST['content'] ?? '');
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        // Auto-generate slug if empty
        if (empty($slug) && !empty($title)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        }

        // Author Avatar initials
        $words = explode(' ', $author);
        $authorAvatar = '';
        foreach ($words as $w) {
            if (!empty($w)) $authorAvatar .= strtoupper($w[0]);
        }
        $authorAvatar = substr($authorAvatar ?: 'AR', 0, 2);

        // Thumbnail Handling (File Upload OR URL)
        $thumbnailUrl = trim($_POST['thumbnail_url'] ?? '');

        if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['thumbnail_file'];
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (in_array($fileExt, $allowedExts)) {
                $uploadDir = __DIR__ . '/../assets/uploads/blogs/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $newFileName = 'blog_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $thumbnailUrl = 'assets/uploads/blogs/' . $newFileName;
                } else {
                    $errorMsg = "Failed to upload thumbnail file.";
                }
            } else {
                $errorMsg = "Invalid image file type. Please upload JPG, PNG, WEBP, or GIF.";
            }
        }

        if (empty($title) || empty($content)) {
            $errorMsg = "Article Title and Content cannot be empty.";
        } else {
            try {
                if ($id > 0) {
                    // Update existing
                    // Check if slug is unique to other records
                    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `blogs` WHERE `slug` = ? AND `id` != ?");
                    $stmtCheck->execute([$slug, $id]);
                    if ($stmtCheck->fetchColumn() > 0) {
                        $slug .= '-' . $id;
                    }

                    // If thumbnail wasn't updated, preserve existing
                    if (empty($thumbnailUrl)) {
                        $stmtThumb = $pdo->prepare("SELECT `thumbnail_url` FROM `blogs` WHERE `id` = ?");
                        $stmtThumb->execute([$id]);
                        $thumbnailUrl = $stmtThumb->fetchColumn() ?: null;
                    }

                    $stmt = $pdo->prepare("UPDATE `blogs` SET 
                        `title` = ?, `slug` = ?, `excerpt` = ?, `category` = ?, `category_color` = ?,
                        `author` = ?, `author_role` = ?, `author_avatar` = ?, `thumbnail_url` = ?,
                        `date` = ?, `read_time` = ?, `content` = ?, `is_published` = ?
                        WHERE `id` = ?");
                    $stmt->execute([
                        $title, $slug, $excerpt, $category, $categoryColor,
                        $author, $authorRole, $authorAvatar, $thumbnailUrl,
                        $publishDate, $readTime, $content, $isPublished, $id
                    ]);

                    $successMsg = "Article '{$title}' updated successfully!";
                    $mode = 'list';
                } else {
                    // Create new
                    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `blogs` WHERE `slug` = ?");
                    $stmtCheck->execute([$slug]);
                    if ($stmtCheck->fetchColumn() > 0) {
                        $slug .= '-' . time();
                    }

                    $stmt = $pdo->prepare("INSERT INTO `blogs` 
                        (`title`, `slug`, `excerpt`, `category`, `category_color`, `author`, `author_role`, `author_avatar`, `thumbnail_url`, `date`, `read_time`, `content`, `is_published`, `views`) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
                    $stmt->execute([
                        $title, $slug, $excerpt, $category, $categoryColor,
                        $author, $authorRole, $authorAvatar, $thumbnailUrl,
                        $publishDate, $readTime, $content, $isPublished
                    ]);

                    $successMsg = "New article '{$title}' published successfully!";
                    $mode = 'list';
                }
            } catch (Exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    } else if ($postAction === 'delete') {
        $delId = (int)($_POST['article_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM `blogs` WHERE `id` = ?");
            $stmt->execute([$delId]);
            $successMsg = "Article deleted successfully!";
        } catch (Exception $e) {
            $errorMsg = "Error deleting article: " . $e->getMessage();
        }
    } else if ($postAction === 'toggle_status') {
        $togId = (int)($_POST['article_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("UPDATE `blogs` SET `is_published` = IF(`is_published` = 1, 0, 1) WHERE `id` = ?");
            $stmt->execute([$togId]);
            $successMsg = "Article status updated!";
        } catch (Exception $e) {
            $errorMsg = "Error toggling status: " . $e->getMessage();
        }
    }
}

// Ensure articles seeded
if ($pdo) {
    ensureBlogsSeeded($pdo);
}

// Fetch article for editing if requested
$editArticle = null;
if ($mode === 'edit' && $editId > 0) {
    $editArticle = getBlogById($editId);
    if (!$editArticle) {
        $mode = 'create';
    }
}

// Fetch all articles for listing
$allArticles = [];
try {
    $stmt = $pdo->query("SELECT * FROM `blogs` ORDER BY `id` DESC");
    $allArticles = $stmt->fetchAll();
} catch (Exception $e) {
    $allArticles = getDefaultBlogArticles();
}

$totalArticles = count($allArticles);
$publishedCount = count(array_filter($allArticles, fn($a) => !empty($a['is_published'])));
$draftCount = $totalArticles - $publishedCount;
$totalViews = array_sum(array_column($allArticles, 'views'));
?>

<!-- Quill Rich Text Editor Styles -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
  .ql-toolbar {
    background: #0f172a !important;
    border-color: #334155 !important;
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
  }
  .ql-container {
    background: #020617 !important;
    border-color: #334155 !important;
    border-bottom-left-radius: 0.75rem;
    border-bottom-right-radius: 0.75rem;
    color: #e2e8f0 !important;
    font-family: inherit;
    font-size: 0.875rem;
    min-height: 280px;
  }
  .ql-stroke { stroke: #94a3b8 !important; }
  .ql-fill { fill: #94a3b8 !important; }
  .ql-picker { color: #94a3b8 !important; }
  .ql-picker-options { background: #0f172a !important; border-color: #334155 !important; }
  .ql-editor img {
    border-radius: 0.75rem;
    max-width: 100%;
    margin: 1rem 0;
  }
  /* Article preview styling */
  .article-body { color: #334155; font-size: 0.95rem; line-height: 1.75; }
  .article-body h1, .article-body h2, .article-body h3, .article-body h4 {
    font-weight: 800; color: #0f172a; margin-top: 1.5rem; margin-bottom: 0.75rem;
  }
  .article-body h2 { font-size: 1.35rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.4rem; }
  .article-body h3 { font-size: 1.15rem; }
  .article-body p { margin-bottom: 1rem; color: #334155; }
  .article-body img {
    max-width: 100%; height: auto; border-radius: 1rem; margin: 1.5rem auto; display: block;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0;
  }
  .article-body ul { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
  .article-body ol { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
  .article-body li { margin-bottom: 0.35rem; }
  .article-body blockquote {
    border-left: 4px solid #0891b2; background: #f8fafc; padding: 0.75rem 1rem;
    border-radius: 0 0.5rem 0.5rem 0; font-style: italic; color: #1e293b; margin: 1.25rem 0;
  }
  .article-body pre {
    background: #0f172a; color: #38bdf8; padding: 1rem; border-radius: 0.75rem; overflow-x: auto; margin: 1.25rem 0;
  }
</style>

<div class="max-w-6xl">

  <!-- TOP HEADER -->
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-white flex items-center gap-2.5">
        <i data-lucide="newspaper" class="w-6 h-6 text-cyan-400"></i>
        <span>Blog Articles & Content Manager</span>
      </h1>
      <p class="text-xs text-slate-400 mt-1">
        Create, edit, publish rich articles with full WYSIWYG editor, custom thumbnail images, and SEO preview.
      </p>
    </div>

    <div class="flex items-center gap-3">
      <?php if ($mode === 'list'): ?>
        <a href="blogs.php?mode=create" class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-2 transition-all shadow-md shadow-cyan-500/20">
          <i data-lucide="plus-circle" class="w-4 h-4"></i>
          <span>Write New Article</span>
        </a>
      <?php else: ?>
        <a href="blogs.php" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs flex items-center gap-2 transition-all">
          <i data-lucide="arrow-left" class="w-4 h-4"></i>
          <span>Back to Articles List</span>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- SUCCESS / ERROR ALERTS -->
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

  <?php if ($mode === 'list'): ?>
    <!-- =========================================================================
         LIST ARTICLES VIEW
         ========================================================================= -->
    <!-- Metrics Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
      <div class="glass-card rounded-2xl p-4 border border-slate-800 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-cyan-500/15 text-cyan-400 flex items-center justify-center">
          <i data-lucide="file-text" class="w-5 h-5"></i>
        </div>
        <div>
          <p class="text-[11px] text-slate-400 font-medium">Total Articles</p>
          <p class="text-lg font-black text-white"><?= $totalArticles ?></p>
        </div>
      </div>

      <div class="glass-card rounded-2xl p-4 border border-slate-800 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center">
          <i data-lucide="check-circle-2" class="w-5 h-5"></i>
        </div>
        <div>
          <p class="text-[11px] text-slate-400 font-medium">Published Live</p>
          <p class="text-lg font-black text-emerald-400"><?= $publishedCount ?></p>
        </div>
      </div>

      <div class="glass-card rounded-2xl p-4 border border-slate-800 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center">
          <i data-lucide="file-clock" class="w-5 h-5"></i>
        </div>
        <div>
          <p class="text-[11px] text-slate-400 font-medium">Drafts</p>
          <p class="text-lg font-black text-amber-400"><?= $draftCount ?></p>
        </div>
      </div>

      <div class="glass-card rounded-2xl p-4 border border-slate-800 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-purple-500/15 text-purple-400 flex items-center justify-center">
          <i data-lucide="eye" class="w-5 h-5"></i>
        </div>
        <div>
          <p class="text-[11px] text-slate-400 font-medium">Total Views</p>
          <p class="text-lg font-black text-purple-300"><?= number_format($totalViews) ?></p>
        </div>
      </div>
    </div>

    <!-- Articles Table Card -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
      <div class="p-5 border-b border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <i data-lucide="list" class="w-4 h-4 text-cyan-400"></i>
          <h2 class="text-sm font-bold text-white">All Articles (<?= count($allArticles) ?>)</h2>
        </div>
        
        <!-- Search filter input in admin -->
        <div class="relative w-full sm:w-64">
          <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
          <input type="text" id="admin-blog-search" placeholder="Search articles..."
            class="w-full pl-9 pr-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:outline-none focus:border-cyan-400">
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-900/80 text-slate-400 uppercase font-mono text-[10px] border-b border-slate-800">
            <tr>
              <th class="p-4">Article & Thumbnail</th>
              <th class="p-4">Category</th>
              <th class="p-4">Author</th>
              <th class="p-4">Views</th>
              <th class="p-4">Status</th>
              <th class="p-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="admin-articles-tbody" class="divide-y divide-slate-800/60 text-slate-300">
            <?php foreach ($allArticles as $art): 
              $thumb = !empty($art['thumbnail_url']) ? $art['thumbnail_url'] : '';
            ?>
              <tr class="hover:bg-slate-800/30 transition-colors article-row" data-search-text="<?= htmlspecialchars(strtolower($art['title'] . ' ' . $art['category'] . ' ' . $art['slug'])) ?>">
                <td class="p-4 max-w-sm">
                  <div class="flex items-start gap-3">
                    <?php if (!empty($thumb)): ?>
                      <img src="<?= htmlspecialchars(formatBlogThumbnailUrl($thumb)) ?>" alt="Thumb" class="w-14 h-11 rounded-lg object-cover border border-slate-700 flex-shrink-0 bg-slate-800">
                    <?php else: ?>
                      <div class="w-14 h-11 rounded-lg bg-gradient-to-tr <?= htmlspecialchars($art['gradient'] ?? 'from-cyan-600 to-blue-700') ?> flex items-center justify-center text-white text-base flex-shrink-0">
                        <i data-lucide="<?= htmlspecialchars($art['icon'] ?? 'file-text') ?>" class="w-5 h-5"></i>
                      </div>
                    <?php endif; ?>
                    <div>
                      <h4 class="font-bold text-white text-xs hover:text-cyan-400 transition-colors line-clamp-2">
                        <a href="blogs.php?mode=edit&id=<?= $art['id'] ?>"><?= htmlspecialchars($art['title']) ?></a>
                      </h4>
                      <div class="flex items-center gap-2 text-[10px] text-slate-500 font-mono mt-1">
                        <span>/blog/<?= htmlspecialchars($art['slug']) ?></span>
                        <span>•</span>
                        <span><?= htmlspecialchars($art['date']) ?></span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="p-4">
                  <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 whitespace-nowrap">
                    <?= htmlspecialchars($art['category']) ?>
                  </span>
                </td>
                <td class="p-4 whitespace-nowrap">
                  <span class="text-slate-300 font-medium"><?= htmlspecialchars($art['author']) ?></span>
                </td>
                <td class="p-4 font-mono text-cyan-400 whitespace-nowrap">
                  <?= number_format((int)($art['views'] ?? 0)) ?>
                </td>
                <td class="p-4 whitespace-nowrap">
                  <form method="POST" action="blogs.php" class="inline">
                    <input type="hidden" name="post_action" value="toggle_status">
                    <input type="hidden" name="article_id" value="<?= $art['id'] ?>">
                    <?php if (!empty($art['is_published'])): ?>
                      <button type="submit" title="Click to unpublish" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 flex items-center gap-1 cursor-pointer hover:bg-emerald-500/25 transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Published
                      </button>
                    <?php else: ?>
                      <button type="submit" title="Click to publish" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-700 text-slate-400 border border-slate-600 flex items-center gap-1 cursor-pointer hover:bg-slate-600 transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                        Draft
                      </button>
                    <?php endif; ?>
                  </form>
                </td>
                <td class="p-4 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1.5">
                    <a href="../blog/<?= urlencode($art['slug']) ?>" target="_blank" title="View live page"
                      class="p-2 rounded-lg bg-slate-800 text-slate-400 hover:text-cyan-400 hover:bg-slate-700 transition-all">
                      <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    </a>
                    <a href="blogs.php?mode=edit&id=<?= $art['id'] ?>" title="Edit article"
                      class="p-2 rounded-lg bg-cyan-500/15 text-cyan-400 hover:bg-cyan-500/30 transition-all">
                      <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    </a>
                    <form method="POST" action="blogs.php" onsubmit="return confirm('Are you sure you want to delete this article?');" class="inline">
                      <input type="hidden" name="post_action" value="delete">
                      <input type="hidden" name="article_id" value="<?= $art['id'] ?>">
                      <button type="submit" title="Delete article" class="p-2 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition-all cursor-pointer">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php else: ?>
    <!-- =========================================================================
         CREATE / EDIT ARTICLE VIEW
         ========================================================================= -->
    <div class="glass-card rounded-2xl p-6 sm:p-8 border border-slate-800 shadow-xl">
      <div class="flex items-center justify-between pb-5 mb-6 border-b border-slate-800">
        <div>
          <h2 class="text-lg font-bold text-white">
            <?= ($mode === 'edit') ? 'Edit Article: ' . htmlspecialchars($editArticle['title'] ?? '') : 'Create New Blog Article' ?>
          </h2>
          <p class="text-xs text-slate-400">Fill in the fields below. Content supports formatted text, embedded images, lists, and headers.</p>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" id="open-preview-btn" class="px-4 py-2 rounded-xl bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 border border-purple-500/40 text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer">
            <i data-lucide="eye" class="w-4 h-4"></i>
            <span>Live Preview</span>
          </button>
        </div>
      </div>

      <form id="blog-form" action="blogs.php" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="post_action" value="save">
        <input type="hidden" name="article_id" value="<?= $editArticle['id'] ?? 0 ?>">
        <input type="hidden" name="content" id="blog-content-input" value="">

        <!-- Title & Slug -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Article Title *</label>
            <input type="text" id="article-title" name="title" required value="<?= htmlspecialchars($editArticle['title'] ?? '') ?>"
              placeholder="e.g. How to Setup IPTV on Firestick in 2026"
              class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">URL Slug (e.g. how-to-setup-firestick) *</label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs font-mono">/blog/</span>
              <input type="text" id="article-slug" name="slug" required value="<?= htmlspecialchars($editArticle['slug'] ?? '') ?>"
                placeholder="how-to-setup-firestick"
                class="w-full pl-16 pr-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none">
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Leaves empty to auto-generate from title.</p>
          </div>
        </div>

        <!-- Category, Author, Read Time, Date -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Category</label>
            <input type="text" list="category-suggestions" name="category" value="<?= htmlspecialchars($editArticle['category'] ?? 'Setup Guides') ?>"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none">
            <datalist id="category-suggestions">
              <option value="Setup Guides">
              <option value="App Reviews">
              <option value="Cost Comparison">
              <option value="Technology">
              <option value="Live Sports">
              <option value="Bandwidth Guide">
            </datalist>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Author Name</label>
            <input type="text" name="author" value="<?= htmlspecialchars($editArticle['author'] ?? 'Alex Rivera') ?>"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Author Role</label>
            <input type="text" name="author_role" value="<?= htmlspecialchars($editArticle['author_role'] ?? 'Senior Streaming Technician') ?>"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Estimated Read Time</label>
            <input type="text" name="read_time" value="<?= htmlspecialchars($editArticle['read_time'] ?? '4 min read') ?>" placeholder="4 min read"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none">
          </div>
        </div>

        <!-- Excerpt -->
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Excerpt / Summary (Used on cards and Google SEO description)</label>
          <textarea name="excerpt" id="article-excerpt" rows="2"
            placeholder="Brief 1-2 sentence summary of what viewers will learn from this article..."
            class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none"><?= htmlspecialchars($editArticle['excerpt'] ?? '') ?></textarea>
        </div>

        <!-- THUMBNAIL IMAGE (Upload OR URL) + Preview -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-700">
          <label class="block text-xs font-bold text-white mb-2 flex items-center gap-2">
            <i data-lucide="image" class="w-4 h-4 text-cyan-400"></i>
            <span>Featured Thumbnail Image</span>
          </label>
          
          <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
            <!-- Upload from PC -->
            <div class="md:col-span-5">
              <label class="block text-[11px] text-slate-400 mb-1">Upload File (JPG, PNG, WEBP)</label>
              <input type="file" name="thumbnail_file" id="thumbnail-file-input" accept="image/*"
                class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-cyan-500/20 file:text-cyan-300 hover:file:bg-cyan-500/30 file:cursor-pointer">
            </div>

            <div class="md:col-span-1 text-center text-xs font-bold text-slate-500 uppercase">OR</div>

            <!-- Paste Image URL -->
            <div class="md:col-span-4">
              <label class="block text-[11px] text-slate-400 mb-1">Paste Direct Image URL</label>
              <input type="text" id="thumbnail-url-input" name="thumbnail_url" value="<?= htmlspecialchars($editArticle['thumbnail_url'] ?? '') ?>"
                placeholder="https://images.unsplash.com/... or assets/images/..."
                class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:border-cyan-400 focus:outline-none">
            </div>

            <!-- Live Image Preview Box -->
            <div class="md:col-span-2 flex flex-col items-center justify-center">
              <span class="text-[10px] text-slate-400 mb-1 font-mono">Image Preview</span>
              <div id="thumb-preview-box" class="w-20 h-16 rounded-xl border border-slate-700 bg-slate-950 flex items-center justify-center overflow-hidden">
                <?php if (!empty($editArticle['thumbnail_url'])): ?>
                  <img id="thumb-preview-img" src="<?= htmlspecialchars(formatBlogThumbnailUrl($editArticle['thumbnail_url'])) ?>" alt="Preview" class="w-full h-full object-cover">
                <?php else: ?>
                  <span id="thumb-preview-placeholder" class="text-[10px] text-slate-600 font-mono">No Image</span>
                  <img id="thumb-preview-img" src="" alt="Preview" class="w-full h-full object-cover hidden">
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- FULL RICH WYSIWYG EDITOR -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="block text-xs font-bold text-white flex items-center gap-2">
              <i data-lucide="edit" class="w-4 h-4 text-cyan-400"></i>
              <span>Article Content (Full WYSIWYG Rich Editor) *</span>
            </label>
            <button type="button" id="toggle-html-view" class="text-xs text-cyan-400 hover:underline flex items-center gap-1 font-mono">
              <i data-lucide="code" class="w-3.5 h-3.5"></i>
              <span>Toggle HTML Source</span>
            </button>
          </div>

          <!-- Quill Editor Container -->
          <div id="quill-editor" class="rounded-xl overflow-hidden shadow-inner">
            <?= $editArticle['content'] ?? '<p>Write your article content here...</p>' ?>
          </div>

          <!-- Raw HTML textarea fallback (hidden by default) -->
          <textarea id="raw-html-textarea" rows="12" class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-cyan-200 font-mono text-xs focus:border-cyan-400 focus:outline-none hidden mt-2"></textarea>
        </div>

        <!-- Publishing Status -->
        <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <label class="flex items-center gap-2.5 cursor-pointer">
            <input type="checkbox" name="is_published" value="1" <?= (!isset($editArticle) || !empty($editArticle['is_published'])) ? 'checked' : '' ?>
              class="w-4 h-4 rounded accent-cyan-500 cursor-pointer">
            <span class="text-xs font-semibold text-slate-200">Publish immediately to live website</span>
          </label>

          <div class="flex items-center gap-3">
            <a href="blogs.php" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition-colors">
              Cancel
            </a>
            <button type="submit" id="save-article-btn" class="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center gap-2 transition-all shadow-lg shadow-cyan-500/25 cursor-pointer">
              <i data-lucide="save" class="w-4 h-4"></i>
              <span>Save & Publish Article</span>
            </button>
          </div>
        </div>

      </form>
    </div>
  <?php endif; ?>

</div>

<!-- =========================================================================
     LIVE PREVIEW MODAL (Replicates exact live website view)
     ========================================================================= -->
<div id="preview-modal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md hidden flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
  <div class="bg-white text-slate-800 rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-6 sm:p-10 relative shadow-2xl">
    
    <button type="button" id="close-preview-modal" class="absolute top-5 right-5 p-2 rounded-xl bg-slate-100 text-slate-500 hover:text-slate-900 transition-colors cursor-pointer">
      <i data-lucide="x" class="w-5 h-5"></i>
    </button>

    <div class="mb-6 flex items-center gap-2">
      <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-cyan-50 text-cyan-700 border border-cyan-200" id="preview-category-badge">Setup Guides</span>
      <span class="text-xs text-slate-400">•</span>
      <span class="text-xs text-slate-500" id="preview-read-time">4 min read</span>
    </div>

    <h1 class="font-heading font-black text-2xl sm:text-4xl text-slate-900 tracking-tight leading-tight mb-4" id="preview-title">
      Article Title Preview
    </h1>

    <p class="text-slate-600 text-sm sm:text-base leading-relaxed mb-6" id="preview-excerpt">
      Article excerpt summary preview...
    </p>

    <!-- Preview Featured Image -->
    <div id="preview-image-wrapper" class="mb-8 hidden">
      <img id="preview-modal-img" src="" alt="Featured Preview" class="w-full max-h-[400px] object-cover rounded-2xl border border-slate-200 shadow-sm">
    </div>

    <!-- Article Content -->
    <div class="border-t border-slate-200 pt-6 article-body text-slate-700 text-sm sm:text-base leading-relaxed" id="preview-rendered-content">
      <!-- Injected by JS -->
    </div>
  </div>
</div>

<!-- Quill Script -->
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Lucide icons
  if (window.lucide) window.lucide.createIcons();

  // Search filter for articles in admin
  const searchInput = document.getElementById('admin-blog-search');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const q = this.value.toLowerCase().trim();
      document.querySelectorAll('.article-row').forEach(row => {
        const text = row.getAttribute('data-search-text') || '';
        row.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }

  // Initialize Quill Editor if container exists
  const quillContainer = document.getElementById('quill-editor');
  let quill = null;
  if (quillContainer) {
    quill = new Quill('#quill-editor', {
      theme: 'snow',
      modules: {
        toolbar: [
          [{ 'header': [1, 2, 3, 4, false] }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ 'color': [] }, { 'background': [] }],
          [{ 'list': 'ordered' }, { 'list': 'bullet' }],
          ['blockquote', 'code-block'],
          [{ 'align': [] }],
          ['link', 'image', 'video'],
          ['clean']
        ]
      }
    });

    // Auto-generate slug from title
    const titleInput = document.getElementById('article-title');
    const slugInput = document.getElementById('article-slug');
    if (titleInput && slugInput) {
      titleInput.addEventListener('input', function() {
        if (!slugInput.dataset.manual) {
          slugInput.value = this.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        }
      });
      slugInput.addEventListener('input', function() {
        this.dataset.manual = 'true';
      });
    }

    // Toggle Raw HTML Code View
    const toggleHtmlBtn = document.getElementById('toggle-html-view');
    const rawHtmlTextarea = document.getElementById('raw-html-textarea');
    let isHtmlView = false;

    if (toggleHtmlBtn && rawHtmlTextarea) {
      toggleHtmlBtn.addEventListener('click', function(e) {
        e.preventDefault();
        isHtmlView = !isHtmlView;
        if (isHtmlView) {
          rawHtmlTextarea.value = quill.root.innerHTML;
          quillContainer.classList.add('hidden');
          rawHtmlTextarea.classList.remove('hidden');
          this.innerHTML = '<i data-lucide="eye" class="w-3.5 h-3.5"></i> Switch to Visual Editor';
        } else {
          quill.root.innerHTML = rawHtmlTextarea.value;
          rawHtmlTextarea.classList.add('hidden');
          quillContainer.classList.remove('hidden');
          this.innerHTML = '<i data-lucide="code" class="w-3.5 h-3.5"></i> Toggle HTML Source';
        }
        if (window.lucide) window.lucide.createIcons();
      });
    }

    // Form submit: sync content from Quill/Raw HTML into hidden input
    const blogForm = document.getElementById('blog-form');
    const contentInput = document.getElementById('blog-content-input');
    if (blogForm && contentInput) {
      blogForm.addEventListener('submit', function() {
        if (isHtmlView && rawHtmlTextarea) {
          contentInput.value = rawHtmlTextarea.value;
        } else {
          contentInput.value = quill.root.innerHTML;
        }
      });
    }

    // Thumbnail Live Preview
    const fileInput = document.getElementById('thumbnail-file-input');
    const urlInput = document.getElementById('thumbnail-url-input');
    const previewImg = document.getElementById('thumb-preview-img');
    const previewPlaceholder = document.getElementById('thumb-preview-placeholder');

    function updateThumbPreview(src) {
      if (src && previewImg) {
        previewImg.src = src;
        previewImg.classList.remove('hidden');
        if (previewPlaceholder) previewPlaceholder.classList.add('hidden');
      }
    }

    if (fileInput) {
      fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
          const reader = new FileReader();
          reader.onload = function(e) {
            updateThumbPreview(e.target.result);
          };
          reader.readAsDataURL(this.files[0]);
        }
      });
    }

    if (urlInput) {
      urlInput.addEventListener('input', function() {
        if (this.value.trim().length > 5) {
          let url = this.value.trim();
          if (!url.startsWith('http') && !url.startsWith('/')) {
            url = '/' + url;
          }
          updateThumbPreview(url);
        }
      });
    }

    // Live Preview Modal
    const openPreviewBtn = document.getElementById('open-preview-btn');
    const previewModal = document.getElementById('preview-modal');
    const closePreviewBtn = document.getElementById('close-preview-modal');

    if (openPreviewBtn && previewModal) {
      openPreviewBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const title = document.getElementById('article-title').value.trim() || 'Untitled Article';
        const excerpt = document.getElementById('article-excerpt').value.trim();
        const category = document.querySelector('input[name="category"]').value.trim() || 'Guides';
        const readTime = document.querySelector('input[name="read_time"]').value.trim() || '4 min read';
        const content = isHtmlView && rawHtmlTextarea ? rawHtmlTextarea.value : quill.root.innerHTML;

        document.getElementById('preview-title').textContent = title;
        document.getElementById('preview-excerpt').textContent = excerpt;
        document.getElementById('preview-category-badge').textContent = category;
        document.getElementById('preview-read-time').textContent = readTime;
        document.getElementById('preview-rendered-content').innerHTML = content;

        // Image in preview modal
        const imgWrap = document.getElementById('preview-image-wrapper');
        const modalImg = document.getElementById('preview-modal-img');
        if (previewImg && previewImg.src && !previewImg.classList.contains('hidden')) {
          modalImg.src = previewImg.src;
          imgWrap.classList.remove('hidden');
        } else {
          imgWrap.classList.add('hidden');
        }

        previewModal.classList.remove('hidden');
      });
    }

    if (closePreviewBtn && previewModal) {
      closePreviewBtn.addEventListener('click', function() {
        previewModal.classList.add('hidden');
      });
    }
  }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
