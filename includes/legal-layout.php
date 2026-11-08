<?php
/**
 * LIVETVMARKET - Legal & Policy Page Shared Layout
 * Pure white modern Tailwind layout with sidebar navigation across all 5 legal policies.
 */

if (!isset($legalSlug)) {
    $legalSlug = 'terms';
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$page = getLegalPage($legalSlug);
if (!$page) {
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit;
}

$activePage    = $legalSlug;
$pageTitle     = htmlspecialchars($page['title']) . ' | ' . SITE_NAME;
$pageDesc      = !empty($page['meta_description']) ? htmlspecialchars($page['meta_description']) : ($page['title'] . ' for ' . SITE_NAME . ' subscribers and visitors.');
$pageCanonical = SITE_URL . '/' . htmlspecialchars($page['slug']);

$legalNavItems = [
    'terms' => [
        'slug'  => 'terms',
        'url'   => 'terms',
        'label' => 'Terms of Service',
        'desc'  => 'Subscriber agreement & SLA',
        'icon'  => 'file-text'
    ],
    'privacy' => [
        'slug'  => 'privacy',
        'url'   => 'privacy',
        'label' => 'Privacy Policy',
        'desc'  => 'Zero-logging & data protection',
        'icon'  => 'shield-check'
    ],
    'refund-policy' => [
        'slug'  => 'refund-policy',
        'url'   => 'refund-policy',
        'label' => 'Refund Policy',
        'desc'  => '7-Day money-back guarantee',
        'icon'  => 'refresh-cw'
    ],
    'dmca' => [
        'slug'  => 'dmca',
        'url'   => 'dmca',
        'label' => 'DMCA & Content Policy',
        'desc'  => 'Digital copyright compliance',
        'icon'  => 'scale'
    ],
    'copyright' => [
        'slug'  => 'copyright',
        'url'   => 'copyright',
        'label' => 'Copyright & Trademarks',
        'desc'  => 'Brand attribution & assets',
        'icon'  => 'copyright'
    ],
];

include __DIR__ . '/head.php';
include __DIR__ . '/top-banner.php';
include __DIR__ . '/navbar.php';
?>

<main class="min-h-screen bg-white">
  
  <!-- HERO BANNER -->
  <section class="relative pt-12 pb-14 bg-gradient-to-b from-slate-50 via-white to-white border-b border-slate-100 overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(#0891b2_1px,transparent_1px)] [background-size:24px_24px] opacity-10"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <a href="/" class="hover:text-cyan-600 transition-colors flex items-center gap-1">
          <i data-lucide="home" class="w-3.5 h-3.5"></i>
          <span>Home</span>
        </a>
        <span>/</span>
        <span class="text-slate-400">Legal Policies</span>
        <span>/</span>
        <span class="text-slate-800 font-semibold"><?= htmlspecialchars($page['title']) ?></span>
      </nav>

      <div class="max-w-3xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-700 text-xs font-bold uppercase tracking-wider mb-4">
          <i data-lucide="<?= htmlspecialchars($page['icon'] ?: 'shield') ?>" class="w-3.5 h-3.5"></i>
          <span><?= htmlspecialchars($page['badge'] ?: 'Legal Agreement') ?></span>
        </div>

        <h1 class="font-heading font-black text-3xl sm:text-4xl text-slate-900 tracking-tight leading-tight mb-4">
          <?= htmlspecialchars($page['title']) ?>
        </h1>

        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 font-medium">
          <span class="flex items-center gap-1.5">
            <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
            Last Updated: <strong class="text-slate-700"><?= htmlspecialchars($page['last_updated'] ?: 'November 2026') ?></strong>
          </span>
          <span>•</span>
          <span class="flex items-center gap-1.5">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i>
            Legally Binding SLA
          </span>
          <span>•</span>
          <span class="flex items-center gap-1.5">
            <i data-lucide="globe" class="w-4 h-4 text-cyan-600"></i>
            <?= htmlspecialchars(SITE_DOMAIN) ?>
          </span>
        </div>
      </div>

    </div>
  </section>

  <!-- CONTENT SECTION WITH STICKY SIDEBAR -->
  <section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- SIDEBAR NAVIGATION (4 Cols) -->
        <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
          
          <!-- Policy Navigation Card -->
          <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <h3 class="font-heading font-bold text-sm text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
              <i data-lucide="book" class="w-4 h-4 text-cyan-600"></i>
              Legal & Compliance Documents
            </h3>
            
            <nav class="space-y-1.5">
              <?php foreach ($legalNavItems as $navKey => $nav): 
                $isCurrent = ($navKey === $legalSlug);
              ?>
                <a href="<?= htmlspecialchars($nav['url']) ?>" 
                   class="p-3 rounded-xl flex items-start gap-3 transition-all <?= $isCurrent ? 'bg-cyan-50/80 border border-cyan-300 text-cyan-900 font-bold shadow-sm' : 'hover:bg-slate-50 text-slate-600 hover:text-slate-900 border border-transparent' ?>">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $isCurrent ? 'bg-cyan-600 text-white' : 'bg-slate-100 text-slate-500' ?>">
                    <i data-lucide="<?= $nav['icon'] ?>" class="w-4 h-4"></i>
                  </div>
                  <div>
                    <div class="text-xs font-bold leading-snug <?= $isCurrent ? 'text-cyan-950' : 'text-slate-800' ?>">
                      <?= htmlspecialchars($nav['label']) ?>
                    </div>
                    <div class="text-[11px] <?= $isCurrent ? 'text-cyan-700' : 'text-slate-400' ?> font-normal mt-0.5">
                      <?= htmlspecialchars($nav['desc']) ?>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </nav>
          </div>

          <!-- Trust & Help Card -->
          <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 text-white shadow-md">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center mb-4 border border-cyan-500/30">
              <i data-lucide="shield-alert" class="w-5 h-5"></i>
            </div>
            <h4 class="font-heading font-bold text-base text-white mb-2">Have a Legal Inquiry?</h4>
            <p class="text-xs text-slate-300 leading-relaxed mb-4">
              Our dedicated legal & compliance team is available 24/7 to answer questions regarding subscription terms, zero-logging privacy, or content licensing.
            </p>
            <div class="space-y-2">
              <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', SUPPORT_WHATSAPP) ?>?text=<?= urlencode('Hello LIVETVMARKET Legal Desk, I have a question regarding ' . $page['title']) ?>" 
                 target="_blank" 
                 class="w-full py-2.5 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition-all">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>Fast-Track WhatsApp Desk</span>
              </a>
              <a href="mailto:<?= htmlspecialchars(SUPPORT_EMAIL) ?>" 
                 class="w-full py-2.5 px-4 rounded-xl bg-white/10 hover:bg-white/15 text-white font-bold text-xs flex items-center justify-center gap-2 border border-white/10 transition-all">
                <i data-lucide="mail" class="w-4 h-4"></i>
                <span>Email Legal Team</span>
              </a>
            </div>
          </div>

        </aside>

        <!-- MAIN ARTICLE BODY (8 Cols) -->
        <article class="lg:col-span-8">
          <div class="p-6 sm:p-10 rounded-2xl bg-white border border-slate-200 shadow-sm leading-relaxed text-slate-700">
            <?= $page['content'] ?>

            <!-- Document Footer Notice -->
            <div class="mt-12 pt-8 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
              <div>
                Document Reference: <span class="font-mono font-bold text-slate-700 uppercase">LTV-DOC-<?= strtoupper($legalSlug) ?></span>
              </div>
              <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 font-semibold text-slate-700 flex items-center gap-1.5 transition-colors cursor-pointer">
                  <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                  <span>Print / Save PDF</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Bottom Action Box -->
          <div class="mt-8 p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
              <h4 class="font-heading font-bold text-sm text-slate-900">Ready to experience zero-buffering 4K streaming?</h4>
              <p class="text-xs text-slate-500 mt-0.5">Backed by our 7-day money-back guarantee and Anti-Freeze™ 9.9 CDN.</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
              <a href="/shop" class="btn-glow-cyan px-5 py-2.5 rounded-xl font-bold text-xs">
                View Pricing Plans
              </a>
              <?php if (TRIAL_IS_ACTIVE): ?>
                <button type="button" data-open-trial class="px-4 py-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-xs font-bold text-slate-800 transition-colors cursor-pointer">
                  <?= htmlspecialchars(TRIAL_BUTTON_LABEL) ?>
                </button>
              <?php endif; ?>
            </div>
          </div>

        </article>

      </div>

    </div>
  </section>

</main>

<?php
include __DIR__ . '/footer.php';
include __DIR__ . '/modals.php';
?>

