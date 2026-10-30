<?php
// tools/measure_worker.php
$input = json_decode(file_get_contents("php://stdin"), true);
if (!$input) exit(1);

$uri = $input['uri'];
$parsed = parse_url($uri);
$path = ltrim($parsed['path'], '/');
$queryString = $parsed['query'] ?? '';

// Set server vars
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = $uri;
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['HTTPS'] = '';
$_GET = [];
if ($queryString) {
    parse_str($queryString, $_GET);
}

// Session simulation
session_start();
$_SESSION = $input['session'] ?? [];

// Define TrackingPDO
class TrackingPDO extends PDO {
    public static int $count = 0;
    public static array $log = [];
    #[\ReturnTypeWillChange]
    public function query(...$args) {
        self::$count++;
        self::$log[] = $args[0] ?? '';
        return parent::query(...$args);
    }
    #[\ReturnTypeWillChange]
    public function prepare(...$args) {
        self::$count++;
        self::$log[] = $args[0] ?? '';
        return parent::prepare(...$args);
    }
    #[\ReturnTypeWillChange]
    public function exec(...$args) {
        self::$count++;
        self::$log[] = $args[0] ?? '';
        return parent::exec(...$args);
    }
}

// Start timer and output buffer
$targetFile = dirname(__DIR__) . '/' . $path;
if (!file_exists($targetFile)) {
    echo json_encode(['error' => "File not found: $targetFile"]);
    exit(1);
}

$start = microtime(true);
ob_start();

try {
    include $targetFile;
} catch (Throwable $e) {
    ob_end_clean();
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    exit(1);
}

$output = ob_get_clean();
$end = microtime(true);

echo json_encode([
    'queries'    => TrackingPDO::$count,
    'time_ms'    => round(($end - $start) * 1000, 2),
    'html_bytes' => strlen($output),
    'log'        => TrackingPDO::$log
]);
