<?php
/**
 * LIVETVMARKET - Multi-Currency & IP Geo-Location System
 * Dynamically converts prices, detects visitor country by IP (without location permission),
 * and provides full admin control over exchange rates and assigned countries.
 */

require_once __DIR__ . '/db.php';

/**
 * Fetch all currencies from MySQL DB with fallback
 */
function getCurrencies($enabledOnly = true) {
    global $pdo;
    if ($pdo) {
        try {
            $where = $enabledOnly ? " WHERE `is_enabled` = 1" : "";
            $stmt = $pdo->query("SELECT * FROM `currencies`{$where} ORDER BY `sort_order` ASC, `id` ASC");
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                return $rows;
            }
        } catch (Exception $e) {}
    }

    // Default Fallback Currencies (USA, EURO, GBP, PKR, CAD, AUD)
    return [
        [
            'id' => 1,
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'rate' => 1.0000,
            'assigned_countries' => 'US,UM',
            'is_default' => 1,
            'is_enabled' => 1,
            'sort_order' => 1
        ],
        [
            'id' => 2,
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'rate' => 0.9200,
            'assigned_countries' => 'DE,FR,IT,ES,NL,BE,AT,IE,PT,GR,FI,EE,LV,LT,SK,SI,CY,MT,LU',
            'is_default' => 0,
            'is_enabled' => 1,
            'sort_order' => 2
        ],
        [
            'id' => 3,
            'code' => 'GBP',
            'name' => 'British Pound',
            'symbol' => '£',
            'rate' => 0.7900,
            'assigned_countries' => 'GB,UK',
            'is_default' => 0,
            'is_enabled' => 1,
            'sort_order' => 3
        ],
        [
            'id' => 4,
            'code' => 'PKR',
            'name' => 'Pakistani Rupee',
            'symbol' => 'Rs',
            'rate' => 278.5000,
            'assigned_countries' => 'PK',
            'is_default' => 0,
            'is_enabled' => 1,
            'sort_order' => 4
        ],
        [
            'id' => 5,
            'code' => 'CAD',
            'name' => 'Canadian Dollar',
            'symbol' => 'C$',
            'rate' => 1.3600,
            'assigned_countries' => 'CA',
            'is_default' => 0,
            'is_enabled' => 1,
            'sort_order' => 5
        ],
        [
            'id' => 6,
            'code' => 'AUD',
            'name' => 'Australian Dollar',
            'symbol' => 'A$',
            'rate' => 1.5200,
            'assigned_countries' => 'AU',
            'is_default' => 0,
            'is_enabled' => 1,
            'sort_order' => 6
        ]
    ];
}

/**
 * Fetch default base currency
 */
function getDefaultCurrency() {
    $currencies = getCurrencies(false);
    foreach ($currencies as $c) {
        if (!empty($c['is_default'])) {
            return $c;
        }
    }
    return $currencies[0];
}

/**
 * Fetch currency by code
 */
function getCurrencyByCode($code) {
    $code = strtoupper(trim($code));
    $currencies = getCurrencies(false);
    foreach ($currencies as $c) {
        if (strtoupper($c['code']) === $code) {
            return $c;
        }
    }
    return getDefaultCurrency();
}

/**
 * Fetch currency symbol by code
 */
function getCurrencySymbol($code) {
    $c = getCurrencyByCode($code);
    return $c['symbol'] ?? '$';
}

if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol($code) {
        return getCurrencySymbol($code);
    }
}

/**
 * Extract visitor client IP (handles reverse proxies, Cloudflare, Hostinger CDN)
 */
function getClientVisitorIp(): string {
    $ipKeys = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'REMOTE_ADDR'
    ];
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $parts = explode(',', $_SERVER[$key]);
            foreach ($parts as $p) {
                $candidate = trim($p);
                if (filter_var($candidate, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $candidate;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

/**
 * Detect visitor country from server headers, client cookie, or IP
 * Note: Uses IP address exclusively — NO client permission prompt or browser location access.
 */
function detectVisitorCountry($forceFresh = false) {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        @session_start();
    }

    // 1. Allow URL test parameter for easy verification (?test_country=PK or ?country=GB)
    if (!empty($_GET['test_country'])) {
        $testCountry = strtoupper(trim($_GET['test_country']));
        $_SESSION['detected_country'] = $testCountry;
        if (!headers_sent()) {
            @setcookie('livetv_detected_country', $testCountry, time() + 86400, '/');
        }
        return $testCountry;
    }
    if (!empty($_GET['country']) && strlen(trim($_GET['country'])) === 2) {
        $testCountry = strtoupper(trim($_GET['country']));
        $_SESSION['detected_country'] = $testCountry;
        if (!headers_sent()) {
            @setcookie('livetv_detected_country', $testCountry, time() + 86400, '/');
        }
        return $testCountry;
    }

    // 2. Cloudflare IPCountry header (Always real-time when Cloudflare proxy is active)
    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY']) && strlen($_SERVER['HTTP_CF_IPCOUNTRY']) === 2 && $_SERVER['HTTP_CF_IPCOUNTRY'] !== 'XX') {
        $cfCountry = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
        $_SESSION['detected_country'] = $cfCountry;
        return $cfCountry;
    }

    // 3. Other standard proxy / cPanel geoip headers
    $geoHeaders = ['HTTP_X_COUNTRY_CODE', 'GEOIP_COUNTRY_CODE', 'HTTP_GEOIP_COUNTRY_CODE', 'HTTP_X_GEOIP_COUNTRY'];
    foreach ($geoHeaders as $h) {
        if (!empty($_SERVER[$h]) && strlen($_SERVER[$h]) === 2) {
            $hCountry = strtoupper($_SERVER[$h]);
            $_SESSION['detected_country'] = $hCountry;
            return $hCountry;
        }
    }

    // 4. Fallback to client cookie if already detected by client browser
    if (!$forceFresh && !empty($_COOKIE['livetv_detected_country']) && strlen($_COOKIE['livetv_detected_country']) === 2) {
        $cCountry = strtoupper(trim($_COOKIE['livetv_detected_country']));
        $_SESSION['detected_country'] = $cCountry;
        return $cCountry;
    }

    // 5. Cached in PHP session
    if (!$forceFresh && !empty($_SESSION['detected_country']) && strlen($_SESSION['detected_country']) === 2) {
        return strtoupper($_SESSION['detected_country']);
    }

    // 6. Fast Server-Side IP Lookup with Visitor IP
    $visitorIp = getClientVisitorIp();
    $isLocal = empty($visitorIp) || $visitorIp === '127.0.0.1' || $visitorIp === '::1' || str_starts_with($visitorIp, '192.168.') || str_starts_with($visitorIp, '10.');
    $lookupUrl = $isLocal ? 'https://api.country.is/' : ('https://api.country.is/' . urlencode($visitorIp));

    $ctx = stream_context_create(['http' => ['timeout' => 1.8, 'ignore_errors' => true]]);
    $json = @file_get_contents($lookupUrl, false, $ctx);
    if ($json) {
        $data = json_decode($json, true);
        if (!empty($data['country']) && strlen($data['country']) === 2) {
            $code = strtoupper($data['country']);
            $_SESSION['detected_country'] = $code;
            if (!headers_sent()) {
                @setcookie('livetv_detected_country', $code, time() + 86400, '/');
            }
            return $code;
        }
    }

    // Backup Server-Side IP Lookup: ip-api.com
    $backupUrl = $isLocal ? 'http://ip-api.com/json/?fields=status,countryCode' : ('http://ip-api.com/json/' . urlencode($visitorIp) . '?fields=status,countryCode');
    $json2 = @file_get_contents($backupUrl, false, $ctx);
    if ($json2) {
        $data2 = json_decode($json2, true);
        if (!empty($data2['countryCode']) && strlen($data2['countryCode']) === 2) {
            $code = strtoupper($data2['countryCode']);
            $_SESSION['detected_country'] = $code;
            if (!headers_sent()) {
                @setcookie('livetv_detected_country', $code, time() + 86400, '/');
            }
            return $code;
        }
    }

    // Default to US if unknown
    return 'US';
}

/**
 * Get the mapped currency code for a given 2-letter country code
 */
function getCurrencyForCountry($countryCode) {
    $countryCode = strtoupper(trim($countryCode));
    $currencies = getCurrencies(true);

    foreach ($currencies as $c) {
        $assigned = array_map('trim', explode(',', strtoupper($c['assigned_countries'] ?? '')));
        if (in_array($countryCode, $assigned)) {
            return $c['code'];
        }
    }

    // Fallback to default currency
    $default = getDefaultCurrency();
    return $default['code'] ?? 'USD';
}

/**
 * Get country display name from 2-letter code
 */
function getCountryName($code) {
    $names = [
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'UK' => 'United Kingdom',
        'PK' => 'Pakistan',
        'CA' => 'Canada',
        'AU' => 'Australia',
        'DE' => 'Germany',
        'FR' => 'France',
        'ES' => 'Spain',
        'IT' => 'Italy',
        'NL' => 'Netherlands',
        'IE' => 'Ireland',
        'BE' => 'Belgium',
        'AT' => 'Austria',
        'CH' => 'Switzerland',
        'SE' => 'Sweden',
        'NO' => 'Norway',
        'DK' => 'Denmark',
        'FI' => 'Finland',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'GR' => 'Greece',
        'TR' => 'Turkey',
        'AE' => 'United Arab Emirates',
        'SA' => 'Saudi Arabia',
        'QA' => 'Qatar',
        'KW' => 'Kuwait',
        'OM' => 'Oman',
        'BH' => 'Bahrain',
        'IN' => 'India',
        'BD' => 'Bangladesh',
        'MY' => 'Malaysia',
        'SG' => 'Singapore',
        'ID' => 'Indonesia',
        'PH' => 'Philippines',
        'TH' => 'Thailand',
        'VN' => 'Vietnam',
        'ZA' => 'South Africa',
        'NG' => 'Nigeria',
        'EG' => 'Egypt',
        'BR' => 'Brazil',
        'MX' => 'Mexico',
        'AR' => 'Argentina',
        'CL' => 'Chile',
        'CO' => 'Colombia',
        'NZ' => 'New Zealand',
        'JP' => 'Japan',
        'KR' => 'South Korea',
        'CN' => 'China',
        'HK' => 'Hong Kong'
    ];
    return $names[strtoupper($code)] ?? strtoupper($code);
}

/**
 * Format currency price
 */
function formatCurrencyPrice($amountUSD, $currencyCode) {
    $curr = getCurrencyByCode($currencyCode);
    $rate = (float)($curr['rate'] ?? 1.0);
    $symbol = $curr['symbol'] ?? '$';
    $converted = $amountUSD * $rate;

    // If PKR or currency with large numbers, format with no decimals or standard 2
    if ($curr['code'] === 'PKR') {
        return $symbol . ' ' . number_format($converted, 0);
    }
    return $symbol . number_format($converted, 2);
}

/**
 * Export data payload for client-side JavaScript engine
 */
function getCurrenciesJsPayload() {
    $currencies = getCurrencies(true);
    $rates = [];
    $countryMap = [];

    foreach ($currencies as $c) {
        $code = strtoupper($c['code']);
        $rates[$code] = [
            'code' => $code,
            'name' => $c['name'],
            'symbol' => $c['symbol'],
            'rate' => (float)$c['rate'],
            'is_default' => (bool)$c['is_default']
        ];

        // Map assigned countries
        $assigned = array_map('trim', explode(',', strtoupper($c['assigned_countries'] ?? '')));
        foreach ($assigned as $cnt) {
            if (!empty($cnt)) {
                $countryMap[$cnt] = $code;
            }
        }
    }

    return [
        'rates' => $rates,
        'countryMap' => $countryMap,
        'defaultCurrency' => (getDefaultCurrency()['code'] ?? 'USD'),
        'detectedCountry' => detectVisitorCountry(),
        'detectedCurrency' => getCurrencyForCountry(detectVisitorCountry())
    ];
}

