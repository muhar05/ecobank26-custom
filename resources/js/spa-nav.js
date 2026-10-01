/**
 * EcoBank026 - SPA Navigation POC
 *
 * Lightweight, dependency-free page navigation for Laravel + Blade + Alpine.js
 * Keeps sidebar and header mounted while swapping the main content area.
 */

const POC_ELIGIBLE_PATHS = [
    '/bank-sampah/dashboard',
    '/bank-sampah/deposits',
    '/bank-sampah/deposits/create',
    '/bank-sampah/customers',
];

// Sidebar paths grouped under the "Master Data" submenu parent.
const MASTER_CHILD_PREFIXES = [
    '/bank-sampah/waste-categories',
    '/bank-sampah/collectors',
    '/bank-sampah/waste-prices',
];

const ACTIVE_CLASSES = ['bg-primary', 'text-white', 'shadow-subtle'];
const INACTIVE_CLASSES = ['text-text-muted', 'hover:bg-surface-muted', 'hover:text-text'];

let isNavigating = false;
let currentAbortController = null;
let progressTimer = null;

function normalizePath(pathname) {
    if (!pathname) return '/';
    return pathname.replace(/\/+$/, '') || '/';
}

export function isEligibleUrl(url) {
    if (url.origin !== window.location.origin) return false;
    const path = normalizePath(url.pathname);
    // Eligible modules: Dashboard, Deposits, and Customers (full CRUD directory)
    if (path === '/bank-sampah/dashboard') return true;
    if (path.startsWith('/bank-sampah/customers')) return true;
    if (path === '/bank-sampah/deposits' || path === '/bank-sampah/deposits/create') return true;
    return POC_ELIGIBLE_PATHS.some(p => normalizePath(p) === path);
}

function isEligibleLink(anchor, event) {
    // 1. Mouse & modifier key guards (Rule 7)
    if (
        event.defaultPrevented ||
        event.button !== 0 ||
        event.metaKey ||
        event.ctrlKey ||
        event.shiftKey ||
        event.altKey
    ) {
        return false;
    }

    // 2. Attribute guards (Rule 7)
    const rawHref = anchor.getAttribute('href');
    if (
        !rawHref ||
        rawHref.startsWith('#') ||
        rawHref.startsWith('javascript:') ||
        rawHref.startsWith('mailto:') ||
        rawHref.startsWith('tel:')
    ) {
        return false;
    }

    if (anchor.target && anchor.target !== '_self') return false;
    if (anchor.hasAttribute('download') || anchor.getAttribute('rel')?.includes('external')) return false;
    if (anchor.hasAttribute('data-no-spa') || anchor.hasAttribute('data-native')) return false;

    // 3. URL origin & file extension guards
    let url;
    try {
        url = new URL(anchor.href, window.location.origin);
    } catch {
        return false;
    }

    if (url.origin !== window.location.origin) return false;

    if (
        url.pathname.match(/\.(xlsx|pdf|csv|png|jpg|jpeg|gif|svg|ico)$/i) ||
        url.pathname.includes('/template')
    ) {
        return false;
    }

    // 4. Scope: explicit data-spa-link or in POC whitelist (Rule 9)
    if (anchor.hasAttribute('data-spa-link')) return true;
    return isEligibleUrl(url);
}

function showLoading() {
    const bar = document.getElementById('eco-nav-progress');
    const slot = document.getElementById('app-content-slot');

    if (bar) {
        clearTimeout(progressTimer);
        bar.style.transition = 'width 300ms cubic-bezier(0.4, 0, 0.2, 1), opacity 150ms ease';
        bar.style.opacity = '1';
        bar.style.width = '35%';

        progressTimer = setTimeout(() => {
            if (bar && bar.style.opacity === '1') {
                bar.style.width = '75%';
            }
        }, 150);
    }

    if (slot) {
        slot.style.transition = 'opacity 150ms ease';
        slot.style.opacity = '0.7';
    }
}

function hideLoading() {
    const bar = document.getElementById('eco-nav-progress');
    const slot = document.getElementById('app-content-slot');

    if (bar) {
        clearTimeout(progressTimer);
        bar.style.width = '100%';
        setTimeout(() => {
            bar.style.opacity = '0';
            setTimeout(() => {
                bar.style.width = '0%';
            }, 200);
        }, 120);
    }

    if (slot) {
        slot.style.opacity = '1';
    }
}

function parseResponseHtml(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');

    // Case A: Fragment response from X-Eco-Nav
    const frag = doc.getElementById('eco-spa-fragment');
    if (frag) {
        const title = frag.getAttribute('data-title') || '';
        const docTitle = frag.getAttribute('data-document-title') || document.title;
        const slotEl = frag.querySelector('#app-content-slot');
        const slotHtml = slotEl ? slotEl.innerHTML : frag.innerHTML;

        const scripts = Array.from(frag.querySelectorAll('script')).map(s => ({
            src: s.getAttribute('src'),
            textContent: s.textContent,
            type: s.getAttribute('type'),
        }));

        return { slotHtml, scripts, title, docTitle };
    }

    // Case B: Full document response fallback
    const slotEl = doc.getElementById('app-content-slot') || doc.getElementById('app-content');
    if (slotEl) {
        const titleEl = doc.getElementById('topbar-page-title');
        const title = titleEl ? titleEl.textContent.trim() : '';
        const docTitle = doc.title || document.title;
        const slotHtml = slotEl.innerHTML;

        const scripts = Array.from(doc.querySelectorAll('#app-content-slot script, #app-content script, main script')).map(s => ({
            src: s.getAttribute('src'),
            textContent: s.textContent,
            type: s.getAttribute('type'),
        }));

        return { slotHtml, scripts, title, docTitle };
    }

    return { slotHtml: null, scripts: [], title: '', docTitle: '' };
}

function executeScripts(container, scriptDescriptors = []) {
    // Remove any inert script nodes in slot
    const inlineScripts = Array.from(container.querySelectorAll('script'));
    inlineScripts.forEach(s => s.remove());

    scriptDescriptors.forEach(item => {
        const s = document.createElement('script');
        if (item.src) {
            s.src = item.src;
        } else {
            s.textContent = item.textContent;
        }
        if (item.type) {
            s.type = item.type;
        }
        container.appendChild(s);
    });
}

function setActiveVisual(el, isActive) {
    if (isActive) {
        el.classList.add(...ACTIVE_CLASSES);
        el.classList.remove(...INACTIVE_CLASSES);
    } else {
        el.classList.remove(...ACTIVE_CLASSES);
        el.classList.add(...INACTIVE_CLASSES);
    }
}

function updateSidebarActive(currentPath) {
    const normCurrent = normalizePath(currentPath);
    const links = document.querySelectorAll('aside nav a[href]');

    links.forEach(a => {
        const rawHref = a.getAttribute('href');
        if (!rawHref) return;

        let linkPath;
        try {
            linkPath = normalizePath(new URL(rawHref, window.location.origin).pathname);
        } catch {
            return;
        }

        // Generic rule: exact match or any sub-path (covers index/create/edit/history/import)
        const isActive = normCurrent === linkPath || normCurrent.startsWith(linkPath + '/');
        setActiveVisual(a, isActive);
    });

    // Master Data parent: highlight + auto-expand when a child route is active.
    // Parent keeps its highlight even while the submenu is manually collapsed.
    const masterActive = MASTER_CHILD_PREFIXES.some(p => normCurrent === p || normCurrent.startsWith(p + '/'));
    document.querySelectorAll('[data-master-toggle]').forEach(btn => {
        setActiveVisual(btn, masterActive);
    });
    if (masterActive) {
        window.dispatchEvent(new CustomEvent('eco:master-sync', { detail: { open: true } }));
    }
}

export async function navigate(targetUrl, { pushHistory = true } = {}) {
    let url;
    try {
        url = new URL(targetUrl, window.location.origin);
    } catch {
        window.location.href = targetUrl;
        return;
    }

    // Exact same destination check
    if (url.href === window.location.href && pushHistory) {
        const mainContent = document.getElementById('app-content');
        if (mainContent) mainContent.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    if (currentAbortController) {
        currentAbortController.abort();
    }
    currentAbortController = new AbortController();

    isNavigating = true;
    showLoading();

    try {
        const response = await fetch(url.href, {
            method: 'GET',
            headers: {
                'X-Eco-Nav': '1',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html, application/xhtml+xml',
            },
            signal: currentAbortController.signal,
        });

        // Redirected (e.g. session expired, redirected to login)
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }

        if (!response.ok) {
            window.location.href = url.href;
            return;
        }

        const html = await response.text();
        const { slotHtml, scripts, title, docTitle } = parseResponseHtml(html);

        if (!slotHtml) {
            window.location.href = url.href;
            return;
        }

        // Clean up previous charts before swap
        if (window._ecoDashboardCharts && Array.isArray(window._ecoDashboardCharts)) {
            window._ecoDashboardCharts.forEach(c => {
                try { c.destroy(); } catch (_) {}
            });
            window._ecoDashboardCharts = null;
        }

        const applyUpdate = () => {
            const slot = document.getElementById('app-content-slot');
            if (!slot) {
                window.location.href = url.href;
                return;
            }

            // 1. Destroy Alpine tree inside slot (Rule 8)
            if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
                try {
                    window.Alpine.destroyTree(slot);
                } catch (e) {
                    console.warn('[EcoNav] Alpine destroyTree warning:', e);
                }
            }

            // 2. Replace HTML
            slot.innerHTML = slotHtml;

            // 3. Update titles
            if (title) {
                const topbarTitle = document.getElementById('topbar-page-title');
                if (topbarTitle) topbarTitle.textContent = title;
            }
            if (docTitle) {
                document.title = docTitle;
            }

            // 4. Update sidebar links
            updateSidebarActive(url.pathname);

            // 5. Close mobile drawer if open
            window.dispatchEvent(new CustomEvent('close-sidebar'));

            // 6. Execute scripts
            executeScripts(slot, scripts);

            // 7. Initialize Alpine on updated slot (Rule 8)
            if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                try {
                    window.Alpine.initTree(slot);
                } catch (e) {
                    console.warn('[EcoNav] Alpine initTree warning:', e);
                }
            }

            // 8. Scroll container to top
            const mainContent = document.getElementById('app-content');
            if (mainContent) mainContent.scrollTo({ top: 0, behavior: 'instant' });
        };

        if (document.startViewTransition) {
            try {
                await document.startViewTransition(applyUpdate).finished;
            } catch {
                applyUpdate();
            }
        } else {
            applyUpdate();
        }

        if (pushHistory) {
            history.pushState({ path: url.href }, '', url.href);
        }
    } catch (err) {
        if (err.name === 'AbortError') {
            return;
        }
        console.warn('[EcoNav] Falling back to traditional navigation:', err);
        window.location.href = url.href;
    } finally {
        hideLoading();
        isNavigating = false;
        currentAbortController = null;
    }
}

export function initSpaNav() {
    if (window._ecoSpaNavInitialized) return;
    window._ecoSpaNavInitialized = true;

    // Delegate click
    document.addEventListener('click', (e) => {
        const anchor = e.target.closest('a');
        if (!anchor) return;

        if (isEligibleLink(anchor, e)) {
            e.preventDefault();
            navigate(anchor.href, { pushHistory: true });
        }
    });

    // Popstate (Back / Forward)
    window.addEventListener('popstate', () => {
        let targetUrl = window.location.href;
        let url;
        try {
            url = new URL(targetUrl);
        } catch {
            return;
        }

        if (isEligibleUrl(url)) {
            navigate(targetUrl, { pushHistory: false });
        } else {
            window.location.reload();
        }
    });
}
