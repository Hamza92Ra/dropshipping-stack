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

/**
 * Simple file-based query/data cache with TTL and safe fallback
 *
 * @param string $key Cache key (alphanumeric and dashes/underscores)
 * @param int $ttl Time to live in seconds (e.g. 300 to 600)
 * @param callable $callback Function that returns fresh data on cache miss
 * @return mixed
 */
if (!function_exists('cache_remember')) {
    function cache_remember(string $key, int $ttl, callable $callback)
    {
        $cacheDir = __DIR__ . '/cache';
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        $cacheFile = $cacheDir . '/' . $safeKey . '.cache';

        // 1. Try reading from cache file if valid
        if (file_exists($cacheFile)) {
            $mtime = @filemtime($cacheFile);
            if ($mtime && (time() - $mtime < $ttl)) {
                $content = @file_get_contents($cacheFile);
                if ($content !== false) {
                    $unserialized = @unserialize($content);
                    if ($unserialized !== false || $content === serialize(false)) {
                        return $unserialized;
                    }
                }
            }
        }

        // 2. Fetch fresh data via callback
        $data = $callback();

        // 3. Attempt to save to cache file (safe fallback if unwritable)
        try {
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0755, true);
            }
            if (is_dir($cacheDir) && is_writable($cacheDir)) {
                @file_put_contents($cacheFile, serialize($data), LOCK_EX);
            }
        } catch (Throwable $e) {
            // Silently fall back to fresh data
        }

        return $data;
    }
}

/**
 * Resolves the logo URL for a tool to an absolute path like /assets/img/logos/<file>
 * Resolution order:
 * 1. logo column (cleans any /dropshipping/ prefix, checks file_exists against __DIR__)
 * 2. domain-without-dots pattern from website_url
 * 3. clean slug pattern
 * 4. fallback: /assets/img/logos/placeholder.svg
 *
 * @param array $tool Tool row/data array
 * @return string Absolute URL path
 */
if (!function_exists('tool_logo_url')) {
    function tool_logo_url(array $tool): string
    {
        $raw = $tool['logo_url'] ?? $tool['icon'] ?? '';
        if ($raw) {
            $clean = preg_replace('#^/dropshipping/#', '/', $raw);
            $basename = basename($clean);
            if ($basename && file_exists(__DIR__ . '/assets/img/logos/' . $basename)) {
                return '/assets/img/logos/' . $basename;
            }
        }

        if (!empty($tool['website_url'])) {
            $host = parse_url($tool['website_url'], PHP_URL_HOST);
            $clean_domain = strtolower(preg_replace('/[^a-z0-9]/i', '', preg_replace('/^www\./i', '', $host ?: '')));
            if ($clean_domain && file_exists(__DIR__ . '/assets/img/logos/' . $clean_domain . '.png')) {
                return '/assets/img/logos/' . $clean_domain . '.png';
            }
        }

        if (!empty($tool['slug'])) {
            $clean_slug = strtolower(preg_replace('/[^a-z0-9]/i', '', $tool['slug']));
            if ($clean_slug && file_exists(__DIR__ . '/assets/img/logos/' . $clean_slug . '.png')) {
                return '/assets/img/logos/' . $clean_slug . '.png';
            }
        }

        return '/assets/img/logos/placeholder.svg';
    }
}

/**
 * Resolves responsive WebP srcset for tool logo if .webp versions exist
 *
 * @param array $tool Tool row/data array
 * @return string E.g. '/assets/img/logos/shopifycom-96.webp 1x, /assets/img/logos/shopifycom-192.webp 2x'
 */
if (!function_exists('tool_logo_srcset')) {
    function tool_logo_srcset(array $tool): string
    {
        $pngUrl = tool_logo_url($tool);
        if (!$pngUrl || substr($pngUrl, -4) !== '.png') {
            return '';
        }
        $baseName = pathinfo($pngUrl, PATHINFO_FILENAME);
        $w96 = __DIR__ . '/assets/img/logos/' . $baseName . '-96.webp';
        $w192 = __DIR__ . '/assets/img/logos/' . $baseName . '-192.webp';

        if (file_exists($w96) && file_exists($w192)) {
            return "/assets/img/logos/{$baseName}-96.webp 1x, /assets/img/logos/{$baseName}-192.webp 2x";
        } elseif (file_exists($w96)) {
            return "/assets/img/logos/{$baseName}-96.webp 1x";
        }
        return '';
    }
}

/**
 * Clear cached query files (e.g. on admin tool/review mutations)
 */
if (!function_exists('cache_clear')) {
    function cache_clear(): void
    {
        $cacheDir = __DIR__ . '/cache';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*.cache');
            if ($files) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }
        }
    }
}

/**
 * Resolves an asset URL, pointing to its minified .min version in assets/dist/
 * if it exists, along with automatic filemtime cache-busting.
 *
 * @param string $path E.g. '/assets/css/style.css'
 * @return string E.g. '/assets/dist/style.min.css?v=12345678'
 */
if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $cleanPath = '/' . ltrim($path, '/');
        $ext = pathinfo($cleanPath, PATHINFO_EXTENSION);
        $filename = pathinfo($cleanPath, PATHINFO_FILENAME);
        $minDistPath = '/assets/dist/' . $filename . '.min.' . $ext;
        $minDiskPath = __DIR__ . $minDistPath;

        if (file_exists($minDiskPath)) {
            return $minDistPath . '?v=' . filemtime($minDiskPath);
        }

        $origDiskPath = __DIR__ . $cleanPath;
        $mtime = file_exists($origDiskPath) ? filemtime($origDiskPath) : time();
        return $cleanPath . '?v=' . $mtime;
    }
}


