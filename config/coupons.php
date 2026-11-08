<?php
/**
 * LIVETVMARKET - Coupons System & Database Helper
 * Handles % base discount coupons, admin CRUD, validation, and usage tracking.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

// Auto-create coupons table if it does not exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `coupons` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `code` VARCHAR(50) NOT NULL UNIQUE,
        `discount_percent` INT NOT NULL,
        `valid_plans` VARCHAR(255) NOT NULL DEFAULT 'all',
        `description` VARCHAR(255) DEFAULT NULL,
        `min_amount` DECIMAL(10,2) DEFAULT 0.00,
        `usage_limit` INT DEFAULT 0,
        `used_count` INT DEFAULT 0,
        `expires_at` DATE DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Auto-migrate valid_plans column if existing table lacks it
    $chkCol = $pdo->query("SHOW COLUMNS FROM `coupons` LIKE 'valid_plans'")->fetch();
    if (!$chkCol) {
        $pdo->exec("ALTER TABLE `coupons` ADD COLUMN `valid_plans` VARCHAR(255) NOT NULL DEFAULT 'all' AFTER `discount_percent`");
    }

    // Seed default coupons if table is empty
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `coupons`")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("INSERT INTO `coupons` (`code`, `discount_percent`, `valid_plans`, `description`, `min_amount`, `usage_limit`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $defaultCoupons = [
            ['MARKET60', 60, 'all', 'Official 60% OFF Launch Promo Code', 0.00, 0, 1],
            ['LIVETV60', 60, 'all', 'LiveTV VIP 60% OFF Voucher', 0.00, 0, 1],
            ['SPECIAL50', 50, 'all', 'Half Price 50% Special Voucher', 0.00, 0, 1],
            ['VIP20', 20, 'all', 'Returning Customer 20% Discount', 0.00, 0, 1],
            ['CRYPTO10', 10, 'all', 'Cryptocurrency 10% Extra Discount', 0.00, 0, 1]
        ];
        foreach ($defaultCoupons as $c) {
            $stmt->execute($c);
        }
    }
} catch (Exception $e) {
    error_log("Coupons table setup error: " . $e->getMessage());
}

/**
 * Retrieve all coupons
 */
function getAllCoupons($onlyActive = false) {
    global $pdo;
    try {
        $sql = "SELECT * FROM `coupons`";
        if ($onlyActive) {
            $sql .= " WHERE `is_active` = 1 AND (`expires_at` IS NULL OR `expires_at` >= CURDATE())";
        }
        $sql .= " ORDER BY `id` DESC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching coupons: " . $e->getMessage());
        return [];
    }
}

/**
 * Retrieve coupon by code
 */
function getCouponByCode($code) {
    global $pdo;
    $cleanCode = strtoupper(trim($code));
    if (empty($cleanCode)) return null;

    try {
        $stmt = $pdo->prepare("SELECT * FROM `coupons` WHERE UPPER(`code`) = ? LIMIT 1");
        $stmt->execute([$cleanCode]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        return $coupon ?: null;
    } catch (Exception $e) {
        error_log("Error fetching coupon: " . $e->getMessage());
        return null;
    }
}

/**
 * Helper to get readable plan names from codes (e.g. '12m' => '12 Months VIP Access')
 */
function getPlanNamesByCodes($codes) {
    if (!is_array($codes)) {
        $codes = explode(',', $codes);
    }
    $allPlans = function_exists('getPricingPlans') ? getPricingPlans() : [];
    $names = [];
    foreach ($codes as $c) {
        $clean = strtolower(trim($c));
        $found = false;
        foreach ($allPlans as $p) {
            if (strtolower($p['plan_code']) === $clean) {
                $names[] = $p['name'];
                $found = true;
                break;
            }
        }
        if (!$found) {
            $fallbackMap = [
                '1m' => '1 Month Access',
                '3m' => '3 Months Access',
                '6m' => '6 Months Access',
                '12m' => '12 Months VIP Access',
                '24m' => '24 Months Ultra Access'
            ];
            $names[] = $fallbackMap[$clean] ?? strtoupper($c);
        }
    }
    return !empty($names) ? implode(', ', $names) : 'Specific Plan';
}

/**
 * Validate a coupon code and calculate discount
 * @param string $code
 * @param float $amount
 * @param string $plan Plan code (e.g. '1m', '3m', '6m', '12m', '24m')
 */
function validateCouponCode($code, $amount = 0.00, $plan = '') {
    $cleanCode = strtoupper(trim($code));
    if (empty($cleanCode)) {
        return [
            'valid' => false,
            'message' => 'Please enter a coupon code.'
        ];
    }

    $coupon = getCouponByCode($cleanCode);
    if (!$coupon) {
        return [
            'valid' => false,
            'message' => 'Invalid promo code. Please check and try again.'
        ];
    }

    if (!(int)$coupon['is_active']) {
        return [
            'valid' => false,
            'message' => 'This promo code is currently inactive.'
        ];
    }

    if (!empty($coupon['expires_at'])) {
        $today = date('Y-m-d');
        if ($coupon['expires_at'] < $today) {
            return [
                'valid' => false,
                'message' => 'This promo code expired on ' . date('M j, Y', strtotime($coupon['expires_at'])) . '.'
            ];
        }
    }

    if ((int)$coupon['usage_limit'] > 0 && (int)$coupon['used_count'] >= (int)$coupon['usage_limit']) {
        return [
            'valid' => false,
            'message' => 'This promo code has reached its maximum redemption limit.'
        ];
    }

    $minAmount = (float)($coupon['min_amount'] ?? 0);
    if ($minAmount > 0 && (float)$amount > 0 && (float)$amount < $minAmount) {
        return [
            'valid' => false,
            'message' => 'This promo code requires a minimum order amount of $' . number_format($minAmount, 2) . '.'
        ];
    }

    // Check applicable plan/product restriction
    $validPlansStr = trim($coupon['valid_plans'] ?? 'all');
    if (!empty($validPlansStr) && strtolower($validPlansStr) !== 'all') {
        $allowedPlans = array_map('strtolower', array_map('trim', explode(',', $validPlansStr)));
        $cleanPlan = strtolower(trim($plan));

        if (!empty($cleanPlan) && !in_array($cleanPlan, $allowedPlans)) {
            $allowedNames = getPlanNamesByCodes($allowedPlans);
            return [
                'valid' => false,
                'message' => "❌ This promo code is only valid for {$allowedNames}."
            ];
        }
    }

    $discountPercent = (int)$coupon['discount_percent'];
    $discountRate = $discountPercent / 100.0;

    return [
        'valid' => true,
        'coupon_id' => (int)$coupon['id'],
        'code' => $coupon['code'],
        'discount_percent' => $discountPercent,
        'discount_rate' => $discountRate,
        'valid_plans' => $validPlansStr,
        'description' => $coupon['description'],
        'message' => "🎉 Coupon {$coupon['code']} applied! {$discountPercent}% discount deducted."
    ];
}

/**
 * Increment usage counter for a coupon upon completed order
 */
function incrementCouponUsage($code) {
    global $pdo;
    $cleanCode = strtoupper(trim($code));
    if (empty($cleanCode)) return false;

    try {
        $stmt = $pdo->prepare("UPDATE `coupons` SET `used_count` = `used_count` + 1 WHERE UPPER(`code`) = ?");
        return $stmt->execute([$cleanCode]);
    } catch (Exception $e) {
        error_log("Error incrementing coupon usage: " . $e->getMessage());
        return false;
    }
}

