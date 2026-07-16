<?php
// ============================================================
// dashboard.php — "My Saved Tools" page
// Link to this from your nav/sidebar once user is logged in.
// ============================================================

session_start();
require_once __DIR__ . '/config.php'; // must provide $conn (mysqli)

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // adjust to your login page
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT t.id, t.name, t.price, t.url
    FROM bookmarks b
    JOIN tools t ON t.id = b.tool_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$savedTools = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Check which of the saved tools already have a price alert active
$stmt = $conn->prepare("SELECT tool_id FROM price_alerts WHERE user_id = ? AND is_active = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$alertRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$activeAlerts = array_column($alertRows, 'tool_id');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Saved Tools</title>
    <link rel="stylesheet" href="assets/darkmode.css">
    <!-- include your site's normal stylesheet(s) here too -->
</head>
<body>

<h1>My Saved Tools</h1>

<?php if (empty($savedTools)): ?>
    <p class="text-muted">You haven't saved any tools yet. Browse the comparison page and hit "Save" on anything you want to track.</p>
<?php else: ?>
    <div class="dashboard-grid">
        <?php foreach ($savedTools as $tool): ?>
            <div class="card tool-card">
                <h3><?= htmlspecialchars($tool['name']) ?></h3>
                <p>$<?= htmlspecialchars($tool['price']) ?>/mo</p>
                <a href="<?= htmlspecialchars($tool['url']) ?>" target="_blank">Visit site</a>
                <br>
                <label>
                    <input type="checkbox"
                           class="alert-toggle"
                           data-tool-id="<?= $tool['id'] ?>"
                           <?= in_array($tool['id'], $activeAlerts) ? 'checked' : '' ?>>
                    Notify me if the price changes
                </label>
                <button class="bookmark-btn saved" data-tool-id="<?= $tool['id'] ?>">❤️ Remove</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script src="assets/darkmode.js"></script>
<script src="assets/bookmarks.js"></script>
<script src="assets/price_alerts.js"></script>
</body>
</html>