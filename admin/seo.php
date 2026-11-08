<?php
/**
 * LIVETVMARKET - Admin: Comprehensive Search Engine Optimization (SEO) Center
 * Manages Verification tags (Google/Bing/Yandex), GA4/GTM IDs, dynamic XML Sitemap,
 * Robots.txt directives, and modern LLM / AI Search Indexing (llms.txt).
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';

$curAdminPage = 'seo';
$errorMsg = '';
$successMsg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seoSettings = [
        'seo_meta_title_template' => trim($_POST['seo_meta_title_template'] ?? '%title% | %site_name%'),
        'seo_default_meta_desc'   => trim($_POST['seo_default_meta_desc'] ?? ''),
        'seo_default_keywords'    => trim($_POST['seo_default_keywords'] ?? ''),
        'seo_google_verification' => trim($_POST['seo_google_verification'] ?? ''),
        'seo_bing_verification'   => trim($_POST['seo_bing_verification'] ?? ''),
        'seo_yandex_verification' => trim($_POST['seo_yandex_verification'] ?? ''),
        'seo_ga4_id'              => trim($_POST['seo_ga4_id'] ?? ''),
        'seo_gtm_id'              => trim($_POST['seo_gtm_id'] ?? ''),
        'seo_robots_index'        => trim($_POST['seo_robots_index'] ?? 'index, follow'),
        'seo_twitter_handle'      => trim($_POST['seo_twitter_handle'] ?? '@livetvmarket'),
        'seo_robots_custom'       => trim($_POST['seo_robots_custom'] ?? ''),
        'seo_llms_custom'         => trim($_POST['seo_llms_custom'] ?? '')
    ];

    // Handle OG Image File Upload
    if (!empty($_FILES['seo_og_image_file']['name']) && $_FILES['seo_og_image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/seo/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['seo_og_image_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $fileName = 'og_default_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['seo_og_image_file']['tmp_name'], $uploadDir . $fileName)) {
                $seoSettings['seo_og_image'] = 'uploads/seo/' . $fileName;
            }
        } else {
            $errorMsg = "Invalid social share image format. Supported: JPG, PNG, WEBP.";
        }
    } elseif (!empty($_POST['seo_og_image_url'])) {
        $seoSettings['seo_og_image'] = trim($_POST['seo_og_image_url']);
    }

    if (empty($errorMsg)) {
        foreach ($seoSettings as $k => $v) {
            updateSetting($k, $v);
        }
        $successMsg = "SEO settings and search engine configuration updated successfully!";
    }
}

// Current Values
$seoMetaTitleTemplate = getSetting('seo_meta_title_template', '%title% | %site_name%');
$seoDefaultMetaDesc   = getSetting('seo_default_meta_desc', 'LIVETVMARKET offers the #1 premium IPTV service with 25,000+ live channels and 120,000+ VOD movies & series. Anti-Freeze™ 9.9 zero-buffering technology, all live sports & PPV in 4K UHD. Instant automated activation & 24h free trial.');
$seoDefaultKeywords   = getSetting('seo_default_keywords', 'IPTV subscription, LiveTVMarket, buy iptv, premium iptv, 4k iptv, best iptv provider 2026, iptv firestick, live sports iptv, m3u playlist, xtream codes, iptv free trial, netflix subscription');
$seoGoogleVer         = getSetting('seo_google_verification', '');
$seoBingVer           = getSetting('seo_bing_verification', '');
$seoYandexVer         = getSetting('seo_yandex_verification', '');
$seoGa4Id             = getSetting('seo_ga4_id', '');
$seoGtmId             = getSetting('seo_gtm_id', '');
$seoRobotsIndex       = getSetting('seo_robots_index', 'index, follow');
$seoTwitterHandle     = getSetting('seo_twitter_handle', '@livetvmarket');
$seoOgImage           = getSetting('seo_og_image', 'assets/images/hero-showcase.jpg');
$seoRobotsCustom      = getSetting('seo_robots_custom', '');
$seoLlmsCustom        = getSetting('seo_llms_custom', '');

// Sitemap Analytics
$productCount = 0;
$categoryCount = 0;
$blogCount = 0;
$legalCount = 0;
if ($pdo) {
    try {
        $productCount = (int)$pdo->query("SELECT COUNT(*) FROM `store_products` WHERE `status` IN ('in_stock', 'out_of_stock')")->fetchColumn();
        $categoryCount = (int)$pdo->query("SELECT COUNT(*) FROM `store_categories` WHERE `status` = 'active'")->fetchColumn();
        $blogCount = (int)$pdo->query("SELECT COUNT(*) FROM `blogs` WHERE `is_published` = 1")->fetchColumn();
        $legalCount = (int)$pdo->query("SELECT COUNT(*) FROM `legal_pages` WHERE `is_active` = 1")->fetchColumn();
    } catch (Exception $e) {}
}
$staticPagesCount = 7; // Home, Shop, Channels, Setup, Blog, Contact, Track
$totalSitemapUrls = $staticPagesCount + $productCount + $categoryCount + $blogCount + $legalCount;

$siteUrl = rtrim(defined('SITE_URL') ? SITE_URL : 'https://livetvmarket.com', '/');

include __DIR__ . '/header.php';
?>

  <div class="space-y-8 max-w-5xl pb-16">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h2 class="font-heading font-black text-2xl text-white flex items-center gap-2.5">
          <i data-lucide="search" class="w-7 h-7 text-emerald-400"></i>
          <span>Search Engine Optimization (SEO) & AI Indexing</span>
        </h2>
        <p class="text-xs text-slate-400 mt-1">
          Configure search console verification tags, XML sitemaps, robots.txt directives, and modern LLM AI crawlers.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <a href="<?= $siteUrl ?>/sitemap.xml" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-cyan-400 text-xs font-bold flex items-center gap-1.5 transition-colors border border-cyan-500/30">
          <i data-lucide="file-code" class="w-3.5 h-3.5"></i>
          <span>XML Sitemap</span>
        </a>
        <a href="<?= $siteUrl ?>/llms.txt" target="_blank" class="px-3.5 py-2 rounded-xl bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 text-xs font-bold flex items-center gap-1.5 transition-colors border border-purple-500/30">
          <i data-lucide="bot" class="w-3.5 h-3.5"></i>
          <span>LLM AI Context</span>
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

    <?php if (!empty($errorMsg)): ?>
    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold flex items-center gap-2">
      <i data-lucide="alert-circle" class="w-4 h-4"></i>
      <span><?= htmlspecialchars($errorMsg) ?></span>
    </div>
    <?php endif; ?>

    <!-- Real-time SEO Diagnostic Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[11px] font-bold text-slate-400">Total Sitemap URLs</span>
          <i data-lucide="layers" class="w-4 h-4 text-cyan-400"></i>
        </div>
        <div class="text-2xl font-mono font-bold text-white"><?= $totalSitemapUrls ?></div>
        <p class="text-[10px] text-slate-500 mt-1">Generated live on-the-fly</p>
      </div>

      <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[11px] font-bold text-slate-400">Indexed Products</span>
          <i data-lucide="package" class="w-4 h-4 text-emerald-400"></i>
        </div>
        <div class="text-2xl font-mono font-bold text-white"><?= $productCount ?></div>
        <p class="text-[10px] text-slate-500 mt-1">Active in store catalog</p>
      </div>

      <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[11px] font-bold text-slate-400">Published Articles</span>
          <i data-lucide="newspaper" class="w-4 h-4 text-purple-400"></i>
        </div>
        <div class="text-2xl font-mono font-bold text-white"><?= $blogCount ?></div>
        <p class="text-[10px] text-slate-500 mt-1">Blog guides & news</p>
      </div>

      <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
        <div class="flex items-center justify-between mb-2">
          <span class="text-[11px] font-bold text-slate-400">AI / LLM Search Index</span>
          <i data-lucide="sparkles" class="w-4 h-4 text-amber-400"></i>
        </div>
        <div class="text-xs font-bold text-emerald-400 flex items-center gap-1 mt-2">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>Ready (llms.txt)</span>
        </div>
        <p class="text-[10px] text-slate-500 mt-1">Supports ChatGPT & Claude</p>
      </div>
    </div>

    <!-- Main SEO Settings Form -->
    <form method="POST" enctype="multipart/form-data" class="space-y-6">

      <!-- SECTION 1: SEARCH ENGINE VERIFICATION & TRACKING -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <h3 class="font-heading font-bold text-sm text-white flex items-center gap-2 border-b border-slate-800 pb-3">
          <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i>
          <span>1. Search Engine Console Verification & Analytics IDs</span>
        </h3>
        <p class="text-xs text-slate-400">
          Paste the verification token or full meta tag provided by Google Search Console, Bing Webmaster, or Yandex. Antigravity automatically extracts the verification code.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">
              Google Search Console Verification Tag
            </label>
            <input type="text" name="seo_google_verification" value="<?= htmlspecialchars($seoGoogleVer) ?>" placeholder="e.g. google-site-verification=abc123xyz OR abc123xyz" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
            <span class="text-[10px] text-slate-500 mt-1 block">Renders: &lt;meta name="google-site-verification" content="..."&gt;</span>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">
              Bing Webmaster Tools (msvalidate.01)
            </label>
            <input type="text" name="seo_bing_verification" value="<?= htmlspecialchars($seoBingVer) ?>" placeholder="e.g. 7A1C2B3D4E5F67890..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
            <span class="text-[10px] text-slate-500 mt-1 block">Renders: &lt;meta name="msvalidate.01" content="..."&gt;</span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">
              Google Analytics 4 (GA4 Measurement ID)
            </label>
            <input type="text" name="seo_ga4_id" value="<?= htmlspecialchars($seoGa4Id) ?>" placeholder="G-XXXXXXXXXX" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">
              Google Tag Manager (GTM Container ID)
            </label>
            <input type="text" name="seo_gtm_id" value="<?= htmlspecialchars($seoGtmId) ?>" placeholder="GTM-XXXXXXX" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">
              Yandex Verification Token
            </label>
            <input type="text" name="seo_yandex_verification" value="<?= htmlspecialchars($seoYandexVer) ?>" placeholder="yandex-verification code" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
          </div>
        </div>
      </div>

      <!-- SECTION 2: GLOBAL META DEFAULTS & HOMEPAGE SERP -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <h3 class="font-heading font-bold text-sm text-white flex items-center gap-2 border-b border-slate-800 pb-3">
          <i data-lucide="globe" class="w-4 h-4 text-cyan-400"></i>
          <span>2. Global Meta Defaults & Search Indexing</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">Title Tag Pattern</label>
            <input type="text" name="seo_meta_title_template" value="<?= htmlspecialchars($seoMetaTitleTemplate) ?>" placeholder="%title% | %site_name%" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
            <span class="text-[10px] text-slate-500 mt-1 block">Variables: %title%, %site_name%</span>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">Search Engine Robots Directives</label>
            <select name="seo_robots_index" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
              <option value="index, follow" <?= ($seoRobotsIndex === 'index, follow') ? 'selected' : '' ?>>index, follow (Recommended - Fully Indexed)</option>
              <option value="noindex, follow" <?= ($seoRobotsIndex === 'noindex, follow') ? 'selected' : '' ?>>noindex, follow (Do not index pages, follow links)</option>
              <option value="noindex, nofollow" <?= ($seoRobotsIndex === 'noindex, nofollow') ? 'selected' : '' ?>>noindex, nofollow (Complete privacy / Maintenance)</option>
            </select>
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block text-xs font-bold text-slate-300">Default Meta Description</label>
            <span id="site-desc-counter" class="text-xs font-mono font-bold text-slate-400"><?= strlen($seoDefaultMetaDesc) ?> / 160</span>
          </div>
          <textarea name="seo_default_meta_desc" id="site-desc-input" rows="3" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500"><?= htmlspecialchars($seoDefaultMetaDesc) ?></textarea>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-300 mb-1">Default Meta Keywords (Comma-separated)</label>
          <input type="text" name="seo_default_keywords" value="<?= htmlspecialchars($seoDefaultKeywords) ?>" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">Twitter / X Handle</label>
            <input type="text" name="seo_twitter_handle" value="<?= htmlspecialchars($seoTwitterHandle) ?>" placeholder="@livetvmarket" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-xs text-white focus:outline-none focus:border-cyan-500">
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-300 mb-1">Default Social Share Image (OG Image)</label>
            <input type="file" name="seo_og_image_file" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-800 file:text-cyan-400 hover:file:bg-slate-700 cursor-pointer">
          </div>
        </div>
      </div>

      <!-- SECTION 3: AUTOMATED SITEMAP & SEARCH ENGINES HUB -->
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
        <h3 class="font-heading font-bold text-sm text-white flex items-center gap-2 border-b border-slate-800 pb-3">
          <i data-lucide="map" class="w-4 h-4 text-purple-400"></i>
          <span>3. Automated Search Engine Files (Sitemap, Robots.txt & LLMs.txt)</span>
        </h3>

        <div class="space-y-3">
          <!-- Sitemap.xml Item -->
          <div class="p-4 rounded-xl bg-slate-800/60 border border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 text-[10px] font-mono font-bold border border-emerald-500/30">Active & Automated</span>
                <span class="text-xs font-bold text-white font-mono"><?= $siteUrl ?>/sitemap.xml</span>
              </div>
              <p class="text-[11px] text-slate-400 mt-1">
                Updates dynamically when you create, edit or delete products, categories, and blog articles. Supports Google Image sitemap schema.
              </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <a href="<?= $siteUrl ?>/sitemap.xml" target="_blank" class="px-3 py-1.5 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 text-xs font-bold flex items-center gap-1 transition-colors border border-cyan-500/20">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                <span>Open XML</span>
              </a>
            </div>
          </div>

          <!-- Robots.txt Item -->
          <div class="p-4 rounded-xl bg-slate-800/60 border border-slate-700 space-y-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-300 text-[10px] font-mono font-bold border border-cyan-500/30">Directives Active</span>
                <span class="text-xs font-bold text-white font-mono"><?= $siteUrl ?>/robots.txt</span>
              </div>
              <a href="<?= $siteUrl ?>/robots.txt" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white text-xs font-bold flex items-center gap-1 transition-colors self-start">
                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                <span>View robots.txt</span>
              </a>
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-400 mb-1">Custom robots.txt Directives Override (Leave blank for default auto-generated rules):</label>
              <textarea name="seo_robots_custom" rows="3" placeholder="User-agent: *&#10;Disallow: /admin/" class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500"><?= htmlspecialchars($seoRobotsCustom) ?></textarea>
            </div>
          </div>

          <!-- LLMs.txt Item -->
          <div class="p-4 rounded-xl bg-slate-800/60 border border-purple-500/20 space-y-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 text-[10px] font-mono font-bold border border-purple-500/30">AI Ready</span>
                <span class="text-xs font-bold text-white font-mono"><?= $siteUrl ?>/llms.txt</span>
                <span class="text-xs font-bold text-purple-300 font-mono">&amp; /llms-full.txt</span>
              </div>
              <div class="flex items-center gap-2">
                <a href="<?= $siteUrl ?>/llms.txt" target="_blank" class="px-3 py-1.5 rounded-lg bg-purple-500/10 hover:bg-purple-500/20 text-purple-300 text-xs font-bold flex items-center gap-1 transition-colors border border-purple-500/20">
                  <i data-lucide="bot" class="w-3.5 h-3.5"></i>
                  <span>llms.txt</span>
                </a>
                <a href="<?= $siteUrl ?>/llms-full.txt" target="_blank" class="px-3 py-1.5 rounded-lg bg-purple-500/10 hover:bg-purple-500/20 text-purple-300 text-xs font-bold flex items-center gap-1 transition-colors border border-purple-500/20">
                  <i data-lucide="file-code" class="w-3.5 h-3.5"></i>
                  <span>llms-full.txt</span>
                </a>
              </div>
            </div>
            <p class="text-[11px] text-slate-400">
              The modern standard implemented by Perplexity AI, ChatGPT, Claude, and Gemini to read live product inventories and services accurately.
            </p>
            <div>
              <label class="block text-[11px] font-bold text-slate-400 mb-1">Custom LLM Instructions Override (Optional - leave blank for dynamic catalog):</label>
              <textarea name="seo_llms_custom" rows="3" placeholder="# Custom AI prompt or product catalog instructions..." class="w-full px-3 py-2 rounded-lg bg-slate-900 border border-slate-700 text-xs text-white font-mono focus:outline-none focus:border-cyan-500"><?= htmlspecialchars($seoLlmsCustom) ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Submit Bar -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <button type="submit" class="px-8 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2 cursor-pointer">
          <i data-lucide="save" class="w-4 h-4"></i>
          <span>Save All SEO &amp; Search Engine Settings</span>
        </button>
      </div>

    </form>

  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const descInput = document.getElementById('site-desc-input');
      const descCounter = document.getElementById('site-desc-counter');
      if (descInput && descCounter) {
        descInput.addEventListener('input', function() {
          descCounter.textContent = `${this.value.length} / 160`;
          if (this.value.length >= 120 && this.value.length <= 165) {
            descCounter.className = 'text-xs font-mono font-bold text-emerald-400';
          } else if (this.value.length > 165) {
            descCounter.className = 'text-xs font-mono font-bold text-rose-400';
          } else {
            descCounter.className = 'text-xs font-mono font-bold text-slate-400';
          }
        });
      }
    });
  </script>

<?php include __DIR__ . '/footer.php'; ?>
