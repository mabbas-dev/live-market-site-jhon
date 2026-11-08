<?php
/**
 * LIVETVMARKET - Custom 404 Error Page
 * Modern, high-converting, pure white Tailwind CSS design.
 * Automatically shown for missing blog articles, dead links, or invalid URLs.
 */

if (!http_response_code() || http_response_code() === 200) {
    http_response_code(404);
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/blogs_data.php';

$pageTitle = ($custom404Title ?? '404 - Channel Not Found / Page Missing') . ' | ' . SITE_NAME;
$pageDesc = $custom404Message ?? 'The requested stream or page was not found. Browse 25,000+ 4K live channels, step-by-step setup guides, or test our zero-lag Anti-Freeze streaming service.';
$pageCanonical = SITE_URL . '/404';
$activePage = '';

// Get 3 recent blog articles for recommendations
$recentArticles = [];
try {
    $allArts = getBlogArticles(true);
    $recentArticles = array_slice($allArts, 0, 3);
} catch (Exception $e) {}

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/top-banner.php';
include __DIR__ . '/includes/navbar.php';
?>

  <main class="min-h-[75vh] bg-gradient-to-b from-slate-50/80 via-white to-slate-50/50 py-16 sm:py-24 relative overflow-hidden flex flex-col justify-center">
    
    <!-- Background Ambient Glow Accents -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
      
      <!-- Status Badge -->
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-mono font-bold mb-6 shadow-sm" data-aos="fade-down">
        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
        <span>ERROR 404 • SIGNAL LOST IN TRANSMISSION</span>
      </div>

      <!-- TV Lost Signal Icon Illustration -->
      <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl bg-gradient-to-tr from-cyan-50 to-blue-50 border border-cyan-200 flex items-center justify-center mx-auto mb-6 shadow-xl shadow-cyan-500/10 relative group" data-aos="zoom-in">
        <div class="absolute inset-0 rounded-3xl bg-cyan-400/20 blur-xl group-hover:blur-2xl transition-all opacity-50"></div>
        <i data-lucide="tv-2" class="w-12 h-12 sm:w-14 sm:h-14 text-cyan-600 relative z-10"></i>
        <span class="absolute -top-1 -right-1 flex h-4 w-4">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 text-[9px] text-white font-bold items-center justify-center">!</span>
        </span>
      </div>

      <!-- Giant 404 Heading -->
      <h1 class="font-heading font-black text-6xl sm:text-8xl lg:text-9xl tracking-tight bg-gradient-to-r from-cyan-600 via-blue-600 to-indigo-600 bg-clip-text text-transparent select-none drop-shadow-sm" data-aos="fade-up" data-aos-delay="50">
        404
      </h1>

      <h2 class="mt-2 font-heading font-black text-2xl sm:text-3xl lg:text-4xl text-slate-900 tracking-tight" data-aos="fade-up" data-aos-delay="100">
        <?= htmlspecialchars($custom404Title ?? 'Channel Offline or Page Not Found') ?>
      </h2>

      <p class="mt-4 text-sm sm:text-base text-slate-600 max-w-xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="150">
        <?= htmlspecialchars($custom404Message ?? 'The page, streaming guide, or link you are trying to access has been moved, renamed, or is temporarily disconnected from our server cluster.') ?>
      </p>

      <!-- Search Input to Help User -->
      <div class="max-w-md mx-auto mt-8" data-aos="fade-up" data-aos-delay="200">
        <form action="/blog" method="GET" class="relative">
          <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
          <input type="text" name="q" placeholder="Search guides, channels, Firestick setup..."
            class="w-full pl-12 pr-28 py-3.5 rounded-2xl bg-white border border-slate-300 text-slate-900 text-xs sm:text-sm focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 shadow-sm transition-all">
          <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-600 text-white font-bold text-xs transition-colors cursor-pointer">
            Search
          </button>
        </form>
      </div>

      <!-- Action Buttons -->
      <div class="mt-8 flex flex-wrap items-center justify-center gap-3" data-aos="fade-up" data-aos-delay="250">
        <a href="/" class="btn-glow-cyan px-6 py-3 rounded-xl font-bold text-xs inline-flex items-center gap-2 shadow-lg shadow-cyan-500/20">
          <i data-lucide="home" class="w-4 h-4"></i>
          <span>Return to Homepage</span>
        </a>

        <a href="/shop" class="px-5 py-3 rounded-xl bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs border border-slate-200 inline-flex items-center gap-2 shadow-sm transition-all hover:border-cyan-300">
          <i data-lucide="shopping-bag" class="w-4 h-4 text-cyan-600"></i>
          <span>Browse Pricing & Plans</span>
        </a>

        <a href="/blog" class="px-5 py-3 rounded-xl bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs border border-slate-200 inline-flex items-center gap-2 shadow-sm transition-all hover:border-cyan-300">
          <i data-lucide="newspaper" class="w-4 h-4 text-cyan-600"></i>
          <span>All Blog Tutorials</span>
        </a>

        <?php if (TRIAL_IS_ACTIVE): ?>
          <button type="button" data-open-trial class="px-5 py-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs border border-emerald-200 inline-flex items-center gap-2 shadow-sm transition-all cursor-pointer">
            <i data-lucide="play-circle" class="w-4 h-4 text-emerald-600"></i>
            <span>Test Free 24H Trial</span>
          </button>
        <?php endif; ?>
      </div>

      <!-- Quick Helpful Navigation Cards -->
      <div class="mt-16 pt-12 border-t border-slate-200/80">
        <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-400 mb-6 text-center">
          Popular Destinations on <?= htmlspecialchars(SITE_NAME) ?>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-left">
          
          <a href="/channels" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-cyan-400 hover:shadow-md transition-all group tilt-card">
            <div class="w-9 h-9 rounded-xl bg-cyan-50 text-cyan-700 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i data-lucide="tv" class="w-4 h-4"></i>
            </div>
            <h4 class="font-bold text-slate-900 text-sm group-hover:text-cyan-600 transition-colors">25,000+ Channels</h4>
            <p class="text-[11px] text-slate-500 mt-1">Live sports, PPV, movies & international EPG list.</p>
          </a>

          <a href="/shop" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-cyan-400 hover:shadow-md transition-all group tilt-card">
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i data-lucide="zap" class="w-4 h-4"></i>
            </div>
            <h4 class="font-bold text-slate-900 text-sm group-hover:text-blue-600 transition-colors">Subscription Shop</h4>
            <p class="text-[11px] text-slate-500 mt-1">Instant activation with 30-day money-back guarantee.</p>
          </a>

          <a href="/track-order" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-cyan-400 hover:shadow-md transition-all group tilt-card">
            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i data-lucide="search" class="w-4 h-4"></i>
            </div>
            <h4 class="font-bold text-slate-900 text-sm group-hover:text-amber-600 transition-colors">Track Your Order</h4>
            <p class="text-[11px] text-slate-500 mt-1">Check credentials delivery, activation status, and expiry.</p>
          </a>

          <a href="/contact" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-cyan-400 hover:shadow-md transition-all group tilt-card">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <i data-lucide="headphones" class="w-4 h-4"></i>
            </div>
            <h4 class="font-bold text-slate-900 text-sm group-hover:text-emerald-600 transition-colors">24/7 Tech Support</h4>
            <p class="text-[11px] text-slate-500 mt-1">Instant technician support on WhatsApp and email.</p>
          </a>

        </div>
      </div>

      <!-- Suggested Guides / Trending Tutorials -->
      <?php if (!empty($recentArticles)): ?>
        <div class="mt-16 text-left">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="font-heading font-black text-xl text-slate-900">Recommended Streaming Guides</h3>
              <p class="text-xs text-slate-500 mt-0.5">Explore our most popular IPTV setup tutorials.</p>
            </div>
            <a href="/blog" class="text-xs font-bold text-cyan-600 hover:text-cyan-700 flex items-center gap-1">
              <span>View All</span>
              <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($recentArticles as $art): ?>
              <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-xl hover:border-cyan-300 transition-all flex flex-col justify-between group tilt-card">
                <div>
                  <?php if (!empty($art['thumbnail_url'])): ?>
                    <div class="h-40 w-full relative overflow-hidden bg-slate-100">
                      <img src="<?= htmlspecialchars($art['thumbnail_url']) ?>" alt="<?= htmlspecialchars($art['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                      <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                      <span class="absolute bottom-2.5 left-2.5 text-[10px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-white/95 text-slate-800 shadow-sm">
                        <?= htmlspecialchars($art['category']) ?>
                      </span>
                    </div>
                  <?php else: ?>
                    <div class="h-40 bg-gradient-to-tr <?= $art['gradient'] ?? 'from-cyan-600 to-blue-700' ?> relative p-5 flex flex-col justify-between overflow-hidden">
                      <span class="text-[10px] font-mono font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-white/90 text-slate-800 self-start shadow-sm">
                        <?= htmlspecialchars($art['category']) ?>
                      </span>
                      <i data-lucide="<?= $art['icon'] ?? 'file-text' ?>" class="w-6 h-6 text-white"></i>
                    </div>
                  <?php endif; ?>

                  <div class="p-5">
                    <p class="text-[11px] text-slate-400 mb-1"><?= htmlspecialchars($art['date']) ?></p>
                    <h4 class="font-heading font-bold text-sm text-slate-900 group-hover:text-cyan-600 transition-colors line-clamp-2 leading-snug">
                      <a href="/blog/<?= urlencode($art['slug']) ?>">
                        <?= htmlspecialchars($art['title']) ?>
                      </a>
                    </h4>
                    <p class="mt-2 text-xs text-slate-600 line-clamp-2">
                      <?= htmlspecialchars($art['excerpt']) ?>
                    </p>
                  </div>
                </div>

                <div class="px-5 pb-5 pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                  <a href="/blog/<?= urlencode($art['slug']) ?>" class="font-bold text-cyan-600 group-hover:text-cyan-700 flex items-center gap-1">
                    <span>Read Tutorial</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                  </a>
                  <span class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($art['read_time']) ?></span>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>

<?php
include __DIR__ . '/includes/modals.php';
include __DIR__ . '/includes/footer.php';
?>

