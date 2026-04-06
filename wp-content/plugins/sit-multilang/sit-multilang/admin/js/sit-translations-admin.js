/**
 * Tab switching for SIT Multilang translation panels.
 */
(function ($) {
	'use strict';

	function activateTab($wrap, lang) {
		$wrap.find('.sit-ml-tabs .nav-tab').removeClass('nav-tab-active');
		$wrap.find('.sit-ml-tabs .nav-tab[data-sit-lang="' + lang + '"]').addClass('nav-tab-active');
		$wrap.find('.sit-ml-panel').removeClass('is-active');
		$wrap.find('.sit-ml-panel[data-sit-lang="' + lang + '"]').addClass('is-active');
	}

	$(document).on('click', '.sit-ml-translations .sit-ml-tabs .nav-tab', function (e) {
		e.preventDefault();
		var $tab = $(this);
		var lang = $tab.attr('data-sit-lang');
		var $wrap = $tab.closest('.sit-ml-translations');
		if (!lang || !$wrap.length) {
			return;
		}
		activateTab($wrap, lang);
	});

	$(function () {
		$('.sit-ml-translations').each(function () {
			var $wrap = $(this);
			var def = $wrap.attr('data-default-lang');
			var $first = $wrap.find('.sit-ml-tabs .nav-tab').first();
			var lang = def || $first.attr('data-sit-lang');
			if (lang) {
				activateTab($wrap, lang);
			}
		});
	});
})(jQuery);
