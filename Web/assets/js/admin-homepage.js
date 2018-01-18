"use strict";

jQuery(function($) {
	$(window).on('load', function() {
		var hash = window.location.hash;
		console.log(typeof hash, hash);
		if (hash) {
			console.log('hash');
			$('html, body').animate({
				scrollTop: $('#bloc-' + hash.replace('#', '')).offset().top + 'px'
			}, '700');
		}
	});
});