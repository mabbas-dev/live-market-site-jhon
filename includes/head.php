<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}
if (!function_exists('getSetting')) {
    require_once __DIR__ . '/../config/db.php';
}

// Global SEO configuration
$defaultMetaDesc = getSetting('seo_default_meta_desc', SITE_NAME . ' offers the #1 premium IPTV service with ' . (defined('TOTAL_CHANNELS') ? TOTAL_CHANNELS : '25,000+') . ' live channels and ' . (defined('TOTAL_VOD') ? TOTAL_VOD : '120,000+') . ' VOD movies & series. Anti-Freeze™ 9.9 zero-buffering technology, all live sports & PPV in 4K UHD. Instant automated activation & 24h free trial.');
$defaultKeywords = getSetting('seo_default_keywords', 'IPTV subscription, LiveTVMarket, buy iptv, premium iptv, 4k iptv, best iptv provider 2026, iptv firestick, live sports iptv, m3u playlist, xtream codes, iptv free trial, netflix subscription');
$defaultOgImg    = getSetting('seo_og_image', 'assets/images/hero-showcase.jpg');
$robotsSetting   = getSetting('seo_robots_index', 'index, follow');

$title      = isset($pageTitle) && !empty($pageTitle) ? $pageTitle : (SITE_NAME . ' - Premium 4K IPTV Subscription | ' . (defined('TOTAL_CHANNELS') ? TOTAL_CHANNELS : '25,000+') . ' Channels & VOD');
$desc       = isset($pageDesc) && !empty($pageDesc) ? $pageDesc : $defaultMetaDesc;
$keywords   = isset($pageKeywords) && !empty($pageKeywords) ? $pageKeywords : $defaultKeywords;
$canonical  = isset($pageCanonical) && !empty($pageCanonical) ? $pageCanonical : (rtrim(SITE_URL, '/') . '/');
$robots     = isset($pageRobots) && !empty($pageRobots) ? $pageRobots : $robotsSetting;
$pageType   = isset($ogType) && !empty($ogType) ? $ogType : 'website';

// Format OG Image to absolute URL
$ogImgRaw = isset($pageOgImage) && !empty($pageOgImage) ? $pageOgImage : $defaultOgImg;
$ogImg = (str_starts_with($ogImgRaw, 'http://') || str_starts_with($ogImgRaw, 'https://')) ? $ogImgRaw : (rtrim(SITE_URL, '/') . '/' . ltrim($ogImgRaw, '/'));

// Verification tags
$googleVer = trim(getSetting('seo_google_verification', ''));
if (!empty($googleVer) && preg_match('/content=["\']([^"\']+)["\']/', $googleVer, $m)) {
    $googleVer = $m[1];
}
$bingVer = trim(getSetting('seo_bing_verification', ''));
if (!empty($bingVer) && preg_match('/content=["\']([^"\']+)["\']/', $bingVer, $m)) {
    $bingVer = $m[1];
}
$yandexVer = trim(getSetting('seo_yandex_verification', ''));
if (!empty($yandexVer) && preg_match('/content=["\']([^"\']+)["\']/', $yandexVer, $m)) {
    $yandexVer = $m[1];
}

// Analytics IDs
$ga4Id = trim(getSetting('seo_ga4_id', ''));
$gtmId = trim(getSetting('seo_gtm_id', ''));
$twitterHandle = trim(getSetting('seo_twitter_handle', '@livetvmarket'));
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
  <?php if (!empty($keywords)): ?>
  <meta name="keywords" content="<?= htmlspecialchars($keywords) ?>">
  <?php endif; ?>
  <meta name="robots" content="<?= htmlspecialchars($robots) ?>">
  <meta name="author" content="<?= htmlspecialchars(SITE_NAME) ?>">
  <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">

  <!-- Search Engine Verification Tags -->
  <?php if (!empty($googleVer)): ?>
  <meta name="google-site-verification" content="<?= htmlspecialchars($googleVer) ?>">
  <?php endif; ?>
  <?php if (!empty($bingVer)): ?>
  <meta name="msvalidate.01" content="<?= htmlspecialchars($bingVer) ?>">
  <?php endif; ?>
  <?php if (!empty($yandexVer)): ?>
  <meta name="yandex-verification" content="<?= htmlspecialchars($yandexVer) ?>">
  <?php endif; ?>

  <!-- Open Graph / Facebook -->
  <meta property="og:site_name" content="<?= htmlspecialchars(SITE_NAME) ?>">
  <meta property="og:type" content="<?= htmlspecialchars($pageType) ?>">
  <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($desc) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($ogImg) ?>">
  <meta property="og:image:alt" content="<?= htmlspecialchars($title) ?>">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <?php if (!empty($twitterHandle)): ?>
  <meta name="twitter:site" content="<?= htmlspecialchars($twitterHandle) ?>">
  <meta name="twitter:creator" content="<?= htmlspecialchars($twitterHandle) ?>">
  <?php endif; ?>
  <meta name="twitter:url" content="<?= htmlspecialchars($canonical) ?>">
  <meta name="twitter:title" content="<?= htmlspecialchars($title) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($desc) ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($ogImg) ?>">

  <!-- Google Tag Manager (if configured) -->
  <?php if (!empty($gtmId)): ?>
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','<?= htmlspecialchars($gtmId) ?>');</script>
  <?php endif; ?>

  <!-- Google Analytics 4 / gtag.js (if configured) -->
  <?php if (!empty($ga4Id)): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga4Id) ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?= htmlspecialchars($ga4Id) ?>');
  </script>
  <?php endif; ?>

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

  <!-- Animation Libraries: AOS & Animate.css -->
  <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
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

  <!-- Dynamic Schema.org JSON-LD Structured Data for Google / Bing Rich Results -->
  <?php
  $schemaGraph = [];

  // 1. Organization Schema
  $schemaGraph[] = [
      "@type" => "Organization",
      "@id" => rtrim(SITE_URL, '/') . '/#organization',
      "name" => SITE_NAME,
      "url" => rtrim(SITE_URL, '/'),
      "logo" => rtrim(SITE_URL, '/') . '/assets/images/logo.png',
      "contactPoint" => [
          "@type" => "ContactPoint",
          "telephone" => defined('CONTACT_WHATSAPP') ? CONTACT_WHATSAPP : '+1 555 019 2831',
          "contactType" => "customer support",
          "email" => defined('CONTACT_EMAIL') ? CONTACT_EMAIL : 'support@livetvmarket.com',
          "availableLanguage" => ["en", "es", "fr", "ar", "ur"]
      ]
  ];

  // 2. WebSite Schema with SearchAction
  $schemaGraph[] = [
      "@type" => "WebSite",
      "@id" => rtrim(SITE_URL, '/') . '/#website',
      "url" => rtrim(SITE_URL, '/'),
      "name" => SITE_NAME,
      "description" => $desc,
      "publisher" => ["@id" => rtrim(SITE_URL, '/') . '/#organization'],
      "potentialAction" => [
          "@type" => "SearchAction",
          "target" => [
              "@type" => "EntryPoint",
              "urlTemplate" => rtrim(SITE_URL, '/') . "/shop?q={search_term_string}"
          ],
          "query-input" => "required name=search_term_string"
      ]
  ];

  // 3. BreadcrumbList Schema (if provided)
  if (!empty($breadcrumbs) && is_array($breadcrumbs)) {
      $bItems = [];
      $pos = 1;
      foreach ($breadcrumbs as $b) {
          $bItems[] = [
              "@type" => "ListItem",
              "position" => $pos++,
              "name" => $b['name'],
              "item" => $b['url']
          ];
      }
      $schemaGraph[] = [
          "@type" => "BreadcrumbList",
          "itemListElement" => $bItems
      ];
  }

  // 4. Product Schema (if on product page or if $productSchema is provided)
  if (!empty($productSchema) && is_array($productSchema)) {
      $schemaGraph[] = $productSchema;
  } elseif (!isset($isNonStorePage) && (!isset($activePage) || $activePage === 'home')) {
      // Default IPTV Service schema for homepage
      $schemaGraph[] = [
          "@type" => "Product",
          "@id" => rtrim(SITE_URL, '/') . '/#iptv-subscription',
          "name" => SITE_NAME . " 4K IPTV Subscription",
          "image" => rtrim(SITE_URL, '/') . "/assets/images/hero-showcase.jpg",
          "description" => "Premium 4K IPTV subscription with " . (defined('TOTAL_CHANNELS') ? TOTAL_CHANNELS : '25,000+') . " live channels, " . (defined('TOTAL_VOD') ? TOTAL_VOD : '120,000+') . " VOD movies & series, anti-freeze streaming technology and 24/7 support.",
          "brand" => [
              "@type" => "Brand",
              "name" => SITE_NAME
          ],
          "aggregateRating" => [
              "@type" => "AggregateRating",
              "ratingValue" => "4.9",
              "reviewCount" => "12480",
              "bestRating" => "5",
              "worstRating" => "1"
          ],
          "offers" => [
              "@type" => "AggregateOffer",
              "priceCurrency" => "USD",
              "lowPrice" => "14.99",
              "highPrice" => "119.99",
              "offerCount" => "4",
              "availability" => "https://schema.org/InStock"
          ]
      ];
  }

  // 5. Article / BlogPosting Schema (if on blog page)
  if (!empty($articleSchema) && is_array($articleSchema)) {
      $schemaGraph[] = $articleSchema;
  }

  // 6. FAQPage Schema (if $pageFaqs is provided)
  if (!empty($pageFaqs) && is_array($pageFaqs)) {
      $faqEntities = [];
      foreach ($pageFaqs as $fq) {
          if (!empty($fq['question']) && !empty($fq['answer'])) {
              $faqEntities[] = [
                  "@type" => "Question",
                  "name" => $fq['question'],
                  "acceptedAnswer" => [
                      "@type" => "Answer",
                      "text" => strip_tags($fq['answer'])
                  ]
              ];
          }
      }
      if (!empty($faqEntities)) {
          $schemaGraph[] = [
              "@type" => "FAQPage",
              "mainEntity" => $faqEntities
          ];
      }
  }

  // 7. Custom extra schema
  if (!empty($customSchema) && is_array($customSchema)) {
      $schemaGraph[] = $customSchema;
  }
  ?>
  <script type="application/ld+json">
  <?= json_encode([
      "@context" => "https://schema.org",
      "@graph" => $schemaGraph
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
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
<?php if (!empty($gtmId)): ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= htmlspecialchars($gtmId) ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>
