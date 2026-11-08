<?php
/**
 * LIVETVMARKET - Global Configuration File
 * Loads database connection and dynamically reads customizable settings from MySQL DB 'iptv-website'.
 */

// Environment & Error Handling
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Load Database & Currency Engine
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/currency.php';

// Site Identity & Branding (Dynamic from Database with Fallback)
define('SITE_NAME', getSetting('site_name', 'LIVETVMARKET'));
define('SITE_DOMAIN', getSetting('site_domain', 'livetvmarket.com'));
define('SITE_URL', getSetting('site_url', 'https://livetvmarket.com'));

// Live Infrastructure & Statistics
define('ACTIVE_CLUSTERS', getSetting('active_clusters', '184'));
define('NETWORK_UPTIME', getSetting('network_uptime', '99.99%'));
define('TOTAL_CHANNELS', getSetting('total_channels', '25,000+'));
define('TOTAL_VOD', getSetting('total_vod', '120,000+'));
define('RATING_SCORE', getSetting('rating_score', '4.9/5'));
define('SUBSCRIBER_COUNT', getSetting('subscriber_count', '12,480+'));

// Promo & Sales Codes
define('FLASH_PROMO_CODE', getSetting('promo_code', 'MARKET60'));
define('FLASH_PROMO_DISCOUNT', getSetting('promo_discount', '60%'));
define('CRYPTO_PROMO_CODE', 'CRYPTO10');
define('LEGAL_BANNER_TEXT', getSetting('legal_banner_text', 'All broadcasts & VOD legally syndicated via certified partners'));

// Support & Contacts
define('SUPPORT_WHATSAPP', getSetting('contact_whatsapp', '+1 555 019 2831'));
define('SUPPORT_EMAIL', getSetting('contact_email', 'support@livetvmarket.com'));
define('CONTACT_EMAIL', SUPPORT_EMAIL);
define('WHATSAPP_LINK', getSetting('whatsapp_url', 'https://wa.me/15550192831'));
define('SUPPORT_TELEGRAM', getSetting('contact_telegram', 'https://t.me/livetvmarket'));

// Trial System Configuration (Controlled via Admin Panel)
define('TRIAL_MODE', getSetting('trial_mode', 'free')); // 'free', 'paid', or 'disabled'
define('TRIAL_PRICE', getSetting('trial_price', '1.99'));
define('TRIAL_DURATION_HOURS', getSetting('trial_duration_hours', '24'));
define('TRIAL_IS_ACTIVE', (TRIAL_MODE !== 'disabled' && TRIAL_MODE !== 'off' && TRIAL_MODE !== '0' && TRIAL_MODE !== ''));
define('TRIAL_BUTTON_LABEL', (TRIAL_MODE === 'paid') ? (TRIAL_DURATION_HOURS . 'h Trial ($' . number_format((float)TRIAL_PRICE, 2) . ')') : 'Free 24h Trial');

// Hero & Content Customization
define('HERO_HEADLINE', getSetting('hero_headline', 'Stream ' . TOTAL_CHANNELS . ' Live Channels & ' . TOTAL_VOD . ' Movies in'));
define('HERO_SUBTITLE', getSetting('hero_subtitle', 'Experience unbuffered live sports, global entertainment, and cinema blockbusters with our proprietary Anti-Freeze™ 9.9 CDN cluster. Zero lag, zero ISP throttling, instant automated delivery.'));

/**
 * Helper to determine active navigation links
 */
function navActiveClass($pageName, $activePage) {
    if ($pageName === $activePage) {
        return 'text-slate-900 bg-white font-bold border border-gray-200 shadow-sm';
    }
    return 'text-slate-600 hover:text-cyan-600';
}

/**
 * Fetch all pricing plans from DB or fallback
 */
function getPricingPlans() {
    global $pdo;
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM `pricing_plans` ORDER BY `sort_order` ASC, `months` ASC");
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                return $rows;
            }
        } catch (Exception $e) {}
    }
    return [
        [
            'id' => 1,
            'plan_code' => '1m',
            'name' => '1 Month Access',
            'months' => 1,
            'price_usd' => 14.99,
            'per_month_display' => '$14.99/mo',
            'savings_badge' => 'Monthly',
            'is_popular' => 0,
            'features' => "25,000+ Live Channels\n120,000+ VOD Movies & Shows\nAll Live Sports & PPV in 4K/FHD\nAnti-Freeze™ 9.9 Included\nM3U URL & Xtream API\n24/7 WhatsApp Tech Support",
            'sort_order' => 1
        ],
        [
            'id' => 2,
            'plan_code' => '3m',
            'name' => '3 Months Access',
            'months' => 3,
            'price_usd' => 34.99,
            'per_month_display' => '$11.66/mo',
            'savings_badge' => 'Save 22%',
            'is_popular' => 0,
            'features' => "25,000+ Live Channels\n120,000+ VOD Movies & Shows\nAll Live Sports & PPV in 4K/FHD\nAnti-Freeze™ 9.9 Included\n7-Day Catch-Up & EPG\nPriority Server Allocation",
            'sort_order' => 2
        ],
        [
            'id' => 3,
            'plan_code' => '12m',
            'name' => '12 Months VIP Access',
            'months' => 12,
            'price_usd' => 74.99,
            'per_month_display' => '$6.24/mo',
            'savings_badge' => 'Best Value • Save 60%',
            'is_popular' => 1,
            'features' => "25,000+ Live Channels (Full Package)\n120,000+ VOD Movies & Shows\nAll PPV & Sports in True 4K 60FPS\nAnti-Freeze™ 9.9 Ultra Cluster\nFree Built-In VPN Proxy Ready\nVIP 24/7 Fast-Track Priority Support",
            'sort_order' => 3
        ],
        [
            'id' => 4,
            'plan_code' => '24m',
            'name' => '24 Months Access',
            'months' => 24,
            'price_usd' => 119.99,
            'per_month_display' => '$4.99/mo',
            'savings_badge' => 'Save 67%',
            'is_popular' => 0,
            'features' => "25,000+ Live Channels\n120,000+ VOD Movies & Shows\nAll PPV & Sports in True 4K UHD\nLifetime Free Playlist Migrations\nAll Device Protocols (M3U, Xtream, MAG)\nVIP Dedicated Account Manager",
            'sort_order' => 4
        ]
    ];
}
