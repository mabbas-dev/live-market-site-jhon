<?php
/**
 * LIVETVMARKET - Dedicated Blog Details Article Page
 * In-depth technical guide with table of contents, author profile, and related articles.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/blogs_data.php';

$articles = getBlogArticles();

// Resolve requested article
$slug = isset($_GET['slug']) ? trim($_GET['slug'], " \t\n\r\0\x0B/") : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fallback: extract slug directly from clean URI if not provided in $_GET
if (empty($slug)) {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (preg_match('#^/blog/([a-zA-Z0-9_-]+)#', $uriPath, $matches)) {
        $slug = $matches[1];
    }
}

$article = null;
if (!empty($slug)) {
    $article = getBlogBySlug($slug);
} elseif ($id > 0) {
    $article = getBlogById($id);
} elseif (!empty($articles)) {
    $article = $articles[0]; // fallback only when no specific slug or id is requested
}

if (!$article) {
    http_response_code(404);
    $custom404Title = 'Blog Article Not Found';
    $custom404Message = 'The blog article "' . htmlspecialchars($slug ?: 'requested') . '" was not found in our database. It may have been unpublished, renamed, or deleted.';
    include __DIR__ . '/404.php';
    exit;
}

$pageTitle = $article['title'] . ' - ' . SITE_NAME;
$pageDesc = $article['excerpt'];
$pageCanonical = SITE_URL . '/blog/' . urlencode($article['slug']);
$activePage = 'blog';
$ogType = 'article';

$artImg = !empty($article['thumbnail_url']) 
    ? (str_starts_with($article['thumbnail_url'], 'http') ? $article['thumbnail_url'] : (rtrim(SITE_URL, '/') . '/' . ltrim($article['thumbnail_url'], '/'))) 
    : (rtrim(SITE_URL, '/') . '/assets/images/hero-showcase.jpg');
$pageOgImage = $artImg;

$breadcrumbs = [
    ['name' => 'Home', 'url' => SITE_URL . '/'],
    ['name' => 'Blog', 'url' => SITE_URL . '/blog'],
    ['name' => $article['category'], 'url' => SITE_URL . '/blog'],
    ['name' => $article['title'], 'url' => $pageCanonical]
];

$articleSchema = [
    "@type" => "BlogPosting",
    "@id" => $pageCanonical . "#article",
    "headline" => $article['title'],
    "description" => $article['excerpt'],
    "image" => $artImg,
    "datePublished" => !empty($article['created_at']) ? date('c', strtotime($article['created_at'])) : date('c'),
    "dateModified" => !empty($article['updated_at']) ? date('c', strtotime($article['updated_at'])) : date('c'),
    "author" => [
        "@type" => "Person",
        "name" => $article['author'] ?: 'Alex Rivera'
    ],
    "publisher" => [
        "@id" => rtrim(SITE_URL, '/') . '/#organization'
    ],
    "mainEntityOfPage" => [
        "@type" => "WebPage",
        "@id" => $pageCanonical
    ]
];

// Related articles (excluding current)
$relatedArticles = [];
foreach ($articles as $a) {
    if ($a['id'] !== $article['id']) {
        $relatedArticles[] = $a;
    }
}
$relatedArticles = array_slice($relatedArticles, 0, 3);

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/top-banner.php';
include __DIR__ . '/includes/navbar.php';
?>

  <main class="bg-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Breadcrumb Navigation -->
      <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-slate-500 mb-8" data-aos="fade-down">
        <a href="/" class="hover:text-cyan-600 transition-colors">Home</a>
        <span>/</span>
        <a href="/blog" class="hover:text-cyan-600 transition-colors">Blogs</a>
        <span>/</span>
        <span class="text-cyan-700 font-semibold"><?= htmlspecialchars($article['category']) ?></span>
        <span>/</span>
        <span class="text-slate-400 truncate max-w-xs sm:max-w-md"><?= htmlspecialchars($article['title']) ?></span>
      </nav>

      <!-- Article Header -->
      <header class="max-w-4xl mx-auto text-center mb-12" data-aos="fade-up" data-aos-duration="700">
        <div class="flex items-center justify-center gap-2 mb-4">
          <span class="text-xs font-mono font-bold uppercase tracking-widest bg-cyan-50 text-cyan-700 px-3 py-1 rounded-full border border-cyan-200">
            <?= htmlspecialchars($article['category']) ?>
          </span>
          <span class="text-xs text-slate-400">•</span>
          <span class="text-xs text-slate-500 font-semibold flex items-center gap-1">
            <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i> <?= htmlspecialchars($article['read_time']) ?>
          </span>
        </div>

        <h1 class="font-heading font-black text-3xl sm:text-4xl lg:text-5xl text-slate-900 tracking-tight leading-tight">
          <?= htmlspecialchars($article['title']) ?>
        </h1>

        <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed max-w-3xl mx-auto">
          <?= htmlspecialchars($article['excerpt']) ?>
        </p>

        <!-- Author & Date Strip -->
        <div class="mt-6 pt-6 border-t border-slate-100 flex flex-wrap items-center justify-center gap-6 text-xs text-slate-600">
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-800 font-bold flex items-center justify-center text-sm shadow-sm">
              <?= htmlspecialchars($article['author_avatar']) ?>
            </div>
            <div class="text-left">
              <p class="font-bold text-slate-900"><?= htmlspecialchars($article['author']) ?></p>
              <p class="text-[11px] text-slate-500"><?= htmlspecialchars($article['author_role']) ?></p>
            </div>
          </div>
          <span class="text-slate-300">•</span>
          <div class="flex items-center gap-1.5 text-slate-500">
            <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
            <span>Published <?= htmlspecialchars($article['date']) ?></span>
          </div>
          <span class="text-slate-300">•</span>
          <div class="flex items-center gap-1.5 text-emerald-600 font-semibold">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i>
            <span>Verified by Technicians</span>
          </div>
        </div>
      </header>

      <!-- Main Article Layout (8 cols article + 4 cols sticky sidebar) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
        
        <!-- Left: Full Article Content (8 Cols) -->
        <div class="lg:col-span-8 bg-white p-6 sm:p-10 rounded-3xl border border-slate-200 shadow-sm" data-aos="fade-up" data-aos-delay="100">
          
          <!-- Featured Image (If added by Admin) -->
          <?php if (!empty($article['thumbnail_url'])): ?>
            <div class="mb-8 rounded-2xl overflow-hidden border border-slate-200 shadow-sm max-h-[460px] bg-slate-50">
              <img src="<?= htmlspecialchars($article['thumbnail_url']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="w-full max-h-[460px] object-cover">
            </div>
          <?php endif; ?>

          <!-- Key Takeaways Box -->
          <div class="p-5 rounded-2xl bg-cyan-50/70 border border-cyan-200 mb-8">
            <h3 class="font-bold text-cyan-900 text-sm flex items-center gap-2 mb-2">
              <i data-lucide="sparkles" class="w-4 h-4 text-cyan-600"></i>
              <span>Key Article Highlights:</span>
            </h3>
            <ul class="space-y-1.5 text-xs text-slate-700">
              <li class="flex items-start gap-2">
                <span class="text-cyan-600 font-bold">•</span>
                <span>Verified for all major Firestick models (4K, Max, Cube, Lite) running Fire OS 7 & 8.</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-cyan-600 font-bold">•</span>
                <span>Anti-Freeze™ 9.9 CDN ensures zero packet drop during peak live sporting events.</span>
              </li>
              <li class="flex items-start gap-2">
                <span class="text-cyan-600 font-bold">•</span>
                <span>Pre-configured quick codes make manual typing unnecessary.</span>
              </li>
            </ul>
          </div>

          <!-- Dynamic HTML Article Body -->
          <div class="article-body prose prose-slate max-w-none text-slate-700 leading-relaxed text-sm sm:text-base">
            <?= $article['content'] ?>
          </div>

          <!-- Social Share Bar -->
          <div class="mt-10 pt-6 border-t border-slate-200 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
              <i data-lucide="share-2" class="w-4 h-4 text-slate-400"></i>
              <span>Share this tutorial:</span>
            </div>
            <div class="flex items-center gap-2">
              <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['title'] . ' ' . $pageCanonical) ?>" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold transition-colors">
                WhatsApp
              </a>
              <a href="https://twitter.com/intent/tweet?text=<?= urlencode($article['title']) ?>&url=<?= urlencode($pageCanonical) ?>" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 text-xs font-bold transition-colors">
                Twitter
              </a>
              <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');" class="px-3 py-1.5 rounded-lg bg-cyan-50 text-cyan-700 hover:bg-cyan-100 border border-cyan-200 text-xs font-bold transition-colors cursor-pointer">
                Copy Link
              </button>
            </div>
          </div>

          <!-- Author Biography Card -->
          <div class="mt-8 p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-cyan-600 text-white font-bold text-lg flex items-center justify-center flex-shrink-0 shadow-md">
              <?= htmlspecialchars($article['author_avatar']) ?>
            </div>
            <div>
              <h4 class="font-bold text-slate-900 text-base"><?= htmlspecialchars($article['author']) ?></h4>
              <p class="text-xs text-cyan-700 font-semibold mb-1"><?= htmlspecialchars($article['author_role']) ?></p>
              <p class="text-xs text-slate-600 leading-relaxed">
                Specializing in low-latency HLS/DASH video delivery, multi-path routing, and Smart TV application architecture. Has configured over 10,000+ client streaming lines.
              </p>
            </div>
          </div>

        </div>

        <!-- Right: Sticky Sidebar (4 Cols) -->
        <aside class="lg:col-span-4 space-y-6 sticky top-28" data-aos="fade-left" data-aos-delay="150">
          
          <!-- Subscription / Trial High-Impact Widget -->
          <?php if (TRIAL_IS_ACTIVE): ?>
          <div class="bg-gradient-to-br from-cyan-50 via-white to-blue-50 p-6 rounded-2xl border border-cyan-300 shadow-md text-center">
            <div class="w-12 h-12 rounded-xl bg-cyan-500 text-white flex items-center justify-center mx-auto mb-3 shadow-md shadow-cyan-500/20">
              <i data-lucide="play" class="w-6 h-6"></i>
            </div>
            <h3 class="font-heading font-bold text-lg text-slate-900"><?= (TRIAL_MODE === 'paid') ? ('Test ' . TOTAL_CHANNELS . ' Channels') : ('Test ' . TOTAL_CHANNELS . ' Channels Free') ?></h3>
            <p class="text-xs text-slate-600 mt-1 mb-4 leading-relaxed">
              Experience Anti-Freeze™ 9.9 on your Firestick or Smart TV with our zero-risk <?= TRIAL_DURATION_HOURS ?>-hour test line.
            </p>
            <button data-open-trial class="btn-glow-cyan w-full py-3 rounded-xl font-bold text-xs uppercase tracking-wide shadow-md cursor-pointer">
              <?= htmlspecialchars(TRIAL_BUTTON_LABEL) ?>
            </button>
            <p class="text-[10px] text-slate-400 mt-2">Instant automated email delivery</p>
          </div>
          <?php else: ?>
          <div class="bg-gradient-to-br from-cyan-50 via-white to-blue-50 p-6 rounded-2xl border border-cyan-300 shadow-md text-center">
            <div class="w-12 h-12 rounded-xl bg-cyan-500 text-white flex items-center justify-center mx-auto mb-3 shadow-md shadow-cyan-500/20">
              <i data-lucide="tv" class="w-6 h-6"></i>
            </div>
            <h3 class="font-heading font-bold text-lg text-slate-900"><?= TOTAL_CHANNELS ?> Live Channels & VOD</h3>
            <p class="text-xs text-slate-600 mt-1 mb-4 leading-relaxed">
              Experience Anti-Freeze™ 9.9 on your Firestick or Smart TV with instant 60s automated activation.
            </p>
            <a href="/shop" class="btn-glow-cyan w-full py-3 rounded-xl font-bold text-xs uppercase tracking-wide shadow-md cursor-pointer block text-center">
              Explore Subscription Plans
            </a>
            <p class="text-[10px] text-slate-400 mt-2">7-Day Money Back Guarantee</p>
          </div>
          <?php endif; ?>

          <!-- Quick Navigation Table of Contents -->
          <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h4 class="font-bold text-slate-900 text-sm mb-3 flex items-center gap-2">
              <i data-lucide="list" class="w-4 h-4 text-cyan-600"></i>
              <span>Article Overview</span>
            </h4>
            <ul class="space-y-2 text-xs text-slate-600">
              <li><a href="#features" class="hover:text-cyan-600 transition-colors flex items-center gap-1.5"><span class="text-cyan-600">›</span> Why Firestick is Recommended</a></li>
              <li><a href="#features" class="hover:text-cyan-600 transition-colors flex items-center gap-1.5"><span class="text-cyan-600">›</span> Step 1: Install Downloader</a></li>
              <li><a href="#features" class="hover:text-cyan-600 transition-colors flex items-center gap-1.5"><span class="text-cyan-600">›</span> Step 2: Enable Unknown Sources</a></li>
              <li><a href="#features" class="hover:text-cyan-600 transition-colors flex items-center gap-1.5"><span class="text-cyan-600">›</span> Step 3: Numeric Quick Code</a></li>
              <li><a href="#features" class="hover:text-cyan-600 transition-colors flex items-center gap-1.5"><span class="text-cyan-600">›</span> Step 4: Xtream Codes Login</a></li>
            </ul>
          </div>

          <!-- WhatsApp Technician Desk Assistance -->
          <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">
                <i data-lucide="message-circle" class="w-5 h-5"></i>
              </div>
              <div>
                <h4 class="font-bold text-slate-900 text-sm">Need Setup Help?</h4>
                <p class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Average response: 2m
                </p>
              </div>
            </div>
            <p class="text-xs text-slate-600 mb-4 leading-relaxed">
              Our technicians can remotely load your playlist or configure your device MAC address in real-time.
            </p>
            <a href="<?= WHATSAPP_LINK ?>" target="_blank" class="block w-full py-2.5 px-3 text-center rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors shadow-sm">
              Chat on WhatsApp Desk →
            </a>
          </div>

        </aside>

      </div>

      <!-- RELATED ARTICLES SECTION -->
      <section class="mt-20 pt-12 border-t border-slate-200">
        <div class="flex items-center justify-between mb-8">
          <div>
            <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-2.5 py-0.5 rounded-full border border-cyan-200">
              Keep Reading
            </span>
            <h2 class="font-heading font-black text-2xl sm:text-3xl text-slate-900 mt-2">
              Related Streaming Guides
            </h2>
          </div>
          <a href="/blog" class="text-xs font-bold text-cyan-600 hover:text-cyan-700 flex items-center gap-1">
            <span>View All <?= count($articles) ?> Articles</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <?php foreach ($relatedArticles as $rel): ?>
            <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-xl hover:border-cyan-300 transition-all flex flex-col justify-between group tilt-card">
              <div>
                <?php if (!empty($rel['thumbnail_url'])): ?>
                  <div class="h-36 w-full relative overflow-hidden bg-slate-100">
                    <img src="<?= htmlspecialchars($rel['thumbnail_url']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <span class="absolute bottom-2.5 left-2.5 text-[10px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-white/95 text-slate-800 shadow-sm">
                      <?= htmlspecialchars($rel['category']) ?>
                    </span>
                    <span class="absolute bottom-2.5 right-2.5 text-[10px] font-semibold text-white/90 flex items-center gap-1 bg-black/40 px-2 py-0.5 rounded backdrop-blur-sm">
                      <i data-lucide="clock" class="w-3 h-3"></i> <?= htmlspecialchars($rel['read_time']) ?>
                    </span>
                  </div>
                <?php else: ?>
                  <div class="h-36 bg-gradient-to-tr <?= $rel['gradient'] ?? 'from-cyan-600 to-blue-700' ?> relative p-5 flex flex-col justify-between overflow-hidden">
                    <div class="absolute inset-0 bg-black/10"></div>
                    <div class="relative z-10 flex items-center justify-between text-white text-[11px] font-semibold">
                      <span class="px-2 py-0.5 rounded-full bg-white/90 text-slate-800 font-bold"><?= htmlspecialchars($rel['category']) ?></span>
                      <span><?= htmlspecialchars($rel['read_time']) ?></span>
                    </div>
                    <div class="relative z-10 text-white">
                      <i data-lucide="<?= $rel['icon'] ?? 'file-text' ?>" class="w-6 h-6"></i>
                    </div>
                  </div>
                <?php endif; ?>

                <div class="p-5">
                  <p class="text-[11px] text-slate-400 mb-1"><?= htmlspecialchars($rel['date']) ?></p>
                  <h3 class="font-heading font-bold text-base text-slate-900 group-hover:text-cyan-600 transition-colors leading-snug">
                    <a href="/blog/<?= urlencode($rel['slug']) ?>">
                      <?= htmlspecialchars($rel['title']) ?>
                    </a>
                  </h3>
                  <p class="mt-2 text-xs text-slate-600 line-clamp-2">
                    <?= htmlspecialchars($rel['excerpt']) ?>
                  </p>
                </div>
              </div>

              <div class="px-5 pb-5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="/blog/<?= urlencode($rel['slug']) ?>" class="font-bold text-cyan-600 group-hover:text-cyan-700 flex items-center gap-1">
                  <span>Read Guide</span>
                  <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

    </div>
  </main>

<?php
include __DIR__ . '/includes/modals.php';
include __DIR__ . '/includes/footer.php';
?>

