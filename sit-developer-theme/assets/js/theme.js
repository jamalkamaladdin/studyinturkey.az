(function () {
	'use strict';

	function initNav() {
		var toggle = document.querySelector('[data-sit-nav-toggle]');
		var panel = document.querySelector('[data-sit-nav-panel]');
		if (!toggle || !panel) {
			return;
		}
		toggle.addEventListener('click', function () {
			panel.classList.toggle('hidden');
			var expanded = !panel.classList.contains('hidden');
			toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
		});
	}

	function initDark() {
		var key = 'sit-theme-pref';
		var root = document.documentElement;
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-sit-dark-toggle]');
			if (!btn) {
				return;
			}
			e.preventDefault();
			var on = !root.classList.contains('dark');
			root.classList.toggle('dark', on);
			try {
				localStorage.setItem(key, on ? 'dark' : 'light');
			} catch (err) {
				/* ignore */
			}
		});
	}

	initNav();
	initDark();
})();
