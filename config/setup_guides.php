<?php
/**
 * LIVETVMARKET - Setup Guides Engine & Database Helper
 * Handles dynamic CRUD, ordering, and rendering for the device setup tutorials page.
 */

require_once __DIR__ . '/db.php';

// Auto-create setup_guides table
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `setup_guides` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `device_slug` VARCHAR(50) NOT NULL UNIQUE,
        `device_name` VARCHAR(150) NOT NULL,
        `nav_title` VARCHAR(80) NOT NULL,
        `setup_time` VARCHAR(100) DEFAULT 'Total Setup Time: ~3 minutes',
        `icon` VARCHAR(50) DEFAULT 'tv',
        `icon_color` VARCHAR(30) DEFAULT 'cyan',
        `steps_json` LONGTEXT NOT NULL,
        `sort_order` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Seed default device setup guides if table is empty
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `setup_guides`")->fetchColumn();
    if ($count === 0) {
        $defaultGuides = [
            [
                'device_slug' => 'firestick',
                'device_name' => 'Amazon Firestick / Fire TV Cube (Recommended)',
                'nav_title' => '🔥 Amazon Firestick',
                'setup_time' => 'Total Setup Time: ~3 minutes',
                'icon' => 'flame',
                'icon_color' => 'amber',
                'sort_order' => 1,
                'is_active' => 1,
                'steps' => [
                    [
                        'title' => 'Step 1: Install the "Downloader" App',
                        'desc' => 'On your Firestick home screen, go to Find > Search, type in Downloader, and click Download.'
                    ],
                    [
                        'title' => 'Step 2: Enable Unknown Apps',
                        'desc' => 'Go to Settings > My Fire TV > Developer Options > Install Unknown Apps, and toggle Downloader to ON.'
                    ],
                    [
                        'title' => 'Step 3: Enter Downloader Quick Code',
                        'desc' => 'Open Downloader, enter numeric code 250931 (IPTV Smarters Pro) or 278077 (TiviMate), and click Go.'
                    ],
                    [
                        'title' => 'Step 4: Enter Xtream Codes Credentials',
                        'desc' => 'Open the installed app, choose Login with Xtream Codes API, and enter the Username, Password, and Server URL from your activation email.'
                    ]
                ]
            ],
            [
                'device_slug' => 'android',
                'device_name' => 'Android TV, Nvidia Shield & Google TV',
                'nav_title' => '📱 Android TV',
                'setup_time' => 'Total Setup Time: ~2 minutes',
                'icon' => 'smartphone',
                'icon_color' => 'emerald',
                'sort_order' => 2,
                'is_active' => 1,
                'steps' => [
                    [
                        'title' => 'Step 1: Download TiviMate or IPTV Smarters Pro',
                        'desc' => 'Open the Google Play Store directly on your Android TV or Shield, search for TiviMate IPTV Player or IPTV Smarters Pro, and install.'
                    ],
                    [
                        'title' => 'Step 2: Add Xtream Playlist',
                        'desc' => 'Click Add Playlist, select Xtream Codes, and input your server link, username, and password.'
                    ]
                ]
            ],
            [
                'device_slug' => 'smarttv',
                'device_name' => 'Samsung Tizen & LG WebOS Smart TVs',
                'nav_title' => '📺 Samsung & LG',
                'setup_time' => 'Total Setup Time: ~3 minutes',
                'icon' => 'tv-2',
                'icon_color' => 'cyan',
                'sort_order' => 3,
                'is_active' => 1,
                'steps' => [
                    [
                        'title' => 'Step 1: Install IBO Player or Smart IPTV',
                        'desc' => 'Search for IBO Player or Nanomid Player in the Samsung Apps or LG Content Store.'
                    ],
                    [
                        'title' => 'Step 2: Note Your MAC Address & Device Key',
                        'desc' => 'Launch the app. It will show a unique MAC address and Device Key on your television screen.'
                    ],
                    [
                        'title' => 'Step 3: Instant Activation',
                        'desc' => 'Send your MAC address to our 24/7 WhatsApp support desk and our technician will load your playlist remotely within 2 minutes!'
                    ]
                ]
            ],
            [
                'device_slug' => 'apple',
                'device_name' => 'Apple TV 4K, iPhone & iPad',
                'nav_title' => '🍎 Apple TV & iOS',
                'setup_time' => 'Total Setup Time: ~2 minutes',
                'icon' => 'apple',
                'icon_color' => 'indigo',
                'sort_order' => 4,
                'is_active' => 1,
                'steps' => [
                    [
                        'title' => 'Step 1: Download App from App Store',
                        'desc' => 'Search for Smarters Player Lite or GSE Smart IPTV in the official Apple App Store.'
                    ],
                    [
                        'title' => 'Step 2: Enter Credentials',
                        'desc' => 'Select Login with Xtream Codes API and enter the details from your activation email.'
                    ]
                ]
            ],
            [
                'device_slug' => 'pc',
                'device_name' => 'Windows & Mac PC',
                'nav_title' => '💻 Windows / Mac',
                'setup_time' => 'Total Setup Time: ~1 minute',
                'icon' => 'monitor',
                'icon_color' => 'cyan',
                'sort_order' => 5,
                'is_active' => 1,
                'steps' => [
                    [
                        'title' => 'VLC Media Player',
                        'desc' => 'Press Ctrl+N (or Cmd+N on Mac), paste your custom M3U playlist URL, and start streaming immediately.'
                    ]
                ]
            ],
            [
                'device_slug' => 'mag',
                'device_name' => 'MAG 250/254/256 & Formuler Z Boxes',
                'nav_title' => '📦 MAG Box',
                'setup_time' => 'Stalker Portal Integration',
                'icon' => 'box',
                'icon_color' => 'rose',
                'sort_order' => 6,
                'is_active' => 1,
                'steps' => [
                    [
                        'title' => 'Enter Stalker Portal URL',
                        'desc' => 'In Settings -> System Settings -> Servers -> Portals, set Portal 1 Name to "LIVETVMARKET" and enter the portal URL from your email.'
                    ]
                ]
            ]
        ];

        $ins = $pdo->prepare("INSERT INTO `setup_guides` (`device_slug`, `device_name`, `nav_title`, `setup_time`, `icon`, `icon_color`, `steps_json`, `sort_order`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($defaultGuides as $g) {
            $ins->execute([
                $g['device_slug'],
                $g['device_name'],
                $g['nav_title'],
                $g['setup_time'],
                $g['icon'],
                $g['icon_color'],
                json_encode($g['steps']),
                $g['sort_order'],
                $g['is_active']
            ]);
        }
    }
} catch (Exception $e) {
    error_log("Setup guides table error: " . $e->getMessage());
}

/**
 * Fetch all setup guides
 */
function getSetupGuides($onlyActive = false) {
    global $pdo;
    try {
        $sql = "SELECT * FROM `setup_guides`";
        if ($onlyActive) {
            $sql .= " WHERE `is_active` = 1";
        }
        $sql .= " ORDER BY `sort_order` ASC, `id` ASC";
        $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $decoded = json_decode($r['steps_json'] ?? '[]', true);
            $r['steps'] = is_array($decoded) ? $decoded : [];
        }
        return $rows;
    } catch (Exception $e) {
        error_log("Error fetching setup guides: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch single guide by ID
 */
function getSetupGuideById($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM `setup_guides` WHERE `id` = ? LIMIT 1");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $decoded = json_decode($row['steps_json'] ?? '[]', true);
            $row['steps'] = is_array($decoded) ? $decoded : [];
            return $row;
        }
        return null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Color classes mapper
 */
function getDeviceColorClasses($color) {
    $map = [
        'amber'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-200'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200'],
        'cyan'    => ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-600', 'border' => 'border-cyan-200'],
        'indigo'  => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-600', 'border' => 'border-indigo-200'],
        'rose'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'border' => 'border-rose-200'],
        'purple'  => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'border' => 'border-purple-200'],
        'blue'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-200']
    ];
    return $map[$color] ?? $map['cyan'];
}

/**
 * Page settings helper for setup page
 */
function getSetupPageSettings() {
    return [
        'badge' => getSetting('setup_page_badge', '3-Minute Tutorials'),
        'title' => getSetting('setup_page_title', 'Device Setup & App Installation Guides'),
        'subtitle' => getSetting('setup_page_subtitle', 'Follow our simple step-by-step guides below to connect your subscription on any streaming device.'),
        'help_title' => getSetting('setup_help_title', 'Need Live Setup Assistance?'),
        'help_subtitle' => getSetting('setup_help_subtitle', 'Our technicians are available 24/7 on WhatsApp to guide you step-by-step.'),
        'help_btn_text' => getSetting('setup_help_btn_text', 'Chat With Technician'),
        'help_btn_link' => getSetting('setup_help_btn_link', '')
    ];
}
