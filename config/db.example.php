<?php
/**
 * LIVETVMARKET - Database Configuration Template
 * Copy to config/db.php and fill in your credentials.
 */

$isLiveServer = (is_dir('/home/u139837297') || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'livetvmarket.com') !== false));

if ($isLiveServer) {
    $db_host = 'localhost';
    $db_user = 'u139837297_live_tv';
    $db_pass = 'YOUR_DB_PASSWORD';
    $db_name = 'u139837297_live_tv';
} else {
    $db_host = '127.0.0.1';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'iptv-website';
}

$pdo = null;

try {
    if (!$isLiveServer) {
        $initPdo = new PDO("mysql:host={$db_host};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $initPdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
}
