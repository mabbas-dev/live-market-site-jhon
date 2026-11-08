<?php
/**
 * LIVETVMARKET - Admin Shared Header & Navigation Shell
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/config.php';

$curAdminPage = $adminPage ?? 'dashboard';

// Quick metrics for badges in sidebar
$pendingTrialsCount = 0;
$newOrdersCount = 0;
try {
    $stmt1 = $pdo->query("SELECT COUNT(*) FROM `trial_leads` WHERE `status` = 'Pending'");
    $pendingTrialsCount = (int)$stmt1->fetchColumn();

    $stmt2 = $pdo->query("SELECT COUNT(*) FROM `orders` WHERE `status` = 'New'");
    $newOrdersCount = (int)$stmt2->fetchColumn();
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#060a14]">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $adminTitle ?? 'Admin Dashboard' ?> | <?= htmlspecialchars(SITE_NAME) ?> Control Center</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .font-mono { font-family: 'JetBrains Mono', monospace; }
    .glass-card {
      background: rgba(14, 22, 41, 0.7);
      backdrop-filter: blur(16px);
      border: 1px solid rgba(56, 189, 248, 0.15);
    }
    .glass-panel {
      background: rgba(10, 16, 31, 0.9);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .btn-glow-cyan {
      background: linear-gradient(135deg, #00d2ff 0%, #0077ff 100%);
      transition: all 0.3s ease;
    }
    .btn-glow-cyan:hover {
      box-shadow: 0 0 25px rgba(0, 210, 255, 0.4);
      transform: translateY(-1px);
    }
    /* Custom scrollbars */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: #070d1a; }
    ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #38bdf8; }
  </style>
</head>
<body class="h-full bg-[#060a14] text-slate-100 flex flex-col md:flex-row antialiased overflow-x-hidden">

  <!-- SIDEBAR (Desktop) -->
  <aside class="w-full md:w-64 bg-[#090f20] border-b md:border-b-0 md:border-r border-slate-800/80 flex flex-col flex-shrink-0 z-30">
    
    <!-- Brand / Logo Area -->
    <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800/80">
      <a href="index.php" class="flex items-center gap-3 group">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 via-blue-600 to-indigo-600 p-[1.5px] shadow-lg shadow-cyan-500/20 group-hover:scale-105 transition-all">
          <div class="w-full h-full bg-[#080d1a] rounded-[10px] flex items-center justify-center">
            <i data-lucide="shield" class="w-5 h-5 text-cyan-400"></i>
          </div>
        </div>
        <div class="flex flex-col">
          <span class="font-black text-lg text-white leading-none">ADMIN <span class="text-cyan-400">PANEL</span></span>
          <span class="text-[9px] font-mono text-emerald-400 mt-1 uppercase tracking-wider flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Control Center
          </span>
        </div>
      </a>
      
      <!-- Mobile Nav Toggle -->
      <button id="admin-mobile-toggle" class="md:hidden p-2 rounded-lg bg-slate-800 text-slate-300">
        <i data-lucide="menu" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Navigation Links -->
    <nav id="admin-nav-menu" class="hidden md:flex flex-col flex-1 p-4 space-y-1.5 text-xs font-semibold overflow-y-auto">
      
      <div class="px-3 pt-2 pb-1 text-[10px] font-mono uppercase tracking-wider text-slate-500 font-bold">Main Navigation</div>

      <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($curAdminPage === 'dashboard') ? 'bg-cyan-500/15 text-cyan-400 font-bold border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
        <i data-lucide="layout-dashboard" class="w-4 h-4 text-cyan-400"></i>
        <span>Dashboard</span>
      </a>

      <a href="settings.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($curAdminPage === 'settings') ? 'bg-cyan-500/15 text-cyan-400 font-bold border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
        <i data-lucide="sliders" class="w-4 h-4 text-amber-400"></i>
        <span>Site Customization</span>
      </a>

      <a href="plans.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($curAdminPage === 'plans') ? 'bg-cyan-500/15 text-cyan-400 font-bold border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
        <i data-lucide="tags" class="w-4 h-4 text-emerald-400"></i>
        <span>Pricing & Plans</span>
      </a>

      <a href="coupons.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($curAdminPage === 'coupons') ? 'bg-cyan-500/15 text-cyan-400 font-bold border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
        <i data-lucide="ticket-percent" class="w-4 h-4 text-cyan-400"></i>
        <span>Promo Coupons</span>
      </a>

      <a href="currencies.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($curAdminPage === 'currencies') ? 'bg-cyan-500/15 text-cyan-400 font-bold border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
        <i data-lucide="coins" class="w-4 h-4 text-amber-400"></i>
        <span>Currency & GeoIP</span>
      </a>

      <a href="blogs.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($curAdminPage === 'blogs') ? 'bg-cyan-500/15 text-cyan-400 font-bold border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
        <i data-lucide="newspaper" class="w-4 h-4 text-cyan-400"></i>
        <span>Blog Articles</span>
      </a>

      <a href="setup_guides.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($curAdminPage === 'setup_guides') ? 'bg-cyan-500/15 text-cyan-400 font-bold border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
        <i data-lucide="book-open" class="w-4 h-4 text-emerald-400"></i>
        <span>Setup Guides</span>
      </a>

      <a href="social_proof.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($curAdminPage === 'social_proof') ? 'bg-cyan-500/15 text-cyan-400 font-bold border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
        <i data-lucide="bell-ring" class="w-4 h-4 text-cyan-400"></i>
        <span>Live Sales Popups</span>
      </a>

      <div class="px-3 pt-4 pb-1 text-[10px] font-mono uppercase tracking-wider text-slate-500 font-bold">Leads & Sales</div>

      <a href="trials.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= ($curAdminPage === 'trials') ? 'bg-cyan-500/15 text-cyan-400 font-bold border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
        <div class="flex items-center gap-3">
          <i data-lucide="play-circle" class="w-4 h-4 text-purple-400"></i>
          <span>Free Trial Leads</span>
        </div>
        <?php if ($pendingTrialsCount > 0): ?>
          <span class="px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 font-mono text-[10px] font-bold border border-purple-500/40"><?= $pendingTrialsCount ?></span>
        <?php endif; ?>
      </a>

      <a href="orders.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition-all <?= ($curAdminPage === 'orders') ? 'bg-cyan-500/15 text-cyan-400 font-bold border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
        <div class="flex items-center gap-3">
          <i data-lucide="shopping-cart" class="w-4 h-4 text-blue-400"></i>
          <span>Customer Orders</span>
        </div>
        <?php if ($newOrdersCount > 0): ?>
          <span class="px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 font-mono text-[10px] font-bold border border-blue-500/40"><?= $newOrdersCount ?></span>
        <?php endif; ?>
      </a>

      <a href="notifications.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all <?= ($curAdminPage === 'notifications') ? 'bg-cyan-500/15 text-cyan-400 font-bold border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
        <i data-lucide="bell" class="w-4 h-4 text-emerald-400"></i>
        <span>SMTP & Pushover</span>
      </a>

      <div class="px-3 pt-4 pb-1 text-[10px] font-mono uppercase tracking-wider text-slate-500 font-bold">External</div>

      <a href="../index.php" target="_blank" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all">
        <i data-lucide="external-link" class="w-4 h-4 text-cyan-400"></i>
        <span>Live Storefront ↗</span>
      </a>

      <a href="logout.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-all mt-auto">
        <i data-lucide="log-out" class="w-4 h-4"></i>
        <span>Sign Out</span>
      </a>

    </nav>

    <!-- Admin User Badge -->
    <div class="p-4 border-t border-slate-800/80 hidden md:block">
      <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold text-xs">
            A
          </div>
          <div>
            <div class="text-xs font-bold text-white"><?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?></div>
            <div class="text-[10px] text-emerald-400 font-mono">Super Administrator</div>
          </div>
        </div>
        <a href="logout.php" title="Sign Out" class="text-slate-400 hover:text-rose-400 p-1.5 transition-colors">
          <i data-lucide="power" class="w-4 h-4"></i>
        </a>
      </div>
    </div>

  </aside>

  <!-- MAIN CONTENT CONTAINER -->
  <div class="flex-1 flex flex-col min-h-screen overflow-y-auto bg-[#070c18]">
    
    <!-- Top Action Bar -->
    <header class="h-16 bg-[#090f20]/90 backdrop-blur-xl border-b border-slate-800/80 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-20">
      <div class="flex items-center gap-3">
        <h2 class="text-base font-bold text-white"><?= $adminTitle ?? 'Dashboard' ?></h2>
      </div>

      <div class="flex items-center gap-3 text-xs">
        <a href="../index.php" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-cyan-300 border border-slate-700 flex items-center gap-1.5 font-semibold transition-all">
          <i data-lucide="globe" class="w-3.5 h-3.5"></i>
          <span class="hidden sm:inline">View Site</span>
        </a>
        <a href="settings.php" class="px-3 py-1.5 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 flex items-center gap-1.5 font-semibold transition-all">
          <i data-lucide="settings" class="w-3.5 h-3.5"></i>
          <span>Settings</span>
        </a>
      </div>
    </header>

    <!-- Page Body Area -->
    <main class="flex-1 p-6 sm:p-8">
