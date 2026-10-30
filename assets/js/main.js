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

            const logoUrl = item.logo_url || (item.domain
                ? `https://img.logo.dev/${encodeURIComponent(item.domain)}?token=pk_IPkJggMaTFC35CqF8UE3hg&size=64`
                : '/assets/img/logos/placeholder.svg');

            return `
                <div class="suggestion-item"
                     id="suggestion-item-${idx}"
                     role="option"
                     aria-selected="false"
                     data-index="${idx}"
                     data-url="/tool/${escapeHtml(item.slug)}">
                    <div class="suggestion-logo-wrap">
                        <img class="suggestion-logo"
                             src="${logoUrl}"
                             alt=""
                             loading="lazy"
                             decoding="async"
                             style="width:28px;height:28px;object-fit:contain;border-radius:6px"
                             onerror="this.onerror=null;this.src='/assets/img/logos/placeholder.svg';">
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

// ════════════════════════════════════════════════════════════
// ALL TOOLS DIRECTORY FILTER & CAROUSEL CONTROLLER (Tasks 1, 2)
// Handles Category Pills + Price Filter Pills with AND Logic
// ════════════════════════════════════════════════════════════
(function initDirectoryController() {
    const grid = document.getElementById('mainToolsGrid');
    const pillsWrap = document.getElementById('dirFilterPills');
    const catPillsWrap = document.getElementById('categoryPillsBar');
    const featuredSection = document.getElementById('featuredSection');
    const liveRegion = document.getElementById('dirLiveRegion');
    if (!grid || !pillsWrap) return;

    const controls = document.getElementById('dirCarouselControls');
    const prevBtn = document.getElementById('dirPrev');
    const nextBtn = document.getElementById('dirNext');
    const counter = document.getElementById('dirCounter');
    const emptyState = document.getElementById('dirEmptyState');
    const showAllBtn = document.getElementById('dirShowAllBtn');
    const countBadge = document.getElementById('dirToolsCount');

    const cards = Array.from(grid.querySelectorAll('.tool-card'));
    const filterCache = new Map();
    let currentCategory = 'all';
    let currentFilter = 'all';
    let isTransitioning = false;
    let scrollRafId = null;

    const isRTL = document.documentElement.dir === 'rtl';

    function getCardStep() {
        const visibleCard = grid.querySelector('.tool-card:not([hidden]):not(.is-filtered-out)');
        return visibleCard ? (visibleCard.offsetWidth + 16) : 340;
    }

    function updateCarouselStatus() {
        if (!grid.classList.contains('is-carousel')) return;

        const maxScroll = Math.max(0, grid.scrollWidth - grid.clientWidth);
        const scrollLeft = Math.abs(grid.scrollLeft);

        if (prevBtn) {
            const isStart = scrollLeft <= 6;
            prevBtn.disabled = isStart;
            prevBtn.setAttribute('aria-disabled', isStart ? 'true' : 'false');
        }

        if (nextBtn) {
            const isEnd = scrollLeft >= maxScroll - 6;
            nextBtn.disabled = isEnd;
            nextBtn.setAttribute('aria-disabled', isEnd ? 'true' : 'false');
        }

        if (counter) {
            const visibleCards = cards.filter(c => !c.hidden && !c.classList.contains('is-filtered-out'));
            const total = visibleCards.length;
            if (total === 0) {
                counter.textContent = '0 / 0';
            } else if (total === 1) {
                counter.textContent = '1 / 1';
            } else {
                let activeIdx = 1;
                for (let i = 0; i < total; i++) {
                    const cardLeft = isRTL
                        ? (grid.scrollWidth - (visibleCards[i].offsetLeft + visibleCards[i].offsetWidth))
                        : (visibleCards[i].offsetLeft - grid.offsetLeft);
                    if (cardLeft <= scrollLeft + 30) {
                        activeIdx = i + 1;
                    }
                }
                counter.textContent = `${activeIdx} / ${total}`;
            }
        }
    }

    function scheduleScrollUpdate() {
        if (scrollRafId) cancelAnimationFrame(scrollRafId);
        scrollRafId = requestAnimationFrame(updateCarouselStatus);
    }

    grid.addEventListener('scroll', scheduleScrollUpdate, { passive: true });
    window.addEventListener('resize', scheduleScrollUpdate, { passive: true });

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            const step = getCardStep();
            grid.scrollBy({ left: isRTL ? step : -step, behavior: 'smooth' });
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            const step = getCardStep();
            grid.scrollBy({ left: isRTL ? -step : step, behavior: 'smooth' });
        });
    }

    grid.addEventListener('keydown', (e) => {
        if (!grid.classList.contains('is-carousel')) return;
        if (e.key === 'ArrowRight') {
            e.preventDefault();
            const step = getCardStep();
            grid.scrollBy({ left: isRTL ? -step : step, behavior: 'smooth' });
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            const step = getCardStep();
            grid.scrollBy({ left: isRTL ? step : -step, behavior: 'smooth' });
        }
    });

    // Mouse drag-to-scroll on desktop
    let isDown = false;
    let startX = 0;
    let scrollStart = 0;
    let dragDistance = 0;

    grid.addEventListener('mousedown', (e) => {
        if (!grid.classList.contains('is-carousel')) return;
        if (e.button !== 0) return;
        isDown = true;
        startX = e.pageX - grid.offsetLeft;
        scrollStart = grid.scrollLeft;
        dragDistance = 0;
        grid.style.scrollBehavior = 'auto';
    });

    window.addEventListener('mouseup', () => {
        if (!isDown) return;
        isDown = false;
        grid.style.scrollBehavior = 'smooth';
    });

    grid.addEventListener('mousemove', (e) => {
        if (!isDown || !grid.classList.contains('is-carousel')) return;
        const x = e.pageX - grid.offsetLeft;
        const walk = (x - startX);
        dragDistance += Math.abs(walk);
        grid.scrollLeft = scrollStart - walk;
    });

    grid.addEventListener('click', (e) => {
        if (grid.classList.contains('is-carousel') && dragDistance > 8) {
            e.preventDefault();
            e.stopPropagation();
            dragDistance = 0;
        }
    }, true);

    // Matching functions
    function matchCategory(card, category) {
        if (!category || category === 'all') return true;
        const catSlug = card.getAttribute('data-category-slug') || '';
        return catSlug.toLowerCase() === category.toLowerCase();
    }

    function matchPrice(card, filter) {
        if (!filter || filter === 'all') return true;
        const price = card.getAttribute('data-price') || card.getAttribute('data-price-type') || '';
        const featured = card.getAttribute('data-featured') || '0';
        if (filter === 'free') return (price === 'free' || price === 'freemium');
        if (filter === 'paid') return (price === 'paid');
        if (filter === 'featured') return (featured === '1');
        return true;
    }

    function matchCombined(card, category, filter) {
        return matchCategory(card, category) && matchPrice(card, filter);
    }

    function getFilteredCards(category, filter) {
        const key = `${category || 'all'}::${filter || 'all'}`;
        if (filterCache.has(key)) {
            return filterCache.get(key);
        }
        const matches = cards.filter(c => matchCombined(c, category, filter));
        filterCache.set(key, matches);
        return matches;
    }

    // Dynamic live pill counts
    function updatePillCounts() {
        if (catPillsWrap) {
            catPillsWrap.querySelectorAll('.cat-pill').forEach(pill => {
                const cat = pill.getAttribute('data-category') || 'all';
                const countSpan = pill.querySelector('.cat-pill-count');
                if (countSpan) {
                    const count = cards.filter(c => matchCombined(c, cat, currentFilter)).length;
                    countSpan.textContent = `(${count})`;
                }
            });
        }
    }

    // Combined Apply Directory State
    window.applyDirectoryState = function(category, filter, updateHistory = true) {
        category = (category || 'all').toLowerCase();
        filter = (filter || 'all').toLowerCase();
        if (!['all', 'free', 'paid', 'featured'].includes(filter)) filter = 'all';

        currentCategory = category;
        currentFilter = filter;

        // 1. Update Category Pills UI
        if (catPillsWrap) {
            catPillsWrap.querySelectorAll('.cat-pill').forEach(pill => {
                const pCat = (pill.getAttribute('data-category') || 'all').toLowerCase();
                const isActive = (pCat === category);
                pill.classList.toggle('active-pill', isActive);
                pill.setAttribute('aria-selected', isActive ? 'true' : 'false');
                pill.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                if (isActive) {
                    pill.scrollIntoView({ inline: 'center', behavior: 'smooth', block: 'nearest' });
                }
            });
        }

        // 2. Update Price Filter Pills UI
        pillsWrap.querySelectorAll('.filter-btn').forEach(btn => {
            const btnFilter = (btn.getAttribute('data-filter') || 'all').toLowerCase();
            const isActive = (btnFilter === filter);
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        // 3. Update pill counts
        updatePillCounts();

        // 4. Featured Section Collapsing
        if (featuredSection) {
            if (category !== 'all') {
                featuredSection.classList.add('is-collapsed');
            } else {
                featuredSection.classList.remove('is-collapsed');
            }
        }

        // 5. Sync URL
        if (updateHistory) {
            const url = new URL(window.location.href);
            if (category === 'all') url.searchParams.delete('category');
            else url.searchParams.set('category', category);

            if (filter === 'all') url.searchParams.delete('filter');
            else url.searchParams.set('filter', filter);

            window.history.pushState({ category, filter }, '', url.toString());
        }

        // 6. Filter cards
        const matchingCards = getFilteredCards(category, filter);
        const matchSet = new Set(matchingCards);
        const visibleCount = matchingCards.length;

        // 7. Live region & counter badge
        if (countBadge) {
            countBadge.textContent = `${visibleCount} tool${visibleCount !== 1 ? 's' : ''}`;
        }
        if (liveRegion) {
            liveRegion.textContent = `Showing ${visibleCount} tools`;
        }

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches || (document.documentElement.dataset.perf === 'lite');

        const executeDOMUpdate = () => {
            const isSpecific = (category !== 'all' || filter !== 'all');
            const isCarousel = isSpecific && visibleCount > 0;

            if (isCarousel) {
                grid.classList.add('is-carousel');
                if (visibleCount === 1) {
                    grid.classList.add('single-tool');
                    if (controls) controls.style.display = 'inline-flex';
                    if (prevBtn) prevBtn.disabled = true;
                    if (nextBtn) nextBtn.disabled = true;
                    if (counter) counter.textContent = '1 / 1';
                } else {
                    grid.classList.remove('single-tool');
                    if (controls) controls.style.display = 'inline-flex';
                }
                grid.scrollLeft = 0;
            } else {
                grid.classList.remove('is-carousel', 'single-tool');
                if (controls) controls.style.display = 'none';
            }

            // Single pass card updates
            cards.forEach(card => {
                const isMatch = matchSet.has(card);
                if (isMatch) {
                    card.hidden = false;
                    card.classList.remove('is-filtered-out', 'anim-fade-out');
                    if (!prefersReducedMotion) {
                        card.classList.add('anim-fade-in');
                        const sIdx = matchingCards.indexOf(card);
                        card.style.transitionDelay = (sIdx >= 0 && sIdx < 8) ? `${sIdx * 30}ms` : '0ms';
                    } else {
                        card.style.transitionDelay = '0ms';
                    }
                } else {
                    card.hidden = true;
                    card.classList.add('is-filtered-out');
                    card.classList.remove('anim-fade-in');
                    card.style.transitionDelay = '0ms';
                }
            });

            // Empty state
            if (visibleCount === 0) {
                if (emptyState) emptyState.style.display = 'block';
                grid.style.display = 'none';
            } else {
                if (emptyState) emptyState.style.display = 'none';
                grid.style.display = '';
            }

            if (isCarousel && visibleCount > 1) {
                updateCarouselStatus();
            } else if (!isSpecific && updateHistory) {
                const heading = document.getElementById('all-tools-heading') || document.getElementById('allToolsSection');
                if (heading) heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            setTimeout(() => {
                cards.forEach(card => {
                    card.classList.remove('anim-fade-in', 'anim-fade-out');
                    card.style.transitionDelay = '';
                });
                isTransitioning = false;
            }, 300);
        };

        if (prefersReducedMotion || isTransitioning) {
            executeDOMUpdate();
        } else {
            isTransitioning = true;
            cards.forEach(card => {
                if (!card.hidden && !card.classList.contains('is-filtered-out')) {
                    card.classList.add('anim-fade-out');
                }
            });
            setTimeout(executeDOMUpdate, 120);
        }
    };

    // Backward-compatible filter alias
    window.applyDirectoryFilter = function(filter, updateHistory = true) {
        window.applyDirectoryState(currentCategory, filter, updateHistory);
    };

    window.applyCategoryFilter = function(category, updateHistory = true) {
        window.applyDirectoryState(category, currentFilter, updateHistory);
    };

    window.filterTools = function(filter) {
        window.applyDirectoryFilter(filter, true);
    };

    // Category pills click handler
    if (catPillsWrap) {
        catPillsWrap.addEventListener('click', (e) => {
            const pill = e.target.closest('.cat-pill');
            if (!pill) return;
            e.preventDefault();
            const cat = pill.getAttribute('data-category') || 'all';
            window.applyCategoryFilter(cat, true);
        });
    }

    // Price pills click handler
    pillsWrap.addEventListener('click', (e) => {
        const btn = e.target.closest('.filter-btn');
        if (!btn) return;
        e.preventDefault();
        const filter = btn.getAttribute('data-filter') || 'all';
        window.applyDirectoryFilter(filter, true);
    });

    if (showAllBtn) {
        showAllBtn.addEventListener('click', () => {
            window.applyDirectoryState('all', 'all', true);
        });
    }

    window.addEventListener('popstate', () => {
        const params = new URLSearchParams(window.location.search);
        const cat = params.get('category') || 'all';
        const flt = params.get('filter') || 'all';
        window.applyDirectoryState(cat, flt, false);
    });

    // Initial read from URL
    const initParams = new URLSearchParams(window.location.search);
    const initCat = initParams.get('category') || 'all';
    const initFlt = initParams.get('filter') || 'all';
    if (initCat !== 'all' || initFlt !== 'all') {
        window.applyDirectoryState(initCat, initFlt, false);
    } else {
        updatePillCounts();
    }
})();

// ════════════════════════════════════════════════════════════
// ADAPTIVE LITE MODE CONTROLLER (Task 5)
// ════════════════════════════════════════════════════════════
(function initPerfModeController() {
    function detectHardwareLite() {
        const nav = navigator;
        const conn = nav.connection || nav.mozConnection || nav.webkitConnection;
        if (conn) {
            if (conn.saveData) return true;
            if (conn.effectiveType === 'slow-2g' || conn.effectiveType === '2g' || conn.effectiveType === '3g') return true;
            if (conn.downlink && conn.downlink < 1.2) return true;
            if (conn.rtt && conn.rtt > 500) return true;
        }
        if (nav.deviceMemory && nav.deviceMemory <= 2) return true;
        if (nav.hardwareConcurrency && nav.hardwareConcurrency <= 2) return true;
        return false;
    }

    function getSavedMode() {
        return localStorage.getItem('theme_perf') || 'auto';
    }

    function resolveEffective(mode) {
        if (mode === 'lite') return 'lite';
        if (mode === 'full') return 'full';
        return detectHardwareLite() ? 'lite' : 'full';
    }

    function updateDOM(mode, effective) {
        document.documentElement.setAttribute('data-perf', effective);
        const btnText = document.getElementById('perfStatusText');
        if (btnText) {
            if (mode === 'auto') {
                btnText.textContent = effective === 'lite' ? 'Lite (auto)' : 'Auto mode';
            } else if (mode === 'lite') {
                btnText.textContent = 'Lite mode';
            } else {
                btnText.textContent = 'Full mode';
            }
        }
    }

    window.cyclePerfMode = function() {
        const cur = getSavedMode();
        let next = 'auto';
        if (cur === 'auto') next = 'lite';
        else if (cur === 'lite') next = 'full';
        else if (cur === 'full') next = 'auto';

        localStorage.setItem('theme_perf', next);
        const effective = resolveEffective(next);
        updateDOM(next, effective);

        if (typeof showActionToast === 'function') {
            const label = next === 'auto' ? (effective === 'lite' ? 'Auto (Lite detected)' : 'Auto') : (next === 'lite' ? 'Lite mode active' : 'Full mode active');
            showActionToast(`Performance: ${label}`);
        }
    };

    // Initialize UI on load
    const initialMode = getSavedMode();
    const initialEffective = resolveEffective(initialMode);
    updateDOM(initialMode, initialEffective);

    // Network change listener
    const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    if (conn && conn.addEventListener) {
        conn.addEventListener('change', () => {
            if (getSavedMode() === 'auto') {
                const eff = resolveEffective('auto');
                updateDOM('auto', eff);
            }
        });
    }

    // Post-load RAF CPU Probe (12 frames, median > 40ms => switch to lite)
    window.addEventListener('load', () => {
        if (getSavedMode() !== 'auto') return;
        setTimeout(() => {
            let frames = [];
            let lastTime = performance.now();
            let count = 0;

            function probeFrame(now) {
                const delta = now - lastTime;
                lastTime = now;
                frames.push(delta);
                count++;
                if (count < 12) {
                    requestAnimationFrame(probeFrame);
                } else {
                    frames.sort((a, b) => a - b);
                    const median = frames[Math.floor(frames.length / 2)];
                    if (median > 40) {
                        updateDOM('auto', 'lite');
                        if (typeof showActionToast === 'function' && !sessionStorage.getItem('lite_toast_shown')) {
                            sessionStorage.setItem('lite_toast_shown', '1');
                            showActionToast('Lite mode enabled to improve performance');
                        }
                    }
                }
            }
            requestAnimationFrame(probeFrame);
        }, 600);
    });
})();

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

// ── Fast Instant Interactions & Optimistic UI Updates ─────
function showActionToast(message) {
    let toast = document.getElementById('undoToast');
    let msg = document.getElementById('undoToastMsg');
    let btn = document.getElementById('undoToastBtn');
    let pbar = document.getElementById('undoProgressBar');
    if (!toast || !msg) return;

    msg.textContent = message;
    if (btn) btn.style.display = 'none';
    if (pbar) pbar.style.display = 'none';
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (btn) btn.style.display = '';
            if (pbar) pbar.style.display = '';
        }, 300);
    }, 4000);
}

// Prefetch on hover/touchstart for internal links
(function initLinkPrefetch() {
    const prefetched = new Set();
    function prefetchUrl(url) {
        if (!url || prefetched.has(url)) return;
        if (url.startsWith('/logout') || url.startsWith('/api') || url.includes('#') || url.startsWith('javascript:')) return;
        prefetched.add(url);
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);
    }

    document.addEventListener('mouseover', function(e) {
        const a = e.target.closest('a[href^="/"]');
        if (a && a.getAttribute('href') && !a.getAttribute('href').startsWith('/#')) {
            prefetchUrl(a.getAttribute('href'));
        }
    }, { passive: true });

    document.addEventListener('touchstart', function(e) {
        const a = e.target.closest('a[href^="/"]');
        if (a && a.getAttribute('href') && !a.getAttribute('href').startsWith('/#')) {
            prefetchUrl(a.getAttribute('href'));
        }
    }, { passive: true });
})();

// Global Event Delegation for Upvotes, Bookmarks & Stack additions
document.addEventListener('click', async function(e) {
    // 1. Upvote Button Delegation
    const upvoteBtn = e.target.closest('.upvote-btn');
    if (upvoteBtn) {
        e.preventDefault();
        const toolId = upvoteBtn.dataset.toolId;
        if (!toolId) return;

        const countEl = upvoteBtn.querySelector('.upvote-count');
        const wasUpvoted = upvoteBtn.classList.contains('upvoted');
        const currentCount = parseInt(countEl?.textContent || '0', 10);

        // Optimistic UI update
        upvoteBtn.classList.toggle('upvoted', !wasUpvoted);
        if (countEl) {
            countEl.textContent = wasUpvoted ? Math.max(0, currentCount - 1) : currentCount + 1;
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch('/upvote.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'tool_id=' + encodeURIComponent(toolId) + '&csrf_token=' + encodeURIComponent(csrfToken)
            });
            const data = await res.json();
            if (data.error === "login_required") {
                // Revert
                upvoteBtn.classList.toggle('upvoted', wasUpvoted);
                if (countEl) countEl.textContent = currentCount;
                showActionToast('Please log in to upvote tools.');
                return;
            }
            if (data.count !== undefined && countEl) {
                countEl.textContent = data.count;
            }
            if (data.action) {
                upvoteBtn.classList.toggle('upvoted', data.action === 'added');
            }
        } catch (err) {
            // Revert
            upvoteBtn.classList.toggle('upvoted', wasUpvoted);
            if (countEl) countEl.textContent = currentCount;
            showActionToast('Connection error. Upvote could not be saved.');
        }
        return;
    }

    // 2. Bookmark Button Delegation
    const bookmarkBtn = e.target.closest('.bookmark-btn, [data-action="bookmark"]');
    if (bookmarkBtn) {
        e.preventDefault();
        const toolId = bookmarkBtn.dataset.toolId;
        if (toolId && typeof toggleBookmark === 'function') {
            toggleBookmark(toolId, bookmarkBtn);
        }
        return;
    }
});
