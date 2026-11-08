<?php
/**
 * LIVETVMARKET - Dynamic robots.txt Generator
 * Route: /robots.txt -> robots.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

header('Content-Type: text/plain; charset=utf-8');

$customRobots = getSetting('seo_robots_custom', '');
if (!empty(trim($customRobots))) {
    echo trim($customRobots) . "\n";
    exit;
}

$siteUrl = rtrim(defined('SITE_URL') ? SITE_URL : 'https://livetvmarket.com', '/');
?>
# ==============================================================================
# LIVETVMARKET - Automated Search Engine & AI Robots Directives
# Optimized for Google, Bing, Yahoo, Yandex, DuckDuckGo & LLM Web Search
# ==============================================================================

User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/
Disallow: /config/
Disallow: /scratch/
Disallow: /includes/
Disallow: /checkout
Disallow: /track-order

# Googlebot specific rules
User-agent: Googlebot
Allow: /
Disallow: /admin/
Disallow: /api/
Disallow: /config/

# Bingbot specific rules
User-agent: Bingbot
Allow: /
Disallow: /admin/
Disallow: /api/
Disallow: /config/

# AI Search & LLM Web Assistants (Perplexity, ChatGPT, Claude, Applebot)
User-agent: GPTBot
Allow: /

User-agent: ChatGPT-User
Allow: /

User-agent: PerplexityBot
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: Claude-Web
Allow: /

User-agent: Applebot
Allow: /

# XML Sitemap Location
Sitemap: <?= $siteUrl ?>/sitemap.xml

# LLM Index Context
# Standardized AI documentation: <?= $siteUrl ?>/llms.txt
