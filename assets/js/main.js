// ============================================
// DropshippingStack — Main JS
// ============================================

// ── Mobile Menu ──────────────────────────
function toggleMenu() {
    document.getElementById('mobileNav').classList.toggle('open');
}

// ── Live Search Suggestions ───────────────
const searchInput = document.getElementById('searchInput');
const suggestions = document.getElementById('searchSuggestions');
let searchTimer;

if (searchInput) {
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        const q = this.value.trim();

        if (q.length < 2) {
            suggestions.classList.remove('show');
            suggestions.innerHTML = '';
            return;
        }

        searchTimer = setTimeout(() => {
            fetch('/api/search.php?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    if (data.length === 0) {
                        suggestions.classList.remove('show');
                        return;
                    }

                    suggestions.innerHTML = data.map(item => `
            <div class="suggestion-item" onclick="window.location='/tool/${item.slug}'">
              <span style="font-size:20px">${item.icon || '🔧'}</span>
              <div>
                <div class="suggestion-name">${item.name}</div>
                <div class="suggestion-cat">${item.category}</div>
              </div>
            </div>
          `).join('');

                    suggestions.classList.add('show');
                })
                .catch(() => suggestions.classList.remove('show'));
        }, 250);
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.classList.remove('show');
        }
    });
}

// ── Tool Card Filters ─────────────────────
function filterTools(type) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');

    document.querySelectorAll('.tool-card').forEach(card => {
        if (type === 'all') {
            card.style.display = '';
        } else if (type === 'free') {
            card.style.display = card.dataset.price === 'free' || card.dataset.price === 'freemium' ? '' : 'none';
        } else if (type === 'featured') {
            card.style.display = card.dataset.featured === '1' ? '' : 'none';
        } else {
            card.style.display = card.dataset.price === type ? '' : 'none';
        }
    });
}

// ── Star Rating Picker ────────────────────
document.querySelectorAll('.star-picker').forEach(picker => {
    const stars = picker.querySelectorAll('.star');
    const input = picker.querySelector('input[type=hidden]');

    stars.forEach((star, i) => {
        star.addEventListener('click', () => {
            input.value = i + 1;
            stars.forEach((s, j) => s.classList.toggle('active', j <= i));
        });

        star.addEventListener('mouseenter', () => {
            stars.forEach((s, j) => s.style.color = j <= i ? '#f59e0b' : '#e2e8f0');
        });
    });

    picker.addEventListener('mouseleave', () => {
        const val = parseInt(input.value) || 0;
        stars.forEach((s, j) => s.style.color = j < val ? '#f59e0b' : '#e2e8f0');
    });
});

// ── Plan selector on submit page ─────────
document.querySelectorAll('.plan-option').forEach(opt => {
    opt.addEventListener('click', function () {
        document.querySelectorAll('.plan-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input').checked = true;
    });
});

// ── Flash message auto-hide ───────────────
setTimeout(() => {
    document.querySelectorAll('.flash').forEach(el => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 4000);