<?php
/**
 * LIVETVMARKET - Dynamic XML Sitemap Generator
 * Conforms to Google / Bing / Yahoo Sitemap Protocol 0.9 & Image Sitemap Schema.
 * Route: /sitemap.xml -> sitemap.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/store.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex, follow', true);

$baseUrl = rtrim(defined('SITE_URL') ? SITE_URL : 'https://livetvmarket.com', '/');
$nowDate = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd">

    <!-- Primary Static Pages -->
    <url>
        <loc><?= htmlspecialchars($baseUrl . '/') ?></loc>
        <lastmod><?= $nowDate ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
        <image:image>
            <image:loc><?= htmlspecialchars($baseUrl . '/assets/images/hero-showcase.jpg') ?></image:loc>
            <image:title><?= htmlspecialchars(SITE_NAME . ' Premium 4K IPTV & Digital Streaming') ?></image:title>
        </image:image>
    </url>

    <url>
        <loc><?= htmlspecialchars($baseUrl . '/shop') ?></loc>
        <lastmod><?= $nowDate ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <url>
        <loc><?= htmlspecialchars($baseUrl . '/channels') ?></loc>
        <lastmod><?= $nowDate ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc><?= htmlspecialchars($baseUrl . '/setup') ?></loc>
        <lastmod><?= $nowDate ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc><?= htmlspecialchars($baseUrl . '/blog') ?></loc>
        <lastmod><?= $nowDate ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>

    <url>
        <loc><?= htmlspecialchars($baseUrl . '/contact') ?></loc>
        <lastmod><?= $nowDate ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>

    <url>
        <loc><?= htmlspecialchars($baseUrl . '/track-order') ?></loc>
        <lastmod><?= $nowDate ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>

    <!-- Digital Store Products -->
    <?php
    if ($pdo) {
        try {
            $prodStmt = $pdo->query("SELECT `name`, `slug`, `image`, `updated_at`, `created_at` 
                                     FROM `store_products` 
                                     WHERE `status` IN ('in_stock', 'out_of_stock') 
                                     ORDER BY `sort_order` ASC, `id` DESC");
            while ($prod = $prodStmt->fetch(PDO::FETCH_ASSOC)) {
                $lastmod = !empty($prod['updated_at']) ? date('Y-m-d', strtotime($prod['updated_at'])) : (!empty($prod['created_at']) ? date('Y-m-d', strtotime($prod['created_at'])) : $nowDate);
                $prodUrl = $baseUrl . '/product/' . urlencode($prod['slug']);
                $prodImg = !empty($prod['image']) ? (str_starts_with($prod['image'], 'http') ? $prod['image'] : ($baseUrl . '/' . ltrim($prod['image'], '/'))) : '';
                ?>
    <url>
        <loc><?= htmlspecialchars($prodUrl) ?></loc>
        <lastmod><?= $lastmod ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
        <?php if (!empty($prodImg)): ?>
        <image:image>
            <image:loc><?= htmlspecialchars($prodImg) ?></image:loc>
            <image:title><?= htmlspecialchars($prod['name'] . ' Subscription Key') ?></image:title>
        </image:image>
        <?php endif; ?>
    </url>
                <?php
            }
        } catch (Exception $e) {}
    }
    ?>

    <!-- Store Categories -->
    <?php
    if ($pdo) {
        try {
            $catStmt = $pdo->query("SELECT `name`, `slug` FROM `store_categories` WHERE `status` = 'active' ORDER BY `sort_order` ASC");
            while ($cat = $catStmt->fetch(PDO::FETCH_ASSOC)) {
                $catUrl = $baseUrl . '/shop?cat=' . urlencode($cat['slug']);
                ?>
    <url>
        <loc><?= htmlspecialchars($catUrl) ?></loc>
        <lastmod><?= $nowDate ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
                <?php
            }
        } catch (Exception $e) {}
    }
    ?>

    <!-- Blog Articles -->
    <?php
    if ($pdo) {
        try {
            $blogStmt = $pdo->query("SELECT `title`, `slug`, `thumbnail_url`, `updated_at`, `created_at` 
                                     FROM `blogs` 
                                     WHERE `is_published` = 1 
                                     ORDER BY `id` DESC");
            while ($b = $blogStmt->fetch(PDO::FETCH_ASSOC)) {
                $bMod = !empty($b['updated_at']) ? date('Y-m-d', strtotime($b['updated_at'])) : (!empty($b['created_at']) ? date('Y-m-d', strtotime($b['created_at'])) : $nowDate);
                $bUrl = $baseUrl . '/blog/' . urlencode($b['slug']);
                $bImg = !empty($b['thumbnail_url']) ? (str_starts_with($b['thumbnail_url'], 'http') ? $b['thumbnail_url'] : ($baseUrl . '/' . ltrim($b['thumbnail_url'], '/'))) : '';
                ?>
    <url>
        <loc><?= htmlspecialchars($bUrl) ?></loc>
        <lastmod><?= $bMod ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        <?php if (!empty($bImg)): ?>
        <image:image>
            <image:loc><?= htmlspecialchars($bImg) ?></image:loc>
            <image:title><?= htmlspecialchars($b['title']) ?></image:title>
        </image:image>
        <?php endif; ?>
    </url>
                <?php
            }
        } catch (Exception $e) {}
    }
    ?>

    <!-- Legal & Policy Pages -->
    <?php
    if ($pdo) {
        try {
            $legalStmt = $pdo->query("SELECT `slug`, `updated_at` FROM `legal_pages` WHERE `is_active` = 1");
            while ($lp = $legalStmt->fetch(PDO::FETCH_ASSOC)) {
                $lpMod = !empty($lp['updated_at']) ? date('Y-m-d', strtotime($lp['updated_at'])) : $nowDate;
                $lpUrl = $baseUrl . '/' . urlencode($lp['slug']);
                ?>
    <url>
        <loc><?= htmlspecialchars($lpUrl) ?></loc>
        <lastmod><?= $lpMod ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.4</priority>
    </url>
                <?php
            }
        } catch (Exception $e) {}
    }
    ?>

</urlset>
