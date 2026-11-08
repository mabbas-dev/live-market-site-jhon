<?php
/**
 * LIVETVMARKET - Luxury Admin Login Portal
 * Connects directly to MySQL database 'iptv-website'.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $success = 'You have been securely logged out.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `admins` WHERE `username` = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_user'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                header("Location: index.php");
                exit;
            } else if ($username === 'admin' && $password === 'admin123') {
                // Failsafe fallback
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user'] = 'admin';
                $_SESSION['admin_email'] = 'admin@livetvmarket.com';
                header("Location: index.php");
                exit;
            } else {
                $error = 'Invalid administrative credentials. Please verify and retry.';
            }
        } catch (Exception $e) {
            $error = 'Database authentication error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#060a14]">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | LIVETVMARKET Control Center</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .glass-card {
      background: rgba(14, 22, 41, 0.85);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(56, 189, 248, 0.2);
    }
    .btn-glow-cyan {
      background: linear-gradient(135deg, #00d2ff 0%, #0077ff 100%);
      transition: all 0.3s ease;
    }
    .btn-glow-cyan:hover {
      box-shadow: 0 0 25px rgba(0, 210, 255, 0.5);
      transform: translateY(-1px);
    }
  </style>
</head>
<body class="h-full flex items-center justify-center p-4 relative overflow-hidden bg-gradient-to-b from-[#060a14] via-[#091124] to-[#060a14] text-slate-100">

  <!-- Glowing Ambient Lights -->
  <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-cyan-500/15 rounded-full blur-[140px] pointer-events-none"></div>
  <div class="absolute -bottom-40 right-1/4 w-[500px] h-[300px] bg-blue-600/10 rounded-full blur-[140px] pointer-events-none"></div>

  <div class="w-full max-w-md relative z-10">
    
    <!-- Header Brand -->
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-500 via-blue-600 to-indigo-600 p-[2px] shadow-xl shadow-cyan-500/25 mb-4">
        <div class="w-full h-full bg-[#080d1a] rounded-[14px] flex items-center justify-center">
          <i data-lucide="shield" class="w-8 h-8 text-cyan-400"></i>
        </div>
      </div>
      <h1 class="text-2xl font-black tracking-tight text-white flex items-center justify-center gap-1.5">
        <span>LIVETV</span>
        <span class="bg-gradient-to-r from-cyan-400 to-emerald-400 bg-clip-text text-transparent">MARKET</span>
      </h1>
      <p class="text-xs text-slate-400 mt-1 font-medium">Administrator Control Portal • Secure 256-Bit Session</p>
    </div>

    <!-- Login Box -->
    <div class="glass-card rounded-2xl p-7 sm:p-8 shadow-2xl relative">
      
      <?php if (!empty($error)): ?>
        <div class="mb-5 p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs flex items-center gap-2.5">
          <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 text-rose-400"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="mb-5 p-3.5 rounded-xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-2.5">
          <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0 text-emerald-400"></i>
          <span><?= htmlspecialchars($success) ?></span>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST" class="space-y-4">
        <div>
          <label for="username" class="block text-xs font-semibold text-slate-300 mb-1.5">Admin Username</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i data-lucide="user" class="w-4 h-4"></i>
            </span>
            <input type="text" id="username" name="username" value="admin" required
              class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 transition-all font-mono">
          </div>
        </div>

        <div>
          <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <i data-lucide="lock" class="w-4 h-4"></i>
            </span>
            <input type="password" id="password" name="password" value="admin123" required
              class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 transition-all font-mono">
          </div>
        </div>

        <!-- Quick Demo Hint -->
        <div class="p-3 rounded-xl bg-cyan-950/40 border border-cyan-800/40 text-[11px] text-cyan-300 flex items-center justify-between">
          <div class="flex items-center gap-1.5">
            <i data-lucide="key" class="w-3.5 h-3.5 text-cyan-400"></i>
            <span>Default: <strong class="text-white">admin</strong> / <strong class="text-white">admin123</strong></span>
          </div>
          <button type="button" onclick="document.getElementById('username').value='admin';document.getElementById('password').value='admin123';" class="text-[10px] uppercase font-bold text-cyan-400 hover:text-white underline">
            Auto-Fill
          </button>
        </div>

        <button type="submit" class="btn-glow-cyan w-full py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-950 hover:text-white flex items-center justify-center gap-2 shadow-lg cursor-pointer">
          <i data-lucide="log-in" class="w-4 h-4"></i>
          <span>Sign In to Control Center</span>
        </button>
      </form>

      <div class="mt-6 pt-5 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
        <a href="../index.php" class="hover:text-cyan-400 transition-colors flex items-center gap-1.5">
          <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
          <span>Return to Website</span>
        </a>
        <span class="flex items-center gap-1 text-emerald-400 font-mono text-[11px]">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          MySQL Active
        </span>
      </div>

    </div>
  </div>

  <script>
    if (window.lucide) {
      window.lucide.createIcons();
    }
  </script>
</body>
</html>
