<?php
/**
 * LIVETVMARKET - Blog & Guides Central Data Repository
 * Provides rich articles for blog listing, blog details, and homepage widgets.
 * Backed by MySQL `blogs` table with auto-seeding and fallback.
 */

require_once __DIR__ . '/db.php';

if (!function_exists('getDefaultBlogArticles')) {
    function getDefaultBlogArticles() {
        return [
            [
                'id' => 1,
                'slug' => 'how-to-setup-iptv-firestick-2026',
                'title' => 'How to Setup IPTV on Amazon Firestick in 2026 (Zero Lag Tutorial)',
                'excerpt' => 'Complete step-by-step walkthrough to sideload Downloader, install Smarters Pro or TiviMate, and unlock 25,000+ live channels with Anti-Freeze™ 9.9.',
                'category' => 'Setup Guides',
                'category_color' => 'cyan',
                'author' => 'Alex Rivera',
                'author_role' => 'Senior Streaming Technician',
                'author_avatar' => 'AR',
                'date' => 'November 2, 2026',
                'read_time' => '4 min read',
                'icon' => 'flame',
                'gradient' => 'from-cyan-600 to-blue-700',
                'content' => '
                    <p class="lead text-lg text-slate-700 font-medium mb-6">
                        Setting up a premium IPTV subscription on an Amazon Fire TV Stick is one of the easiest ways to replace overpriced traditional cable TV. In this 2026 updated tutorial, our senior technicians walk you through every step in under 4 minutes.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Why Amazon Firestick is the #1 Streaming Device</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        The Amazon Firestick 4K and Firestick 4K Max feature hardware-accelerated HEVC (H.265) video decoders. When paired with LIVETVMARKET Anti-Freeze™ 9.9 cluster streams, you get native 60 frames-per-second playback without buffering or frame skipping, even during peak live soccer and UFC matches.
                    </p>

                    <div class="my-6 p-4 rounded-xl bg-cyan-50 border border-cyan-200 text-cyan-800 text-sm">
                        <strong class="font-bold flex items-center gap-1.5 mb-1"><i data-lucide="info" class="w-4 h-4"></i> Prerequisites:</strong>
                        Ensure your Firestick has at least 500MB of free storage and is connected to high-speed WiFi or an Ethernet adapter (recommended: 25+ Mbps for 4K).
                    </div>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Step 1: Install the Free "Downloader" App</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        Since dedicated IPTV applications are streaming media players, you will download them safely using Amazon’s verified Downloader utility:
                    </p>
                    <ol class="list-decimal pl-6 space-y-2 text-slate-600 mb-6">
                        <li>From the Firestick home screen, click <strong>Find</strong> and select <strong>Search</strong>.</li>
                        <li>Type <strong>Downloader</strong> and select the orange Downloader app icon.</li>
                        <li>Click <strong>Download / Get</strong> and wait for the automated installation to complete.</li>
                    </ol>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Step 2: Enable Installation from Unknown Apps</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        To allow Downloader to install your preferred IPTV app:
                    </p>
                    <ol class="list-decimal pl-6 space-y-2 text-slate-600 mb-6">
                        <li>Navigate to <strong>Settings (Gear Icon)</strong> on the far right of your Firestick menu.</li>
                        <li>Select <strong>My Fire TV</strong> > <strong>Developer Options</strong>.</li>
                        <li>Click <strong>Install Unknown Apps</strong> and toggle <strong>Downloader</strong> to <strong>ON</strong>.</li>
                    </ol>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Step 3: Enter the Pre-Configured App Numeric Code</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        Launch Downloader and click into the URL bar. Simply enter our official numeric quick code:
                    </p>
                    <div class="my-4 p-4 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-slate-500 font-bold block">IPTV Smarters Pro Quick Code:</span>
                            <span class="font-mono text-xl font-black text-cyan-700">250931</span>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 bg-white rounded-lg border border-slate-200 text-slate-700">Auto-Download</span>
                    </div>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Step 4: Login with Xtream Codes API</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        Open the newly installed app and choose <strong>Login with Xtream Codes API</strong> (recommended over M3U because it downloads the full interactive TV guide and VOD poster metadata). Input the Server URL, Username, and Password received in your instant activation email.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Troubleshooting & Optimization Tips</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        If you ever experience frame stuttering, go to App Settings > Stream Format and switch from MPEG-TS to HLS. This tells the video player to buffer adaptive chunks, ensuring ultra-smooth playback even on fluctuating home WiFi connections.
                    </p>
                '
            ],
            [
                'id' => 2,
                'slug' => 'best-iptv-apps-smart-tv',
                'title' => 'Best IPTV Players for Smart TVs: TiviMate vs IBO Player vs Smarters',
                'excerpt' => 'We benchmarked the top 5 IPTV applications on speed, EPG guide synchronization, 4K HDR playback stability, and catch-up controls.',
                'category' => 'App Reviews',
                'category_color' => 'blue',
                'author' => 'Sophie Bernard',
                'author_role' => 'Smart TV Software Reviewer',
                'author_avatar' => 'SB',
                'date' => 'October 28, 2026',
                'read_time' => '6 min read',
                'icon' => 'tv-2',
                'gradient' => 'from-blue-600 to-indigo-700',
                'content' => '
                    <p class="lead text-lg text-slate-700 font-medium mb-6">
                        Smart TVs have become the central hub for home entertainment. But choosing the right IPTV client application can make the difference between instant channel switching and sluggish navigation. We put the top 5 players head-to-head.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">1. TiviMate IPTV Player (Best Overall for Android & Fire TV)</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        TiviMate remains the undisputed gold standard for users running Android TV, Google TV, or Fire OS. Its modern, dark/light customizable cable-box interface, multi-screen viewing (up to 4 live channels simultaneously), and lightning-fast EPG guide scrolling make it feel identical to premium satellite hardware.
                    </p>
                    <ul class="list-disc pl-6 space-y-1 text-slate-600 mb-6">
                        <li><strong>Channel Zap Speed:</strong> 0.4 seconds</li>
                        <li><strong>EPG Guide Sync:</strong> Automatic XMLTV background fetching</li>
                        <li><strong>Multi-Room Support:</strong> Yes</li>
                    </ul>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">2. IBO Player (Best for Samsung Tizen & LG WebOS)</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        If you have a Samsung or LG Smart TV without a separate streaming stick, IBO Player is your best choice. Available directly within the official Samsung Apps Store and LG Content Store, it requires zero sideloading. You simply upload your M3U or Xtream playlist via the IBO web portal using your TV’s Device MAC and Key.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">3. IPTV Smarters Pro (Best for Beginners & Cross-Platform)</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        IPTV Smarters Pro provides native apps for iOS, Android, Windows PC, Mac, Firestick, and Smart TVs. Its intuitive categorized tabs (Live TV, Movies, Series, Catch-Up) make it ideal for family members of all technical levels.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Benchmark Comparison Table</h2>
                    <div class="my-6 overflow-x-auto">
                        <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
                            <thead class="bg-slate-100 text-slate-700 font-bold">
                                <tr>
                                    <th class="p-3 border-b border-slate-200">App Name</th>
                                    <th class="p-3 border-b border-slate-200">Platforms</th>
                                    <th class="p-3 border-b border-slate-200">4K 60FPS Support</th>
                                    <th class="p-3 border-b border-slate-200">Ease of Setup</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 text-slate-600">
                                <tr>
                                    <td class="p-3 font-bold text-slate-900">TiviMate</td>
                                    <td class="p-3">Android TV, Firestick</td>
                                    <td class="p-3 text-emerald-600 font-bold">Excellent</td>
                                    <td class="p-3">Very Easy</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-bold text-slate-900">IBO Player</td>
                                    <td class="p-3">Samsung, LG, Android</td>
                                    <td class="p-3 text-emerald-600 font-bold">Excellent</td>
                                    <td class="p-3">Instant via Portal</td>
                                </tr>
                                <tr>
                                    <td class="p-3 font-bold text-slate-900">IPTV Smarters Pro</td>
                                    <td class="p-3">All Platforms (Cross-Device)</td>
                                    <td class="p-3 text-emerald-600 font-bold">Great</td>
                                    <td class="p-3">Easiest</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                '
            ],
            [
                'id' => 3,
                'slug' => 'iptv-vs-cable-tv-comparison',
                'title' => 'IPTV vs Cable TV: The Ultimate Cord-Cutting Breakdown',
                'excerpt' => 'Traditional cable bills now exceed $135/month. Discover how modern IPTV gives you 10x more international channels, live sports, and VOD for only $6.24/month.',
                'category' => 'Cost Comparison',
                'category_color' => 'emerald',
                'author' => 'Thomas Hoffmann',
                'author_role' => 'Consumer Electronics Analyst',
                'author_avatar' => 'TH',
                'date' => 'October 21, 2026',
                'read_time' => '5 min read',
                'icon' => 'badge-dollar-sign',
                'gradient' => 'from-emerald-600 to-teal-700',
                'content' => '
                    <p class="lead text-lg text-slate-700 font-medium mb-6">
                        Over 8.2 million households cancelled traditional cable and satellite subscriptions last year alone. In this comprehensive financial and technical comparison, we examine why cord-cutting with a verified IPTV gateway delivers superior value.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">The True Cost of Cable TV in 2026</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        Cable companies advertise "$49.99/mo promotional bundles," but when you examine the actual monthly billing statement, the hidden fees multiply rapidly:
                    </p>
                    <ul class="list-disc pl-6 space-y-1 text-slate-600 mb-6">
                        <li><strong>Broadcast TV Surcharge:</strong> $18.50 - $24.00/mo</li>
                        <li><strong>Regional Sports Fee:</strong> $14.00 - $18.00/mo</li>
                        <li><strong>HD & DVR Box Rental (3 TVs):</strong> $30.00/mo</li>
                        <li><strong>Local Franchise & Regulatory Taxes:</strong> $12.00/mo</li>
                        <li><strong>Average Real Monthly Cost:</strong> $140.00+ per month ($1,680 / year)</li>
                    </ul>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">The LIVETVMARKET Advantage</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        With an annual IPTV subscription at $74.99/year (approximately $6.24/month), you eliminate equipment rentals entirely. You get 25,000+ live premium channels, 120,000+ movies on demand, and all sports pay-per-view events without surcharges.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Comparison Matrix</h2>
                    <div class="my-6 p-6 rounded-2xl bg-slate-50 border border-slate-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-4 rounded-xl bg-white border border-rose-200">
                                <h4 class="font-bold text-rose-700 text-sm mb-2">❌ Traditional Cable TV</h4>
                                <ul class="space-y-2 text-xs text-slate-600">
                                    <li>• $1,400 to $1,800 annual cost</li>
                                    <li>• 12 to 24 month binding contracts</li>
                                    <li>• Expensive pay-per-view fees ($80/fight)</li>
                                    <li>• Heavy satellite dish / coax box cables</li>
                                    <li>• Regional blackout restrictions</li>
                                </ul>
                            </div>
                            <div class="p-4 rounded-xl bg-white border border-emerald-300 shadow-sm">
                                <h4 class="font-bold text-emerald-700 text-sm mb-2">✓ LIVETVMARKET 4K IPTV</h4>
                                <ul class="space-y-2 text-xs text-slate-600">
                                    <li>• Only $74.99 for 12 months ($6.24/mo)</li>
                                    <li>• No contract • Cancel anytime</li>
                                    <li>• All UFC, Boxing, and PPV included free</li>
                                    <li>• Runs directly inside your Firestick/Smart TV app</li>
                                    <li>• Worldwide channels with no blackouts</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                '
            ],
            [
                'id' => 4,
                'slug' => 'anti-freeze-technology-explained',
                'title' => 'How Anti-Freeze™ Technology Prevents Buffering During Big Sports Matches',
                'excerpt' => 'A deep-dive behind the scenes into our 184 high-speed cloud edge nodes, multi-path routing, and automatic load balancers that eliminate lag forever.',
                'category' => 'Technology',
                'category_color' => 'purple',
                'author' => 'Alex Rivera',
                'author_role' => 'Infrastructure Architect',
                'author_avatar' => 'AR',
                'date' => 'October 15, 2026',
                'read_time' => '7 min read',
                'icon' => 'zap',
                'gradient' => 'from-purple-600 to-indigo-800',
                'content' => '
                    <p class="lead text-lg text-slate-700 font-medium mb-6">
                        Anyone who has used budget IPTV services knows the frustration of a broadcast freezing right as a penalty kick is taken or during a UFC championship round. Here is how our engineering team eliminated that problem.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Why Cheap IPTV Services Buffer</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        Most budget resellers run single-origin virtual private servers (VPS). When 5,000 subscribers tune into the same Champions League match, the server’s network interface card saturates, packets drop, and every connected client experiences buffering spinning wheels.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">The Anti-Freeze™ 9.9 Cluster Architecture</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        LIVETVMARKET operates a federated mesh of 184 dedicated Content Delivery Network (CDN) edge clusters located directly in Tier-3 data centers across Frankfurt, London, New York, Dallas, and Singapore.
                    </p>
                    <ol class="list-decimal pl-6 space-y-2 text-slate-600 mb-6">
                        <li><strong>Anycast DNS Geolocation:</strong> Your streaming client is automatically routed to the lowest-latency edge node within 20 milliseconds of your location.</li>
                        <li><strong>BGP Multi-Homed Uplinks:</strong> If an internet transit provider experiences packet congestion, our automatic border gateways reroute live frames through alternate optical routes within 300 milliseconds.</li>
                        <li><strong>End-to-End TLS Stream Masking:</strong> Streams travel over encrypted HTTPS protocol on port 443, making it indistinguishable from regular web traffic. Your local Internet Service Provider cannot throttle your video bandwidth.</li>
                    </ol>
                '
            ],
            [
                'id' => 5,
                'slug' => 'top-sports-channels-guide-4k',
                'title' => 'Where to Watch Every Major Football, UFC & F1 Event in 4K 60FPS',
                'excerpt' => 'Sky Sports, TNT Sports, beIN Sports, DAZN, ESPN, and Canal+ live stream channels mapped by event schedule with zero geographic restrictions.',
                'category' => 'Live Sports',
                'category_color' => 'amber',
                'author' => 'Marcus Davies',
                'author_role' => 'Live Sports Correspondent',
                'author_avatar' => 'MD',
                'date' => 'October 10, 2026',
                'read_time' => '4 min read',
                'icon' => 'trophy',
                'gradient' => 'from-amber-500 to-rose-600',
                'content' => '
                    <p class="lead text-lg text-slate-700 font-medium mb-6">
                        Never miss a live kickoff or fight night again. Here is the definitive channel guide for streaming the world’s biggest sporting tournaments in pristine 4K 60FPS.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">English Premier League & UEFA Champions League</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        Every match is broadcast across:
                    </p>
                    <ul class="list-disc pl-6 space-y-1 text-slate-600 mb-6">
                        <li><strong>Sky Sports Main Event & Premier League 4K:</strong> UK commentary with HDR color grading.</li>
                        <li><strong>TNT Sports 1 & Ultimate 4K:</strong> UEFA Champions League & Europa League live matches.</li>
                        <li><strong>Peacock & USA Network:</strong> Complete 3 PM Saturday blacked-out matches.</li>
                        <li><strong>Canal+ Foot UHD:</strong> French commentary with studio analysis.</li>
                    </ul>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">UFC & Boxing Pay-Per-View Events</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        Included at zero additional cost: TNT Sports Box Office, ESPN+ PPV, DAZN 1 Bar, and Sony Sports Ten. Main cards and preliminary bouts stream in uninterrupted 1080p60 and 4K UHD.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Formula 1 & Motorsport</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        Experience every Practice, Qualifying, and Grand Prix on Sky Sports F1 4K UHD and Canal+ Sport in ultra-low latency with on-board cockpit camera audio channels.
                    </p>
                '
            ],
            [
                'id' => 6,
                'slug' => 'internet-speed-requirements-4k-streaming',
                'title' => 'What Internet Speed Do You Really Need for 4K UHD Streaming?',
                'excerpt' => 'Learn how H.265 (HEVC) compression lowers required bandwidth from 50Mbps down to 25Mbps while retaining crystal-clear 60 frames-per-second fidelity.',
                'category' => 'Bandwidth Guide',
                'category_color' => 'teal',
                'author' => 'Alex Rivera',
                'author_role' => 'Senior Streaming Technician',
                'author_avatar' => 'AR',
                'date' => 'October 4, 2026',
                'read_time' => '5 min read',
                'icon' => 'gauge',
                'gradient' => 'from-cyan-700 to-teal-800',
                'content' => '
                    <p class="lead text-lg text-slate-700 font-medium mb-6">
                        One of the most frequent questions our customer support desk receives is: "Will my home internet connection handle 4K IPTV?" The short answer is yes, and you probably need far less bandwidth than you think.
                    </p>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Speed Requirements by Stream Quality</h2>
                    <div class="my-6 space-y-3">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Standard Definition (SD / 576p)</h4>
                                <p class="text-xs text-slate-500">Older regional TV channels and legacy news</p>
                            </div>
                            <span class="font-mono font-bold text-slate-700 text-xs px-2.5 py-1 bg-white border border-slate-200 rounded-lg">5 - 8 Mbps</span>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">Full HD (FHD / 1080p 60FPS)</h4>
                                <p class="text-xs text-slate-500">Live sports, documentaries, national networks</p>
                            </div>
                            <span class="font-mono font-bold text-cyan-700 text-xs px-2.5 py-1 bg-cyan-50 border border-cyan-200 rounded-lg">15 - 20 Mbps</span>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">True 4K Ultra-HD (2160p 60FPS HEVC)</h4>
                                <p class="text-xs text-slate-500">Premier sports, cinema releases, IMAX VOD</p>
                            </div>
                            <span class="font-mono font-bold text-emerald-700 text-xs px-2.5 py-1 bg-emerald-50 border border-emerald-200 rounded-lg">25 - 30 Mbps</span>
                        </div>
                    </div>

                    <h2 class="font-heading font-black text-2xl text-slate-900 mt-8 mb-4">Why Latency & Jitter Matter More Than Raw Speed</h2>
                    <p class="text-slate-600 mb-4 leading-relaxed">
                        A stable 30 Mbps line with low jitter (under 8ms) performs significantly better than an unstable 200 Mbps connection dropping packets over congested 2.4 GHz WiFi. For the absolute best performance, connect your Firestick or Smart TV via a direct 5 GHz WiFi band or an inexpensive USB Ethernet adapter.
                    </p>
                '
            ]
        ];
    }

    /**
     * Ensure database table has initial seed articles
     */
    function ensureBlogsSeeded($db) {
        if (!$db) return;
        try {
            $check = $db->query("SELECT COUNT(*) FROM `blogs`");
            if ($check && (int)$check->fetchColumn() === 0) {
                $defaults = getDefaultBlogArticles();
                $ins = $db->prepare("INSERT INTO `blogs` 
                    (`slug`, `title`, `excerpt`, `category`, `category_color`, `author`, `author_role`, `author_avatar`, `thumbnail_url`, `date`, `read_time`, `icon`, `gradient`, `content`, `is_published`, `views`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0)");
                foreach ($defaults as $a) {
                    $ins->execute([
                        $a['slug'],
                        $a['title'],
                        $a['excerpt'],
                        $a['category'],
                        $a['category_color'] ?? 'cyan',
                        $a['author'],
                        $a['author_role'],
                        $a['author_avatar'],
                        $a['thumbnail_url'] ?? null,
                        $a['date'],
                        $a['read_time'],
                        $a['icon'] ?? 'flame',
                        $a['gradient'] ?? 'from-cyan-600 to-blue-700',
                        $a['content']
                    ]);
                }
            }
        } catch (Exception $e) {}
    }

    /**
     * Helper to format thumbnail URLs cleanly (handles relative and external paths)
     */
    function formatBlogThumbnailUrl($url) {
        if (empty($url)) return '';
        $url = trim($url);
        if (preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, 'data:')) {
            return $url;
        }
        return '/' . ltrim($url, '/');
    }

    /**
     * Normalize article array with safe URLs
     */
    function normalizeBlogRecord($row) {
        if (!is_array($row)) return $row;
        if (!empty($row['thumbnail_url'])) {
            $row['thumbnail_url'] = formatBlogThumbnailUrl($row['thumbnail_url']);
        }
        return $row;
    }

    /**
     * Fetch all blog articles (published only by default)
     */
    function getBlogArticles($publishedOnly = true) {
        global $pdo;
        if ($pdo) {
            try {
                ensureBlogsSeeded($pdo);
                $where = $publishedOnly ? " WHERE `is_published` = 1" : "";
                $stmt = $pdo->query("SELECT * FROM `blogs`{$where} ORDER BY `id` DESC");
                $rows = $stmt->fetchAll();
                if (!empty($rows)) {
                    return array_map('normalizeBlogRecord', $rows);
                }
            } catch (Exception $e) {}
        }
        return array_map('normalizeBlogRecord', getDefaultBlogArticles());
    }

    /**
     * Fetch single blog by slug and increment views
     */
    function getBlogBySlug($slug) {
        global $pdo;
        if ($pdo) {
            try {
                ensureBlogsSeeded($pdo);
                $stmt = $pdo->prepare("SELECT * FROM `blogs` WHERE `slug` = ? LIMIT 1");
                $stmt->execute([$slug]);
                $row = $stmt->fetch();
                if ($row) {
                    // Increment view counter
                    $pdo->prepare("UPDATE `blogs` SET `views` = `views` + 1 WHERE `id` = ?")->execute([$row['id']]);
                    return normalizeBlogRecord($row);
                }
            } catch (Exception $e) {}
        }
        $articles = getDefaultBlogArticles();
        foreach ($articles as $art) {
            if ($art['slug'] === $slug) {
                return normalizeBlogRecord($art);
            }
        }
        return null;
    }

    /**
     * Fetch single blog by ID
     */
    function getBlogById($id) {
        global $pdo;
        if ($pdo) {
            try {
                ensureBlogsSeeded($pdo);
                $stmt = $pdo->prepare("SELECT * FROM `blogs` WHERE `id` = ? LIMIT 1");
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if ($row) return normalizeBlogRecord($row);
            } catch (Exception $e) {}
        }
        $articles = getDefaultBlogArticles();
        foreach ($articles as $art) {
            if ($art['id'] == $id) {
                return normalizeBlogRecord($art);
            }
        }
        return null;
    }
}

