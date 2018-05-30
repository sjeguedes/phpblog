"use strict";

jQuery(function($) {
	$(window).on('load', function() {
		var hash = window.location.hash;
		if (hash) {
			$('html, body').animate({
				scrollTop: $('#bloc-' + hash.replace('#', '')).offset().top + 'px'
			}, '700');
		}
	});
});