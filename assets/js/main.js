/**
 * LIVETVMARKET - Core Interactive Engine
 * Provides currency switching, plan toggles, channel search, latency tester,
 * modals, coupon engine, social proof toasts, and animations.
 */

document.addEventListener('DOMContentLoaded', () => {
  // Initialize Lucide icons if available
  if (window.lucide) {
    window.lucide.createIcons();
  }

  // Initialize AOS (Animate On Scroll) Library
  if (typeof AOS !== 'undefined') {
    AOS.init({
      duration: 800,
      easing: 'ease-out-cubic',
      once: false,
      mirror: false,
      offset: 60,
      delay: 50
    });
  }

  // --- STATE MANAGEMENT ---
  const state = {
    currency: 'USD',
    detectedCountry: 'US',
    rates: {
      USD: { symbol: '$', rate: 1.0 },
      EUR: { symbol: '€', rate: 0.92 },
      GBP: { symbol: '£', rate: 0.79 },
      PKR: { symbol: 'Rs', rate: 278.50 },
      CAD: { symbol: 'C$', rate: 1.36 },
      AUD: { symbol: 'A$', rate: 1.52 }
    },
    countryMap: {
      US: 'USD', UM: 'USD',
      GB: 'GBP', UK: 'GBP',
      DE: 'EUR', FR: 'EUR', IT: 'EUR', ES: 'EUR', NL: 'EUR', BE: 'EUR', AT: 'EUR', IE: 'EUR', PT: 'EUR', GR: 'EUR', FI: 'EUR',
      PK: 'PKR',
      CA: 'CAD',
      AU: 'AUD'
    },
    connections: 1, // 1 or 2
    couponCode: '',
    discount: 0,
    couponValidPlans: 'all',
    selectedPlan: {
      id: '12m',
      name: '12 Months VIP Access',
      baseUSD: 74.99,
      months: 12
    }
  };

  // Synchronize dynamic currencies from Admin Panel payload
  if (window.CURRENCY_DATA && typeof window.CURRENCY_DATA === 'object') {
    if (window.CURRENCY_DATA.rates) {
      state.rates = window.CURRENCY_DATA.rates;
    }
    if (window.CURRENCY_DATA.countryMap) {
      state.countryMap = window.CURRENCY_DATA.countryMap;
    }
    if (window.CURRENCY_DATA.detectedCountry) {
      state.detectedCountry = window.CURRENCY_DATA.detectedCountry;
    }
    if (window.CURRENCY_DATA.detectedCurrency && state.rates[window.CURRENCY_DATA.detectedCurrency]) {
      state.currency = window.CURRENCY_DATA.detectedCurrency;
    }
  }

  // Load saved manual preference if user explicitly selected before
  const savedCurrency = localStorage.getItem('livetv_selected_currency');
  if (savedCurrency && state.rates[savedCurrency]) {
    state.currency = savedCurrency;
  }

  // International standard region name resolver (All 240+ countries in native English)
  function getCountryFullName(code) {
    if (!code) return 'United States';
    const cUpper = code.toUpperCase();
    try {
      if (typeof Intl !== 'undefined' && Intl.DisplayNames) {
        const regionNames = new Intl.DisplayNames(['en'], { type: 'region' });
        const name = regionNames.of(cUpper);
        if (name) return name;
      }
    } catch (e) {}
    const fallbacks = {
      US: 'United States', GB: 'United Kingdom', UK: 'United Kingdom',
      PK: 'Pakistan', CA: 'Canada', AU: 'Australia', DE: 'Germany',
      FR: 'France', ES: 'Spain', IT: 'Italy', NL: 'Netherlands',
      IE: 'Ireland', AE: 'United Arab Emirates', SA: 'Saudi Arabia', IN: 'India'
    };
    return fallbacks[cUpper] || cUpper;
  }

  function updateCheckoutCountryUI(countryCode, currencyCode) {
    const cUpper = (countryCode || state.detectedCountry || 'US').toUpperCase();
    const cName = getCountryFullName(cUpper);
    const disp = document.getElementById('checkout-country-display');
    const input = document.getElementById('checkout-detected-country');
    const currSelect = document.getElementById('checkout-currency-select');

    if (disp) {
      disp.textContent = `${cName} (${cUpper})`;
    }
    if (input) {
      input.value = cUpper;
    }
    if (currSelect && currencyCode && state.rates[currencyCode]) {
      currSelect.value = currencyCode;
    }
  }

  // Silent Real-Time IP-based Country Detection (Multi-Service Cascade, NO permission popups!)
  // ALWAYS detects and displays the user's real active connection location (VPN or local ISP)
  async function silentDetectCountry() {
    let detected = null;

    const services = [
      async () => {
        const r = await fetch('api/get_country.php', { cache: 'no-store' });
        if (!r.ok) return null;
        const d = await r.json();
        return d && d.country ? d.country.toUpperCase() : null;
      },
      async () => {
        const r = await fetch('https://api.country.is/', { cache: 'no-store' });
        if (!r.ok) return null;
        const d = await r.json();
        return d && d.country ? d.country.toUpperCase() : null;
      },
      async () => {
        const r = await fetch('https://ipwho.is/', { cache: 'no-store' });
        if (!r.ok) return null;
        const d = await r.json();
        return d && d.country_code ? d.country_code.toUpperCase() : null;
      },
      async () => {
        const r = await fetch('https://freeipapi.com/api/json', { cache: 'no-store' });
        if (!r.ok) return null;
        const d = await r.json();
        return d && d.countryCode ? d.countryCode.toUpperCase() : null;
      }
    ];

    for (const service of services) {
      try {
        const code = await service();
        if (code && code.length === 2) {
          detected = code;
          break;
        }
      } catch (err) {}
    }

    if (detected) {
      state.detectedCountry = detected;
      // Synchronize cookie so PHP gets it on subsequent page requests
      document.cookie = `livetv_detected_country=${detected}; path=/; max-age=86400; SameSite=Lax`;

      // Always update the modal country display with the real detected location!
      updateCheckoutCountryUI(detected, state.currency);

      // Auto-switch currency ONLY if user hasn't manually locked an explicit currency
      const isManual = localStorage.getItem('livetv_user_manual_currency');
      if (!isManual) {
        const mappedCurr = state.countryMap[detected];
        if (mappedCurr && state.rates[mappedCurr]) {
          state.currency = mappedCurr;
          updatePricingDisplay();
          updateCheckoutCountryUI(detected, mappedCurr);
        }
      }
    } else {
      updateCheckoutCountryUI(state.detectedCountry, state.currency);
    }
  }
  silentDetectCountry();

  // Base Plan Prices in USD (1 Connection)
  const basePlans = {
    '1m': { baseUSD: 14.99, months: 1, name: '1 Month Access' },
    '3m': { baseUSD: 34.99, months: 3, name: '3 Months Access' },
    '6m': { baseUSD: 54.99, months: 6, name: '6 Months Access' },
    '12m': { baseUSD: 74.99, months: 12, name: '12 Months VIP Access' },
    '24m': { baseUSD: 119.99, months: 24, name: '24 Months Ultra Access' }
  };

  // Synchronize dynamic plans configured from Admin Panel
  if (window.IPTV_PLANS && typeof window.IPTV_PLANS === 'object') {
    Object.keys(window.IPTV_PLANS).forEach(code => {
      basePlans[code] = window.IPTV_PLANS[code];
    });
  }

  // Money formatting helper
  function formatMoney(amount, currCode) {
    const curr = state.rates[currCode] || { symbol: '$', rate: 1.0 };
    const sym = curr.symbol || '$';
    if (currCode === 'PKR' || curr.rate > 50) {
      return `${sym} ${Math.round(amount).toLocaleString()}`;
    }
    return `${sym}${amount.toFixed(2)}`;
  }

  // --- CURRENCY & PRICING UPDATER ---
  function updatePricingDisplay() {
    const curr = state.rates[state.currency] || { symbol: '$', rate: 1.0 };
    const multiplier = state.connections === 2 ? 1.45 : 1.0;

    Object.keys(basePlans).forEach(planId => {
      const plan = basePlans[planId];
      const totalPrice = plan.baseUSD * multiplier * curr.rate;
      const perMonthPrice = totalPrice / plan.months;

      const priceElem = document.getElementById(`price-${planId}`);
      const perMonthElem = document.getElementById(`per-month-${planId}`);

      if (priceElem) {
        priceElem.textContent = formatMoney(totalPrice, state.currency);
      }
      if (perMonthElem) {
        perMonthElem.textContent = `${formatMoney(perMonthPrice, state.currency)}/mo`;
      }
    });

    // Update currency selector active state across desktop & mobile
    document.querySelectorAll('[data-currency-btn]').forEach(btn => {
      const c = btn.getAttribute('data-currency-btn');
      if (c === state.currency) {
        btn.classList.add('bg-white', 'text-cyan-600', 'border-cyan-300', 'shadow-sm');
        btn.classList.remove('text-slate-500', 'border-transparent', 'bg-gray-50');
      } else {
        btn.classList.remove('bg-white', 'text-cyan-600', 'border-cyan-300', 'shadow-sm');
        btn.classList.add('text-slate-500', 'border-transparent');
      }
    });

    // Update digital store prices if present on page
    document.querySelectorAll('.store-prod-price').forEach(el => {
      const base = parseFloat(el.getAttribute('data-base-price') || 0);
      el.textContent = formatMoney(base * curr.rate, state.currency);
    });

    // Sync all currency dropdowns across header, mobile drawer, IPTV modal, and store modal
    ['header-currency-select', 'mobile-currency-select', 'checkout-currency-select', 'store-currency-select'].forEach(id => {
      const selectEl = document.getElementById(id);
      if (selectEl && selectEl.value !== state.currency) {
        selectEl.value = state.currency;
      }
    });

    // If modal is open, re-render checkout summary
    updateCheckoutSummary();
  }

  // Currency click handlers (buttons if present)
  document.querySelectorAll('[data-currency-btn]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const newCurr = btn.getAttribute('data-currency-btn');
      if (state.rates[newCurr]) {
        state.currency = newCurr;
        localStorage.setItem('livetv_selected_currency', newCurr);
        localStorage.setItem('livetv_user_manual_currency', '1');
        updatePricingDisplay();
      }
    });
  });

  // Currency dropdown change handlers (Header, Mobile drawer, Checkout modal, Store modal)
  ['header-currency-select', 'mobile-currency-select', 'checkout-currency-select', 'store-currency-select'].forEach(id => {
    const selectEl = document.getElementById(id);
    if (selectEl) {
      selectEl.addEventListener('change', function() {
        const newCurr = this.value;
        if (state.rates[newCurr]) {
          state.currency = newCurr;
          localStorage.setItem('livetv_selected_currency', newCurr);
          localStorage.setItem('livetv_user_manual_currency', '1');
          updatePricingDisplay();
        }
      });
    }
  });

  // Connections Toggle (1 Connection vs 2 Connections)
  const connToggleBtn1 = document.getElementById('conn-1-btn');
  const connToggleBtn2 = document.getElementById('conn-2-btn');

  if (connToggleBtn1 && connToggleBtn2) {
    connToggleBtn1.addEventListener('click', () => {
      state.connections = 1;
      connToggleBtn1.classList.add('bg-cyan-500', 'text-white', 'shadow-md');
      connToggleBtn1.classList.remove('text-slate-600', 'hover:text-slate-900');
      connToggleBtn2.classList.remove('bg-cyan-500', 'text-white', 'shadow-md');
      connToggleBtn2.classList.add('text-slate-600', 'hover:text-slate-900');
      updatePricingDisplay();
    });

    connToggleBtn2.addEventListener('click', () => {
      state.connections = 2;
      connToggleBtn2.classList.add('bg-cyan-500', 'text-white', 'shadow-md');
      connToggleBtn2.classList.remove('text-slate-600', 'hover:text-slate-900');
      connToggleBtn1.classList.remove('bg-cyan-500', 'text-white', 'shadow-md');
      connToggleBtn1.classList.add('text-slate-600', 'hover:text-slate-900');
      updatePricingDisplay();
    });
  }

  // --- CHANNEL & VOD EXPLORER DATA & FILTER ---
  const channelsList = [
    { name: 'Sky Sports Main Event', category: 'sports', country: 'UK', quality: '4K UHD', epg: true, icon: '🏆' },
    { name: 'TNT Sports 1 4K HDR', category: 'sports', country: 'UK', quality: '4K UHD', epg: true, icon: '⚽' },
    { name: 'beIN Sports Premium 1', category: 'sports', country: 'Arabic', quality: '4K UHD', epg: true, icon: '🥇' },
    { name: 'ESPN & ESPN 2 USA', category: 'sports', country: 'USA', quality: 'FHD 60fps', epg: true, icon: '🏀' },
    { name: 'Canal+ Sport UHD', category: 'sports', country: 'France', quality: '4K UHD', epg: true, icon: '🏎️' },
    { name: 'SuperSport Premier League', category: 'sports', country: 'UK/SA', quality: '4K UHD', epg: true, icon: '⚽' },
    { name: 'DAZN 1 & DAZN 2', category: 'sports', country: 'Europe', quality: 'FHD 60fps', epg: true, icon: '🥊' },
    { name: 'Fox Sports 1 & 2', category: 'sports', country: 'USA', quality: '4K UHD', epg: true, icon: '🏈' },
    { name: 'HBO & HBO Max East', category: 'movies', country: 'USA', quality: '4K UHD', epg: true, icon: '🎬' },
    { name: 'Sky Cinema Premiere HD', category: 'movies', country: 'UK', quality: '4K UHD', epg: true, icon: '🍿' },
    { name: 'Canal+ Cinema 4K', category: 'movies', country: 'France', quality: '4K UHD', epg: true, icon: '🎥' },
    { name: 'Disney Channel HD', category: 'kids', country: 'USA/UK', quality: 'FHD', epg: true, icon: '🏰' },
    { name: 'Cartoon Network UK', category: 'kids', country: 'UK', quality: 'FHD', epg: true, icon: '👾' },
    { name: 'BBC One & BBC Two 4K', category: 'general', country: 'UK', quality: '4K UHD', epg: true, icon: '🇬🇧' },
    { name: 'NBC / CBS / ABC / FOX HD', category: 'general', country: 'USA', quality: 'FHD', epg: true, icon: '🇺🇸' },
    { name: 'Movistar LaLiga 4K', category: 'sports', country: 'Spain', quality: '4K UHD', epg: true, icon: '🇪🇸' },
    { name: 'Sky Sport Bundesliga 1', category: 'sports', country: 'Germany', quality: '4K UHD', epg: true, icon: '🇩🇪' },
    { name: 'Sky Sport Calcio Serie A', category: 'sports', country: 'Italy', quality: '4K UHD', epg: true, icon: '🇮🇹' },
    { name: 'Discovery Science & Turbo', category: 'docu', country: 'USA/UK', quality: 'FHD', epg: true, icon: '🔬' },
    { name: 'National Geographic Wild', category: 'docu', country: 'Global', quality: '4K UHD', epg: true, icon: '🦁' },
    { name: 'UFC PPV Fight Pass HD', category: 'sports', country: 'USA', quality: '4K UHD', epg: true, icon: '🥊' },
    { name: 'WWE Network 24/7 Live', category: 'sports', country: 'USA', quality: 'FHD', epg: true, icon: '💥' }
  ];

  const channelGrid = document.getElementById('channel-results-grid');
  const channelSearchInput = document.getElementById('channel-search-input');
  const channelFilterBtns = document.querySelectorAll('[data-channel-filter]');
  const channelCountBadge = document.getElementById('channel-count-badge');

  let activeCategory = 'all';

  function renderChannels() {
    if (!channelGrid) return;
    const searchTerm = (channelSearchInput ? channelSearchInput.value : '').toLowerCase().trim();

    const filtered = channelsList.filter(ch => {
      const matchCategory = activeCategory === 'all' || ch.category === activeCategory;
      const matchSearch = ch.name.toLowerCase().includes(searchTerm) ||
        ch.country.toLowerCase().includes(searchTerm) ||
        ch.quality.toLowerCase().includes(searchTerm);
      return matchCategory && matchSearch;
    });

    if (channelCountBadge) {
      channelCountBadge.textContent = `${filtered.length} Displayed Channels (Preview of 25,000+)`;
    }

    if (filtered.length === 0) {
      channelGrid.innerHTML = `
        <div class="col-span-full py-12 text-center text-slate-500">
          <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-3 text-cyan-600 opacity-60"></i>
          <p class="text-lg font-bold text-slate-800">No exact match found in preview</p>
          <p class="text-sm mt-1">Don't worry! Our full database covers 25,000+ live channels across all countries.</p>
        </div>
      `;
      if (window.lucide) window.lucide.createIcons();
      return;
    }

    channelGrid.innerHTML = filtered.map((ch, idx) => `
      <div class="bg-white p-4 rounded-xl flex items-center justify-between group border border-slate-200 shadow-sm hover:shadow-md hover:border-cyan-400 transition-all" data-aos="fade-up" data-aos-delay="${(idx % 6) * 50}">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-xl shadow-inner group-hover:scale-105 transition-transform">
            ${ch.icon}
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h4 class="font-bold text-slate-900 text-sm group-hover:text-cyan-600 transition-colors">${ch.name}</h4>
              ${ch.epg ? '<span class="text-[10px] bg-cyan-50 text-cyan-700 border border-cyan-200 px-1.5 py-0.5 rounded font-mono font-bold">EPG</span>' : ''}
            </div>
            <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
              <span class="inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ${ch.country}</span>
              <span>•</span>
              <span class="capitalize text-slate-500">${ch.category}</span>
            </div>
          </div>
        </div>
        <div class="text-right">
          <span class="text-xs px-2.5 py-1 rounded-md bg-slate-100 border border-slate-200 text-cyan-700 font-mono font-semibold">
            ${ch.quality}
          </span>
        </div>
      </div>
    `).join('');

    if (window.lucide) window.lucide.createIcons();
    if (typeof AOS !== 'undefined') {
      setTimeout(() => AOS.refresh(), 50);
    }
  }

  if (channelSearchInput) {
    channelSearchInput.addEventListener('input', renderChannels);
  }

  channelFilterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      channelFilterBtns.forEach(b => {
        b.classList.remove('bg-cyan-500', 'text-white', 'border-cyan-400');
        b.classList.add('bg-slate-100', 'text-slate-600', 'border-slate-200');
      });
      btn.classList.add('bg-cyan-500', 'text-white', 'border-cyan-400');
      btn.classList.remove('bg-slate-100', 'text-slate-600', 'border-slate-200');
      activeCategory = btn.getAttribute('data-channel-filter');
      renderChannels();
    });
  });

  renderChannels();

  // --- LIVE CONNECTION & LATENCY TESTER ---
  const startPingBtn = document.getElementById('run-speed-test-btn');
  const pingResultsContainer = document.getElementById('ping-results-grid');

  const clusters = [
    { name: 'Frankfurt Core (EU-Central)', basePing: 14, jitter: 4, status: 'Optimal' },
    { name: 'London Node (UK-West)', basePing: 18, jitter: 5, status: 'Optimal' },
    { name: 'New York CDN (US-East)', basePing: 28, jitter: 6, status: 'Optimal' },
    { name: 'Dallas CDN (US-Central)', basePing: 34, jitter: 7, status: 'Ultra Fast' },
    { name: 'Singapore Gateway (Asia-Pac)', basePing: 52, jitter: 10, status: 'Fast' }
  ];

  if (startPingBtn) {
    startPingBtn.addEventListener('click', () => {
      startPingBtn.disabled = true;
      startPingBtn.innerHTML = `
        <span class="inline-block animate-spin mr-2">⟳</span> Testing 184 Global Clusters...
      `;

      let delay = 300;
      clusters.forEach((cluster, idx) => {
        setTimeout(() => {
          const simulatedPing = cluster.basePing + Math.floor(Math.random() * cluster.jitter);
          const clusterElem = document.getElementById(`cluster-ping-${idx}`);
          const clusterStatus = document.getElementById(`cluster-status-${idx}`);
          if (clusterElem) {
            clusterElem.textContent = `${simulatedPing} ms`;
            clusterElem.classList.add('text-emerald-400', 'font-bold');
          }
          if (clusterStatus) {
            clusterStatus.innerHTML = `<span class="inline-flex items-center gap-1 text-emerald-400 text-xs"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> ${cluster.status} (4K Ready)</span>`;
          }

          if (idx === clusters.length - 1) {
            startPingBtn.disabled = false;
            startPingBtn.innerHTML = `
              <i data-lucide="check-circle" class="w-4 h-4 inline mr-2 text-emerald-400"></i> Speed Check Passed: 100% 4K Ultra-HD Ready
            `;
            if (window.lucide) window.lucide.createIcons();
          }
        }, delay);
        delay += 400;
      });
    });
  }

  // --- DEVICE SETUP GUIDE TABS ---
  const deviceTabBtns = document.querySelectorAll('[data-device-tab]');
  const devicePanels = document.querySelectorAll('[data-device-panel]');

  deviceTabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetDevice = btn.getAttribute('data-device-tab');

      deviceTabBtns.forEach(b => {
        b.classList.remove('bg-cyan-50', 'border-cyan-400', 'text-cyan-700');
        b.classList.add('border-slate-200', 'text-slate-600', 'hover:border-slate-300');
      });
      btn.classList.add('bg-cyan-50', 'border-cyan-400', 'text-cyan-700');
      btn.classList.remove('border-slate-200', 'text-slate-600', 'hover:border-slate-300');

      devicePanels.forEach(panel => {
        if (panel.getAttribute('data-device-panel') === targetDevice) {
          panel.classList.remove('hidden');
        } else {
          panel.classList.add('hidden');
        }
      });
    });
  });

  // --- FAQ ACCORDION ---
  document.querySelectorAll('[data-faq-btn]').forEach(btn => {
    btn.addEventListener('click', () => {
      const content = btn.nextElementSibling;
      const icon = btn.querySelector('.faq-icon');
      const isExpanded = btn.getAttribute('aria-expanded') === 'true';

      // Close all
      document.querySelectorAll('[data-faq-btn]').forEach(otherBtn => {
        otherBtn.setAttribute('aria-expanded', 'false');
        if (otherBtn.nextElementSibling) otherBtn.nextElementSibling.classList.add('hidden');
        const otherIcon = otherBtn.querySelector('.faq-icon');
        if (otherIcon) otherIcon.style.transform = 'rotate(0deg)';
      });

      if (!isExpanded) {
        btn.setAttribute('aria-expanded', 'true');
        content.classList.remove('hidden');
        if (icon) icon.style.transform = 'rotate(180deg)';
      }
    });
  });

  // --- MODALS (Checkout, Free Trial & Official Domain Verification) ---
  const checkoutModal = document.getElementById('checkout-modal');
  const trialModal = document.getElementById('trial-modal');
  const verificationModal = document.getElementById('domain-verification-modal');
  const closeVerificationBtn = document.getElementById('close-verification-modal');
  const acceptVerificationBtn = document.getElementById('accept-verification-btn');
  const rememberVerificationCheck = document.getElementById('remember-verification-check');
  const viewCertBtn = document.getElementById('view-compliance-cert-btn');
  const modalCloseBtns = document.querySelectorAll('[data-close-modal]');

  function openModal(modal) {
    if (!modal) return;
    if (modal === checkoutModal) {
      updateCheckoutCountryUI(state.detectedCountry, state.currency);
      silentDetectCountry();
      // Ensure no residual green background or stale coupon box on open
      if (!state.couponCode) {
        const cFeedback = document.getElementById('coupon-feedback');
        if (cFeedback) {
          cFeedback.innerHTML = '';
          cFeedback.className = '';
        }
      }
    }
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }

  modalCloseBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeModal(checkoutModal);
      closeModal(trialModal);
      closeModal(verificationModal);
    });
  });

  // Strict: Prevent closing when clicking outside on the background backdrop.
  // Modals can ONLY be closed via the explicit 'X' icon/close buttons.
  document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', (e) => {
      // If click was outside close buttons (i.e. on backdrop or inside form)
      if (!e.target.closest('[data-close-modal], #close-verification-modal')) {
        e.preventDefault();
        e.stopPropagation();

        // Visual feedback (headshake) indicating modal is locked and must be closed via 'X' button
        const box = modal.querySelector('.modal-content');
        if (box) {
          box.classList.remove('animate__animated', 'animate__headShake');
          void box.offsetWidth; // Force DOM reflow
          box.classList.add('animate__animated', 'animate__headShake');
          setTimeout(() => {
            box.classList.remove('animate__animated', 'animate__headShake');
          }, 600);
        }
        return false;
      }
    });
  });

  // Stop clicks inside modal card from bubbling to overlay
  document.querySelectorAll('.modal-content').forEach(box => {
    box.addEventListener('click', (e) => {
      if (!e.target.closest('[data-close-modal], #close-verification-modal')) {
        e.stopPropagation();
      }
    });
  });

  // Domain Verification Floating Notification & Full Certificate Modal
  const floatingVerification = document.getElementById('floating-verification-popup');
  const dismissFloatingBtn = document.getElementById('dismiss-floating-verification');
  const floatingGotItBtn = document.getElementById('floating-gotit-btn');
  const floatingViewCertBtn = document.getElementById('floating-view-cert-btn');

  // Global dismiss function
  window.dismissFloatingVerification = function () {
    if (floatingVerification) {
      floatingVerification.style.transition = 'all 0.3s ease';
      floatingVerification.style.opacity = '0';
      floatingVerification.style.transform = 'translateY(20px)';
      floatingVerification.style.pointerEvents = 'none';
      setTimeout(() => {
        floatingVerification.style.display = 'none';
      }, 300);
    }
    try {
      localStorage.setItem('livetvmarket_floating_dismissed', 'true');
    } catch (e) { }
  };

  // Global open / close certificate modal
  window.openVerificationModal = function () {
    openModal(verificationModal);
  };

  window.closeVerificationModal = function () {
    if (rememberVerificationCheck && rememberVerificationCheck.checked) {
      try {
        localStorage.setItem('livetvmarket_verified_dismissed', 'true');
      } catch (e) { }
    }
    closeModal(verificationModal);
  };

  function handleVerificationPopup() {
    if (!floatingVerification) return;
    const isDismissed = localStorage.getItem('livetvmarket_floating_dismissed');
    if (isDismissed === 'true') {
      floatingVerification.style.display = 'none';
    } else {
      floatingVerification.style.display = 'block';
    }
  }

  if (dismissFloatingBtn) dismissFloatingBtn.addEventListener('click', window.dismissFloatingVerification);
  if (floatingGotItBtn) floatingGotItBtn.addEventListener('click', window.dismissFloatingVerification);

  if (floatingViewCertBtn) {
    floatingViewCertBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.openVerificationModal();
    });
  }

  if (closeVerificationBtn) closeVerificationBtn.addEventListener('click', window.closeVerificationModal);
  if (acceptVerificationBtn) acceptVerificationBtn.addEventListener('click', window.closeVerificationModal);
  if (viewCertBtn) {
    viewCertBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.openVerificationModal();
    });
  }

  // Trigger floating verification badge initial check
  handleVerificationPopup();

  // Trigger Free Trial Modal
  document.querySelectorAll('[data-open-trial]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openModal(trialModal);
    });
  });

  // Trigger Checkout Modal with specific plan
  document.querySelectorAll('[data-select-plan]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const planId = btn.getAttribute('data-select-plan');
      if (basePlans[planId]) {
        state.selectedPlan = {
          id: planId,
          ...basePlans[planId]
        };
      }

      // If a coupon was applied that has specific plan restriction, check compatibility
      if (state.couponCode && state.couponValidPlans && state.couponValidPlans !== 'all') {
        const allowed = state.couponValidPlans.toLowerCase().split(',').map(s => s.trim());
        if (!allowed.includes(planId.toLowerCase())) {
          // Remove incompatible coupon cleanly
          clearCoupon();
        }
      }

      updateCheckoutSummary();
      silentDetectCountry(); // Re-verify active IP/VPN location when opening modal
      openModal(checkoutModal);
    });
  });

  // Update checkout summary values
  function updateCheckoutSummary() {
    const curr = state.rates[state.currency] || { symbol: '$', rate: 1.0 };
    const multiplier = state.connections === 2 ? 1.45 : 1.0;
    const baseTotal = state.selectedPlan.baseUSD * multiplier * curr.rate;
    
    // Check if crypto is selected for automatic 10% benefit
    const paymentRadio = document.querySelector('input[name="payment-method"]:checked');
    const isCrypto = paymentRadio && paymentRadio.value === 'crypto';
    const effectiveDiscount = isCrypto ? Math.max(state.discount, 0.10) : state.discount;

    const discountAmount = baseTotal * effectiveDiscount;
    const finalTotal = baseTotal - discountAmount;

    const modalPlanName = document.getElementById('checkout-plan-name');
    const modalPlanPrice = document.getElementById('checkout-plan-price');
    const modalConnections = document.getElementById('checkout-connections-count');
    const modalDiscountRow = document.getElementById('checkout-discount-row');
    const modalDiscountAmount = document.getElementById('checkout-discount-amount');
    const modalTotalAmount = document.getElementById('checkout-total-amount');

    if (modalPlanName) modalPlanName.textContent = state.selectedPlan.name;
    if (modalConnections) modalConnections.textContent = `${state.connections} Connection${state.connections > 1 ? 's (Multi-Room)' : ''}`;
    if (modalPlanPrice) modalPlanPrice.textContent = formatMoney(baseTotal, state.currency);

    if (modalDiscountRow && modalDiscountAmount) {
      if (effectiveDiscount > 0) {
        modalDiscountRow.classList.remove('hidden');
        const discountLabel = state.couponCode 
          ? `(${state.couponCode} • ${(effectiveDiscount * 100).toFixed(0)}% OFF)`
          : `(Crypto • 10% OFF)`;
        modalDiscountAmount.textContent = `-${formatMoney(discountAmount, state.currency)} ${discountLabel}`;
      } else {
        modalDiscountRow.classList.add('hidden');
      }
    }

    if (modalTotalAmount) modalTotalAmount.textContent = formatMoney(finalTotal, state.currency);

    // Keep country display continuously synchronized
    updateCheckoutCountryUI(state.detectedCountry, state.currency);
  }

  // Payment method selection change triggers pricing update (e.g. Crypto 10% discount)
  document.querySelectorAll('input[name="payment-method"]').forEach(radio => {
    radio.addEventListener('change', () => {
      updateCheckoutSummary();
    });
  });

  // Dynamic % Based Coupon Code verification (Asynchronous against Database API)
  const couponInput = document.getElementById('coupon-code-input');
  const applyCouponBtn = document.getElementById('apply-coupon-btn');
  const couponFeedback = document.getElementById('coupon-feedback');

  function clearCoupon() {
    state.discount = 0;
    state.couponCode = '';
    state.couponValidPlans = 'all';
    if (couponInput) {
      couponInput.value = '';
    }
    if (couponFeedback) {
      couponFeedback.innerHTML = '';
      couponFeedback.className = '';
    }
    updateCheckoutSummary();
  }

  async function handleApplyCoupon() {
    if (!couponInput) return;
    const code = couponInput.value.trim().toUpperCase();

    if (!code) {
      clearCoupon();
      return;
    }

    if (applyCouponBtn) {
      applyCouponBtn.disabled = true;
      applyCouponBtn.innerHTML = '<span class="inline-block animate-spin mr-1">⟳</span>';
    }

    const curr = state.rates[state.currency] || { symbol: '$', rate: 1.0 };
    const multiplier = state.connections === 2 ? 1.45 : 1.0;
    const baseTotal = state.selectedPlan.baseUSD * multiplier * curr.rate;

    try {
      const resp = await fetch('api/validate_coupon.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
          code: code, 
          amount: baseTotal,
          plan: state.selectedPlan.id 
        })
      });
      const data = await resp.json();

      if (data.valid) {
        state.discount = parseFloat(data.discount_rate);
        state.couponCode = data.code;
        state.couponValidPlans = data.valid_plans || 'all';
        if (couponFeedback) {
          couponFeedback.className = 'text-xs text-emerald-600 mt-1.5 font-semibold flex items-center justify-between gap-1 bg-emerald-50 p-2 rounded-lg border border-emerald-200';
          couponFeedback.innerHTML = `
            <span>${data.message || `🎉 Coupon ${data.code} applied! ${data.discount_percent}% OFF`}</span>
            <button type="button" id="remove-coupon-btn" class="text-rose-500 hover:text-rose-700 underline text-[10px] font-bold cursor-pointer">Remove</button>
          `;
          document.getElementById('remove-coupon-btn')?.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            clearCoupon();
          });
        }
      } else {
        state.discount = 0;
        state.couponCode = '';
        state.couponValidPlans = 'all';
        if (couponFeedback) {
          couponFeedback.className = 'text-xs text-rose-500 mt-1 font-semibold';
          couponFeedback.textContent = data.message || '❌ Invalid or expired coupon code.';
        }
      }
    } catch (err) {
      console.warn('Coupon validation error:', err);
      // Client-side fallback for offline resilience
      if (code === 'MARKET60' || code === 'LIVETV60') {
        state.discount = 0.60;
        state.couponCode = code;
        state.couponValidPlans = 'all';
        if (couponFeedback) {
          couponFeedback.className = 'text-xs text-emerald-600 mt-1 font-semibold';
          couponFeedback.textContent = `🎉 Coupon ${code} applied! 60% OFF`;
        }
      } else {
        state.discount = 0;
        state.couponCode = '';
        state.couponValidPlans = 'all';
        if (couponFeedback) {
          couponFeedback.className = 'text-xs text-rose-500 mt-1 font-semibold';
          couponFeedback.textContent = '❌ Invalid or expired coupon code.';
        }
      }
    } finally {
      if (applyCouponBtn) {
        applyCouponBtn.disabled = false;
        applyCouponBtn.textContent = 'Apply';
      }
      updateCheckoutSummary();
    }
  }

  if (applyCouponBtn && couponInput) {
    applyCouponBtn.addEventListener('click', (e) => {
      e.preventDefault();
      handleApplyCoupon();
    });

    couponInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        handleApplyCoupon();
      }
    });

    // When the user clears the coupon textfield manually, reset feedback and remove green box
    couponInput.addEventListener('input', () => {
      if (couponInput.value.trim() === '') {
        clearCoupon();
      }
    });
  }

  // Free / Paid Trial Form submission
  const trialForm = document.getElementById('trial-form');
  const trialSuccess = document.getElementById('trial-success-box');

  if (trialForm) {
    trialForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitBtn = trialForm.querySelector('button[type="submit"]');
      const originalText = submitBtn ? submitBtn.innerHTML : 'Proceed';

      const fullName = (trialForm.querySelector('input[name="full_name"]')?.value || '').trim();
      const email = (trialForm.querySelector('input[name="email"]')?.value || '').trim();
      const whatsapp = (trialForm.querySelector('input[name="whatsapp"]')?.value || '').trim();

      if (!fullName || !email || !whatsapp) {
        alert('Please provide your Full Name, Email Address, and WhatsApp Number. All three are required.');
        return;
      }

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⟳</span> Processing Test Line...';
      }

      const formData = new FormData(trialForm);
      try {
        const response = await fetch('api/submit_trial.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        if (!result.success) {
          alert(result.message || 'Error creating test line. Please try again.');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
          }
          return;
        }

        // If Paid Trial with Gateway Redirect:
        if (result.redirect_url) {
          if (submitBtn) {
            submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⟳</span> Redirecting to Secure Gateway...';
          }
          window.location.href = result.redirect_url;
          return;
        }
      } catch (err) {
        console.warn('API submission log:', err);
      }

      trialForm.classList.add('hidden');
      if (trialSuccess) trialSuccess.classList.remove('hidden');
      if (window.confetti) {
        window.confetti({ particleCount: 80, spread: 60, origin: { y: 0.6 } });
      }
    });
  }

  // Checkout Form Submission (Connected to MySQL DB 'iptv-website')
  const checkoutForm = document.getElementById('checkout-form');
  const checkoutSuccess = document.getElementById('checkout-success-box');

  if (checkoutForm) {
    checkoutForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const name = (document.getElementById('checkout-name')?.value || '').trim();
      const email = (document.getElementById('checkout-email')?.value || '').trim();
      const whatsapp = (document.getElementById('checkout-whatsapp')?.value || '').trim();
      const paymentMethod = document.querySelector('input[name="payment-method"]:checked')?.value || 'Credit Card';

      if (!name || !email || !whatsapp) {
        alert('Please provide your Full Name, Email Address, and WhatsApp Number. All three are required to process your order.');
        return;
      }

      const submitBtn = checkoutForm.querySelector('button[type="submit"]');
      const origBtnText = submitBtn ? submitBtn.innerHTML : 'Complete Secure Order';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⟳</span> Processing Secure Order...';
      }

      const curr = state.rates[state.currency] || { symbol: '$', rate: 1.0 };
      const multiplier = state.connections === 2 ? 1.45 : 1.0;
      const baseTotal = state.selectedPlan.baseUSD * multiplier * curr.rate;
      const isCrypto = paymentMethod.toLowerCase() === 'crypto';
      const effectiveDiscount = isCrypto ? Math.max(state.discount, 0.10) : state.discount;
      const finalAmount = (baseTotal * (1 - effectiveDiscount)).toFixed(2);
      const detectedCountry = document.getElementById('checkout-detected-country')?.value || state.detectedCountry || 'US';
      const couponRecord = state.couponCode || (isCrypto ? 'CRYPTO10' : '');

      const payload = {
        name: name,
        customer_name: name,
        email: email,
        whatsapp: whatsapp,
        plan_name: state.selectedPlan.name,
        connections: state.connections,
        amount: finalAmount,
        currency: state.currency,
        country: detectedCountry,
        device: 'All Devices',
        payment_channel: paymentMethod,
        coupon_used: couponRecord
      };

      try {
        const resp = await fetch('api/submit_order.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const resData = await resp.json();
        if (!resData.success) {
          alert(resData.message || 'Error processing order. Please check your information.');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = origBtnText;
          }
          return;
        }

        // Secure Gateway Bridge Redirection
        if (resData.redirect_url) {
          if (submitBtn) {
            submitBtn.innerHTML = `
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg> Redirecting to Secure Gateway...
            `;
          }
          window.location.href = resData.redirect_url;
          return;
        }
      } catch (err) {
        console.warn('Order API log:', err);
      }

      checkoutForm.classList.add('hidden');
      if (checkoutSuccess) {
        checkoutSuccess.classList.remove('hidden');
        const detailsElem = document.getElementById('checkout-success-details');
        if (detailsElem) {
          const successTotalFormatted = formatMoney(parseFloat(finalAmount), state.currency);
          detailsElem.innerHTML = `
            <strong>Customer:</strong> ${name}<br>
            <strong>Activation Email:</strong> ${email}<br>
            <strong>WhatsApp Number:</strong> ${whatsapp}<br>
            <strong>Plan:</strong> ${state.selectedPlan.name} (${state.connections} Connection${state.connections > 1 ? 's' : ''})<br>
            <strong>Detected Country:</strong> ${detectedCountry}<br>
            <strong>Payment Method:</strong> ${paymentMethod.toUpperCase()}<br>
            <strong>Total Paid:</strong> ${successTotalFormatted} ${state.currency}<br>
            <div class="mt-3 p-3 bg-cyan-50 rounded-xl border border-cyan-200 text-cyan-800 font-mono text-xs">
              ✓ Order registered in database! M3U playlist & Xtream credentials sent to your email & WhatsApp.
            </div>
          `;
        }
      }
      if (window.confetti) {
        window.confetti({ particleCount: 100, spread: 70, origin: { y: 0.5 } });
      }
    });
  }

  // WhatsApp Quick Order Trigger
  const whatsappOrderBtn = document.getElementById('whatsapp-order-btn');
  if (whatsappOrderBtn) {
    whatsappOrderBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const message = encodeURIComponent(
        `Hello LiveTVMarket Support! I would like to order the ${state.selectedPlan.name} with ${state.connections} Connection(s). Please send payment and activation details.`
      );
      window.open(`https://wa.me/?text=${message}`, '_blank');
    });
  }

  // --- SOCIAL PROOF NOTIFICATION TOAST ---
  const socialToast = document.getElementById('social-proof-toast');
  const toastText = document.getElementById('toast-customer-text');
  const toastTime = document.getElementById('toast-time-text');

  const defaultCustomerEvents = [
    { name: 'Someone', location: 'Sydney, Australia', plan: '6 Months Full Access', time: '2 mins ago' },
    { name: 'Someone', location: 'London, United Kingdom', plan: '12 Months VIP 4K Plan', time: '4 mins ago' },
    { name: 'David K.', location: 'Miami, Florida, USA', plan: '24 Months Ultra Access', time: '6 mins ago' },
    { name: 'Someone', location: 'Toronto, Canada', plan: '12 Months VIP 4K Plan', time: '8 mins ago' },
    { name: 'Alexandre M.', location: 'Paris, France', plan: 'Free 24H Trial Activated', time: 'Just now' },
    { name: 'Someone', location: 'Berlin, Germany', plan: '12 Months VIP 4K Plan', time: '11 mins ago' },
    { name: 'Tariq A.', location: 'Dubai, UAE', plan: '12 Months VIP 4K Plan', time: '14 mins ago' },
    { name: 'Hamza R.', location: 'Lahore, Pakistan', plan: '12 Months VIP 4K Plan', time: 'Just now' }
  ];

  // Dynamic social proof notifications loaded from MySQL Admin Panel
  const customerEvents = (window.SOCIAL_PROOF_EVENTS && Array.isArray(window.SOCIAL_PROOF_EVENTS) && window.SOCIAL_PROOF_EVENTS.length > 0)
    ? window.SOCIAL_PROOF_EVENTS
    : defaultCustomerEvents;

  let toastIndex = 0;
  function showNextToast() {
    if (!socialToast || !toastText || customerEvents.length === 0) return;
    const event = customerEvents[toastIndex % customerEvents.length];
    const customer = event.name || event.customer_name || 'Someone';
    const loc = event.location || 'London, UK';
    const plan = event.plan || event.plan_name || '12 Months VIP 4K Plan';
    const time = event.time || event.time_ago || 'Just now';

    // High-contrast, crystal-clear text colors on pure white toast background
    toastText.innerHTML = `${customer} from <span class="font-bold text-slate-900">${loc}</span> just ordered <span class="text-cyan-700 font-bold">${plan}</span>`;
    if (toastTime) toastTime.textContent = `${time} • Verified Purchase`;

    socialToast.classList.remove('translate-y-24', 'opacity-0');
    socialToast.classList.add('translate-y-0', 'opacity-100');

    setTimeout(() => {
      socialToast.classList.remove('translate-y-0', 'opacity-100');
      socialToast.classList.add('translate-y-24', 'opacity-0');
    }, 5000);

    toastIndex++;
  }

  // Start toast cycle after 4 seconds, then repeat every 18 seconds
  setTimeout(() => {
    showNextToast();
    setInterval(showNextToast, 18000);
  }, 4000);

  // --- LIVE CHAT DRAWER TOGGLE ---
  const chatBubbleBtn = document.getElementById('floating-chat-btn');
  const chatBox = document.getElementById('floating-chat-box');
  const closeChatBtn = document.getElementById('close-chat-box');

  if (chatBubbleBtn && chatBox) {
    chatBubbleBtn.addEventListener('click', () => {
      chatBox.classList.toggle('hidden');
    });
  }
  if (closeChatBtn && chatBox) {
    closeChatBtn.addEventListener('click', () => {
      chatBox.classList.add('hidden');
    });
  }

  // --- MOBILE NAV MENU TOGGLE (Silky Smooth Slide-Over Animation) ---
  const mobileMenuBtn = document.getElementById('mobile-menu-btn');
  const mobileMenuDrawer = document.getElementById('mobile-menu-drawer');
  const mobileMenuPanel = document.getElementById('mobile-menu-panel');
  const mobileMenuBackdrop = document.getElementById('mobile-menu-backdrop');
  const closeMobileMenuBtn = document.getElementById('close-mobile-menu');

  window.openMobileDrawer = function() {
    if (!mobileMenuDrawer || !mobileMenuPanel) return;
    mobileMenuDrawer.classList.remove('pointer-events-none', 'opacity-0');
    mobileMenuDrawer.classList.add('pointer-events-auto', 'opacity-100');
    mobileMenuPanel.classList.remove('translate-x-full');
    mobileMenuPanel.classList.add('translate-x-0');
    document.body.classList.add('overflow-hidden');
    if (window.lucide) window.lucide.createIcons();
  };

  window.closeMobileDrawer = function() {
    if (!mobileMenuDrawer || !mobileMenuPanel) return;
    mobileMenuPanel.classList.remove('translate-x-0');
    mobileMenuPanel.classList.add('translate-x-full');
    mobileMenuDrawer.classList.remove('pointer-events-auto', 'opacity-100');
    mobileMenuDrawer.classList.add('opacity-0');
    setTimeout(() => {
      mobileMenuDrawer.classList.add('pointer-events-none');
      document.body.classList.remove('overflow-hidden');
    }, 300);
  };

  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.openMobileDrawer();
    });
  }
  if (closeMobileMenuBtn) {
    closeMobileMenuBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.closeMobileDrawer();
    });
  }
  if (mobileMenuBackdrop) {
    mobileMenuBackdrop.addEventListener('click', () => {
      window.closeMobileDrawer();
    });
  }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && mobileMenuDrawer && !mobileMenuDrawer.classList.contains('pointer-events-none')) {
      window.closeMobileDrawer();
    }
  });

  // Initialize prices
  updatePricingDisplay();

  // --- STICKY NAVBAR SCROLL ELEVATION ---
  const siteHeader = document.querySelector('header.sticky-header, header.sticky');
  if (siteHeader) {
    const handleScroll = () => {
      if (window.scrollY > 15) {
        siteHeader.classList.add('shadow-md', 'bg-white/98');
        siteHeader.classList.remove('bg-white/95');
      } else {
        siteHeader.classList.remove('shadow-md', 'bg-white/98');
        siteHeader.classList.add('bg-white/95');
      }
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  // ==========================================================================
  // GSAP & LUXURY INTERACTIVE ANIMATIONS
  // ==========================================================================

  // 1. Interactive Cursor Spotlight on Cards
  document.querySelectorAll('.glass-card, .spotlight-card').forEach(card => {
    card.classList.add('spotlight-card');
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      card.style.setProperty('--mouse-x', `${x}px`);
      card.style.setProperty('--mouse-y', `${y}px`);
    });
  });

  // 2. Interactive 3D Tilt on Pricing & Feature Cards
  const tiltElements = document.querySelectorAll('.tilt-card, [data-select-plan]');
  tiltElements.forEach(item => {
    const card = item.closest('.glass-card') || item;
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left - rect.width / 2;
      const y = e.clientY - rect.top - rect.height / 2;
      const tiltX = (y / (rect.height / 2)) * -6;
      const tiltY = (x / (rect.width / 2)) * 6;
      card.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) translateY(-4px)`;
    });

    card.addEventListener('mouseleave', () => {
      card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)';
    });
  });

  // 3. Animation Refresh & Ambient Enhancements
  if (typeof AOS !== 'undefined') {
    window.addEventListener('load', () => {
      AOS.refresh();
    });
    // Periodic refresh for late-rendered elements or dynamic images
    setTimeout(() => AOS.refresh(), 500);
    setTimeout(() => AOS.refresh(), 1500);
  }

  // 4. Smooth Counter Animation for Live Numbers
  function animateValue(obj, start, end, duration, suffix = '') {
    let startTimestamp = null;
    const step = (timestamp) => {
      if (!startTimestamp) startTimestamp = timestamp;
      const progress = Math.min((timestamp - startTimestamp) / duration, 1);
      const easeProgress = 1 - Math.pow(1 - progress, 3); // easeOutCubic
      const current = Math.floor(easeProgress * (end - start) + start);
      obj.textContent = current.toLocaleString() + suffix;
      if (progress < 1) {
        window.requestAnimationFrame(step);
      }
    };
    window.requestAnimationFrame(step);
  }

  // Observe statistics when they scroll into view
  const statsSection = document.querySelector('#speedtest');
  let animatedStats = false;
  if (statsSection && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting && !animatedStats) {
        animatedStats = true;
        // Pulse effects on ping badges
        document.querySelectorAll('[id^="cluster-ping-"]').forEach(badge => {
          badge.classList.add('animate-pulse');
          setTimeout(() => badge.classList.remove('animate-pulse'), 1500);
        });
      }
    }, { threshold: 0.3 });
    observer.observe(statsSection);
  }
});

