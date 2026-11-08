<?php
/**
 * LIVETVMARKET - Social Proof & Live Sales Notifications Engine
 * Handles database CRUD, ordering, and frontend export for floating purchase toasts.
 */

require_once __DIR__ . '/db.php';

// Auto-create social_proof_toasts table
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `social_proof_toasts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `customer_name` VARCHAR(100) DEFAULT 'Someone',
        `location` VARCHAR(150) NOT NULL,
        `plan_name` VARCHAR(150) NOT NULL,
        `time_ago` VARCHAR(50) DEFAULT 'Just now',
        `is_active` TINYINT(1) DEFAULT 1,
        `sort_order` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Seed default notifications if table is empty
    $count = (int)$pdo->query("SELECT COUNT(*) FROM `social_proof_toasts`")->fetchColumn();
    if ($count === 0) {
        $defaultToasts = [
            ['customer_name' => 'Someone', 'location' => 'Sydney, Australia', 'plan_name' => '6 Months Full Access', 'time_ago' => '2 mins ago', 'sort_order' => 1],
            ['customer_name' => 'Someone', 'location' => 'London, United Kingdom', 'plan_name' => '12 Months VIP 4K Plan', 'time_ago' => '4 mins ago', 'sort_order' => 2],
            ['customer_name' => 'David K.', 'location' => 'Miami, Florida, USA', 'plan_name' => '24 Months Ultra Access', 'time_ago' => '6 mins ago', 'sort_order' => 3],
            ['customer_name' => 'Someone', 'location' => 'Toronto, Canada', 'plan_name' => '12 Months VIP 4K Plan', 'time_ago' => '8 mins ago', 'sort_order' => 4],
            ['customer_name' => 'Alexandre M.', 'location' => 'Paris, France', 'plan_name' => 'Free 24H Trial Activated', 'time_ago' => 'Just now', 'sort_order' => 5],
            ['customer_name' => 'Someone', 'location' => 'Berlin, Germany', 'plan_name' => '12 Months VIP 4K Plan', 'time_ago' => '11 mins ago', 'sort_order' => 6],
            ['customer_name' => 'Tariq A.', 'location' => 'Dubai, UAE', 'plan_name' => '12 Months VIP 4K Plan', 'time_ago' => '14 mins ago', 'sort_order' => 7],
            ['customer_name' => 'Hamza R.', 'location' => 'Lahore, Pakistan', 'plan_name' => '12 Months VIP 4K Plan', 'time_ago' => 'Just now', 'sort_order' => 8]
        ];

        $ins = $pdo->prepare("INSERT INTO `social_proof_toasts` (`customer_name`, `location`, `plan_name`, `time_ago`, `sort_order`, `is_active`) VALUES (?, ?, ?, ?, ?, 1)");
        foreach ($defaultToasts as $t) {
            $ins->execute([
                $t['customer_name'],
                $t['location'],
                $t['plan_name'],
                $t['time_ago'],
                $t['sort_order']
            ]);
        }
    }
} catch (Exception $e) {
    error_log("Social proof table error: " . $e->getMessage());
}

/**
 * Fetch all social proof toasts
 */
function getSocialProofToasts($onlyActive = false) {
    global $pdo;
    try {
        $sql = "SELECT * FROM `social_proof_toasts`";
        if ($onlyActive) {
            $sql .= " WHERE `is_active` = 1";
        }
        $sql .= " ORDER BY `sort_order` ASC, `id` ASC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching social proof toasts: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch formatted array for front-end JS
 */
function getSocialProofJsPayload() {
    $rows = getSocialProofToasts(true);
    $payload = [];
    foreach ($rows as $r) {
        $payload[] = [
            'name' => htmlspecialchars($r['customer_name'] ?: 'Someone'),
            'location' => htmlspecialchars($r['location']),
            'plan' => htmlspecialchars($r['plan_name']),
            'time' => htmlspecialchars($r['time_ago'] ?: 'Just now')
        ];
    }
    return $payload;
}

