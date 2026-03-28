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
	var grid = root.querySelector('[data-sit-prog-grid]');
	var pag = root.querySelector('[data-sit-prog-pagination]');
	var summary = root.querySelector('[data-sit-prog-summary]');
	var countEl = root.querySelector('[data-sit-prog-count]');
	var loading = root.querySelector('[data-sit-prog-loading]');
	var wrap = root.querySelector('[data-sit-prog-table-wrap]');

	if (!form || !grid) {
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
			return '';
		}
		return arr
			.map(function (t) {
				return t.name || '';
			})
			.filter(Boolean)
			.join(', ');
	}

	function fmtMoney(n) {
		if (n === null || n === undefined || isNaN(n)) {
			return '';
		}
		var x = Number(n);
		if (x <= 0) {
			return '';
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

	function cardHtml(it) {
		var logoUrl = it.university_logo_url || '';
		var uniHref = it.university_link || it.link;
		var uniTitle = it.university_title || '';
		var fieldLine = fmtTerms(it.field_of_study);
		var deg = fmtTerms(it.degree_type);
		var langs = fmtTerms(it.program_language);
		var dur = it.duration || '';

		var logoBlock;
		if (logoUrl) {
			logoBlock =
				'<a href="' +
				esc(uniHref) +
				'" class="h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-slate-100 bg-white p-1 dark:border-slate-700">' +
				'<img src="' +
				esc(logoUrl) +
				'" alt="" class="h-full w-full object-contain" loading="lazy" width="56" height="56" />' +
				'</a>';
		} else {
			var letter = uniTitle ? String(uniTitle).charAt(0) : '★';
			logoBlock =
				'<div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-lg font-bold text-brand-600 dark:bg-brand-950/80 dark:text-brand-300" aria-hidden="true">' +
				esc(letter) +
				'</div>';
		}

		var uniName =
			it.university_link && it.university_title
				? '<a href="' +
				  esc(it.university_link) +
				  '" class="text-sm font-semibold text-slate-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-300">' +
				  esc(it.university_title) +
				  '</a>'
				: '<span class="text-sm font-semibold text-slate-500">—</span>';

		var fieldSub = fieldLine
			? '<p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">' + esc(fieldLine) + '</p>'
			: '';

		var degLine = deg
			? '<div class="flex gap-2"><dt class="shrink-0 font-medium text-slate-500 dark:text-slate-500">' +
			  esc(cfg.strings.degree) +
			  '</dt><dd>' +
			  esc(dur ? deg + ' (' + dur + ')' : deg) +
			  '</dd></div>'
			: '';

		var langLine = langs
			? '<div class="flex gap-2"><dt class="shrink-0 font-medium text-slate-500 dark:text-slate-500">' +
			  esc(cfg.strings.languages) +
			  '</dt><dd>' +
			  esc(langs) +
			  '</dd></div>'
			: '';

		var fee = it.tuition_fee;
		var ref = it.tuition_fee_reference;
		var feeBlock;
		if (fee !== null && fee !== undefined && !isNaN(fee) && Number(fee) > 0) {
			var main = fmtMoney(fee);
			if (ref !== null && ref !== undefined && !isNaN(ref) && Number(ref) > Number(fee)) {
				feeBlock =
					'<span class="text-brand-600 dark:text-brand-400">' +
					esc(main) +
					'$</span><span class="ms-1 text-slate-400 line-through dark:text-slate-500">' +
					esc(fmtMoney(ref)) +
					'$</span>';
			} else {
				feeBlock = '<span>' + esc(main) + '$</span>';
			}
			feeBlock +=
				'<span class="ms-1 text-xs font-normal text-slate-500">' + esc(cfg.strings.perYear) + '</span>';
		} else {
			feeBlock = '<span class="text-slate-400">—</span>';
		}

		return (
			'<article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900">' +
			'<div class="flex gap-3 border-b border-slate-100 p-4 dark:border-slate-800">' +
			logoBlock +
			'<div class="min-w-0 flex-1">' +
			uniName +
			fieldSub +
			'</div></div>' +
			'<div class="flex flex-1 flex-col gap-2 px-4 py-3">' +
			'<p class="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">' +
			esc(cfg.strings.program) +
			'</p>' +
			'<h2 class="text-base font-semibold leading-snug text-slate-900 dark:text-white">' +
			'<a href="' +
			esc(it.link) +
			'" class="text-inherit hover:text-brand-600 dark:hover:text-brand-400">' +
			esc(it.title) +
			'</a></h2>' +
			'<dl class="mt-1 grid gap-1.5 text-xs text-slate-600 dark:text-slate-400">' +
			degLine +
			langLine +
			'</dl></div>' +
			'<div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-3 dark:border-slate-800">' +
			'<div class="text-sm font-semibold text-slate-900 dark:text-white">' +
			feeBlock +
			'</div>' +
			'<a href="' +
			esc(it.link) +
			'" class="inline-flex min-h-[2.5rem] items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-700 dark:hover:bg-brand-500">' +
			esc(cfg.strings.apply) +
			'</a></div></article>'
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
					: 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700');
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
			'<span class="px-2 text-sm text-slate-600 dark:text-slate-400">' +
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
		if (countEl) {
			countEl.textContent = String(Math.max(0, total));
		}
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
					grid.innerHTML =
						'<div class="col-span-full rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center text-slate-600 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">' +
						esc(cfg.strings.empty) +
						'</div>';
				} else {
					grid.innerHTML = items.map(cardHtml).join('');
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
				grid.innerHTML =
					'<div class="col-span-full rounded-2xl border border-dashed border-red-200 bg-red-50 px-6 py-12 text-center text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">' +
					esc(cfg.strings.error) +
					'</div>';
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
