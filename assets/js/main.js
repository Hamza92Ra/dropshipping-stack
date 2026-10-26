// ============================================
// DropshippingStack — Main JS
// ============================================

// ── Mobile Menu ──────────────────────────
function toggleMenu() {
    document.getElementById('mobileNav').classList.toggle('open');
}

// ── Spotlight Live Search ──────────────────
(function () {
    const searchInput = document.getElementById('searchInput');
    const suggestions = document.getElementById('searchSuggestions');
    const searchBox = document.getElementById('searchBoxContainer');
    const clearBtn = document.getElementById('searchClearBtn');
    const searchKbd = document.getElementById('searchKbd');
    const searchForm = document.getElementById('searchForm');

    if (!searchInput || !suggestions) return;

    let searchTimer = null;
    let abortController = null;
    let activeIndex = -1;
    let currentResults = [];

    // Detect OS and adjust keyboard shortcut badge
    if (searchKbd) {
        const isMac = /(Mac|iPhone|iPod|iPad)/i.test(navigator.platform || navigator.userAgent);
        searchKbd.textContent = isMac ? '⌘K' : 'Ctrl K';
        searchKbd.setAttribute('title', isMac ? 'Press ⌘K or / to search' : 'Press Ctrl+K or / to search');
    }

    // Global keyboard shortcuts (Cmd/Ctrl + K or / to focus search)
    document.addEventListener('keydown', function (e) {
        const isCmdK = (e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k';
        const isSlash = e.key === '/' && !e.metaKey && !e.ctrlKey && !e.altKey;

        if (isCmdK || isSlash) {
            const activeEl = document.activeElement;
            const isTyping = activeEl && (
                activeEl.tagName === 'INPUT' ||
                activeEl.tagName === 'TEXTAREA' ||
                activeEl.isContentEditable
            );

            // If pressing '/' while typing inside another field, don't hijack
            if (isSlash && isTyping) return;

            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });

    function syncSearchBoxState() {
        const val = searchInput.value.trim();
        if (searchBox) {
            searchBox.classList.toggle('has-value', val.length > 0);
        }
    }

    function hideSuggestions() {
        suggestions.classList.remove('show');
        suggestions.innerHTML = '';
        searchInput.setAttribute('aria-expanded', 'false');
        searchInput.removeAttribute('aria-activedescendant');
        activeIndex = -1;
        currentResults = [];
        if (searchBox) searchBox.classList.remove('is-loading');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function highlightMatch(text, query) {
        if (!text) return '';
        if (!query) return escapeHtml(text);
        const safeQuery = query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        if (!safeQuery) return escapeHtml(text);
        const regex = new RegExp(`(${safeQuery})`, 'gi');
        const parts = text.split(regex);
        return parts.map(part => {
            if (part.toLowerCase() === query.trim().toLowerCase()) {
                return `<mark class="search-highlight">${escapeHtml(part)}</mark>`;
            }
            return escapeHtml(part);
        }).join('');
    }

    function updateActiveSuggestion() {
        const items = suggestions.querySelectorAll('.suggestion-item');
        items.forEach((item, idx) => {
            const isActive = idx === activeIndex;
            item.classList.toggle('active-suggestion', isActive);
            item.setAttribute('aria-selected', isActive ? 'true' : 'false');
            if (isActive) {
                item.scrollIntoView({ block: 'nearest' });
                searchInput.setAttribute('aria-activedescendant', `suggestion-item-${idx}`);
            }
        });

        if (activeIndex === -1) {
            searchInput.removeAttribute('aria-activedescendant');
        }
    }

    function renderSuggestions(data, query) {
        currentResults = data || [];
        activeIndex = -1;

        if (currentResults.length === 0) {
            suggestions.innerHTML = `
                <div class="suggestion-empty" style="padding: 24px; text-align: center;">
                    <div class="suggestion-empty-icon" style="color:var(--muted);margin-bottom:8px">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                    <div class="suggestion-empty-title" style="font-weight:700;color:var(--text);margin-bottom:4px">No tools found for "${escapeHtml(query)}"</div>
                    <p class="suggestion-empty-desc" style="font-size:12.5px;color:var(--muted);margin-bottom:12px">Try searching by category, feature, or keyword like <em>Shopify, supplier, ads, analytics</em>.</p>
                    <a href="/search.php?q=${encodeURIComponent(query)}" class="suggestion-empty-action" style="font-size:13px;font-weight:600;color:var(--accent)">Search all directory resources →</a>
                </div>
            `;
            suggestions.classList.add('show');
            searchInput.setAttribute('aria-expanded', 'true');
            return;
        }

        const itemsHtml = currentResults.map((item, idx) => {
            let priceLabel = 'Paid';
            if (item.price_type === 'free') {
                priceLabel = 'Free';
            } else if (item.price_type === 'freemium') {
                priceLabel = 'Freemium';
            } else if (item.price_from && parseFloat(item.price_from) > 0) {
                priceLabel = `$${Math.round(parseFloat(item.price_from))}/mo`;
            }

            const logoUrl = item.domain
                ? `https://img.logo.dev/${encodeURIComponent(item.domain)}?token=pk_IPkJggMaTFC35CqF8UE3hg&size=64`
                : '';

            return `
                <div class="suggestion-item"
                     id="suggestion-item-${idx}"
                     role="option"
                     aria-selected="false"
                     data-index="${idx}"
                     data-url="/tool/${escapeHtml(item.slug)}">
                    <div class="suggestion-logo-wrap">
                        ${logoUrl ? `
                            <img class="suggestion-logo"
                                 src="${logoUrl}"
                                 alt=""
                                 loading="lazy"
                                 onload="this.style.display='block'; if (this.nextElementSibling) this.nextElementSibling.style.display='none';"
                                 onerror="this.style.display='none'; if (this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                        ` : ''}
                        <div class="suggestion-fallback-icon" style="${logoUrl ? 'display:none;' : ''};color:var(--accent);display:flex;align-items:center;justify-content:center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        </div>
                    </div>
                    <div class="suggestion-content">
                        <div class="suggestion-topline">
                            <span class="suggestion-name">${highlightMatch(item.name, query)}</span>
                            ${item.rating && parseFloat(item.rating) > 0 ? `
                                <span class="suggestion-rating" style="display:inline-flex;align-items:center;gap:3px;"><svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> ${escapeHtml(item.rating_formatted)}</span>
                            ` : ''}
                        </div>
                        <div class="suggestion-tagline">${highlightMatch(item.tagline || '', query)}</div>
                    </div>
                    <div class="suggestion-meta">
                        <span class="suggestion-badge badge-${escapeHtml(item.price_type || 'paid')}">${priceLabel}</span>
                        ${item.category ? `<span class="suggestion-cat-pill">${escapeHtml(item.category)}</span>` : ''}
                        <svg class="suggestion-arrow" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 5 14 10 9 15"></polyline>
                        </svg>
                    </div>
                </div>
            `;
        }).join('');

        suggestions.innerHTML = `
            <div class="suggestions-header">
                <span>Tools &amp; Services</span>
                <span class="suggestions-count">${currentResults.length} found</span>
            </div>
            <div class="suggestions-list" role="group">
                ${itemsHtml}
            </div>
            <div class="suggestions-footer">
                <div class="suggestions-hints">
                    <span><kbd class="hint-kbd">↑</kbd><kbd class="hint-kbd">↓</kbd> navigate</span>
                    <span><kbd class="hint-kbd">↵</kbd> select</span>
                    <span><kbd class="hint-kbd">esc</kbd> close</span>
                </div>
                <a href="/search.php?q=${encodeURIComponent(query)}" class="suggestions-see-all">
                    See all results for "<strong>${escapeHtml(query)}</strong>" →
                </a>
            </div>
        `;

        // Attach item click and hover listeners
        const suggestionItems = suggestions.querySelectorAll('.suggestion-item');
        suggestionItems.forEach(el => {
            el.addEventListener('click', function () {
                const url = this.getAttribute('data-url');
                if (url) window.location.href = url;
            });
            el.addEventListener('mouseenter', function () {
                const idx = parseInt(this.getAttribute('data-index'), 10);
                if (!isNaN(idx)) {
                    activeIndex = idx;
                    updateActiveSuggestion();
                }
            });
        });

        suggestions.classList.add('show');
        searchInput.setAttribute('aria-expanded', 'true');
    }

    function executeSearch() {
        const q = searchInput.value.trim();
        if (q.length < 2) {
            hideSuggestions();
            return;
        }

        if (searchBox) searchBox.classList.add('is-loading');

        if (abortController) {
            abortController.abort();
        }
        abortController = new AbortController();

        fetch('/api/search.php?q=' + encodeURIComponent(q), { signal: abortController.signal })
            .then(r => {
                if (!r.ok) throw new Error('Search request failed');
                return r.json();
            })
            .then(data => {
                if (searchBox) searchBox.classList.remove('is-loading');
                renderSuggestions(data, q);
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                if (searchBox) searchBox.classList.remove('is-loading');
                hideSuggestions();
            });
    }

    // Input events
    searchInput.addEventListener('input', function () {
        syncSearchBoxState();
        clearTimeout(searchTimer);
        const q = this.value.trim();

        if (q.length < 2) {
            hideSuggestions();
            return;
        }

        searchTimer = setTimeout(executeSearch, 180);
    });

    searchInput.addEventListener('focus', function () {
        syncSearchBoxState();
        const q = this.value.trim();
        if (q.length >= 2 && suggestions.children.length > 0) {
            suggestions.classList.add('show');
            searchInput.setAttribute('aria-expanded', 'true');
        }
    });

    // Keyboard navigation on input
    searchInput.addEventListener('keydown', function (e) {
        const isSuggestionsOpen = suggestions.classList.contains('show');

        if (e.key === 'ArrowDown') {
            if (isSuggestionsOpen && currentResults.length > 0) {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % currentResults.length;
                updateActiveSuggestion();
            } else if (searchInput.value.trim().length >= 2) {
                e.preventDefault();
                executeSearch();
            }
        } else if (e.key === 'ArrowUp') {
            if (isSuggestionsOpen && currentResults.length > 0) {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + currentResults.length) % currentResults.length;
                updateActiveSuggestion();
            }
        } else if (e.key === 'Enter') {
            if (isSuggestionsOpen && activeIndex >= 0 && currentResults[activeIndex]) {
                e.preventDefault();
                window.location.href = '/tool/' + currentResults[activeIndex].slug;
            }
        } else if (e.key === 'Escape') {
            if (isSuggestionsOpen) {
                e.preventDefault();
                hideSuggestions();
            }
        }
    });

    // Clear button
    if (clearBtn) {
        clearBtn.addEventListener('click', function (e) {
            e.preventDefault();
            searchInput.value = '';
            syncSearchBoxState();
            hideSuggestions();
            searchInput.focus();
        });
    }

    // Submit button: focus and expand if empty, otherwise submit
    const submitBtn = document.getElementById('searchSubmitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function (e) {
            if (!searchInput.value.trim()) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }

    // Input blur
    searchInput.addEventListener('blur', function () {
        syncSearchBoxState();
    });

    // Close on click outside
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !suggestions.contains(e.target) && (!clearBtn || !clearBtn.contains(e.target))) {
            hideSuggestions();
        }
    });

    // Initialize state
    syncSearchBoxState();
})();

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

// ── Single Account Dropdown Controller ────
(function () {
    const wrap = document.getElementById('accountDropdownWrap');
    const btn = document.getElementById('accountDropdownBtn');

    if (!wrap || !btn) return;

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = wrap.classList.toggle('open');
        btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', function (e) {
        if (!wrap.contains(e.target)) {
            wrap.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && wrap.classList.contains('open')) {
            wrap.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
            btn.focus();
        }
    });
})();

// ── Mobile Menu Sheet Controller ──────────
function openMobileMenu() {
    const sheet = document.getElementById('mobileMenuSheet');
    const overlay = document.getElementById('mobileMenuOverlay');
    if (sheet && overlay) {
        sheet.classList.add('open');
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeMobileMenu() {
    const sheet = document.getElementById('mobileMenuSheet');
    const overlay = document.getElementById('mobileMenuOverlay');
    if (sheet && overlay) {
        sheet.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
}

// ── Mobile Full-Width Search Controller ────
function openMobileSearch() {
    const sheet = document.getElementById('mobileSearchSheet');
    const overlay = document.getElementById('mobileSearchOverlay');
    const input = document.getElementById('mobileSearchInput');
    if (sheet && overlay) {
        sheet.classList.add('open');
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        if (input) {
            setTimeout(() => input.focus(), 120);
        }
    }
}

function closeMobileSearch() {
    const sheet = document.getElementById('mobileSearchSheet');
    const overlay = document.getElementById('mobileSearchOverlay');
    if (sheet && overlay) {
        sheet.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }
}

function clearMobileSearch() {
    const input = document.getElementById('mobileSearchInput');
    const results = document.getElementById('mobileSearchResults');
    if (input) {
        input.value = '';
        input.focus();
    }
    if (results) {
        results.innerHTML = '';
        results.classList.remove('has-results');
    }
}

// Mobile Search Live Query
(function() {
    const input = document.getElementById('mobileSearchInput');
    const results = document.getElementById('mobileSearchResults');
    if (!input || !results) return;

    let searchTimer = null;
    input.addEventListener('input', function() {
        const q = this.value.trim();
        clearTimeout(searchTimer);

        if (q.length < 2) {
            results.innerHTML = '';
            results.classList.remove('has-results');
            return;
        }

        searchTimer = setTimeout(async () => {
            try {
                const res = await fetch('/api/search.php?q=' + encodeURIComponent(q));
                const data = await res.json();
                const list = Array.isArray(data) ? data : (data.tools || []);
                if (list && list.length > 0) {
                    let html = '<div class="mobile-search-group-title">Verified Tools</div>';
                    list.slice(0, 6).forEach(t => {
                        html += `
                            <a href="/tool/index.php?slug=${encodeURIComponent(t.slug)}" class="mobile-search-item">
                                <div class="mobile-search-item-info">
                                    <strong class="mobile-search-item-name">${escapeHtml(t.name)}</strong>
                                    <span class="mobile-search-item-tagline">${escapeHtml(t.tagline || '')}</span>
                                </div>
                                <span class="mobile-search-item-arrow">&rarr;</span>
                            </a>
                        `;
                    });
                    results.innerHTML = html;
                    results.classList.add('has-results');
                } else {
                    results.innerHTML = '<div class="mobile-search-empty">No tools matched your query.</div>';
                    results.classList.add('has-results');
                }
            } catch (err) {
                // Silently fallback
            }
        }, 220);
    });

    function escapeHtml(str) {
        return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
})();

// Close mobile sheets on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMobileMenu();
        closeMobileSearch();
    }
});
