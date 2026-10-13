/**
 * EcoBank026 - Live Search
 *
 * Progressive enhancement untuk form search/filter. Mengambil fragmen HTML
 * (header X-Eco-Nav) lalu menukar HANYA region hasil (#table-section) sehingga:
 * - tidak ada full page reload,
 * - toolbar & kotak pencarian tidak dirender ulang (fokus/caret aman),
 * - URL diperbarui via history.replaceState.
 *
 * Tanpa JavaScript, form tetap submit biasa (GET) seperti semula.
 *
 * Kontrak markup:
 * - form[data-live-search]         -> form yang di-live-kan
 * - form[data-live-region="#sel"]  -> opsional, default #table-section
 * - [data-live-reset]              -> link Reset yang di-toggle sesuai isi filter
 */

const DEFAULT_REGION = '#table-section';
const DEBOUNCE_MS = 300;
let initialized = false;

function regionFor(form) {
  return (form && form.dataset.liveRegion) || DEFAULT_REGION;
}

function buildUrl(form) {
  const url = new URL(form.action || window.location.href, window.location.origin);

  // Mulai dari query string saat ini agar filter di form lain tidak hilang
  // (mis. filter grup di waste-categories + kata kunci dari toolbar).
  const params = new URLSearchParams(window.location.search);
  const data = new FormData(form);

  for (const [key, value] of data.entries()) {
    if (key === '_token' || key === '_method') continue;
    const str = typeof value === 'string' ? value : String(value);
    if (str.trim() === '') {
      params.delete(key);
    } else {
      params.set(key, str);
    }
  }

  // Setiap perubahan filter mengulang dari halaman pertama.
  params.delete('page');

  url.search = params.toString();
  return url;
}

function captureFocus(region) {
  const active = document.activeElement;
  if (!active || !region.contains(active)) return null;

  const info = {
    id: active.id || null,
    name: active.getAttribute ? active.getAttribute('name') : null,
    selectionStart: null,
    selectionEnd: null,
  };

  try {
    if (typeof active.selectionStart === 'number') {
      info.selectionStart = active.selectionStart;
      info.selectionEnd = active.selectionEnd;
    }
  } catch (_) {
    /* input type tanpa selection (mis. date) */
  }

  return info;
}

function restoreFocus(region, info) {
  if (!info || !region) return;

  let target = null;
  if (info.id) {
    try {
      target = region.querySelector('#' + CSS.escape(info.id)) || document.getElementById(info.id);
    } catch (_) {
      target = document.getElementById(info.id);
    }
  }
  if (!target && info.name) {
    try {
      target = region.querySelector('[name="' + CSS.escape(info.name) + '"]');
    } catch (_) {
      target = null;
    }
  }
  if (!target || typeof target.focus !== 'function') return;

  try {
    target.focus({ preventScroll: true });
  } catch (_) {
    target.focus();
  }

  if (info.selectionStart !== null && typeof target.setSelectionRange === 'function') {
    try {
      target.setSelectionRange(info.selectionStart, info.selectionEnd);
    } catch (_) {
      /* noop */
    }
  }
}

function extractRegion(html, selector) {
  const doc = new DOMParser().parseFromString(html, 'text/html');
  const frag = doc.getElementById('eco-spa-fragment');
  const root = frag || doc;

  return {
    region: root.querySelector(selector),
    title: frag ? frag.getAttribute('data-title') || '' : '',
    docTitle: frag ? frag.getAttribute('data-document-title') || '' : '',
  };
}

function runScripts(container) {
  container.querySelectorAll('script').forEach((old) => {
    const script = document.createElement('script');
    if (old.src) script.src = old.src;
    else script.textContent = old.textContent;
    if (old.type) script.type = old.type;
    old.replaceWith(script);
  });
}

function swapRegion(current, incoming) {
  if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
    try {
      window.Alpine.destroyTree(current);
    } catch (e) {
      console.warn('[EcoLiveSearch] destroyTree:', e);
    }
  }

  current.replaceWith(incoming);

  runScripts(incoming);

  if (window.Alpine && typeof window.Alpine.initTree === 'function') {
    try {
      window.Alpine.initTree(incoming);
    } catch (e) {
      console.warn('[EcoLiveSearch] initTree:', e);
    }
  }

  window.dispatchEvent(new CustomEvent('eco:region-swapped', { detail: { region: incoming } }));
}

function syncResets(form) {
  const resets = form.querySelectorAll('[data-live-reset]');
  if (!resets.length) return;

  const hasValue = Array.from(new FormData(form).entries())
    .some(([key, value]) => key !== '_token' && String(value).trim() !== '');

  resets.forEach((el) => el.classList.toggle('hidden', !hasValue));
}

async function performSearch(form) {
  const url = buildUrl(form);
  const selector = regionFor(form);
  const current = document.querySelector(selector);

  if (!current) {
    window.location.href = url.href;
    return;
  }

  if (form._liveAbort) form._liveAbort.abort();
  const controller = new AbortController();
  form._liveAbort = controller;

  const focusInfo = captureFocus(current);
  current.setAttribute('aria-busy', 'true');
  current.classList.add('opacity-60');

  try {
    const response = await fetch(url.href, {
      method: 'GET',
      headers: {
        'X-Eco-Nav': '1',
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'text/html, application/xhtml+xml',
      },
      signal: controller.signal,
    });

    if (response.redirected) {
      window.location.href = response.url;
      return;
    }

    if (!response.ok) {
      window.location.href = url.href;
      return;
    }

    const html = await response.text();
    const parsed = extractRegion(html, selector);

    if (!parsed.region) {
      window.location.href = url.href;
      return;
    }

    const live = document.querySelector(selector) || current;
    swapRegion(live, parsed.region);
    restoreFocus(document.querySelector(selector), focusInfo);

    if (parsed.title) {
      const topbarTitle = document.getElementById('topbar-page-title');
      if (topbarTitle) topbarTitle.textContent = parsed.title;
    }
    if (parsed.docTitle) {
      document.title = parsed.docTitle;
    }

    history.replaceState(history.state, '', url.href);
    syncResets(form);
  } catch (err) {
    if (err.name === 'AbortError') return;
    console.warn('[EcoLiveSearch] Falling back to navigation:', err);
    window.location.href = url.href;
  } finally {
    if (form._liveAbort === controller) {
      form._liveAbort = null;
      const region = document.querySelector(selector);
      if (region) {
        region.setAttribute('aria-busy', 'false');
        region.classList.remove('opacity-60');
      }
    }
  }
}

function scheduleSearch(form, delay = DEBOUNCE_MS) {
  clearTimeout(form._liveTimer);
  form._liveTimer = setTimeout(() => performSearch(form), delay);
}

function closestLiveForm(target) {
  if (!target || typeof target.closest !== 'function') return null;
  return target.closest('form[data-live-search]');
}

function isTextControl(el) {
  const tag = el.tagName;
  const type = (el.getAttribute('type') || '').toLowerCase();
  return tag === 'TEXTAREA' || (tag === 'INPUT' && (type === '' || type === 'text' || type === 'search'));
}

function onInput(event) {
  const form = closestLiveForm(event.target);
  if (!form) return;
  if (event.isComposing || event.target._isComposing) return;
  if (!isTextControl(event.target)) return;

  scheduleSearch(form, DEBOUNCE_MS);
}

function onChange(event) {
  const form = closestLiveForm(event.target);
  if (!form) return;

  // Perubahan teks sudah ditangani onInput (debounce); hindari dobel saat blur.
  if (isTextControl(event.target) && event instanceof Event && !(event instanceof CustomEvent)) {
    return;
  }

  scheduleSearch(form, 0);
}

function onSubmit(event) {
  const form = closestLiveForm(event.target);
  if (!form) return;

  event.preventDefault();
  clearTimeout(form._liveTimer);
  performSearch(form);
}

function onCompositionStart(event) {
  if (closestLiveForm(event.target)) event.target._isComposing = true;
}

function onCompositionEnd(event) {
  const form = closestLiveForm(event.target);
  if (!form) return;
  event.target._isComposing = false;
  scheduleSearch(form, 0);
}

export function initLiveSearch() {
  if (initialized) return;
  initialized = true;

  document.addEventListener('input', onInput, true);
  document.addEventListener('change', onChange, true);
  document.addEventListener('submit', onSubmit, true);
  document.addEventListener('compositionstart', onCompositionStart, true);
  document.addEventListener('compositionend', onCompositionEnd, true);
}
