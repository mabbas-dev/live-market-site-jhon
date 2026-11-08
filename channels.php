<?php
/**
 * LIVETVMARKET - Complete Channels & VOD Directory
 * Pure Tailwind CSS and clean white theme architecture.
 */

require_once __DIR__ . '/config/config.php';

$pageTitle = SITE_NAME . ' - ' . TOTAL_CHANNELS . ' Live Channels & VOD Catalog (2026 Updated)';
$pageDesc = 'Browse our complete list of ' . TOTAL_CHANNELS . ' live TV channels and ' . TOTAL_VOD . ' VOD cinema titles across 60+ countries in 4K UHD and 60FPS. Filter by country, sports, movies, and news.';
$pageCanonical = SITE_URL . '/channels';
$activePage = 'channels';

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/top-banner.php';
include __DIR__ . '/includes/navbar.php';
?>

  <main class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Page Hero -->
      <div class="text-center max-w-3xl mx-auto mb-12" data-aos="fade-up" data-aos-duration="700">
        <span class="text-xs font-mono font-bold text-cyan-700 uppercase tracking-widest bg-cyan-50 px-3 py-1 rounded-full border border-cyan-200">
          Complete Channel Guide
        </span>
        <h1 class="mt-4 font-heading font-black text-4xl sm:text-5xl text-slate-900">
          <?= TOTAL_CHANNELS ?> Premium Live Channels & VOD
        </h1>
        <p class="mt-4 text-slate-600 text-sm sm:text-base">
          Browse our worldwide lineup featuring premium sports packages, cinema channels, news, kids, and regional programming in 4K UHD and FHD 60FPS.
        </p>
      </div>

      <!-- Country / Package Categories -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-10 text-xs font-bold text-center" data-aos="fade-up" data-aos-delay="100">
        <div class="bg-white p-3 rounded-xl border border-cyan-300 text-cyan-700 shadow-sm">
          <span class="text-xl block mb-1">🇬🇧</span>
          <span class="text-slate-900">United Kingdom</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">850+ Channels</span>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 shadow-sm transition-colors">
          <span class="text-xl block mb-1">🇺🇸</span>
          <span class="text-slate-900">United States</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">1,420+ Channels</span>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 shadow-sm transition-colors">
          <span class="text-xl block mb-1">🇨🇦</span>
          <span class="text-slate-900">Canada</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">480+ Channels</span>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 shadow-sm transition-colors">
          <span class="text-xl block mb-1">🇫🇷</span>
          <span class="text-slate-900">France</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">620+ Channels</span>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 shadow-sm transition-colors">
          <span class="text-xl block mb-1">🇩🇪</span>
          <span class="text-slate-900">Germany</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">540+ Channels</span>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 shadow-sm transition-colors">
          <span class="text-xl block mb-1">🇪🇸</span>
          <span class="text-slate-900">Spain</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">490+ Channels</span>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 shadow-sm transition-colors">
          <span class="text-xl block mb-1">🇮🇹</span>
          <span class="text-slate-900">Italy</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">430+ Channels</span>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 shadow-sm transition-colors">
          <span class="text-xl block mb-1">🇦🇪</span>
          <span class="text-slate-900">Arabic / MENA</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">1,890+ Channels</span>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 shadow-sm transition-colors">
          <span class="text-xl block mb-1">🇳🇱</span>
          <span class="text-slate-900">Netherlands / BE</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">360+ Channels</span>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 shadow-sm transition-colors">
          <span class="text-xl block mb-1">🇵🇹</span>
          <span class="text-slate-900">Portugal & Brazil</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">670+ Channels</span>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 shadow-sm transition-colors">
          <span class="text-xl block mb-1">🇦🇺</span>
          <span class="text-slate-900">Australia & NZ</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">310+ Channels</span>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-700 shadow-sm transition-colors">
          <span class="text-xl block mb-1">🌍</span>
          <span class="text-slate-900">Rest of World</span>
          <span class="block text-[10px] text-slate-500 font-mono mt-0.5">17,000+ Channels</span>
        </div>
      </div>

      <!-- Sports Channels Spotlight Table -->
      <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 mb-12 border border-slate-200 shadow-sm" data-aos="zoom-in-up" data-aos-duration="750" data-aos-delay="150">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="font-heading font-bold text-xl text-slate-900">🏆 Premium Sports Packages Included</h2>
            <p class="text-xs text-slate-500">All major sports networks broadcasted without blackouts in 4K HDR & 60FPS.</p>
          </div>
          <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded border border-emerald-200">Zero Extra PPV Fees</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
          <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-sm">
            <strong class="text-slate-900 block text-sm mb-1">Sky Sports UK (4K & FHD)</strong>
            <p class="text-slate-600">Main Event, Premier League, Football, F1, Cricket, Action, Golf, News, Arena, Mix.</p>
          </div>
          <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-sm">
            <strong class="text-slate-900 block text-sm mb-1">TNT Sports 1-4 (4K HDR)</strong>
            <p class="text-slate-600">UEFA Champions League, Europa League, Serie A, UFC Fight Nights, MotoGP, Boxing PPV.</p>
          </div>
          <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-sm">
            <strong class="text-slate-900 block text-sm mb-1">beIN Sports 1-16 (Arabic, FR, EN)</strong>
            <p class="text-slate-600">Full 4K beIN Sports channels covering FIFA World Cup qualifiers, Ligue 1, LaLiga, AFCON.</p>
          </div>
          <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-sm">
            <strong class="text-slate-900 block text-sm mb-1">USA Major Sports Networks</strong>
            <p class="text-slate-600">ESPN, ESPN2, ESPNU, Fox Sports 1 & 2, CBS Sports, NBC Sports, MLB Extra Innings, NBA TV.</p>
          </div>
          <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-sm">
            <strong class="text-slate-900 block text-sm mb-1">DAZN 1 & 2 + Bar Channels</strong>
            <p class="text-slate-600">Matchroom Boxing, Golden Boy, Serie A, NFL Game Pass, UEFA Women's Champions League.</p>
          </div>
          <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-sm">
            <strong class="text-slate-900 block text-sm mb-1">Canal+ & RMC Sport UHD</strong>
            <p class="text-slate-600">Canal+ Sport, Canal+ Foot, Formula 1 4K, Premier League, Top 14 Rugby in French.</p>
          </div>
        </div>
      </div>

      <!-- Action Banner -->
      <div class="text-center p-8 rounded-2xl bg-gradient-to-r from-cyan-50 via-white to-blue-50 border border-cyan-200 shadow-sm" data-aos="zoom-in" data-aos-delay="150">
        <h3 class="font-heading font-black text-2xl text-slate-900">Experience All <?= TOTAL_CHANNELS ?> Channels On Your TV</h3>
        <p class="mt-2 text-xs sm:text-sm text-slate-600 max-w-xl mx-auto">
          Start enjoying uninterrupted live sports, cinema, and news with our proprietary Anti-Freeze™ 9.9 technology.
        </p>
        <div class="mt-6 flex flex-wrap items-center justify-center gap-4">
          <a href="/shop" class="btn-glow-cyan px-6 py-3 rounded-xl text-xs font-bold shadow-md shadow-cyan-500/20">
            Order Subscription from $6.24/mo
          </a>
          <?php if (TRIAL_IS_ACTIVE): ?>
          <button data-open-trial class="px-6 py-3 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-800 hover:bg-slate-50 cursor-pointer shadow-sm">
            <?= htmlspecialchars(TRIAL_BUTTON_LABEL) ?>
          </button>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>

<?php
include __DIR__ . '/includes/modals.php';
include __DIR__ . '/includes/footer.php';
?>
