<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}

$title = isset($pageTitle) ? $pageTitle : SITE_NAME . ' - Premium 4K IPTV Subscription | ' . TOTAL_CHANNELS . ' Channels & VOD';
$desc = isset($pageDesc) ? $pageDesc : SITE_NAME . ' offers the #1 premium IPTV service with ' . TOTAL_CHANNELS . ' live channels and ' . TOTAL_VOD . ' VOD movies & series. Anti-Freeze™ 9.9 zero-buffering technology, all live sports & PPV in 4K UHD. Instant automated activation & 24h free trial.';
$canonical = isset($pageCanonical) ? $pageCanonical : SITE_URL . '/';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <base href="/">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- SEO Primary Meta Tags -->
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="title" content="<?= htmlspecialchars($title) ?>">
  <meta name="description" content="<?= htmlspecialchars($desc) ?>">
  <meta name="keywords" content="IPTV subscription, LiveTVMarket, buy iptv, premium iptv, 4k iptv, best iptv provider 2026, iptv firestick, live sports iptv, m3u playlist, xtream codes, iptv free trial">
  <meta name="robots" content="index, follow">
  <meta name="author" content="<?= SITE_NAME ?>">
  <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($desc) ?>">
  <meta property="og:image" content="assets/images/hero-showcase.jpg">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= htmlspecialchars($canonical) ?>">
  <meta property="twitter:title" content="<?= htmlspecialchars($title) ?>">
  <meta property="twitter:description" content="<?= htmlspecialchars($desc) ?>">
  <meta property="twitter:image" content="assets/images/hero-showcase.jpg">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#ecfeff',
              100: '#cffafe',
              200: '#a5f3fc',
              300: '#67e8f9',
              400: '#22d3ee',
              500: '#06b6d4',
              600: '#0891b2',
              700: '#0e7490',
              800: '#155e75',
              900: '#164e63',
            },
            slatebg: {
              main: '#ffffff',
              surface: '#f8fafc',
              card: '#ffffff',
              border: '#e2e8f0',
            }
          },
          fontFamily: {
            sans: ['Plus Jakarta Sans', 'sans-serif'],
            heading: ['Outfit', 'sans-serif'],
          }
        }
      }
    }
  </script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Animation Libraries: AOS (Animate On Scroll) & Animate.css -->
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    // Fail-safe immediate AOS initialization so page content is never hidden
    if (typeof AOS !== 'undefined') {
      window.addEventListener('DOMContentLoaded', () => {
        try { AOS.init({ duration: 700, once: true }); } catch (e) {}
      });
    } else {
      document.documentElement.classList.add('no-aos');
    }
  </script>

  <!-- GSAP & ScrollTrigger -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

  <!-- Canvas Confetti -->
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css?v=<?= file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time() ?>">

  <!-- Schema.org JSON-LD Structured Data for SEO -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Organization",
        "@id": "<?= SITE_URL ?>/#organization",
        "name": "<?= SITE_NAME ?>",
        "url": "<?= SITE_URL ?>",
        "logo": "<?= SITE_URL ?>/assets/images/logo.png"
      },
      {
        "@type": "Product",
        "@id": "<?= SITE_URL ?>/#product",
        "name": "<?= SITE_NAME ?> 4K IPTV Subscription",
        "image": "<?= SITE_URL ?>/assets/images/hero-showcase.jpg",
        "description": "Premium 4K IPTV subscription with <?= TOTAL_CHANNELS ?> live channels, <?= TOTAL_VOD ?> VOD movies & series, anti-freeze streaming technology and 24/7 support.",
        "brand": {
          "@type": "Brand",
          "name": "<?= SITE_NAME ?>"
        },
        "aggregateRating": {
          "@type": "AggregateRating",
          "ratingValue": "4.9",
          "reviewCount": "12480",
          "bestRating": "5",
          "worstRating": "1"
        },
        "offers": {
          "@type": "AggregateOffer",
          "priceCurrency": "USD",
          "lowPrice": "14.99",
          "highPrice": "119.99",
          "offerCount": "4"
        }
      }
    ]
  }
  </script>

  <!-- Multi-Currency, IP Geo-Location & Social Proof Data Payloads -->
  <?php
  require_once __DIR__ . '/../config/social_proof.php';
  $currencyPayload = getCurrenciesJsPayload();
  $socialProofPayload = getSocialProofJsPayload();
  ?>
  <script>
    window.CURRENCY_DATA = <?= json_encode($currencyPayload) ?>;
    window.SOCIAL_PROOF_EVENTS = <?= json_encode($socialProofPayload) ?>;
  </script>
</head>
<body class="bg-white text-slate-800 antialiased selection:bg-cyan-500 selection:text-white">
