<?php
// tools/measure.php
// Benchmark runner that measures query count, PHP execution time, and HTML size.

function measure_url(string $urlPath, array $sessionData = []): array {
    $script = __DIR__ . '/measure_worker.php';
    $payload = json_encode([
        'uri' => $urlPath,
        'session' => $sessionData,
    ]);
    
    $descriptors = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];
    
    $proc = proc_open("php " . escapeshellarg($script), $descriptors, $pipes, dirname(__DIR__));
    if (!is_resource($proc)) {
        throw new RuntimeException("Failed to open process");
    }
    
    fwrite($pipes[0], $payload);
    fclose($pipes[0]);
    
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    
    proc_close($proc);
    
    $res = json_decode($stdout, true);
    if (!$res) {
        echo "STDERR:\n" . $stderr . "\n";
        echo "STDOUT:\n" . $stdout . "\n";
        throw new RuntimeException("Invalid worker output for $urlPath");
    }
    return $res;
}

$pages = [
    'index.php (guest)'           => ['url' => '/index.php', 'session' => []],
    'dashboard.php (logged in)'    => ['url' => '/dashboard.php', 'session' => ['user_id' => 1, 'username' => 'testuser', 'email' => 'test@example.com']],
    'category/index.php (slug)'   => ['url' => '/category/index.php?slug=store-builders', 'session' => []],
    'tool/index.php (slug)'       => ['url' => '/tool/index.php?slug=shopify', 'session' => []],
];

echo str_repeat("=", 75) . "\n";
printf("%-28s | %-7s | %-10s | %-10s\n", "Page", "Queries", "PHP Time", "HTML Size");
echo str_repeat("-", 75) . "\n";

foreach ($pages as $label => $info) {
    // Warm up run
    measure_url($info['url'], $info['session']);
    
    // Measured runs (average of 3)
    $q = 0; $timeSum = 0; $bytes = 0;
    for ($i = 0; $i < 3; $i++) {
        $m = measure_url($info['url'], $info['session']);
        $q = $m['queries'];
        $timeSum += $m['time_ms'];
        $bytes = $m['html_bytes'];
    }
    $avgTime = $timeSum / 3;
    printf("%-28s | %-7d | %7.2f ms | %7.2f KB\n", $label, $q, $avgTime, $bytes / 1024);
}
echo str_repeat("=", 75) . "\n";
