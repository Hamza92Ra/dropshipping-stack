<?php
// ============================================================
// check_price_changes.php — run this on a schedule (see README)
// Compares each tool's current price to the last known price,
// and emails everyone subscribed to that tool if it changed.
// ============================================================

require_once __DIR__ . '/config.php'; // must provide $conn (mysqli)

$tools = $conn->query("SELECT id, name, price FROM tools")->fetch_all(MYSQLI_ASSOC);

foreach ($tools as $tool) {
    $tool_id = (int) $tool['id'];
    $current_price = (float) $tool['price'];

    $stmt = $conn->prepare("SELECT last_price FROM price_history WHERE tool_id = ?");
    $stmt->bind_param("i", $tool_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row === null) {
        // First time we've seen this tool — just record it, nothing to alert on yet
        $stmt = $conn->prepare("INSERT INTO price_history (tool_id, last_price) VALUES (?, ?)");
        $stmt->bind_param("id", $tool_id, $current_price);
        $stmt->execute();
        continue;
    }

    $old_price = (float) $row['last_price'];

    if (abs($old_price - $current_price) > 0.001) {
        // Price changed — notify subscribers
        notifySubscribers($conn, $tool_id, $tool['name'], $old_price, $current_price);

        // Update stored price
        $stmt = $conn->prepare("UPDATE price_history SET last_price = ? WHERE tool_id = ?");
        $stmt->bind_param("di", $current_price, $tool_id);
        $stmt->execute();
    }
}

function notifySubscribers($conn, $tool_id, $tool_name, $old_price, $new_price) {
    $stmt = $conn->prepare("
        SELECT u.email
        FROM price_alerts pa
        JOIN users u ON u.id = pa.user_id
        WHERE pa.tool_id = ? AND pa.is_active = 1
    ");
    $stmt->bind_param("i", $tool_id);
    $stmt->execute();
    $subscribers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($subscribers)) return;

    $direction = $new_price < $old_price ? 'dropped' : 'increased';
    $subject = "Price {$direction}: {$tool_name}";
    $body = "Heads up — {$tool_name}'s price just {$direction} from \${$old_price} to \${$new_price}.\n\n"
          . "Check it out: http://localhost/dropshipping/tool.php?id={$tool_id}\n\n"
          . "You're getting this because you turned on price alerts for this tool.";

    foreach ($subscribers as $sub) {
        // NOTE: PHP's mail() usually does NOT work out of the box on WAMP.
        // For real sending, use PHPMailer with an SMTP account (e.g. Gmail
        // app password) — see README.md for a drop-in swap.
        mail($sub['email'], $subject, $body);
    }
}

echo "Price check complete.\n";