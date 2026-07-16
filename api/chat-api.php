<?php

/**
 * /api/chat-api.php
 *
 * Same in/out shape as before ({ message, history } -> { reply }), so
 * chat-widget.js mostly doesn't need to change — EXCEPT it must now send
 * cookies with the request (credentials: 'include') so this script can
 * see the user's login session. See notes at the bottom of this file.
 *
 * Two changes from your original version:
 *   1. AUTH GATE — only logged-in users get a response.
 *   2. GENERAL FALLBACK — instead of a canned "I'm not sure" message,
 *      unmatched questions are now sent to Claude, along with a summary
 *      of your live tool catalog so it stays grounded in real data.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
// NOTE: '*' does not work with cookie-based (credentialed) requests.
// If the widget lives on the same domain as this script, delete the next
// two lines entirely. If it's cross-origin, replace '*' with your exact
// site origin (e.g. https://yourdomain.com) and keep the credentials header.
header('Access-Control-Allow-Origin: https://yourdomain.com');
header('Access-Control-Allow-Credentials: true');

require __DIR__ . '/db-config.php';
require __DIR__ . '/llm-config.php'; // new file — holds your free Groq API key

// ---------------------------------------------------------------
// AUTH GATE — only registered, logged-in users can use the chatbot
// Adjust 'user_id' below if your login code stores the session under a
// different key.
// ---------------------------------------------------------------
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'reply' => "Please log in to chat with our assistant.",
        'auth_required' => true,
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$history = is_array($input['history'] ?? null) ? $input['history'] : [];

if ($message === '') {
    echo json_encode(['reply' => "What would you like to know about dropshipping tools?"]);
    exit;
}

// ---------------------------------------------------------------
// load tools live from the database (active ones only)
// ---------------------------------------------------------------
try {
    $pdo = ds_get_pdo();
    $stmt = $pdo->query("
        SELECT t.name, t.slug, t.tagline, t.description, t.pros, t.cons,
               t.commission, t.price_type, t.price_from, t.rating, t.review_count,
               t.affiliate_link, t.website_url, t.is_featured,
               c.name AS category_name
        FROM tools t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.is_active = 1
    ");
    $tools = $stmt->fetchAll();
} catch (Exception $e) {
    echo json_encode(['reply' => "I'm having trouble reaching the tool database right now — please try again shortly."]);
    exit;
}

$categories = array_values(array_unique(array_filter(array_column($tools, 'category_name'))));

// ---------------------------------------------------------------
// helpers
// ---------------------------------------------------------------
function priceLabel($t)
{
    if ($t['price_type'] === 'free') return 'Free';
    if ($t['price_type'] === 'freemium') return 'Freemium';
    $from = floatval($t['price_from']);
    return $from > 0 ? 'From $' . rtrim(rtrim(number_format($from, 2), '0'), '.') . '/mo' : 'Paid';
}

function findToolsInMessage($msg, $tools)
{
    $found = [];
    foreach ($tools as $t) {
        if (stripos($msg, $t['name']) !== false) $found[] = $t;
    }
    return $found;
}

function toolLine($t)
{
    $tagline = $t['tagline'] ?: $t['description'];
    return "**{$t['name']}** ({$t['category_name']}, " . priceLabel($t) . ") — {$tagline}";
}

function describeTool($t)
{
    $desc = $t['description'] ?: $t['tagline'];
    $line = "{$t['name']} is in our {$t['category_name']} category, priced " . priceLabel($t) . ". {$desc}";
    if (!empty($t['rating']) && floatval($t['rating']) > 0) {
        $line .= " Rated {$t['rating']}/5";
        if (!empty($t['review_count'])) $line .= " ({$t['review_count']} reviews)";
        $line .= ".";
    }
    if (!empty($t['commission'])) $line .= " Affiliate commission: {$t['commission']}.";
    return $line;
}

function prosConsLine($t)
{
    $reply = "{$t['name']}:\n";
    if (!empty($t['pros'])) $reply .= "👍 Pros: {$t['pros']}\n";
    if (!empty($t['cons'])) $reply .= "👎 Cons: {$t['cons']}\n";
    if (empty($t['pros']) && empty($t['cons'])) $reply .= "I don't have pros/cons listed for this one yet — check its review page for more detail.";
    return trim($reply);
}

function compareTools($a, $b)
{
    $reply = "Here's a quick comparison:\n\n";
    $reply .= "• {$a['name']}: " . priceLabel($a) . " — " . ($a['tagline'] ?: $a['description']) . "\n";
    $reply .= "• {$b['name']}: " . priceLabel($b) . " — " . ($b['tagline'] ?: $b['description']) . "\n\n";
    if ($a['category_name'] === $b['category_name']) {
        $reply .= "Since they're both {$a['category_name']} tools, it mostly comes down to budget and which features matter most. Check the Compare page for a full side-by-side.";
    } else {
        $reply .= "These serve different purposes ({$a['category_name']} vs {$b['category_name']}), so you may want both rather than choosing one.";
    }
    return $reply;
}

function buildCatalogSummary($tools)
{
    $lines = [];
    foreach ($tools as $t) {
        $tagline = $t['tagline'] ?: $t['description'];
        $lines[] = "- {$t['name']} ({$t['category_name']}, " . priceLabel($t) . "): {$tagline}";
    }
    return implode("\n", $lines);
}

/**
 * Sends the question to a free-tier LLM (Groq, running Llama 3.3 70B),
 * grounded with the live tool catalog and recent conversation history,
 * and returns the reply text. Groq's free tier needs no credit card —
 * just an API key from console.groq.com/keys — and is rate-limited
 * (roughly 30 requests/min, ~1,000 requests/day) rather than billed.
 */
function askLLM($message, $history, $tools)
{
    $catalog = buildCatalogSummary($tools);

    $systemPrompt = "You are the assistant embedded on a dropshipping tools comparison website. "
        . "Answer questions directly and conversationally, in a few short paragraphs at most. "
        . "You can answer general questions about dropshipping, e-commerce, and running an "
        . "online store, not just questions about the tools below. Only claim a tool is "
        . "offered on this site if it appears in the list; for anything else, answer from "
        . "general knowledge and make clear it isn't one this site lists.\n\n"
        . "TOOLS LISTED ON THIS SITE:\n{$catalog}";

    // Keep only the last few turns to stay well within the free rate limit.
    $recentHistory = array_slice($history, -6);
    $messages = [['role' => 'system', 'content' => $systemPrompt]];
    foreach ($recentHistory as $turn) {
        $role = $turn['role'] ?? '';
        $content = $turn['content'] ?? '';
        if (in_array($role, ['user', 'assistant'], true) && $content !== '') {
            $messages[] = ['role' => $role, 'content' => $content];
        }
    }
    $messages[] = ['role' => 'user', 'content' => $message];

    $payload = json_encode([
        'model' => 'openai/gpt-oss-120b',
        'max_tokens' => 600,
        'messages' => $messages,
    ]);

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY,
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200) {
        error_log('Groq API error: HTTP ' . $httpCode . ' | ' . $response);
        return "I'm having trouble reaching my brain right now — please try again in a moment.";
    }

    if ($curlErr || !$response) {
        // TEMPORARY: logs the real reason to your PHP error log so we can
        // diagnose it. Remove this error_log line once it's working.
        error_log('Groq curl failure: ' . $curlErr . ' | HTTP code: ' . $httpCode);
        return "I'm having trouble reaching my brain right now — please try again in a moment.";
    }

    if ($httpCode === 429) {
        return "I'm getting a lot of questions right now and hit my free-tier limit — please try again in a minute.";
    }

    $data = json_decode($response, true);
    if (!empty($data['choices'][0]['message']['content'])) {
        return trim($data['choices'][0]['message']['content']);
    }
    return "Sorry, I couldn't come up with an answer to that — could you rephrase it?";
}

$lower = strtolower($message);

// ---------------------------------------------------------------
// 1. greetings
// ---------------------------------------------------------------
if (preg_match('/^(hi|hello|hey|yo|sup)\b/i', trim($message))) {
    echo json_encode(['reply' => "Hey! I can help you find a tool, compare options, or answer general dropshipping questions. Try asking something like \"compare Shopify vs Zendrop\" or \"how do I pick a supplier?\""]);
    exit;
}

// ---------------------------------------------------------------
// 2. "what is a stack"
// ---------------------------------------------------------------
$mentioned = findToolsInMessage($message, $tools);
if (preg_match('/\bstack\b/i', $lower) && count($mentioned) === 0) {
    echo json_encode(['reply' => "A \"stack\" is just the combination of tools you use to run your store — e.g. a store builder, a supplier, and a marketing tool. Use our Stack Builder page to put one together and see the total monthly cost."]);
    exit;
}

// ---------------------------------------------------------------
// 3. pros / cons of a specific tool
// ---------------------------------------------------------------
if (count($mentioned) === 1 && preg_match('/\b(pros|cons|good|bad|downsides?|drawbacks?)\b/i', $lower)) {
    echo json_encode(['reply' => prosConsLine($mentioned[0])]);
    exit;
}

// ---------------------------------------------------------------
// 4. compare two tools
// ---------------------------------------------------------------
$isCompareIntent = preg_match('/\b(vs|versus|compare|or)\b/i', $lower);
if ($isCompareIntent && count($mentioned) >= 2) {
    echo json_encode(['reply' => compareTools($mentioned[0], $mentioned[1])]);
    exit;
}

// ---------------------------------------------------------------
// 5. asking about specific tool(s)
// ---------------------------------------------------------------
if (count($mentioned) === 1) {
    echo json_encode(['reply' => describeTool($mentioned[0])]);
    exit;
}
if (count($mentioned) >= 2) {
    $reply = "Here's what I have on those:\n\n" . implode("\n", array_map('toolLine', $mentioned));
    echo json_encode(['reply' => $reply]);
    exit;
}

// ---------------------------------------------------------------
// 6. top rated / best tools
// ---------------------------------------------------------------
if (preg_match('/\b(best|top|highest rated|top.?rated)\b/i', $lower)) {
    $rated = array_filter($tools, fn($t) => !empty($t['rating']) && floatval($t['rating']) > 0);
    usort($rated, fn($a, $b) => floatval($b['rating']) <=> floatval($a['rating']));
    $top = array_slice($rated, 0, 3);
    $reply = $top
        ? "Top rated tools right now:\n\n" . implode("\n", array_map('toolLine', $top))
        : "We don't have enough reviews yet to rank tools by rating — check the Compare page in the meantime.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// ---------------------------------------------------------------
// 7. budget / price questions ("under $50", "cheap", "free")
// ---------------------------------------------------------------
if (preg_match('/\$?(\d+)\s*\/?\s*(mo|month)?/i', $lower, $m) && preg_match('/\b(under|below|less than|cheaper than)\b/i', $lower)) {
    $budget = (int)$m[1];
    $matches = array_filter($tools, function ($t) use ($budget) {
        if ($t['price_type'] === 'free' || $t['price_type'] === 'freemium') return true;
        return floatval($t['price_from']) <= $budget;
    });
    $reply = $matches
        ? "Tools under \${$budget}/mo:\n\n" . implode("\n", array_map('toolLine', $matches))
        : "I don't have anything listed under \${$budget}/mo right now — try the Calculator page to explore options.";
    echo json_encode(['reply' => $reply]);
    exit;
}
if (preg_match('/\b(free|cheap|cheapest|budget)\b/i', $lower)) {
    $matches = array_filter($tools, fn($t) => in_array($t['price_type'], ['free', 'freemium']));
    $reply = $matches
        ? "These are free / freemium to start:\n\n" . implode("\n", array_map('toolLine', $matches))
        : "Nothing fully free right now, but check the Calculator page to compare costs.";
    echo json_encode(['reply' => $reply]);
    exit;
}

// ---------------------------------------------------------------
// 8. category questions ("marketing tools", "any suppliers?")
// ---------------------------------------------------------------
foreach ($categories as $cat) {
    $catLower = strtolower($cat);
    if (stripos($lower, $catLower) !== false || stripos($lower, rtrim($catLower, 's')) !== false) {
        $matches = array_filter($tools, fn($t) => $t['category_name'] === $cat);
        $reply = $matches
            ? "{$cat} tools we have listed:\n\n" . implode("\n", array_map('toolLine', $matches))
            : "I don't have any {$cat} tools listed yet.";
        echo json_encode(['reply' => $reply]);
        exit;
    }
}

// ---------------------------------------------------------------
// 9. commission / affiliate questions
// ---------------------------------------------------------------
if (preg_match('/\b(commission|affiliate|earn)\b/i', $lower)) {
    $withCommission = array_filter($tools, fn($t) => !empty($t['commission']));
    if ($withCommission) {
        $reply = "Some tools have affiliate commissions if you sign up through us:\n\n";
        foreach ($withCommission as $t) $reply .= "• {$t['name']}: {$t['commission']}\n";
        $reply = trim($reply);
    } else {
        $reply = "I don't see commission info listed yet — check each tool's page for details.";
    }
    echo json_encode(['reply' => $reply]);
    exit;
}

// ---------------------------------------------------------------
// 10. general fallback — anything else goes to Claude, grounded in
//     the live tool catalog above.
// ---------------------------------------------------------------
echo json_encode(['reply' => askLLM($message, $history, $tools)]);
