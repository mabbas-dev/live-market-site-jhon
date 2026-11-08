  <!-- PROFESSIONAL FOOTER -->
  <footer class="bg-slate-50 border-t border-slate-200/80 pt-16 pb-12 text-xs text-slate-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-8 pb-12 border-b border-slate-200" data-aos="fade-up" data-aos-duration="700">
        
        <!-- Brand Column -->
        <div class="lg:col-span-2">
          <div class="flex items-center gap-2.5 mb-4">
            <div class="w-8 h-8 rounded-lg bg-cyan-600 flex items-center justify-center text-white shadow-sm shadow-cyan-500/20">
              <i data-lucide="tv" class="w-4 h-4"></i>
            </div>
            <span class="font-heading font-black text-xl text-slate-900">LIVETV</span>
            <span class="font-heading font-bold text-xl text-gradient-cyan">MARKET</span>
          </div>
          <p class="text-slate-600 leading-relaxed text-xs max-w-sm">
            <?= SITE_NAME ?> is a leading worldwide provider of high-speed digital streaming subscriptions, offering <?= TOTAL_CHANNELS ?> live channels and <?= TOTAL_VOD ?> VOD cinema titles powered by Anti-Freeze™ 9.9 CDN clusters.
          </p>
          <div class="mt-4 flex items-center gap-2 text-emerald-600 font-mono text-[11px] font-medium">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>All <?= ACTIVE_CLUSTERS ?> Streaming Nodes Operational (<?= NETWORK_UPTIME ?>)</span>
          </div>
        </div>

        <!-- Links Column 1: Site Navigation -->
        <div>
          <h4 class="font-heading font-bold text-slate-900 text-sm mb-4">Navigation</h4>
          <ul class="space-y-2.5">
            <li><a href="/" class="hover:text-cyan-600 transition-colors">Home</a></li>
            <li><a href="/shop" class="hover:text-cyan-600 transition-colors font-semibold text-slate-700">Shop Packages</a></li>
            <li><a href="/track-order" class="hover:text-cyan-600 transition-colors">Track Order</a></li>
            <li><a href="/blog" class="hover:text-cyan-600 transition-colors">Blogs & News</a></li>
            <li><a href="/contact" class="hover:text-cyan-600 transition-colors">Contact Support</a></li>
            <?php if (TRIAL_IS_ACTIVE): ?>
            <li><button data-open-trial class="hover:text-cyan-600 transition-colors text-left cursor-pointer text-cyan-600 font-semibold"><?= TRIAL_BUTTON_LABEL ?></button></li>
            <?php endif; ?>
          </ul>
        </div>

        <!-- Links Column 2: Channels & Setup -->
        <div>
          <h4 class="font-heading font-bold text-slate-900 text-sm mb-4">Channels & Setup</h4>
          <ul class="space-y-2.5">
            <li><a href="/channels" class="hover:text-cyan-600 transition-colors">All Live Channels (<?= TOTAL_CHANNELS ?>)</a></li>
            <li><a href="/setup#firestick" class="hover:text-cyan-600 transition-colors">Amazon Firestick 4K</a></li>
            <li><a href="/setup#android" class="hover:text-cyan-600 transition-colors">Android TV & Box</a></li>
            <li><a href="/setup#smarttv" class="hover:text-cyan-600 transition-colors">Samsung & LG Smart TV</a></li>
            <li><a href="/setup#apple" class="hover:text-cyan-600 transition-colors">Apple TV & iOS</a></li>
            <li><a href="/setup#mag" class="hover:text-cyan-600 transition-colors">MAG & Formuler Boxes</a></li>
          </ul>
        </div>

        <!-- Links Column 3: Legal & Policies -->
        <div>
          <h4 class="font-heading font-bold text-slate-900 text-sm mb-4">Legal & Policies</h4>
          <ul class="space-y-2.5">
            <li><a href="/terms" class="hover:text-cyan-600 transition-colors">Terms of Service</a></li>
            <li><a href="/privacy" class="hover:text-cyan-600 transition-colors">Privacy Policy</a></li>
            <li><a href="/refund-policy" class="hover:text-cyan-600 transition-colors">Refund Policy</a></li>
            <li><a href="/dmca" class="hover:text-cyan-600 transition-colors">DMCA Compliance</a></li>
            <li><a href="/copyright" class="hover:text-cyan-600 transition-colors">Copyright Notice</a></li>
          </ul>
        </div>

        <!-- Links Column 4: Trust & Security -->
        <div>
          <h4 class="font-heading font-bold text-slate-900 text-sm mb-4">Security & Trust</h4>
          <ul class="space-y-2.5">
            <li class="flex items-center gap-2"><i data-lucide="shield" class="w-3.5 h-3.5 text-cyan-600"></i> 256-Bit SSL Encryption</li>
            <li class="flex items-center gap-2"><i data-lucide="check" class="w-3.5 h-3.5 text-cyan-600"></i> 7-Day Money Back SLA</li>
            <li class="flex items-center gap-2"><i data-lucide="zap" class="w-3.5 h-3.5 text-cyan-600"></i> Instant 60s Activation</li>
            <li class="flex items-center gap-2"><i data-lucide="message-square" class="w-3.5 h-3.5 text-cyan-600"></i> 24/7 VIP WhatsApp Desk</li>
          </ul>
        </div>

      </div>

      <!-- Legal disclaimer & Copyright -->
      <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
        <div class="flex flex-wrap items-center gap-2">
          <span>&copy; <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME) ?>. All rights reserved.</span>
          <span>•</span>
          <a href="/terms" class="hover:text-cyan-600 transition-colors">Terms</a>
          <span>•</span>
          <a href="/privacy" class="hover:text-cyan-600 transition-colors">Privacy</a>
          <span>•</span>
          <a href="/refund-policy" class="hover:text-cyan-600 transition-colors">Refunds</a>
          <span>•</span>
          <a href="/dmca" class="hover:text-cyan-600 transition-colors">DMCA</a>
          <span>•</span>
          <a href="/copyright" class="hover:text-cyan-600 transition-colors">Copyright</a>
        </div>
        <p class="max-w-xl text-center md:text-right">
          Disclaimer: All trademarks, logos, and brand names are property of their respective owners. <?= htmlspecialchars(SITE_NAME) ?> operates as an authenticated streaming subscription gateway.
        </p>
      </div>

    </div>
  </footer>

  <!-- Core Scripts -->
  <script src="assets/js/main.js?v=<?= file_exists(__DIR__ . '/../assets/js/main.js') ? filemtime(__DIR__ . '/../assets/js/main.js') : time() ?>"></script>
</body>
</html>
