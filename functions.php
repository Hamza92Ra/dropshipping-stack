<?php

/**
 * Fetch a user's curated stack tools and compute real statistics
 */
if (!function_exists('get_user_stack_data')) {
    function get_user_stack_data(int $user_id, PDO $pdo): array
    {
        $quizMap = [
            'beginner' => [
                'free' => ['woocommerce', 'dsers', 'canva-pro'],
                'low' => ['shopify', 'dsers', 'minea', 'tidio'],
                'mid' => ['shopify', 'zendrop', 'minea', 'klaviyo'],
                'high' => ['shopify', 'spocket', 'adspy', 'triple-whale']
            ],
            'intermediate' => [
                'free' => ['woocommerce', 'spocket'],
                'low' => ['shopify', 'autods'],
                'mid' => ['shopify', 'zendrop', 'klaviyo'],
                'high' => ['shopify', 'triple-whale', 'adspy']
            ],
            'advanced' => [
                'free' => ['shopify'],
                'low' => ['shopify', 'klaviyo'],
                'mid' => ['shopify', 'triple-whale', 'adspy', 'klaviyo'],
                'high' => ['shopify', 'triple-whale', 'adspy', 'gorgias']
            ]
        ];

        $tool_ids = [];

        // 1. Fetch from user_stack table
        $stmt = $pdo->prepare("SELECT tools_json FROM user_stack WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stackRow = $stmt->fetch();

        if ($stackRow && !empty($stackRow['tools_json'])) {
            $parsed = json_decode($stackRow['tools_json'], true);
            if (is_array($parsed)) {
                if (isset($parsed['tools']) && is_array($parsed['tools'])) {
                    $tool_ids = array_map('intval', $parsed['tools']);
                } elseif (isset($parsed[0]) && is_numeric($parsed[0])) {
                    $tool_ids = array_map('intval', $parsed);
                } elseif (isset($parsed['experience']) && isset($parsed['budget'])) {
                    $exp = $parsed['experience'] ?? 'beginner';
                    $bud = $parsed['budget'] ?? 'low';
                    $slugs = $quizMap[$exp][$bud] ?? ['shopify', 'dsers'];
                    if (!empty($slugs)) {
                        $inQuery = implode(',', array_fill(0, count($slugs), '?'));
                        $sStmt = $pdo->prepare("SELECT id FROM tools WHERE slug IN ($inQuery)");
                        $sStmt->execute($slugs);
                        $tool_ids = array_map('intval', $sStmt->fetchAll(PDO::FETCH_COLUMN));
                    }
                }
            }
        }

        // 2. Merge with bookmarks
        $bStmt = $pdo->prepare("SELECT tool_id FROM bookmarks WHERE user_id = ?");
        $bStmt->execute([$user_id]);
        $bookmarkIds = array_map('intval', $bStmt->fetchAll(PDO::FETCH_COLUMN));
        $tool_ids = array_values(array_unique(array_merge($tool_ids, $bookmarkIds)));

        // 3. Fetch tool records
        $tools = [];
        $total_cost = 0.0;
        $free_count = 0;
        $paid_count = 0;

        if (!empty($tool_ids)) {
            $ph = implode(',', array_fill(0, count($tool_ids), '?'));
            $tStmt = $pdo->prepare("
                SELECT t.*, c.name AS cat_name, c.slug AS cat_slug
                FROM tools t
                LEFT JOIN categories c ON c.id = t.category_id
                WHERE t.id IN ($ph) AND t.is_active = 1
                ORDER BY t.name ASC
            ");
            $tStmt->execute($tool_ids);
            $tools = $tStmt->fetchAll();

            foreach ($tools as $t) {
                if ($t['price_type'] === 'paid') {
                    $paid_count++;
                    $total_cost += (float)$t['price_from'];
                } else {
                    $free_count++;
                }
            }
        }

        // 4. Find 3 recommended tools from missing categories
        $used_cat_ids = array_unique(array_filter(array_column($tools, 'category_id')));
        $used_tool_ids = !empty($tools) ? array_column($tools, 'id') : [0];
        $phTools = implode(',', array_fill(0, count($used_tool_ids), '?'));

        $recommended = [];
        if (!empty($used_cat_ids)) {
            $phCats = implode(',', array_fill(0, count($used_cat_ids), '?'));
            $recStmt = $pdo->prepare("
                SELECT t.*, c.name AS cat_name, c.slug AS cat_slug
                FROM tools t
                LEFT JOIN categories c ON c.id = t.category_id
                WHERE t.id NOT IN ($phTools)
                  AND t.category_id NOT IN ($phCats)
                  AND t.is_active = 1
                ORDER BY t.is_featured DESC, t.clicks DESC, t.rating DESC
                LIMIT 3
            ");
            $recStmt->execute(array_merge($used_tool_ids, $used_cat_ids));
            $recommended = $recStmt->fetchAll();
        }

        // If still need more recommendations (e.g. stack empty or categories covered)
        if (count($recommended) < 3) {
            $needed = 3 - count($recommended);
            $alreadyPicked = array_merge($used_tool_ids, array_column($recommended, 'id'));
            $phAlready = implode(',', array_fill(0, count($alreadyPicked), '?'));
            $fallbackStmt = $pdo->prepare("
                SELECT t.*, c.name AS cat_name, c.slug AS cat_slug
                FROM tools t
                LEFT JOIN categories c ON c.id = t.category_id
                WHERE t.id NOT IN ($phAlready) AND t.is_active = 1
                ORDER BY t.is_featured DESC, t.clicks DESC
                LIMIT $needed
            ");
            $fallbackStmt->execute($alreadyPicked);
            $recommended = array_merge($recommended, $fallbackStmt->fetchAll());
        }

        // 5. User reviews count
        $rStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ? AND approved = 1");
        $rStmt->execute([$user_id]);
        $reviews_count = (int)$rStmt->fetchColumn();

        return [
            'tools'         => $tools,
            'tool_ids'      => $tool_ids,
            'count'         => count($tools),
            'total_cost'    => $total_cost,
            'free_count'    => $free_count,
            'paid_count'    => $paid_count,
            'recommended'   => $recommended,
            'reviews_count' => $reviews_count
        ];
    }
}

// Global SVG Lucide icons
require_once __DIR__ . '/partials/icons.php';
