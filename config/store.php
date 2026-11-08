<?php
/**
 * LIVETVMARKET - Digital Store Configuration & Data Helpers
 * Handles products, variations, categories, and store toggle logic.
 */

require_once __DIR__ . '/db.php';

/**
 * Check whether the Digital Store is enabled globally in site settings.
 */
function isStoreEnabled(): bool {
    $val = getSetting('store_enabled', '1');
    return $val === '1' || strtolower($val) === 'true';
}

/**
 * Get all active store categories.
 */
function getStoreCategories(bool $activeOnly = true): array {
    global $pdo;
    if (!$pdo) return [];
    try {
        $sql = "SELECT * FROM `store_categories`";
        if ($activeOnly) {
            $sql .= " WHERE `status` = 'active'";
        }
        $sql .= " ORDER BY `sort_order` ASC, `name` ASC";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        error_log("[STORE CATEGORIES ERROR] " . $e->getMessage());
        return [];
    }
}

/**
 * Get store products with optional category and featured filters.
 */
function getStoreProducts(?int $categoryId = null, bool $featuredOnly = false, ?string $status = 'in_stock'): array {
    global $pdo;
    if (!$pdo) return [];
    try {
        $conditions = [];
        $params = [];

        if ($status !== null) {
            if ($status === 'in_stock') {
                $conditions[] = "p.`status` IN ('in_stock', 'out_of_stock')";
            } else {
                $conditions[] = "p.`status` = ?";
                $params[] = $status;
            }
        }

        if ($categoryId !== null && $categoryId > 0) {
            $conditions[] = "p.`category_id` = ?";
            $params[] = $categoryId;
        }

        if ($featuredOnly) {
            $conditions[] = "p.`is_featured` = 1";
        }

        $whereClause = !empty($conditions) ? (' WHERE ' . implode(' AND ', $conditions)) : '';
        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug 
                FROM `store_products` p 
                LEFT JOIN `store_categories` c ON p.category_id = c.id 
                {$whereClause} 
                ORDER BY p.sort_order ASC, p.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Attach variations summary or lowest price
        foreach ($products as &$prod) {
            $prod['variations'] = getProductVariations((int)$prod['id']);
            if (!empty($prod['variations'])) {
                $prices = array_column($prod['variations'], 'price');
                $prod['min_price'] = min($prices);
                $prod['max_price'] = max($prices);
            } else {
                $prod['min_price'] = (float)$prod['starting_price'];
                $prod['max_price'] = (float)$prod['starting_price'];
            }
        }
        return $products;
    } catch (Exception $e) {
        error_log("[STORE PRODUCTS ERROR] " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single product by its clean URL slug.
 */
function getStoreProductBySlug(string $slug): ?array {
    global $pdo;
    if (!$pdo || empty($slug)) return null;
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug 
                               FROM `store_products` p 
                               LEFT JOIN `store_categories` c ON p.category_id = c.id 
                               WHERE p.slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) return null;

        $product['variations'] = getProductVariations((int)$product['id']);
        return $product;
    } catch (Exception $e) {
        error_log("[STORE PRODUCT BY SLUG ERROR] " . $e->getMessage());
        return null;
    }
}

/**
 * Get a single product by its ID.
 */
function getStoreProductById(int $id): ?array {
    global $pdo;
    if (!$pdo || $id <= 0) return null;
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug 
                               FROM `store_products` p 
                               LEFT JOIN `store_categories` c ON p.category_id = c.id 
                               WHERE p.id = ? LIMIT 1");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) return null;

        $product['variations'] = getProductVariations((int)$product['id']);
        return $product;
    } catch (Exception $e) {
        error_log("[STORE PRODUCT BY ID ERROR] " . $e->getMessage());
        return null;
    }
}

/**
 * Get all variations for a given product.
 */
function getProductVariations(int $productId, bool $inStockOnly = false): array {
    global $pdo;
    if (!$pdo || $productId <= 0) return [];
    try {
        $sql = "SELECT * FROM `store_product_variations` WHERE `product_id` = ?";
        if ($inStockOnly) {
            $sql .= " AND `status` = 'in_stock'";
        }
        $sql .= " ORDER BY `sort_order` ASC, `price` ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        error_log("[PRODUCT VARIATIONS ERROR] " . $e->getMessage());
        return [];
    }
}

/**
 * Format a price display.
 */
function formatStorePrice(float $amount, string $currency = 'USD'): string {
    return '$' . number_format($amount, 2);
}
