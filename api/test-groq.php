<?php
/**
 * test-groq.php
 *
 * TEMPORARY DIAGNOSTIC FILE — upload this into the same /api folder as
 * chat-api.php, then open it directly in your browser, e.g.:
 *   https://yourdomain.com/api/test-groq.php
 *
 * It will print exactly what's going wrong. DELETE this file once you're
 * done, since it will show part of your API key on screen.
 */

header('Content-Type: text/plain');

echo "=== Step 1: Does llm-config.php exist and load? ===\n";
$configPath = __DIR__ . '/llm-config.php';
if (!file_exists($configPath)) {
    echo "FAIL: llm-config.php was not found at: $configPath\n";
    echo "-> Upload llm-config.php into this same folder and re-run this test.\n";
    exit;
}
require $configPath;
echo "OK: llm-config.php found and loaded.\n\n";

echo "=== Step 2: Is GROQ_API_KEY set to something real? ===\n";
if (!defined('GROQ_API_KEY') || GROQ_API_KEY === '' || strpos(GROQ_API_KEY, 'REPLACE') !== false) {
    echo "FAIL: GROQ_API_KEY is missing or still the placeholder value.\n";
    echo "-> Get a real key from https://console.groq.com/keys and put it in llm-config.php\n";
    exit;
}
$masked = substr(GROQ_API_KEY, 0, 6) . str_repeat('*', max(0, strlen(GROQ_API_KEY) - 10)) . substr(GROQ_API_KEY, -4);
echo "OK: GROQ_API_KEY is set (masked): $masked\n\n";

echo "=== Step 3: Is the curl extension available? ===\n";
if (!function_exists('curl_init')) {
    echo "FAIL: PHP's curl extension is not enabled on this server.\n";
    echo "-> Ask your host to enable the php-curl / ext-curl extension.\n";
    exit;
}
echo "OK: curl extension is available.\n\n";

echo "=== Step 4: Can this server actually reach Groq? ===\n";
$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . GROQ_API_KEY,
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'llama-3.3-70b-versatile',
        'max_tokens' => 50,
        'messages' => [['role' => 'user', 'content' => 'Say hello in one short sentence.']],
    ]),
    CURLOPT_TIMEOUT => 20,
    CURLOPT_VERBOSE => false,
]);
$response = curl_exec($ch);
$curlErrNo = curl_errno($ch);
$curlErr = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlErrNo || !$response) {
    echo "FAIL: The connection to Groq itself failed.\n";
    echo "curl error number: $curlErrNo\n";
    echo "curl error message: $curlErr\n\n";
    echo "COMMON MEANINGS:\n";
    echo "- 'Could not resolve host' -> DNS/network issue, or outbound connections blocked by your host.\n";
    echo "- 'SSL certificate problem' -> Your server's CA certificate bundle is outdated; ask your host to update it, or enable ext-openssl.\n";
    echo "- 'Connection timed out' / 'Connection refused' -> Your host is blocking outbound HTTPS to external APIs. Contact their support and ask them to allow outbound connections to api.groq.com on port 443.\n";
    exit;
}

echo "OK: Connection succeeded. HTTP status code: $httpCode\n\n";

echo "=== Step 5: Raw response from Groq ===\n";
echo $response . "\n\n";

$data = json_decode($response, true);
if ($httpCode === 200 && !empty($data['choices'][0]['message']['content'])) {
    echo "=== SUCCESS ===\n";
    echo "Groq replied: " . $data['choices'][0]['message']['content'] . "\n";
    echo "\nEverything works! You can delete this test file now and go back to chat-api.php.\n";
} elseif ($httpCode === 401) {
    echo "=== FAIL: 401 Unauthorized ===\n";
    echo "-> Your API key is invalid, revoked, or mistyped. Generate a new one at https://console.groq.com/keys\n";
} elseif ($httpCode === 429) {
    echo "=== FAIL: 429 Rate limited ===\n";
    echo "-> You've hit Groq's free-tier limit. Wait a minute and try again.\n";
} else {
    echo "=== FAIL: Unexpected response, see raw response above for the exact error message from Groq. ===\n";
}