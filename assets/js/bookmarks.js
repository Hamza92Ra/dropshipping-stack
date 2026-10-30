// ============================================
// DropshippingStack — Bookmarks Handler
// ============================================

async function toggleBookmark(toolId, btn) {
    if (!toolId) return;
    const isBookmarked = btn ? btn.classList.contains('bookmarked') : false;
    if (btn) {
        btn.classList.toggle('bookmarked', !isBookmarked);
        btn.setAttribute('aria-pressed', !isBookmarked ? 'true' : 'false');
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
        const res = await fetch('/bookmarks.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'toggle', tool_id: toolId, csrf_token: csrfToken })
        });
        const data = await res.json();
        if (data.error || !data.success) {
            // Revert
            if (btn) {
                btn.classList.toggle('bookmarked', isBookmarked);
                btn.setAttribute('aria-pressed', isBookmarked ? 'true' : 'false');
            }
            if (typeof showActionToast === 'function') {
                showActionToast(data.error || 'Please log in to save tools.');
            }
        }
    } catch (e) {
        if (btn) {
            btn.classList.toggle('bookmarked', isBookmarked);
            btn.setAttribute('aria-pressed', isBookmarked ? 'true' : 'false');
        }
        if (typeof showActionToast === 'function') {
            showActionToast('Failed to save bookmark.');
        }
    }
}
