<?php
/**
 * LIVETVMARKET - API: Real-Time Visitor Country Detector
 * Returns visitor country code and mapped store currency silently from IP.
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/../config/currency.php';

// Force fresh check so live IP/VPN changes are detected immediately
$country = detectVisitorCountry(true);
$name = getCountryName($country);
$currency = getCurrencyForCountry($country);

echo json_encode([
    'success' => true,
    'country' => $country,
    'name' => $name,
    'currency' => $currency
]);
