(function () {
	'use strict';

	var root = document.querySelector('[data-sit-prog-root]');
	if (!root) {
		return;
	}

	var cfg = window.sitProgramsArchive || {};
	if (!cfg.restUrl) {
		return;
	}

	var form = root.querySelector('[data-sit-prog-form]');
	var tbody = root.querySelector('[data-sit-prog-tbody]');
	var pag = root.querySelector('[data-sit-prog-pagination]');
	var summary = root.querySelector('[data-sit-prog-summary]');
	var loading = root.querySelector('[data-sit-prog-loading]');
	var wrap = root.querySelector('[data-sit-prog-table-wrap]');

	if (!form || !tbody) {
		return;
	}

	var perPage = parseInt(cfg.perPage, 10) || 12;
	var lang = cfg.lang || '';

	function esc(s) {
		if (!s) {
			return '';
		}
		var d = document.createElement('div');
		d.textContent = String(s);
		return d.innerHTML;
	}

	function fmtTerms(arr) {
		if (!arr || !arr.length) {
			return '—';
		}
		return arr
			.map(function (t) {
				return t.name || '';
			})
			.filter(Boolean)
			.join(', ') || '—';
	}

	function fmtMoney(n) {
		if (n === null || n === undefined || isNaN(n)) {
			return '—';
		}
		var x = Number(n);
		if (x <= 0) {
			return '—';
		}
		try {
			return new Intl.NumberFormat(cfg.locale || undefined, {
				maximumFractionDigits: 0,
				minimumFractionDigits: 0,
			}).format(Math.round(x));
		} catch (e2) {
			return String(Math.round(x));
		}
	}

	function rowHtml(it) {
		var uniCell = '—';
		if (it.university_link && it.university_title) {
			uniCell =
				'<a href="' +
				esc(it.university_link) +
				'" class="hover:text-brand-700">' +
				esc(it.university_title) +
				'</a>';
		}
		var sch = it.scholarship_available
			? '<span class="inline-flex rounded-md bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-800 dark:bg-brand-950/80 dark:text-brand-200">' +
			  esc(cfg.strings.scholarshipYes) +
			  '</span>'
			: '<span class="text-slate-400 dark:text-slate-500">—</span>';
		return (
			'<tr class="border-b border-slate-100 hover:bg-slate-50/80 dark:border-slate-800 dark:hover:bg-slate-800/50">' +
			'<td class="px-3 py-3 font-medium text-slate-900 dark:text-slate-100"><a href="' +
			esc(it.link) +
			'" class="text-brand-700 hover:text-brand-600">' +
			esc(it.title) +
			'</a></td>' +
			'<td class="hidden px-3 py-3 text-sm text-slate-600 dark:text-slate-400 lg:table-cell">' +
			uniCell +
			'</td>' +
			'<td class="hidden px-3 py-3 text-sm text-slate-600 dark:text-slate-400 md:table-cell">' +
			esc(fmtTerms(it.degree_type)) +
			'</td>' +
			'<td class="hidden px-3 py-3 text-sm text-slate-600 dark:text-slate-400 xl:table-cell">' +
			esc(fmtTerms(it.program_language)) +
			'</td>' +
			'<td class="hidden px-3 py-3 text-sm text-slate-600 dark:text-slate-400 2xl:table-cell">' +
			esc(fmtTerms(it.field_of_study)) +
			'</td>' +
			'<td class="whitespace-nowrap px-3 py-3 text-sm text-slate-700 dark:text-slate-300">' +
			esc(fmtMoney(it.tuition_fee)) +
			'</td>' +
			'<td class="hidden px-3 py-3 text-sm text-slate-600 dark:text-slate-400 sm:table-cell">' +
			esc(it.duration || '—') +
			'</td>' +
			'<td class="px-3 py-3 text-center text-sm">' +
			sch +
			'</td>' +
			'<td class="px-3 py-3 text-end text-sm"><a href="' +
			esc(it.link) +
			'" class="font-semibold text-brand-700 hover:text-brand-600">' +
			esc(cfg.strings.view) +
			'</a></td>' +
			'</tr>'
		);
	}

	function restParams(page) {
		var fd = new FormData(form);
		var p = new URLSearchParams();
		p.set('page', String(page));
		p.set('per_page', String(perPage));
		if (lang) {
			p.set('lang', lang);
		}
		var deg = fd.get('sit_degree');
		if (deg) {
			p.set('degree', deg);
		}
		var lng = fd.get('sit_language');
		if (lng) {
			p.set('language', lng);
		}
		var fld = fd.get('sit_field');
		if (fld) {
			p.set('field', fld);
		}
		var city = fd.get('sit_city');
		if (city) {
			p.set('city', city);
		}
		var pmin = fd.get('sit_price_min');
		if (pmin !== null && pmin !== '') {
			p.set('price_min', String(pmin));
		}
		var pmax = fd.get('sit_price_max');
		if (pmax !== null && pmax !== '') {
			p.set('price_max', String(pmax));
		}
		var sort = fd.get('sit_sort');
		if (sort) {
			p.set('sort', sort);
		}
		var univ = fd.get('sit_university');
		if (univ) {
			p.set('university', String(univ));
		}
		return p;
	}

	function urlParamsForHistory() {
		var fd = new FormData(form);
		var p = new URLSearchParams();
		[
			'sit_university',
			'sit_city',
			'sit_degree',
			'sit_language',
			'sit_field',
			'sit_price_min',
			'sit_price_max',
			'sit_sort',
		].forEach(function (name) {
			var v = fd.get(name);
			if (v !== null && v !== '') {
				p.set(name, String(v));
			}
		});
		return p;
	}

	function setLoading(on) {
		if (!loading) {
			return;
		}
		loading.classList.toggle('hidden', !on);
		loading.classList.toggle('flex', on);
		if (wrap) {
			wrap.setAttribute('aria-busy', on ? 'true' : 'false');
		}
	}

	function renderPagination(totalPages, current) {
		if (!pag) {
			return;
		}
		if (totalPages <= 1) {
			pag.innerHTML = '';
			pag.classList.add('hidden');
			pag.classList.remove('flex');
			return;
		}
		pag.classList.remove('hidden');
		pag.classList.add('flex');
		function btn(page, label, disabled) {
			var cls =
				'rounded-lg border px-3 py-1.5 text-sm ' +
				(disabled
					? 'cursor-not-allowed border-slate-100 text-slate-300'
					: 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50');
			return (
				'<button type="button" class="' +
				cls +
				'" data-sit-prog-page="' +
				page +
				'"' +
				(disabled ? ' disabled' : '') +
				'>' +
				label +
				'</button>'
			);
		}
		var mid =
			'<span class="px-2 text-sm text-slate-600">' +
			esc(cfg.strings.pageOf.replace('%1$s', String(current)).replace('%2$s', String(totalPages))) +
			'</span>';
		var inner =
			'<div class="flex flex-wrap items-center justify-center gap-2">' +
			btn(current - 1, cfg.strings.prev, current <= 1) +
			mid +
			btn(current + 1, cfg.strings.next, current >= totalPages) +
			'</div>';
		pag.innerHTML = inner;
	}

	function updateSummary(total) {
		if (!summary) {
			return;
		}
		if (total < 1) {
			summary.textContent = cfg.strings.none;
			return;
		}
		var tpl = cfg.strings.summary;
		summary.textContent = tpl.replace('%d', String(total));
	}

	function pushHistory(page) {
		var p = urlParamsForHistory();
		if (page > 1) {
			p.set('paged', String(page));
		}
		var qs = p.toString();
		var base = cfg.archiveUrl || window.location.href.split('?')[0];
		try {
			var u = new URL(base, window.location.origin);
			u.search = qs;
			window.history.pushState({ sitProg: true, page: page }, '', u.pathname + u.search + u.hash);
		} catch (e) {
			/* ignore */
		}
	}

	function fetchPage(page, push) {
		setLoading(true);
		var url = cfg.restUrl;
		if (url.indexOf('?') === -1) {
			url += '?';
		} else {
			url += '&';
		}
		url += restParams(page).toString();
		fetch(url, { credentials: 'same-origin' })
			.then(function (r) {
				if (!r.ok) {
					throw new Error('HTTP ' + r.status);
				}
				return r.json();
			})
			.then(function (data) {
				var items = data.items || [];
				if (!items.length) {
					tbody.innerHTML =
						'<tr><td colspan="9" class="px-3 py-10 text-center text-slate-600">' +
						esc(cfg.strings.empty) +
						'</td></tr>';
				} else {
					tbody.innerHTML = items.map(rowHtml).join('');
				}
				var total = typeof data.total === 'number' ? data.total : 0;
				var totalPages = typeof data.total_pages === 'number' ? data.total_pages : 0;
				updateSummary(total);
				renderPagination(totalPages, page);
				if (push) {
					pushHistory(page);
				}
			})
			.catch(function () {
				tbody.innerHTML =
					'<tr><td colspan="9" class="px-3 py-10 text-center text-red-600">' +
					esc(cfg.strings.error) +
					'</td></tr>';
				if (pag) {
					pag.innerHTML = '';
				}
			})
			.finally(function () {
				setLoading(false);
			});
	}

	if (pag) {
		pag.addEventListener('click', function (e) {
			var t = e.target.closest('[data-sit-prog-page]');
			if (!t || t.disabled) {
				return;
			}
			e.preventDefault();
			var pg = parseInt(t.getAttribute('data-sit-prog-page'), 10);
			if (isNaN(pg) || pg < 1) {
				return;
			}
			fetchPage(pg, true);
		});
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		fetchPage(1, true);
	});

	window.addEventListener('popstate', function () {
		var u = new URL(window.location.href);
		var p = parseInt(u.searchParams.get('paged') || '1', 10);
		if (isNaN(p) || p < 1) {
			p = 1;
		}
		['sit_university', 'sit_city', 'sit_degree', 'sit_language', 'sit_field', 'sit_price_min', 'sit_price_max', 'sit_sort'].forEach(
			function (name) {
				var el = form.elements.namedItem(name);
				if (!el) {
					return;
				}
				var v = u.searchParams.get(name);
				if (v === null || v === '') {
					el.value = '';
				} else {
					el.value = v;
				}
			}
		);
		fetchPage(p, false);
	});
})();
