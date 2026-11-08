<?php
/**
 * SEO Database Migration Script
 */
require_once __DIR__ . '/db.php';

echo "Running SEO Database Migration...\n";

$cols = [
    'meta_title' => 'VARCHAR(255) NULL AFTER `badge`',
    'meta_description' => 'TEXT NULL AFTER `short_desc`',
    'meta_keywords' => 'VARCHAR(255) NULL AFTER `meta_description`',
    'og_image' => 'VARCHAR(255) NULL AFTER `image`',
    'canonical_url' => 'VARCHAR(255) NULL AFTER `og_image`',
    'schema_type' => "VARCHAR(50) DEFAULT 'Product' AFTER `canonical_url`",
    'rating_value' => "DECIMAL(2,1) DEFAULT 4.9 AFTER `schema_type`",
    'rating_count' => "INT DEFAULT 184 AFTER `rating_value`",
    'faq_json' => "LONGTEXT NULL AFTER `description`"
];

foreach ($cols as $col => $def) {
    try {
        $pdo->exec("ALTER TABLE `store_products` ADD COLUMN `{$col}` {$def}");
        echo "✓ Added column {$col}\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "• Column {$col} already exists\n";
        } else {
            echo "! {$col}: " . $e->getMessage() . "\n";
        }
    }
}

// Add category SEO columns if missing
$catCols = [
    'meta_title' => 'VARCHAR(255) NULL',
    'meta_description' => 'TEXT NULL',
    'meta_keywords' => 'VARCHAR(255) NULL'
];
foreach ($catCols as $col => $def) {
    try {
        $pdo->exec("ALTER TABLE `store_categories` ADD COLUMN `{$col}` {$def}");
        echo "✓ Added category column {$col}\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "• Category column {$col} already exists\n";
        } else {
            echo "! Category {$col}: " . $e->getMessage() . "\n";
        }
    }
}

// Default Global SEO settings
$defaultSeoSettings = [
    'seo_meta_title_template' => '%title% | %site_name%',
    'seo_default_meta_desc' => 'LIVETVMARKET offers the #1 premium IPTV streaming service with 25,000+ live 4K channels, 120,000+ VOD movies & series, and instant digital subscription keys.',
    'seo_default_keywords' => 'IPTV subscription, LiveTVMarket, buy iptv, premium iptv, 4k iptv, best iptv provider 2026, iptv firestick, live sports iptv, m3u playlist, xtream codes, iptv free trial, netflix subscription, amazon prime video',
    'seo_google_verification' => '',
    'seo_bing_verification' => '',
    'seo_yandex_verification' => '',
    'seo_ga4_id' => '',
    'seo_gtm_id' => '',
    'seo_robots_index' => 'index, follow',
    'seo_og_image' => 'assets/images/hero-showcase.jpg',
    'seo_twitter_handle' => '@livetvmarket',
    'seo_robots_custom' => '',
    'seo_llms_custom' => ''
];

$setStmt = $pdo->prepare("INSERT INTO `site_settings` (`key_name`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `key_name` = `key_name`");
foreach ($defaultSeoSettings as $k => $v) {
    $setStmt->execute([$k, $v]);
}
echo "✓ Default SEO site_settings ensured\n";

echo "Migration finished successfully!\n";
