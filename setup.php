<?php
/**
 * LIVETVMARKET - Complete Device Setup & Installation Tutorials
 * Pure Tailwind CSS and clean white theme architecture.
 * Fully controlled dynamically from MySQL & Admin Panel.
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/setup_guides.php';

$setupSettings = getSetupPageSettings();
$setupGuides = getSetupGuides(true); // only active

$pageTitle = SITE_NAME . ' - ' . ($setupSettings['title'] ?: 'Complete Device Setup & Installation Guide');
$pageDesc = $setupSettings['subtitle'] ?: 'Step-by-step installation guides for Firestick, Android TV, Smart TV, Apple TV, Windows, and MAG boxes. Learn how to configure TiviMate, IPTV Smarters, and IBO Player in 3 minutes.';
$pageCanonical = SITE_URL . '/setup';
$activePage = 'setup';

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/top-banner.php';
include __DIR__ . '/includes/navbar.php';

$helpLink = !empty($setupSettings['help_btn_link']) ? $setupSettings['help_btn_link'] : WHATSAPP_LINK;
?>

  <main class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Page Hero -->
      <div class="text-center max-w-3xl mx-auto mb-14" data-aos="fade-up" data-aos-duration="700">
        <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3 py-1 rounded-full border border-cyan-200">
          <?= htmlspecialchars($setupSettings['badge']) ?>
        </span>
        <h1 class="mt-4 font-heading font-black text-4xl sm:text-5xl text-slate-900">
          <?= htmlspecialchars($setupSettings['title']) ?>
        </h1>
        <p class="mt-4 text-slate-600 text-sm sm:text-base">
          <?= htmlspecialchars($setupSettings['subtitle']) ?>
        </p>
      </div>

      <!-- Quick Nav anchors -->
      <?php if (!empty($setupGuides)): ?>
      <div class="flex flex-wrap items-center justify-center gap-2 mb-12 text-xs font-bold" data-aos="fade-up" data-aos-delay="100">
        <?php foreach ($setupGuides as $guide): ?>
          <a href="#<?= htmlspecialchars($guide['device_slug']) ?>" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:border-cyan-400 shadow-sm transition-colors">
            <?= htmlspecialchars($guide['nav_title']) ?>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Device Setup Sections -->
      <?php foreach ($setupGuides as $index => $guide): 
        $colors = getDeviceColorClasses($guide['icon_color']);
      ?>
        <article id="<?= htmlspecialchars($guide['device_slug']) ?>" class="bg-white rounded-2xl p-6 sm:p-8 mb-8 border border-slate-200 shadow-sm" data-aos="fade-up" data-aos-delay="<?= 100 + ($index * 50) ?>">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl <?= $colors['bg'] ?> <?= $colors['text'] ?> border <?= $colors['border'] ?> flex items-center justify-center flex-shrink-0">
              <i data-lucide="<?= htmlspecialchars($guide['icon']) ?>" class="w-5 h-5"></i>
            </div>
            <div>
              <h2 class="font-heading font-bold text-xl text-slate-900"><?= htmlspecialchars($guide['device_name']) ?></h2>
              <?php if (!empty($guide['setup_time'])): ?>
                <p class="text-xs text-slate-500"><?= htmlspecialchars($guide['setup_time']) ?></p>
              <?php endif; ?>
            </div>
          </div>

          <div class="space-y-4 text-sm text-slate-600">
            <?php foreach ($guide['steps'] as $step): ?>
              <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                <h3 class="font-bold text-slate-900 text-xs mb-1"><?= htmlspecialchars($step['title']) ?></h3>
                <div class="text-xs text-slate-500 leading-relaxed"><?= $step['desc'] ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </article>
      <?php endforeach; ?>

      <!-- Need Help Banner -->
      <div class="p-6 rounded-2xl bg-gradient-to-r from-cyan-50 via-white to-blue-50 border border-cyan-200 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm" data-aos="zoom-in" data-aos-delay="100">
        <div>
          <h4 class="font-bold text-slate-900 text-base"><?= htmlspecialchars($setupSettings['help_title']) ?></h4>
          <p class="text-xs text-slate-500"><?= htmlspecialchars($setupSettings['help_subtitle']) ?></p>
        </div>
        <a href="<?= htmlspecialchars($helpLink) ?>" target="_blank" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-sm transition-colors flex-shrink-0">
          <i data-lucide="message-circle" class="w-4 h-4"></i>
          <span><?= htmlspecialchars($setupSettings['help_btn_text']) ?></span>
        </a>
      </div>

    </div>
  </main>

<?php
include __DIR__ . '/includes/modals.php';
include __DIR__ . '/includes/footer.php';
?>
