// ============================================================
// Price alert checkbox behavior (used on dashboard.php)
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const toolId = checkbox.getAttribute('data-tool-id');
            fetch('price_alerts.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tool_id: toolId, enable: checkbox.checked })
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert(data.error || 'Something went wrong.');
                        checkbox.checked = !checkbox.checked; // revert on failure
                    }
                });
        });
    });
});