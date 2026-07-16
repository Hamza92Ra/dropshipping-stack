<?php if (!defined('APP')) die('Direct access not allowed'); ?>
<?php include 'partials/chatbot.php'; ?>
<footer class="site-footer">
    <div class="footer-inner">

        <div class="footer-brand">
            <a href="/dropshipping/" class="logo">
                <span class="logo-icon">⚡</span>
                <span class="logo-text"><?= SITE_NAME ?></span>
            </a>
            <p><?= SITE_TAGLINE ?></p>
            <p class="footer-disclaimer">
                Some links on this site are affiliate links. We may earn a commission at no extra cost to you.
            </p>
        </div>

        <div class="footer-links">
            <div class="footer-col">
                <h4>Categories</h4>
                <?php
                $fcats = $pdo->query("SELECT name, slug FROM categories ORDER BY sort_order")->fetchAll();
                foreach ($fcats as $c): ?>
                    <a href="/dropshipping/category/index.php?slug=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a>
                <?php endforeach; ?>
            </div>

            <div class="footer-col">
                <h4>Site</h4>
                <a href="/dropshipping/">Home</a>
                <a href="/dropshipping/submit.php">Submit a Tool</a>
                <a href="/dropshipping/about.php">About</a>
                <a href="/dropshipping/privacy.php">Privacy Policy</a>
                <a href="/dropshipping/affiliate-disclosure.php">Affiliate Disclosure</a>
            </div>

            <div class="footer-col">
                <h4>Top Tools</h4>
                <?php
                $top = $pdo->query("SELECT name, slug FROM tools WHERE is_active=1 ORDER BY clicks DESC LIMIT 5")->fetchAll();
                foreach ($top as $t): ?>
                    <a href="/dropshipping/tool/index.php?slug=<?= e($t['slug']) ?>"><?= e($t['name']) ?></a>
                <?php endforeach; ?>
            </div>
            <div class="footer-col">
                <h4>Connect</h4>
                <a href="mailto:you@yourdomain.com">Email us</a>
                <a href="https://www.instagram.com/hdro.pshipping/#" target="_blank" rel="noopener">Instagram</a>
                <a href="https://www.facebook.com/share/18ojiJyzhF/" target="_blank" rel="noopener">Facebook</a>
                <!-- <a href="https://tiktok.com/@yourhandle" target="_blank" rel="noopener">TikTok</a> -->
            </div>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
    </div>
</footer>
<!-- Email Popup -->
<div id="emailPopup" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:16px;padding:40px;max-width:440px;width:90%;text-align:center;position:relative">
        <button onclick="closePopup()" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:20px;cursor:pointer;color:#999">✕</button>
        <div style="font-size:48px;margin-bottom:12px">🎁</div>
        <h2 style="font-size:22px;font-weight:800;color:#0d0d1a;margin-bottom:8px">Get the Free Dropshipping Starter Kit</h2>
        <p style="font-size:14px;color:#9898b8;margin-bottom:24px;line-height:1.6">Join 2,000+ dropshippers getting weekly tool reviews, exclusive deals and the best affiliate programs straight to their inbox.</p>
        <input type="email" id="popupEmail" placeholder="your@email.com" style="width:100%;border:1.5px solid #e2e2eb;border-radius:8px;padding:11px 14px;font-size:14px;margin-bottom:10px;outline:none;font-family:inherit">
        <button onclick="submitEmail()" style="width:100%;background:#6366f1;color:#fff;border:none;padding:12px;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit">Get Free Access →</button>
        <p style="font-size:11px;color:#c0c0d8;margin-top:12px">No spam. Unsubscribe anytime.</p>
    </div>
</div>

<script>
    function closePopup() {
        document.getElementById('emailPopup').style.display = 'none';
        localStorage.setItem('popupClosed', '1');
    }

    function submitEmail() {
        const email = document.getElementById('popupEmail').value;
        if (!email || !email.includes('@')) {
            alert('Please enter a valid email address.');
            return;
        }
        document.getElementById('emailPopup').innerHTML = '<div style="background:#fff;border-radius:16px;padding:48px 40px;max-width:440px;width:90%;text-align:center"><div style="font-size:52px;margin-bottom:16px">✅</div><h2 style="font-size:20px;font-weight:800;color:#0d0d1a;margin-bottom:8px">You\'re in!</h2><p style="color:#9898b8;font-size:14px">Check your inbox for the Starter Kit.</p></div>';
        setTimeout(closePopup, 3000);
    }

    // Show popup after 30 seconds if not already closed
    if (!localStorage.getItem('popupClosed')) {
        setTimeout(() => {
            document.getElementById('emailPopup').style.display = 'flex';
        }, 30000);
    }
</script>
<script>
    function handleUpvote(btn) {
        if (btn.classList.contains('needs-login')) {
            window.location.href = '/dropshipping/login.php';
            return;
        }
        const toolId = btn.dataset.id;
        fetch('/dropshipping/upvote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'tool_id=' + toolId
            })
            .then(r => r.json())
            .then(data => {
                if (data.error === 'login_required') {
                    window.location.href = '/dropshipping/login.php';
                    return;
                }
                btn.querySelector('.upvote-count').textContent = data.count;
                btn.style.borderColor = data.action === 'added' ? 'var(--accent)' : 'rgba(255,255,255,0.1)';
                btn.style.color = data.action === 'added' ? 'var(--accent)' : 'rgba(255,255,255,0.6)';
            });
    }
</script>
<script src="/dropshipping/assets/js/main.js"></script>
<script src="/dropshipping/assets/js/chat-widget.js" data-api="/dropshipping/api/chat-api.php"></script>
</body>

</html>