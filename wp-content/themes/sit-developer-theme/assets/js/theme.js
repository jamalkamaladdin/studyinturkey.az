(function () {
	'use strict';

	/* ── Mobile nav (slide from right — Figma style) ── */
	function initNav() {
		var toggle = document.querySelector('[data-sit-nav-toggle]');
		var panel = document.querySelector('[data-sit-nav-panel]');
		var backdrop = document.querySelector('[data-sit-nav-backdrop]');
		var drawer = document.querySelector('[data-sit-nav-drawer]');
		var iconOpen = document.querySelector('[data-sit-nav-icon-open]');
		var iconClose = document.querySelector('[data-sit-nav-icon-close]');
		if (!toggle || !panel) return;

		// Set initial transform
		if (drawer) drawer.style.transform = 'translateX(100%)';

		function setOpen(open) {
			if (open) {
				panel.classList.remove('hidden');
				document.body.classList.add('overflow-hidden');
				requestAnimationFrame(function () {
					if (drawer) drawer.style.transform = 'translateX(0)';
					if (backdrop) backdrop.style.opacity = '1';
				});
			} else {
				if (drawer) drawer.style.transform = 'translateX(100%)';
				if (backdrop) backdrop.style.opacity = '0';
				document.body.classList.remove('overflow-hidden');
				setTimeout(function () { panel.classList.add('hidden'); }, 300);
			}
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			if (iconOpen) iconOpen.classList.toggle('hidden', open);
			if (iconClose) iconClose.classList.toggle('hidden', !open);
		}

		// Add transition styles
		if (drawer) drawer.style.transition = 'transform 0.3s cubic-bezier(0.4,0,0.2,1)';
		if (backdrop) { backdrop.style.transition = 'opacity 0.3s ease'; backdrop.style.opacity = '0'; }

		toggle.addEventListener('click', function () { setOpen(panel.classList.contains('hidden')); });
		if (backdrop) backdrop.addEventListener('click', function () { setOpen(false); });
	}

	/* ── Dark mode ── */
	function initDark() {
		var key = 'sit-theme-pref';
		var root = document.documentElement;
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-sit-dark-toggle]');
			if (!btn) return;
			e.preventDefault();
			var on = !root.classList.contains('dark');
			root.classList.toggle('dark', on);
			try { localStorage.setItem(key, on ? 'dark' : 'light'); } catch (err) {}
		});
	}

	/* ── Search overlay ── */
	function initSearch() {
		var overlay = document.querySelector('[data-sit-search-overlay]');
		var input = document.querySelector('[data-sit-search-input]');
		var results = document.querySelector('[data-sit-search-results]');
		var backdrop = document.querySelector('[data-sit-search-backdrop]');
		var closeBtn = document.querySelector('[data-sit-search-close]');
		if (!overlay || !input) return;

		var toggleBtns = document.querySelectorAll('[data-sit-search-toggle]');
		var timer = null;
		var cfg = window.sitSearchCfg || {};
		var restBase = cfg.restUrl || '/wp-json/wp/v2';
		var lang = cfg.lang || '';
		var strings = cfg.strings || {};

		function open() {
			overlay.classList.remove('hidden');
			document.body.classList.add('overflow-hidden');
			setTimeout(function () { input.focus(); }, 50);
		}
		function close() {
			overlay.classList.add('hidden');
			document.body.classList.remove('overflow-hidden');
			input.value = '';
			results.innerHTML = '<p class="py-8 text-center text-sm text-slate-400">' + esc(strings.placeholder || '') + '</p>';
		}

		toggleBtns.forEach(function (btn) { btn.addEventListener('click', open); });
		if (backdrop) backdrop.addEventListener('click', close);
		if (closeBtn) closeBtn.addEventListener('click', close);
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && !overlay.classList.contains('hidden')) close();
			if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); open(); }
		});

		function esc(s) {
			if (!s) return '';
			var d = document.createElement('div');
			d.textContent = String(s);
			return d.innerHTML;
		}

		function renderGroup(title, items) {
			if (!items.length) return '';
			var html = '<div class="mb-4"><p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">' + esc(title) + '</p><ul class="space-y-1">';
			items.forEach(function (it) {
				html += '<li><a href="' + esc(it.link) + '" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 transition-colors hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">';
				html += '<span class="min-w-0 truncate">' + esc(it.title) + '</span>';
				html += '</a></li>';
			});
			html += '</ul></div>';
			return html;
		}

		function doSearch(q) {
			if (q.length < 2) {
				results.innerHTML = '<p class="py-8 text-center text-sm text-slate-400">' + esc(strings.placeholder || '') + '</p>';
				return;
			}
			results.innerHTML = '<p class="py-8 text-center text-sm text-slate-400">' + esc(strings.loading || 'Yüklənir...') + '</p>';

			var langParam = lang ? '&lang=' + lang : '';
			var endpoints = [
				{ url: restBase + '/university?search=' + encodeURIComponent(q) + '&per_page=5' + langParam, label: strings.universities || 'Universitetlər' },
				{ url: restBase + '/program?search=' + encodeURIComponent(q) + '&per_page=5' + langParam, label: strings.programs || 'Proqramlar' },
				{ url: restBase + '/posts?search=' + encodeURIComponent(q) + '&per_page=5', label: strings.blog || 'Bloq' },
			];

			Promise.all(endpoints.map(function (ep) {
				return fetch(ep.url, { credentials: 'same-origin' })
					.then(function (r) { return r.ok ? r.json() : []; })
					.catch(function () { return []; });
			})).then(function (all) {
				var html = '';
				all.forEach(function (data, idx) {
					var items = (Array.isArray(data) ? data : []).map(function (p) {
						return {
							title: p.title && p.title.rendered ? p.title.rendered : (p.title || ''),
							link: p.link || '#',
						};
					});
					html += renderGroup(endpoints[idx].label, items);
				});
				if (!html) html = '<p class="py-8 text-center text-sm text-slate-400">' + esc(strings.noResults || 'Nəticə tapılmadı.') + '</p>';
				results.innerHTML = html;
			});
		}

		input.addEventListener('input', function () {
			clearTimeout(timer);
			timer = setTimeout(function () { doSearch(input.value.trim()); }, 300);
		});
	}

	/* ── Language dropdown ── */
	function initLang() {
		var wrap = document.querySelector('[data-sit-lang-wrap]');
		var toggle = document.querySelector('[data-sit-lang-toggle]');
		var dropdown = document.querySelector('[data-sit-lang-dropdown]');
		var chevron = document.querySelector('[data-sit-lang-chevron]');
		if (!wrap || !toggle || !dropdown) return;

		toggle.addEventListener('click', function () {
			var open = dropdown.classList.contains('hidden');
			dropdown.classList.toggle('hidden', !open);
			if (chevron) chevron.style.transform = open ? 'rotate(180deg)' : '';
		});

		document.addEventListener('click', function (e) {
			if (!wrap.contains(e.target)) {
				dropdown.classList.add('hidden');
				if (chevron) chevron.style.transform = '';
			}
		});
	}

	initNav();
	initDark();
	initSearch();
	initLang();
})();
