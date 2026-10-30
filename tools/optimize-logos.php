<?php
/**
 * tools/optimize-logos.php
 * Generates 96x96 and 192x192 .webp copies next to every PNG in assets/img/logos/
 */

$logoDir = dirname(__DIR__) . '/assets/img/logos';
if (!is_dir($logoDir)) {
    die("Logo directory not found: $logoDir\n");
}

$files = glob($logoDir . '/*.png');
echo "Found " . count($files) . " PNG logos in $logoDir\n";

$hasGD = function_exists('imagecreatefrompng') && function_exists('imagewebp');
$hasMagick = false;
if (!$hasGD) {
    $whichMagick = trim((string)@shell_exec('which magick 2>/dev/null || which convert 2>/dev/null'));
    $hasMagick = !empty($whichMagick);
}

echo "Image engine: " . ($hasGD ? "GD" : ($hasMagick ? "ImageMagick ($whichMagick)" : "None")) . "\n";

$totalPngBytes = 0;
$totalWebpBytes = 0;
$sizes = [96, 192];

foreach ($files as $pngPath) {
    $pngSize = filesize($pngPath);
    $totalPngBytes += $pngSize;
    $baseName = pathinfo($pngPath, PATHINFO_FILENAME);
    
    echo sprintf("\nProcessing %-25s (%6.2f KB):\n", basename($pngPath), $pngSize / 1024);

    foreach ($sizes as $size) {
        $webpPath = $logoDir . '/' . $baseName . '-' . $size . '.webp';
        
        if ($hasGD) {
            $src = @imagecreatefrompng($pngPath);
            if ($src) {
                $origW = imagesx($src);
                $origH = imagesy($src);
                
                $dst = imagecreatetruecolor($size, $size);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                imagefilledrectangle($dst, 0, 0, $size, $size, $transparent);
                
                // Keep aspect ratio
                $ratio = min($size / $origW, $size / $origH);
                $newW = (int)round($origW * $ratio);
                $newH = (int)round($origH * $ratio);
                $dstX = (int)round(($size - $newW) / 2);
                $dstY = (int)round(($size - $newH) / 2);
                
                imagecopyresampled($dst, $src, $dstX, $dstY, 0, 0, $newW, $newH, $origW, $origH);
                imagewebp($dst, $webpPath, 88);
                imagedestroy($src);
                imagedestroy($dst);
            }
        } elseif ($hasMagick) {
            $cmd = sprintf(
                'magick %s -background none -gravity center -resize %dx%d -extent %dx%d -quality 88 %s 2>&1',
                escapeshellarg($pngPath),
                $size, $size,
                $size, $size,
                escapeshellarg($webpPath)
            );
            @shell_exec($cmd);
        }
        
        if (file_exists($webpPath)) {
            $wSize = filesize($webpPath);
            $totalWebpBytes += $wSize;
            $savedPct = $pngSize > 0 ? round((1 - ($wSize / $pngSize)) * 100, 1) : 0;
            echo sprintf("  -> %-30s %6.2f KB (saved %5.1f%%)\n", basename($webpPath), $wSize / 1024, $savedPct);
        } else {
            echo "  -> Failed to create $webpPath\n";
        }
    }
}

echo "\n" . str_repeat('=', 65) . "\n";
echo sprintf("Total original PNGs:  %8s bytes (%6.2f KB)\n", number_format($totalPngBytes), $totalPngBytes / 1024);
echo sprintf("Total generated WebPs: %8s bytes (%6.2f KB)\n", number_format($totalWebpBytes), $totalWebpBytes / 1024);
echo str_repeat('=', 65) . "\n";
