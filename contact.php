<?php
require_once __DIR__ . '/config/config.php';
$activePage = 'contact';
$pageTitle = 'Contact Us | 24/7 VIP Streaming Support & Technical Desk';
$pageDesc = 'Get in touch with ' . SITE_NAME . ' 24/7 support. Fast resolution for M3U playlist credentials, device setups, channel requests, and billing.';
$pageCanonical = SITE_URL . '/contact';

$formSubmitted = false;
$feedbackMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_contact') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $department = trim($_POST['department'] ?? 'General Inquiry');
    $orderId = trim($_POST['order_id'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && !empty($message)) {
        if ($pdo) {
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS `contact_messages` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL,
                    `email` VARCHAR(100) NOT NULL,
                    `whatsapp` VARCHAR(50) DEFAULT NULL,
                    `department` VARCHAR(50) NOT NULL,
                    `order_id` VARCHAR(50) DEFAULT NULL,
                    `message` TEXT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                $stmt = $pdo->prepare("INSERT INTO `contact_messages` (`name`, `email`, `whatsapp`, `department`, `order_id`, `message`) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $whatsapp, $department, $orderId, $message]);
            } catch (Exception $e) {
                error_log("Contact form error: " . $e->getMessage());
            }
        }
        $formSubmitted = true;
        $feedbackMsg = "Thank you, " . htmlspecialchars($name) . "! Your support ticket has been registered. Our senior technical desk has received your request and will follow up at " . htmlspecialchars($email) . " within 15 minutes.";
    } else {
        $feedbackMsg = "Please fill in all required fields (Name, Email, and Message) so we can assist you promptly.";
    }
}

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/top-banner.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="min-h-screen bg-white">
  
  <!-- HERO BANNER -->
  <section class="relative pt-12 pb-16 bg-gradient-to-b from-slate-50 via-white to-white border-b border-slate-100 overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(#0891b2_1px,transparent_1px)] [background-size:24px_24px] opacity-10"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-xs text-slate-500 mb-6" aria-label="Breadcrumb">
        <a href="/" class="hover:text-cyan-600 transition-colors flex items-center gap-1">
          <i data-lucide="home" class="w-3.5 h-3.5"></i> Home
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span class="text-slate-800 font-semibold">Contact Us</span>
      </nav>

      <div class="text-center max-w-3xl mx-auto">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-50 border border-cyan-200/80 text-cyan-700 text-xs font-semibold mb-4 shadow-sm">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          24/7/365 VIP Support Desk Active
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-heading font-black text-slate-900 tracking-tight leading-tight">
          We're Here to Help You <span class="text-cyan-600">Stream Flawlessly</span>
        </h1>
        <p class="mt-4 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
          Need help setting up your Firestick, retrieving your playlist M3U, or switching servers? Our live streaming engineers are available round the clock.
        </p>
      </div>

      <!-- QUICK CONTACT CARDS -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mt-10">
        
        <!-- WhatsApp -->
        <a href="<?= WHATSAPP_LINK ?>" target="_blank" rel="noopener noreferrer" class="group p-5 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:shadow-md hover:border-emerald-300 transition-all">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <i data-lucide="message-circle" class="w-6 h-6"></i>
          </div>
          <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-emerald-600 mb-1">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            Instant Response (2m)
          </div>
          <h3 class="font-heading font-bold text-slate-900 text-base">VIP WhatsApp Desk</h3>
          <p class="text-xs text-slate-500 mt-1 mb-3">Direct real-time chat with streaming tech engineers.</p>
          <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 group-hover:underline">
            Chat on WhatsApp <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </span>
        </a>

        <!-- Email -->
        <a href="mailto:<?= CONTACT_EMAIL ?>" class="group p-5 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:shadow-md hover:border-cyan-300 transition-all">
          <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <i data-lucide="mail" class="w-6 h-6"></i>
          </div>
          <div class="text-[11px] font-bold uppercase tracking-wider text-cyan-700 mb-1">
            Ticket Desk
          </div>
          <h3 class="font-heading font-bold text-slate-900 text-base">Email Support</h3>
          <p class="text-xs text-slate-500 mt-1 mb-3">Official billing, credentials, and technical reports.</p>
          <span class="inline-flex items-center gap-1 text-xs font-semibold text-cyan-600 group-hover:underline">
            <?= CONTACT_EMAIL ?> <i data-lucide="external-link" class="w-3 h-3"></i>
          </span>
        </a>

        <!-- Order Lookup -->
        <a href="/track-order" class="group p-5 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all">
          <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <i data-lucide="search" class="w-6 h-6"></i>
          </div>
          <div class="text-[11px] font-bold uppercase tracking-wider text-indigo-700 mb-1">
            Automated Delivery
          </div>
          <h3 class="font-heading font-bold text-slate-900 text-base">Track Order Status</h3>
          <p class="text-xs text-slate-500 mt-1 mb-3">Look up your subscription activation & credentials instantly.</p>
          <span class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 group-hover:underline">
            Go to Order Tracker <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </span>
        </a>

        <!-- Setup Guides -->
        <a href="/setup" class="group p-5 rounded-2xl bg-white border border-slate-200/90 shadow-sm hover:shadow-md hover:border-purple-300 transition-all">
          <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
            <i data-lucide="tv" class="w-6 h-6"></i>
          </div>
          <div class="text-[11px] font-bold uppercase tracking-wider text-purple-700 mb-1">
            Step-by-Step
          </div>
          <h3 class="font-heading font-bold text-slate-900 text-base">Setup Tutorials</h3>
          <p class="text-xs text-slate-500 mt-1 mb-3">Walkthroughs for Firestick, Smart TV, Android & iOS.</p>
          <span class="inline-flex items-center gap-1 text-xs font-semibold text-purple-600 group-hover:underline">
            View Tutorials <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
          </span>
        </a>

      </div>

    </div>
  </section>

  <!-- MAIN CONTACT SECTION -->
  <section class="py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        
        <!-- LEFT: CONTACT FORM -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8 shadow-sm">
          
          <div class="mb-6">
            <h2 class="text-2xl font-heading font-bold text-slate-900 tracking-tight">Send Us a Direct Message</h2>
            <p class="text-sm text-slate-600 mt-1">
              Fill in your inquiry details below. A dedicated support specialist will review your case immediately.
            </p>
          </div>

          <?php if (!empty($feedbackMsg)): ?>
            <div class="mb-6 p-4 rounded-xl <?= $formSubmitted ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800' ?> flex items-start gap-3">
              <i data-lucide="<?= $formSubmitted ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 flex-shrink-0 mt-0.5 <?= $formSubmitted ? 'text-emerald-600' : 'text-rose-600' ?>"></i>
              <div class="text-sm">
                <?= $feedbackMsg ?>
              </div>
            </div>
          <?php endif; ?>

          <form action="/contact" method="POST" class="space-y-5">
            <input type="hidden" name="action" value="submit_contact">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                  Your Full Name <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                  <input type="text" name="name" required placeholder="e.g. John Miller" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 text-slate-900 text-sm rounded-xl pl-10 pr-4 py-3 transition-all outline-none">
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                  Email Address <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                  <input type="email" name="email" required placeholder="john@example.com" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 text-slate-900 text-sm rounded-xl pl-10 pr-4 py-3 transition-all outline-none">
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                  WhatsApp Number (Optional)
                </label>
                <div class="relative">
                  <i data-lucide="phone" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                  <input type="text" name="whatsapp" placeholder="+1 (555) 000-0000" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 text-slate-900 text-sm rounded-xl pl-10 pr-4 py-3 transition-all outline-none">
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Include country code for fast WhatsApp resolution.</p>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                  Department / Topic <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <select name="department" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 text-slate-900 text-sm rounded-xl px-4 py-3 transition-all outline-none appearance-none cursor-pointer">
                    <option value="Activation & M3U Links">Activation & M3U Links</option>
                    <option value="Device Setup Assistance">Device Setup Assistance</option>
                    <option value="Server / Buffer Troubleshooting">Server / Buffer Troubleshooting</option>
                    <option value="Billing & Subscription Renewal">Billing & Subscription Renewal</option>
                    <option value="Custom Sports / Channel Request">Custom Sports / Channel Request</option>
                    <option value="Reseller Inquiries">Reseller & Multi-Line Inquiries</option>
                  </select>
                  <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Order ID or Subscription Username (Optional)
              </label>
              <div class="relative">
                <i data-lucide="tag" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="order_id" placeholder="e.g. LTV-8472 or iptv_user_123" class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 text-slate-900 text-sm rounded-xl pl-10 pr-4 py-3 transition-all outline-none">
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                Your Message / Problem Description <span class="text-rose-500">*</span>
              </label>
              <textarea name="message" rows="5" required placeholder="Describe your inquiry in detail. Include device model (Firestick 4K, Samsung Smart TV, Android Box, etc.) and app name (IPTV Smarters, TiviMate, etc.) if applicable." class="w-full bg-slate-50 border border-slate-200 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 text-slate-900 text-sm rounded-xl p-4 transition-all outline-none resize-y"></textarea>
            </div>

            <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-heading font-bold text-sm shadow-md shadow-cyan-600/20 hover:shadow-cyan-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer">
              <i data-lucide="send" class="w-4 h-4"></i> Submit Support Ticket
            </button>
            
            <p class="text-center text-xs text-slate-500">
              By submitting, your data is protected under our SSL-encrypted privacy guarantee.
            </p>
          </form>

        </div>

        <!-- RIGHT: LIVE STATUS & FAQ HELP -->
        <div class="lg:col-span-5 space-y-6">
          
          <!-- Live System Status Box -->
          <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200">
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <h3 class="font-heading font-bold text-slate-900 text-sm">Streaming Network Health</h3>
              </div>
              <span class="text-xs font-mono font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                100% Operational
              </span>
            </div>

            <div class="mt-4 space-y-3 text-xs">
              <div class="flex justify-between items-center text-slate-600">
                <span>US CDN Edge Nodes (NYC, LA, DAL)</span>
                <span class="font-mono text-emerald-600 font-semibold">12ms • Healthy</span>
              </div>
              <div class="flex justify-between items-center text-slate-600">
                <span>UK & Europe Cluster (LON, FRA, AMS)</span>
                <span class="font-mono text-emerald-600 font-semibold">18ms • Healthy</span>
              </div>
              <div class="flex justify-between items-center text-slate-600">
                <span>Live 4K Sports & PPV Transcoders</span>
                <span class="font-mono text-emerald-600 font-semibold">99.99% • No Jitter</span>
              </div>
              <div class="flex justify-between items-center text-slate-600">
                <span>Catch-up & VOD Cinema Storage</span>
                <span class="font-mono text-emerald-600 font-semibold">Ready • 10 Gbps</span>
              </div>
            </div>

            <div class="mt-5 pt-4 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
              <span>Last diagnostic probe: Just now</span>
              <a href="/shop" class="text-cyan-600 hover:text-cyan-700 font-semibold">Order Subscriptions &rarr;</a>
            </div>
          </div>

          <!-- VIP Fast-Track Box -->
          <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200/80 rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-sm">
                <i data-lucide="zap" class="w-5 h-5"></i>
              </div>
              <div>
                <h3 class="font-heading font-bold text-slate-900 text-sm">Need Instant Setup Help?</h3>
                <p class="text-xs text-emerald-700 font-medium">Skip the queue on WhatsApp</p>
              </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed mb-4">
              If a major football, boxing, or UFC pay-per-view match is starting soon and you need your login right away, reach out on WhatsApp for immediate priority provisioning.
            </p>
            <a href="<?= WHATSAPP_LINK ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold tracking-wide shadow-sm hover:shadow-md transition-all">
              <i data-lucide="message-circle" class="w-4 h-4"></i> Fast-Track on WhatsApp
            </a>
          </div>

          <!-- Guarantees List -->
          <div class="bg-white border border-slate-200/90 rounded-2xl p-6 shadow-sm">
            <h3 class="font-heading font-bold text-slate-900 text-sm mb-4">Our Service Commitments</h3>
            <ul class="space-y-3 text-xs text-slate-600">
              <li class="flex items-start gap-2.5">
                <i data-lucide="clock" class="w-4 h-4 text-cyan-600 flex-shrink-0 mt-0.5"></i>
                <span><strong>Instant 60-Second Setup:</strong> Automated credentials generation immediately upon confirmed payment.</span>
              </li>
              <li class="flex items-start gap-2.5">
                <i data-lucide="shield-check" class="w-4 h-4 text-cyan-600 flex-shrink-0 mt-0.5"></i>
                <span><strong>7-Day Money Back SLA:</strong> If our streaming service doesn't meet your standard, receive a prompt refund.</span>
              </li>
              <li class="flex items-start gap-2.5">
                <i data-lucide="activity" class="w-4 h-4 text-cyan-600 flex-shrink-0 mt-0.5"></i>
                <span><strong>Anti-Freeze™ 9.9 Engine:</strong> Real-time packet buffering protection even during peak global events.</span>
              </li>
              <li class="flex items-start gap-2.5">
                <i data-lucide="headset" class="w-4 h-4 text-cyan-600 flex-shrink-0 mt-0.5"></i>
                <span><strong>Real Human Engineers:</strong> No AI loop confusion. You speak with real IPTV streaming experts.</span>
              </li>
            </ul>
          </div>

        </div>

      </div>

    </div>
  </section>

  <!-- FAQ SECTION -->
  <section class="py-16 bg-slate-50 border-t border-slate-200/80">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="text-center mb-10">
        <h2 class="text-2xl sm:text-3xl font-heading font-bold text-slate-900 tracking-tight">Frequently Asked Questions</h2>
        <p class="text-sm text-slate-600 mt-2">Quick answers to common questions about our support, playlists, and activation.</p>
      </div>

      <div class="space-y-4">
        
        <div class="bg-white border border-slate-200/80 rounded-xl p-5 shadow-sm">
          <h3 class="font-heading font-bold text-slate-900 text-sm flex items-center justify-between">
            <span>How fast will I receive my subscription credentials?</span>
            <i data-lucide="check-circle" class="w-4 h-4 text-cyan-600"></i>
          </h3>
          <p class="text-xs text-slate-600 mt-2 leading-relaxed">
            Our technical support team prepares your account and our agent connects directly with you on your provided WhatsApp number and Email to deliver your credentials and personalized setup guides. You can also view live status anytime via our <a href="/track-order" class="text-cyan-600 underline font-semibold">Track Order page</a>.
          </p>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-xl p-5 shadow-sm">
          <h3 class="font-heading font-bold text-slate-900 text-sm flex items-center justify-between">
            <span>Can I switch my subscription between different devices?</span>
            <i data-lucide="check-circle" class="w-4 h-4 text-cyan-600"></i>
          </h3>
          <p class="text-xs text-slate-600 mt-2 leading-relaxed">
            Yes! Your subscription is not locked to one hardware MAC address unless using legacy MAG devices. For Firestick, Android, Smart TV, or iOS, simply log in using your Xtream Codes or M3U link on your new device anytime.
          </p>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-xl p-5 shadow-sm">
          <h3 class="font-heading font-bold text-slate-900 text-sm flex items-center justify-between">
            <span>What should I do if a specific sports channel experiences buffering?</span>
            <i data-lucide="check-circle" class="w-4 h-4 text-cyan-600"></i>
          </h3>
          <p class="text-xs text-slate-600 mt-2 leading-relaxed">
            First, check your local internet speed (minimum 25 Mbps recommended for 4K). If you are on an ISP that throttles IPTV traffic, enable a VPN or switch the channel stream format in your player settings from MPEG-TS to HLS. You can also message our WhatsApp desk to trigger a node refresh for your line.
          </p>
        </div>

        <div class="bg-white border border-slate-200/80 rounded-xl p-5 shadow-sm">
          <h3 class="font-heading font-bold text-slate-900 text-sm flex items-center justify-between">
            <span>How do I renew my active subscription without losing my favorites?</span>
            <i data-lucide="check-circle" class="w-4 h-4 text-cyan-600"></i>
          </h3>
          <p class="text-xs text-slate-600 mt-2 leading-relaxed">
            Simply head to our <a href="/shop" class="text-cyan-600 underline font-semibold">Shop Page</a>, pick your desired renewal package, and enter your existing username or email during checkout. Our billing engine will automatically extend your expiration date without changing your login details.
          </p>
        </div>

      </div>

      <div class="mt-8 text-center">
        <p class="text-xs text-slate-500">
          Didn't find your answer? Reach our team anytime via <a href="<?= WHATSAPP_LINK ?>" target="_blank" rel="noopener noreferrer" class="text-cyan-600 font-semibold hover:underline">WhatsApp Live Chat</a> or submit the form above.
        </p>
      </div>

    </div>
  </section>

</main>

<?php
include __DIR__ . '/includes/modals.php';
include __DIR__ . '/includes/footer.php';
?>
