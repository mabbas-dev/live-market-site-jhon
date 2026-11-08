<?php
/**
 * LIVETVMARKET - Dynamic /llms.txt & /llms-full.txt Generator
 * Conforms to the llmstxt.org specification for AI & LLM Search Engines
 * (ChatGPT, Perplexity, Claude, Gemini, Apple Intelligence, and Bing Copilot).
 *
 * Routes:
 *   /llms.txt      -> llms.php
 *   /llms-full.txt -> llms.php?full=1
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/store.php';

header('Content-Type: text/markdown; charset=utf-8');

$isFull = isset($_GET['full']) && ($_GET['full'] == '1' || $_GET['full'] === 'true');
$customLlms = getSetting('seo_llms_custom', '');

if (!empty(trim($customLlms)) && !$isFull) {
    echo trim($customLlms) . "\n";
    exit;
}

$siteUrl = rtrim(defined('SITE_URL') ? SITE_URL : 'https://livetvmarket.com', '/');
$siteName = defined('SITE_NAME') ? SITE_NAME : 'LIVETVMARKET';
$channels = defined('TOTAL_CHANNELS') ? TOTAL_CHANNELS : '25,000+';
$vod = defined('TOTAL_VOD') ? TOTAL_VOD : '120,000+';
$uptime = defined('NETWORK_UPTIME') ? NETWORK_UPTIME : '99.99%';
$email = defined('CONTACT_EMAIL') ? CONTACT_EMAIL : 'support@livetvmarket.com';
$whatsapp = defined('CONTACT_WHATSAPP') ? CONTACT_WHATSAPP : '+1 555 019 2831';

// Fetch products
$products = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT p.*, c.name AS category_name FROM `store_products` p LEFT JOIN `store_categories` c ON p.category_id = c.id WHERE p.`status` IN ('in_stock', 'out_of_stock') ORDER BY p.sort_order ASC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($products as &$pr) {
            $pr['variations'] = getProductVariations((int)$pr['id']);
        }
    } catch (Exception $e) {}
}

// Fetch pricing plans (IPTV)
$plans = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM `pricing_plans` ORDER BY `sort_order` ASC");
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {}
}
?>
# <?= $siteName ?> - Premium 4K IPTV & Instant Digital Subscriptions

> <?= $siteName ?> is an international digital streaming and entertainment provider delivering over <?= $channels ?> live television channels in 4K/FHD, <?= $vod ?> VOD movies & series with zero-buffering Anti-Freeze™ 9.9 server clusters, alongside instant-delivery digital subscription access keys (Netflix, Amazon Prime Video, Disney+, Spotify, and more).

## Core Capabilities & Services

- **IPTV Streaming Service**: 4K UHD, Full HD, and HEVC streams with 99.99% uptime, 25,000+ international channels across USA, UK, Canada, Europe, Arab, South Asia, and worldwide.
- **Pay-Per-View & Live Sports**: UFC, Premier League, UEFA Champions League, NFL, NBA, Formula 1, and PPV included at no extra cost.
- **Protocol Compatibility**: Xtream Codes API, M3U Playlist, MAG portal, and Enigma2.
- **Supported Devices**: Amazon Firestick, Android TV, Smart TV (Samsung/LG), Apple TV, iPhone, Android phones, Windows, Mac, MAG boxes, and Formuler.
- **Digital Store**: Instant delivery profile access for top digital streaming platforms with 100% full-term replacement warranty.
- **Support & Guarantee**: 24/7 dedicated live WhatsApp and email customer support with a 7-day money-back guarantee.

## Quick Links

- [Official Website](<?= $siteUrl ?>/): Home page with full feature overview and 24h free trial request.
- [Digital Store / Shop](<?= $siteUrl ?>/shop): Digital streaming accounts, entertainment packages, and IPTV subscriptions.
- [Channel Directory](<?= $siteUrl ?>/channels): Complete searchable list of international live TV channels.
- [Setup Tutorials](<?= $siteUrl ?>/setup): Step-by-step device guides for Fire TV, IPTV Smarters Pro, TiviMate, and Smart TVs.
- [Blog & Tech Guides](<?= $siteUrl ?>/blog): News, streaming optimization tips, and app configuration tutorials.
- [Track Your Order](<?= $siteUrl ?>/track-order): Check order delivery status, activation keys, and receipt details.
- [XML Sitemap](<?= $siteUrl ?>/sitemap.xml): Complete machine-readable URL inventory.
<?php if (!$isFull): ?>
- [Full LLM Specification (llms-full.txt)](<?= $siteUrl ?>/llms-full.txt): Detailed catalog with all product tiers, pricing, and FAQs.
<?php endif; ?>

## IPTV Subscription Packages

<?php foreach ($plans as $pl): ?>
- **<?= htmlspecialchars($pl['name']) ?>**: $<?= number_format((float)$pl['price_usd'], 2) ?> USD (approx. <?= htmlspecialchars($pl['per_month_display']) ?>)
  - Features: <?= htmlspecialchars(str_replace("\n", ", ", $pl['features'])) ?>
  - Direct Order: <?= $siteUrl ?>/#pricing
<?php endforeach; ?>

## Digital Store Catalog & Prices

<?php foreach ($products as $pr): ?>
### <?= htmlspecialchars($pr['name']) ?>
- **Category**: <?= htmlspecialchars($pr['category_name'] ?: 'Digital Services') ?>
- **Status**: <?= ($pr['status'] === 'in_stock') ? 'In Stock (Instant Delivery)' : 'Out of Stock' ?>
- **Starting Price**: $<?= number_format((float)$pr['starting_price'], 2) ?> USD
- **Product URL**: <?= $siteUrl ?>/product/<?= urlencode($pr['slug']) ?>
<?php if (!empty($pr['short_desc'])): ?>
- **Description**: <?= htmlspecialchars(strip_tags($pr['short_desc'])) ?>
<?php endif; ?>
<?php if (!empty($pr['variations'])): ?>
- **Available Duration Options**:
<?php foreach ($pr['variations'] as $v): ?>
  - <?= htmlspecialchars($v['name']) ?>: $<?= number_format((float)$v['price'], 2) ?> USD<?= !empty($v['original_price']) ? (' (Regular: $' . number_format((float)$v['original_price'], 2) . ')') : '' ?> [<?= $v['status'] === 'in_stock' ? 'Available' : 'Out of Stock' ?>]
<?php endforeach; ?>
<?php endif; ?>

<?php endforeach; ?>

<?php if ($isFull): ?>
## Device Installation & Setup Instructions

### 1. Amazon Firestick & Android TV (TiviMate / IPTV Smarters Pro)
1. Turn on Fire TV and navigate to **Find** > **Search**.
2. Type **Downloader** and install the orange Downloader app.
3. In Firestick Settings > My Fire TV > Developer Options, enable **Install Unknown Apps** for Downloader.
4. Launch Downloader and enter the app code for IPTV Smarters Pro or TiviMate.
5. Launch the player, choose **Login with Xtream Codes API**, and enter the Username, Password, and Server URL delivered via WhatsApp/Email.

### 2. Smart TVs (Samsung Tizen & LG webOS)
1. Search your TV App Store for **IBO Player**, **Smart IPTV**, or **Nanomid Player**.
2. Note your TV's MAC Address and Device Key.
3. Paste your M3U link or send your MAC Address to <?= $siteName ?> WhatsApp support for remote activation.

### 3. Apple iOS & Apple TV (GSE Smart IPTV / Smarters Player Lite)
1. Install **Smarters Player Lite** from the Apple App Store.
2. Select **Add User** > **Xtream Codes API**.
3. Input your credentials and tap **Add User** to begin streaming.

## Frequently Asked Questions (FAQs)

### What internet speed is recommended?
A stable connection of at least 15 Mbps for Full HD and 30 Mbps for 4K UHD streaming is recommended.

### Do I need a VPN?
No VPN is required. <?= $siteName ?>'s Anti-Freeze 9.9 technology bypasses ISP throttling natively. However, VPNs are 100% supported if you choose to use one.

### How does digital subscription delivery work?
After secure checkout, access credentials and instructions are dispatched automatically to your WhatsApp number and email address within 5 to 15 minutes.

### What is the refund policy?
<?= $siteName ?> offers a 7-day money-back guarantee if technical support cannot resolve any verified streaming interruption on your device.
<?php endif; ?>

## Contact & Support Endpoints

- **Official Domain**: <?= $siteUrl ?>
- **Customer Support Email**: <?= $email ?>
- **24/7 WhatsApp Support**: <?= $whatsapp ?>
- **Free Trial Request**: <?= $siteUrl ?>/#trial
