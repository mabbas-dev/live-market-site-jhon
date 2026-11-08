<?php
/**
 * LIVETVMARKET - Admin SMTP Email & Pushover Push Notification Settings
 * Configure mail delivery for customers & admins, and manage Pushover alerts for admin devices.
 */
$adminTitle = 'SMTP & Pushover Notifications';
$adminPage = 'notifications';

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/notifications.php';

$successMsg = '';
$errorMsg = '';
$diagnosticLog = '';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save_settings';

    if ($action === 'save_settings') {
        $settingsToSave = [
            'smtp_enabled'        => isset($_POST['smtp_enabled']) ? '1' : '0',
            'smtp_host'           => trim($_POST['smtp_host'] ?? ''),
            'smtp_port'           => trim($_POST['smtp_port'] ?? '465'),
            'smtp_secure'         => trim($_POST['smtp_secure'] ?? 'ssl'),
            'smtp_username'       => trim($_POST['smtp_username'] ?? ''),
            'smtp_password'       => trim($_POST['smtp_password'] ?? ''),
            'smtp_from_email'     => trim($_POST['smtp_from_email'] ?? ''),
            'smtp_from_name'      => trim($_POST['smtp_from_name'] ?? 'LIVETVMARKET'),
            'smtp_admin_email'    => trim($_POST['smtp_admin_email'] ?? ''),
            'pushover_enabled'    => isset($_POST['pushover_enabled']) ? '1' : '0',
            'pushover_app_token'  => trim($_POST['pushover_app_token'] ?? ''),
            'pushover_user_key'   => trim($_POST['pushover_user_key'] ?? ''),
            'pushover_devices'    => trim($_POST['pushover_devices'] ?? 'pixel8'),
            'pushover_sound'      => trim($_POST['pushover_sound'] ?? 'cashregister'),
            'pushover_priority'   => trim($_POST['pushover_priority'] ?? '0'),
        ];

        foreach ($settingsToSave as $k => $v) {
            updateSetting($k, $v);
        }
        $successMsg = 'All SMTP Email & Pushover settings saved successfully!';
    } elseif ($action === 'test_pushover') {
        $testDevice = trim($_POST['test_device'] ?? 'pixel8');
        $testMsg = "Test alert from LIVETVMARKET Control Center!\nDevice: {$testDevice}\nTime: " . date('Y-m-d H:i:s');
        $siteUrl = getSetting('site_url', 'https://livetvmarket.com');
        $res = sendPushoverNotification(
            "LIVETVMARKET Notification Test",
            $testMsg,
            rtrim($siteUrl, '/') . '/admin/orders.php',
            (int)getSetting('pushover_priority', '0'),
            getSetting('pushover_sound', 'cashregister'),
            $testDevice
        );

        if ($res['success']) {
            $successMsg = "Pushover notification successfully delivered to '{$testDevice}'!";
        } else {
            $errorMsg = "Pushover Error: " . ($res['error'] ?? 'Failed to send');
        }
    } elseif ($action === 'test_email') {
        $testRecipient = trim($_POST['test_email_recipient'] ?? '');
        if (empty($testRecipient) || !filter_var($testRecipient, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Please enter a valid recipient email address for testing.';
        } else {
            $subject = "LIVETVMARKET SMTP Test Message (" . date('H:i:s') . ")";
            $html = "
            <div style='background:#070913; color:#fff; padding:30px; font-family:sans-serif;'>
                <div style='max-width:500px; margin:0 auto; background:#0D1126; border:1px solid #00d2ff; border-radius:16px; padding:24px;'>
                    <h2 style='color:#00d2ff; margin-top:0;'>SMTP Connection Successful!</h2>
                    <p style='color:#94a3b8;'>Your SMTP configuration is working properly.</p>
                    <div style='background:rgba(0,0,0,0.3); padding:12px; border-radius:8px; font-size:12px; font-family:monospace; color:#38bdf8;'>
                        Timestamp: " . date('r') . "<br>
                        Server: " . htmlspecialchars(getSetting('smtp_host')) . ":" . htmlspecialchars(getSetting('smtp_port')) . "
                    </div>
                </div>
            </div>";

            $res = sendSMTPEmail($testRecipient, 'Admin Tester', $subject, $html, "SMTP Test Successful!", $diagnosticLog);
            if ($res['success']) {
                $successMsg = "Test email successfully delivered to '{$testRecipient}'!";
            } else {
                $errorMsg = "SMTP Error: " . ($res['error'] ?? 'Failed to send test email.');
            }
        }
    }
}

// Current Values
$smtpEnabled      = getSetting('smtp_enabled', '0');
$smtpHost         = getSetting('smtp_host', 'smtp.titan.email');
$smtpPort         = getSetting('smtp_port', '465');
$smtpSecure       = getSetting('smtp_secure', 'ssl');
$smtpUsername     = getSetting('smtp_username', '');
$smtpPassword     = getSetting('smtp_password', '');
$smtpFromEmail    = getSetting('smtp_from_email', 'support@livetvmarket.com');
$smtpFromName     = getSetting('smtp_from_name', 'LIVETVMARKET Support');
$smtpAdminEmail   = getSetting('smtp_admin_email', 'admin@livetvmarket.com');

$pushoverEnabled  = getSetting('pushover_enabled', '1');
$pushoverToken    = getSetting('pushover_app_token', 'aec7p4gjfhdaaxewy6fo8knvogw84r');
$pushoverUser     = getSetting('pushover_user_key', 'u6vnh57r1i4w5h84eoeh9jx9epnfh7');
$pushoverDevices  = getSetting('pushover_devices', 'pixel8');
$pushoverSound    = getSetting('pushover_sound', 'cashregister');
$pushoverPriority = getSetting('pushover_priority', '0');
?>

<div class="max-w-5xl">
  
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-white">SMTP Email & Pushover Notifications</h1>
      <p class="text-xs text-slate-400 mt-1">Configure automated credentials dispatch via SMTP and instant order push notifications for admin devices.</p>
    </div>
    <div class="flex items-center gap-2">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold <?= ($pushoverEnabled === '1') ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'bg-slate-800 text-slate-400' ?>">
        <span class="w-1.5 h-1.5 rounded-full <?= ($pushoverEnabled === '1') ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500' ?>"></span>
        Pushover: <?= ($pushoverEnabled === '1') ? 'ACTIVE' : 'DISABLED' ?>
      </span>
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold <?= ($smtpEnabled === '1') ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/40' : 'bg-slate-800 text-slate-400' ?>">
        <span class="w-1.5 h-1.5 rounded-full <?= ($smtpEnabled === '1') ? 'bg-cyan-400 animate-pulse' : 'bg-slate-500' ?>"></span>
        SMTP: <?= ($smtpEnabled === '1') ? 'ACTIVE' : 'DISABLED' ?>
      </span>
    </div>
  </div>

  <?php if (!empty($successMsg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-3">
      <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400"></i>
      <span><?= htmlspecialchars($successMsg) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($errorMsg)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-rose-500/15 border border-rose-500/40 text-rose-300 text-xs flex items-center gap-3">
      <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400"></i>
      <span><?= htmlspecialchars($errorMsg) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($diagnosticLog)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-slate-900 border border-slate-800 text-slate-300 text-xs font-mono">
      <div class="text-[11px] text-cyan-400 font-bold mb-2">SMTP Socket Diagnostic Output:</div>
      <pre class="overflow-x-auto whitespace-pre-wrap max-h-48 text-[10px] leading-relaxed"><?= htmlspecialchars($diagnosticLog) ?></pre>
    </div>
  <?php endif; ?>

  <form action="notifications.php" method="POST">
    <input type="hidden" name="action" value="save_settings">

    <!-- TWO MAIN CARDS: PUSHOVER & SMTP -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      
      <!-- CARD 1: PUSHOVER PUSH NOTIFICATIONS -->
      <div class="glass-card rounded-2xl p-6 flex flex-col justify-between border border-slate-800">
        <div>
          <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-800">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-purple-500/15 border border-purple-500/30 flex items-center justify-center text-purple-400">
                <i data-lucide="smartphone" class="w-5 h-5"></i>
              </div>
              <div>
                <h2 class="text-sm font-bold text-white">Pushover Mobile Alerts</h2>
                <p class="text-[11px] text-slate-400">Instant sound notifications on admin devices</p>
              </div>
            </div>
            
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" name="pushover_enabled" value="1" <?= ($pushoverEnabled === '1') ? 'checked' : '' ?> class="sr-only peer">
              <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
            </label>
          </div>

          <div class="space-y-4 text-xs">
            <div>
              <label class="block text-slate-400 mb-1 font-semibold">Pushover App API Token:</label>
              <input type="text" name="pushover_app_token" value="<?= htmlspecialchars($pushoverToken) ?>" placeholder="e.g. aec7p4gjfhdaaxewy6fo8knvogw84r"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-purple-400">
            </div>

            <div>
              <label class="block text-slate-400 mb-1 font-semibold">Pushover User Key:</label>
              <input type="text" name="pushover_user_key" value="<?= htmlspecialchars($pushoverUser) ?>" placeholder="e.g. u6vnh57r1i4w5h84eoeh9jx9epnfh7"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-purple-400">
            </div>

            <div>
              <label class="block text-slate-400 mb-1 font-semibold">Target Admin Devices (Comma-separated):</label>
              <input type="text" name="pushover_devices" value="<?= htmlspecialchars($pushoverDevices) ?>" placeholder="e.g. pixel8, ipad, iphone"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-purple-400">
              <span class="text-[10px] text-slate-500 mt-1 block">Your current testing phone: <strong class="text-purple-300">pixel8</strong>. Multiple devices can be separated with commas.</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-slate-400 mb-1 font-semibold">Alert Sound:</label>
                <select name="pushover_sound" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:outline-none focus:border-purple-400 font-mono">
                  <option value="cashregister" <?= ($pushoverSound === 'cashregister') ? 'selected' : '' ?>>cashregister 💰</option>
                  <option value="pushover" <?= ($pushoverSound === 'pushover') ? 'selected' : '' ?>>pushover (default)</option>
                  <option value="magic" <?= ($pushoverSound === 'magic') ? 'selected' : '' ?>>magic ✨</option>
                  <option value="climb" <?= ($pushoverSound === 'climb') ? 'selected' : '' ?>>climb</option>
                  <option value="cosmic" <?= ($pushoverSound === 'cosmic') ? 'selected' : '' ?>>cosmic</option>
                  <option value="intermission" <?= ($pushoverSound === 'intermission') ? 'selected' : '' ?>>intermission</option>
                  <option value="siren" <?= ($pushoverSound === 'siren') ? 'selected' : '' ?>>siren 🚨</option>
                </select>
              </div>

              <div>
                <label class="block text-slate-400 mb-1 font-semibold">Priority Level:</label>
                <select name="pushover_priority" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:outline-none focus:border-purple-400 font-mono">
                  <option value="0" <?= ($pushoverPriority === '0') ? 'selected' : '' ?>>0 - Normal</option>
                  <option value="1" <?= ($pushoverPriority === '1') ? 'selected' : '' ?>>1 - High (Bypass quiet)</option>
                  <option value="-1" <?= ($pushoverPriority === '-1') ? 'selected' : '' ?>>-1 - Quiet / Silent</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-800/80">
          <div class="text-[11px] text-purple-300/80 flex items-center gap-1.5">
            <i data-lucide="zap" class="w-3.5 h-3.5"></i>
            <span>Sends real-time popups with cash register sound on every order!</span>
          </div>
        </div>
      </div>

      <!-- CARD 2: SMTP EMAIL SERVER CONFIGURATION -->
      <div class="glass-card rounded-2xl p-6 flex flex-col justify-between border border-slate-800">
        <div>
          <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-800">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                <i data-lucide="mail" class="w-5 h-5"></i>
              </div>
              <div>
                <h2 class="text-sm font-bold text-white">SMTP Email Server Delivery</h2>
                <p class="text-[11px] text-slate-400">Deliver activation M3U credentials & admin receipts</p>
              </div>
            </div>
            
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" name="smtp_enabled" value="1" <?= ($smtpEnabled === '1') ? 'checked' : '' ?> class="sr-only peer">
              <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-600"></div>
            </label>
          </div>

          <div class="space-y-4 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="sm:col-span-2">
                <label class="block text-slate-400 mb-1 font-semibold">SMTP Host:</label>
                <input type="text" name="smtp_host" value="<?= htmlspecialchars($smtpHost) ?>" placeholder="e.g. smtp.titan.email or smtp.gmail.com"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-cyan-400">
              </div>

              <div>
                <label class="block text-slate-400 mb-1 font-semibold">Port:</label>
                <input type="number" name="smtp_port" value="<?= htmlspecialchars($smtpPort) ?>" placeholder="465"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-cyan-400">
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-slate-400 mb-1 font-semibold">Encryption:</label>
                <select name="smtp_secure" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:outline-none focus:border-cyan-400 font-mono">
                  <option value="ssl" <?= ($smtpSecure === 'ssl') ? 'selected' : '' ?>>SSL (Port 465)</option>
                  <option value="tls" <?= ($smtpSecure === 'tls') ? 'selected' : '' ?>>TLS (Port 587)</option>
                </select>
              </div>

              <div>
                <label class="block text-slate-400 mb-1 font-semibold">From Name:</label>
                <input type="text" name="smtp_from_name" value="<?= htmlspecialchars($smtpFromName) ?>" placeholder="LIVETVMARKET"
                  class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white text-xs focus:outline-none focus:border-cyan-400">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-slate-400 mb-1 font-semibold">SMTP Username / Email:</label>
                <input type="text" name="smtp_username" value="<?= htmlspecialchars($smtpUsername) ?>" placeholder="support@livetvmarket.com"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-cyan-400">
              </div>

              <div>
                <label class="block text-slate-400 mb-1 font-semibold">SMTP Password:</label>
                <input type="password" name="smtp_password" value="<?= htmlspecialchars($smtpPassword) ?>" placeholder="••••••••••••"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-cyan-400">
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-slate-400 mb-1 font-semibold">Sender (From) Email:</label>
                <input type="email" name="smtp_from_email" value="<?= htmlspecialchars($smtpFromEmail) ?>" placeholder="support@livetvmarket.com"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-cyan-400">
              </div>

              <div>
                <label class="block text-slate-400 mb-1 font-semibold">Admin Recipient Email:</label>
                <input type="email" name="smtp_admin_email" value="<?= htmlspecialchars($smtpAdminEmail) ?>" placeholder="admin@livetvmarket.com"
                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-cyan-400">
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-800/80">
          <div class="text-[11px] text-cyan-300/80 flex items-center gap-1.5">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            <span>Encrypted socket protocol with automated customer credential emails.</span>
          </div>
        </div>
      </div>

    </div>

    <!-- SAVE SETTINGS BUTTON -->
    <div class="mb-10 flex justify-end">
      <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-slate-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-cyan-500/20 flex items-center gap-2 transition-all">
        <i data-lucide="save" class="w-4 h-4"></i>
        Save All Notification Settings
      </button>
    </div>
  </form>

  <!-- TEST TOOLS SECTION (TWO INTERACTIVE TEST FORMS) -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
    
    <!-- TEST PUSHOVER FORM -->
    <div class="glass-card rounded-2xl p-6 border border-slate-800/80">
      <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-800">
        <i data-lucide="send" class="w-4 h-4 text-purple-400"></i>
        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Test Pushover Push to Phone</h3>
      </div>
      <p class="text-[11px] text-slate-400 mb-4">Send an immediate test alert with the cash register sound to your phone.</p>

      <form action="notifications.php" method="POST" class="flex gap-2">
        <input type="hidden" name="action" value="test_pushover">
        <input type="text" name="test_device" value="pixel8" placeholder="device name (e.g. pixel8)"
          class="flex-1 px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-purple-400">
        <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs flex items-center gap-1.5 transition-colors">
          <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
          Test Phone
        </button>
      </form>
    </div>

    <!-- TEST SMTP EMAIL FORM -->
    <div class="glass-card rounded-2xl p-6 border border-slate-800/80">
      <div class="flex items-center gap-2.5 mb-4 pb-3 border-b border-slate-800">
        <i data-lucide="mail-check" class="w-4 h-4 text-cyan-400"></i>
        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Test SMTP Server Delivery</h3>
      </div>
      <p class="text-[11px] text-slate-400 mb-4">Verify SMTP socket handshake by delivering a test message to an inbox.</p>

      <form action="notifications.php" method="POST" class="flex gap-2">
        <input type="hidden" name="action" value="test_email">
        <input type="email" name="test_email_recipient" value="<?= htmlspecialchars($smtpAdminEmail) ?>" placeholder="recipient@example.com"
          class="flex-1 px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono text-xs focus:outline-none focus:border-cyan-400">
        <button type="submit" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-colors">
          <i data-lucide="send" class="w-3.5 h-3.5"></i>
          Test Email
        </button>
      </form>
    </div>

  </div>

</div>

<?php
require_once __DIR__ . '/footer.php';
?>

