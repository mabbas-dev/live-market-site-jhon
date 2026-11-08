    </main>

    <!-- Admin Footer -->
    <footer class="py-4 px-6 sm:px-8 border-t border-slate-800/80 bg-[#090f20]/50 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span>&copy; <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME) ?> Administration Panel</span>
        <span>•</span>
        <span class="text-emerald-400 font-mono text-[11px] flex items-center gap-1">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Connected to Database: iptv-website
        </span>
      </div>
      <div class="text-[11px] font-mono text-slate-500">
        System Node Status: 184 Edge Servers Active (99.99% Uptime)
      </div>
    </footer>

  </div>

  <script>
    // Initialize Lucide icons
    if (window.lucide) {
      window.lucide.createIcons();
    }

    // Admin Mobile Nav Toggle
    const mobileToggle = document.getElementById('admin-mobile-toggle');
    const adminMenu = document.getElementById('admin-nav-menu');
    if (mobileToggle && adminMenu) {
      mobileToggle.addEventListener('click', () => {
        adminMenu.classList.toggle('hidden');
      });
    }
  </script>
</body>
</html>
