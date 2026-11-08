<?php
/**
 * LIVETVMARKET - Dedicated Blogs & Insights Page
 * Comprehensive streaming tutorials, setup guides, and cord-cutting comparisons.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/blogs_data.php';

$pageTitle = SITE_NAME . ' - IPTV Blogs, Setup Tutorials & Streaming Guides (2026)';
$pageDesc = 'Discover expert IPTV tutorials, Firestick setup walkthroughs, Smart TV player reviews, cord-cutting cost comparisons, and 4K streaming technology insights.';
$pageKeywords = 'iptv blog, streaming tutorials, firestick setup guide, tivimate pro review, cord cutting 2026, best iptv providers';
$pageCanonical = SITE_URL . '/blog';
$activePage = 'blog';
$breadcrumbs = [
    ['name' => 'Home', 'url' => SITE_URL . '/'],
    ['name' => 'Blog & Guides', 'url' => SITE_URL . '/blog']
];

$articles = getBlogArticles();
$featuredArticle = $articles[0];
$gridArticles = array_slice($articles, 1);

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/top-banner.php';
include __DIR__ . '/includes/navbar.php';
?>

  <main class="bg-white py-14 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Blog Header -->
      <div class="text-center max-w-3xl mx-auto mb-10" data-aos="fade-up" data-aos-duration="700">
        <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3.5 py-1.5 rounded-full border border-cyan-200 shadow-sm">
          Streaming Insights & Knowledge Base
        </span>
        <h1 class="mt-4 font-heading font-black text-4xl sm:text-5xl lg:text-6xl text-slate-900 tracking-tight">
          Latest IPTV Guides & Tech Tutorials
        </h1>
        <p class="mt-4 text-slate-600 text-sm sm:text-lg leading-relaxed">
          Master your digital entertainment setup with verified tutorials, hardware comparisons, and buffering troubleshooting guides written by our technicians.
        </p>
      </div>

      <!-- BLOG SEARCH & FILTER BAR -->
      <div class="max-w-2xl mx-auto mb-8" data-aos="fade-up" data-aos-delay="100">
        <div class="relative">
          <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
          <input type="text" id="blog-search-input" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Search guides, tutorials, apps, Firestick setup..."
            class="w-full pl-12 pr-10 py-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-900 text-sm focus:outline-none focus:border-cyan-500 focus:bg-white focus:ring-2 focus:ring-cyan-500/20 shadow-sm transition-all">
          <button type="button" id="blog-search-clear" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 hidden cursor-pointer">
            <i data-lucide="x" class="w-4 h-4"></i>
          </button>
        </div>
        <div class="mt-2.5 flex items-center justify-between text-xs text-slate-500 px-2">
          <span id="blog-count-display">Showing all <?= count($articles) ?> articles</span>
          <span class="text-[11px] text-cyan-600 font-semibold flex items-center gap-1">
            <i data-lucide="sparkles" class="w-3 h-3"></i> Instant live search
          </span>
        </div>
      </div>

      <!-- Category Filter Pills -->
      <div class="flex flex-wrap items-center justify-center gap-2 mb-12" data-aos="fade-up" data-aos-delay="150">
        <button type="button" data-cat-filter="all" class="cat-pill px-4 py-2 rounded-xl text-xs font-bold bg-cyan-500 text-white shadow-sm border border-cyan-400 cursor-pointer transition-all">All Articles</button>
        <button type="button" data-cat-filter="Setup Guides" class="cat-pill px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 border border-slate-200 cursor-pointer transition-all">🔥 Setup Guides</button>
        <button type="button" data-cat-filter="App Reviews" class="cat-pill px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 border border-slate-200 cursor-pointer transition-all">📺 App Reviews</button>
        <button type="button" data-cat-filter="Cost Comparison" class="cat-pill px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 border border-slate-200 cursor-pointer transition-all">💰 Cost Comparison</button>
        <button type="button" data-cat-filter="Technology" class="cat-pill px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 border border-slate-200 cursor-pointer transition-all">⚡ Technology</button>
        <button type="button" data-cat-filter="Live Sports" class="cat-pill px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 border border-slate-200 cursor-pointer transition-all">🏆 Live Sports</button>
      </div>

      <!-- FEATURED HERO ARTICLE (Spotlight) -->
      <?php if (!empty($featuredArticle)): ?>
      <section id="featured-article-section" class="mb-16 article-item" data-category="<?= htmlspecialchars($featuredArticle['category']) ?>" data-search-text="<?= htmlspecialchars(strtolower($featuredArticle['title'] . ' ' . $featuredArticle['excerpt'] . ' ' . $featuredArticle['category'])) ?>" data-aos="zoom-in" data-aos-duration="800">
        <div class="bg-gradient-to-br from-cyan-50/60 via-white to-blue-50/60 rounded-3xl border border-cyan-200 shadow-xl overflow-hidden hover:shadow-2xl hover:border-cyan-400 transition-all">
          <div class="grid grid-cols-1 lg:grid-cols-12 items-stretch">
            
            <!-- Graphic / Thumbnail Banner Left -->
            <div class="lg:col-span-5 relative overflow-hidden min-h-[280px] flex flex-col justify-between">
              <?php if (!empty($featuredArticle['thumbnail_url'])): ?>
                <img src="<?= htmlspecialchars($featuredArticle['thumbnail_url']) ?>" alt="<?= htmlspecialchars($featuredArticle['title']) ?>" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-black/30"></div>
              <?php else: ?>
                <div class="absolute inset-0 bg-gradient-to-br <?= $featuredArticle['gradient'] ?? 'from-cyan-600 to-blue-700' ?>"></div>
                <div class="absolute inset-0 bg-black/10"></div>
              <?php endif; ?>

              <div class="relative z-10 p-6 sm:p-8 flex items-center justify-between text-white">
                <span class="text-xs font-mono font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-white/90 text-cyan-800 shadow-sm">
                  Featured Guide
                </span>
                <span class="text-xs font-semibold text-white/90 flex items-center gap-1.5 bg-black/30 px-2.5 py-1 rounded-full backdrop-blur-sm">
                  <i data-lucide="clock" class="w-3.5 h-3.5"></i> <?= $featuredArticle['read_time'] ?>
                </span>
              </div>
              
              <div class="relative z-10 p-6 sm:p-8 text-white mt-auto">
                <div class="flex items-center gap-3 pt-4 border-t border-white/20 text-xs">
                  <div class="w-8 h-8 rounded-full bg-white/20 font-bold flex items-center justify-center text-xs backdrop-blur-sm">
                    <?= $featuredArticle['author_avatar'] ?>
                  </div>
                  <div>
                    <p class="font-bold text-white"><?= $featuredArticle['author'] ?></p>
                    <p class="text-[11px] text-cyan-200"><?= $featuredArticle['author_role'] ?></p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Content Right -->
            <div class="lg:col-span-7 p-8 sm:p-12 flex flex-col justify-between">
              <div>
                <div class="flex items-center gap-3 text-xs text-slate-500 font-medium mb-3">
                  <span class="text-cyan-700 font-bold"><?= $featuredArticle['category'] ?></span>
                  <span>•</span>
                  <span><?= $featuredArticle['date'] ?></span>
                </div>
                
                <h2 class="font-heading font-black text-2xl sm:text-3xl lg:text-4xl text-slate-900 hover:text-cyan-600 transition-colors leading-tight">
                  <a href="/blog/<?= urlencode($featuredArticle['slug']) ?>">
                    <?= htmlspecialchars($featuredArticle['title']) ?>
                  </a>
                </h2>

                <p class="mt-4 text-slate-600 text-sm sm:text-base leading-relaxed">
                  <?= htmlspecialchars($featuredArticle['excerpt']) ?>
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-2 text-xs">
                  <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 border border-slate-200">#4KStreaming</span>
                  <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 border border-slate-200">#SetupGuide</span>
                  <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 border border-slate-200">#AntiFreeze9.9</span>
                </div>
              </div>

              <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between">
                <a href="/blog/<?= urlencode($featuredArticle['slug']) ?>" class="btn-glow-cyan px-6 py-3 rounded-xl font-bold text-xs inline-flex items-center gap-2 group shadow-lg shadow-cyan-500/20">
                  <span>Read Step-by-Step Guide</span>
                  <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1.5 transition-transform"></i>
                </a>
                <span class="text-xs font-mono text-emerald-600 font-semibold flex items-center gap-1">
                  <i data-lucide="check-circle-2" class="w-4 h-4"></i> 2026 Updated
                </span>
              </div>
            </div>

          </div>
        </div>
      </section>
      <?php endif; ?>

      <!-- NO SEARCH RESULTS FOUND STATE -->
      <div id="no-search-results" class="hidden text-center py-16 px-4 bg-slate-50 border border-slate-200 rounded-3xl mb-16">
        <div class="w-16 h-16 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center mx-auto mb-4 border border-cyan-200">
          <i data-lucide="search-x" class="w-8 h-8"></i>
        </div>
        <h3 class="font-heading font-black text-2xl text-slate-900 mb-2">No Matching Articles Found</h3>
        <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto mb-6">
          We couldn't find any articles matching your search query. Try different keywords or reset your filter.
        </p>
        <button type="button" id="reset-search-btn" class="btn-glow-cyan px-6 py-2.5 rounded-xl text-xs font-bold inline-flex items-center gap-2 cursor-pointer">
          <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
          <span>Clear Search & Show All</span>
        </button>
      </div>

      <!-- ALL ARTICLES GRID -->
      <section id="articles-grid-section" class="mb-16">
        <div class="flex items-center justify-between mb-8">
          <h2 class="font-heading font-black text-2xl sm:text-3xl text-slate-900">
            All Streaming Articles & Tutorials
          </h2>
          <span class="text-xs font-mono font-bold text-slate-500"><?= count($articles) ?> Published Guides</span>
        </div>

        <div id="articles-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <?php foreach ($gridArticles as $idx => $art): ?>
            <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-xl hover:border-cyan-300 transition-all flex flex-col justify-between group tilt-card article-item" 
              data-category="<?= htmlspecialchars($art['category']) ?>" 
              data-search-text="<?= htmlspecialchars(strtolower($art['title'] . ' ' . $art['excerpt'] . ' ' . $art['category'])) ?>"
              data-aos="fade-up" data-aos-delay="<?= ($idx * 75) ?>">
              <div>
                <!-- Header Thumbnail / Banner -->
                <?php if (!empty($art['thumbnail_url'])): ?>
                  <div class="h-48 w-full relative overflow-hidden bg-slate-100">
                    <img src="<?= htmlspecialchars($art['thumbnail_url']) ?>" alt="<?= htmlspecialchars($art['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <span class="absolute bottom-3 left-3 text-[11px] font-mono font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white/95 text-slate-800 shadow-sm">
                      <?= htmlspecialchars($art['category']) ?>
                    </span>
                    <span class="absolute bottom-3 right-3 text-[11px] font-semibold text-white/90 flex items-center gap-1 bg-black/40 px-2 py-0.5 rounded backdrop-blur-sm">
                      <i data-lucide="clock" class="w-3 h-3"></i> <?= htmlspecialchars($art['read_time']) ?>
                    </span>
                  </div>
                <?php else: ?>
                  <div class="h-44 bg-gradient-to-tr <?= $art['gradient'] ?? 'from-cyan-600 to-blue-700' ?> relative p-6 flex flex-col justify-between overflow-hidden">
                    <div class="absolute inset-0 bg-black/10 group-hover:scale-105 transition-transform duration-500"></div>
                    <div class="relative z-10 flex items-center justify-between">
                      <span class="text-[11px] font-mono font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white/90 text-slate-800 shadow-sm">
                        <?= htmlspecialchars($art['category']) ?>
                      </span>
                      <span class="text-[11px] font-semibold text-white/90 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i> <?= htmlspecialchars($art['read_time']) ?>
                      </span>
                    </div>
                    <div class="relative z-10 text-white">
                      <i data-lucide="<?= $art['icon'] ?? 'file-text' ?>" class="w-8 h-8 mb-1 opacity-90"></i>
                      <p class="text-xs text-white/90 font-mono"><?= htmlspecialchars($art['author_role']) ?></p>
                    </div>
                  </div>
                <?php endif; ?>

                <!-- Text Body -->
                <div class="p-6">
                  <p class="text-xs text-slate-400 font-medium mb-2"><?= htmlspecialchars($art['date']) ?> • by <?= htmlspecialchars($art['author']) ?></p>
                  <h3 class="font-heading font-bold text-xl text-slate-900 group-hover:text-cyan-600 transition-colors leading-snug">
                    <a href="/blog/<?= urlencode($art['slug']) ?>">
                      <?= htmlspecialchars($art['title']) ?>
                    </a>
                  </h3>
                  <p class="mt-3 text-xs text-slate-600 leading-relaxed line-clamp-3">
                    <?= htmlspecialchars($art['excerpt']) ?>
                  </p>
                </div>
              </div>

              <!-- Footer link -->
              <div class="px-6 pb-6 pt-2 border-t border-slate-100 flex items-center justify-between">
                <a href="/blog/<?= urlencode($art['slug']) ?>" class="text-xs font-bold text-cyan-600 group-hover:text-cyan-700 inline-flex items-center gap-1.5">
                  <span>Read Full Article</span>
                  <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                </a>
                <span class="text-[11px] text-slate-400 font-mono">2026 Guide</span>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- NEWSLETTER / GUIDE UPDATES BOX -->
      <section class="p-8 sm:p-12 rounded-3xl bg-slate-50 border border-slate-200 text-center relative overflow-hidden" data-aos="fade-up">
        <div class="max-w-2xl mx-auto relative z-10">
          <div class="w-12 h-12 rounded-2xl bg-cyan-100 text-cyan-700 flex items-center justify-center mx-auto mb-4 border border-cyan-200 shadow-sm">
            <i data-lucide="mail" class="w-6 h-6"></i>
          </div>
          <h3 class="font-heading font-black text-2xl sm:text-3xl text-slate-900">
            Get Weekly Sports Schedules & Setup Codes
          </h3>
          <p class="mt-2 text-xs sm:text-sm text-slate-600">
            Subscribe to our newsletter for weekly 4K sports schedules, new app codes, and exclusive <?= FLASH_PROMO_DISCOUNT ?> promotional voucher codes.
          </p>

          <form onsubmit="event.preventDefault(); alert('Thank you for subscribing! Your promo voucher code is: <?= FLASH_PROMO_CODE ?>');" class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3 max-w-md mx-auto">
            <input type="email" required placeholder="Enter your email address..." class="w-full px-4 py-3 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:outline-none focus:border-cyan-500 shadow-sm">
            <button type="submit" class="btn-glow-cyan w-full sm:w-auto px-6 py-3 rounded-xl text-xs font-bold whitespace-nowrap cursor-pointer">
              Subscribe Free
            </button>
          </form>
          <p class="text-[10px] text-slate-400 mt-3">🔒 No spam ever. Unsubscribe at any time with one click.</p>
        </div>
      </section>

    </div>
  </main>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('blog-search-input');
    const searchClear = document.getElementById('blog-search-clear');
    const countDisplay = document.getElementById('blog-count-display');
    const noResults = document.getElementById('no-search-results');
    const resetBtn = document.getElementById('reset-search-btn');
    const gridSection = document.getElementById('articles-grid-section');
    const catPills = document.querySelectorAll('.cat-pill');
    const articleItems = document.querySelectorAll('.article-item');
    const totalCount = articleItems.length;

    let activeCategory = 'all';
    let searchQuery = '';

    function filterBlogs() {
      let visibleCount = 0;
      let gridVisibleCount = 0;

      articleItems.forEach(item => {
        const itemCat = (item.getAttribute('data-category') || '').trim();
        const itemText = (item.getAttribute('data-search-text') || '').toLowerCase();

        const matchCat = (activeCategory === 'all') || (itemCat.toLowerCase() === activeCategory.toLowerCase());
        const matchSearch = (!searchQuery) || itemText.includes(searchQuery);

        if (matchCat && matchSearch) {
          item.classList.remove('hidden');
          visibleCount++;
          if (item.closest('#articles-grid')) {
            gridVisibleCount++;
          }
        } else {
          item.classList.add('hidden');
        }
      });

      // Show / hide no results message
      if (visibleCount === 0) {
        if (noResults) noResults.classList.remove('hidden');
        if (gridSection) gridSection.classList.add('hidden');
      } else {
        if (noResults) noResults.classList.add('hidden');
        if (gridSection) {
          gridSection.classList.remove('hidden');
          // Hide grid section heading if only featured item matched and no grid items
          const gridHeading = gridSection.querySelector('h2');
          if (gridVisibleCount === 0) {
            gridSection.classList.add('hidden');
          }
        }
      }

      // Update count text
      if (countDisplay) {
        if (visibleCount === totalCount && !searchQuery && activeCategory === 'all') {
          countDisplay.textContent = `Showing all ${totalCount} articles`;
        } else {
          countDisplay.textContent = `Showing ${visibleCount} of ${totalCount} articles`;
        }
      }
    }

    // Search input typing
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        searchQuery = this.value.trim().toLowerCase();
        if (searchClear) {
          if (searchQuery.length > 0) {
            searchClear.classList.remove('hidden');
          } else {
            searchClear.classList.add('hidden');
          }
        }
        filterBlogs();
      });
    }

    // Clear search button
    if (searchClear) {
      searchClear.addEventListener('click', function() {
        if (searchInput) searchInput.value = '';
        searchQuery = '';
        searchClear.classList.add('hidden');
        filterBlogs();
        if (searchInput) searchInput.focus();
      });
    }

    // Reset button in No Results box
    if (resetBtn) {
      resetBtn.addEventListener('click', function() {
        if (searchInput) searchInput.value = '';
        searchQuery = '';
        if (searchClear) searchClear.classList.add('hidden');
        setActiveCategory('all');
        filterBlogs();
      });
    }

    // Category pill click handler
    function setActiveCategory(cat) {
      activeCategory = cat;
      catPills.forEach(pill => {
        const filterVal = pill.getAttribute('data-cat-filter');
        if (filterVal.toLowerCase() === activeCategory.toLowerCase()) {
          pill.className = 'cat-pill px-4 py-2 rounded-xl text-xs font-bold bg-cyan-500 text-white shadow-sm border border-cyan-400 cursor-pointer transition-all';
        } else {
          pill.className = 'cat-pill px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200 border border-slate-200 cursor-pointer transition-all';
        }
      });
    }

    catPills.forEach(pill => {
      pill.addEventListener('click', function() {
        const cat = this.getAttribute('data-cat-filter') || 'all';
        setActiveCategory(cat);
        filterBlogs();
      });
    });

    // Auto-run search if pre-filled from URL parameter (?q=...)
    if (searchInput && searchInput.value.trim().length > 0) {
      searchQuery = searchInput.value.trim().toLowerCase();
      if (searchClear) searchClear.classList.remove('hidden');
      filterBlogs();
    }
  });
  </script>

<?php
include __DIR__ . '/includes/modals.php';
include __DIR__ . '/includes/footer.php';
?>

