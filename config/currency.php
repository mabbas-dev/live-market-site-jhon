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
 * Detect visitor country from server headers, client cookie, or IP
 * Note: Uses IP address exclusively — NO client permission prompt or browser location access.
 */
function detectVisitorCountry($forceFresh = false) {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        @session_start();
    }

    // 1. Allow URL test parameter for easy verification (?test_country=PK or ?test_country=GB)
    if (!empty($_GET['test_country'])) {
        $testCountry = strtoupper(trim($_GET['test_country']));
        $_SESSION['detected_country'] = $testCountry;
        return $testCountry;
    }

    // 2. Cloudflare IPCountry header (Always real-time for live production)
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

    // 4. Local development / Public IP check via fast external lookup
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $isLocal = empty($ip) || $ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.');
    if ($isLocal || $forceFresh) {
        $ctx = stream_context_create(['http' => ['timeout' => 1.5, 'ignore_errors' => true]]);
        $json = @file_get_contents('https://api.country.is/', false, $ctx);
        if ($json) {
            $data = json_decode($json, true);
            if (!empty($data['country']) && strlen($data['country']) === 2) {
                $code = strtoupper($data['country']);
                $_SESSION['detected_country'] = $code;
                return $code;
            }
        }
    }

    // 5. Fallback to client cookie ONLY if server headers unavailable and outbound lookup not performed
    if (!$forceFresh && !empty($_COOKIE['livetv_detected_country']) && strlen($_COOKIE['livetv_detected_country']) === 2) {
        return strtoupper(trim($_COOKIE['livetv_detected_country']));
    }

    // 6. Cached in PHP session
    if (!$forceFresh && !empty($_SESSION['detected_country']) && strlen($_SESSION['detected_country']) === 2) {
        return strtoupper($_SESSION['detected_country']);
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

