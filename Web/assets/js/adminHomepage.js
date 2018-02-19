"use strict";

jQuery(function($) {

    // -------------------------------------------------------------------------------------------------------

    // Better user experience with scroll
    $(window).on('load hashchange', function() {
        // Scroll to bloc-"name-of-bloc"
		var hash = window.location.hash;
		if (hash) {
			$('html, body').animate({
				scrollTop: $('#bloc-' + hash.replace('#', '')).offset().top + 'px'
			}, '700');
		}

        // Scroll to form notice messages boxes if it is visible (obviously, in case of no AJAX mode).
        $('.form-error, .form-success').each(function() {
            if ($(this).is(':visible')) {
                $('html, body').animate({
                    scrollTop: ($(this).offset().top - 125) + 'px'
                }, '700');
                return false;
            }
        });

        // Position paging sliders correctly after form action submission (after error or success redirection)
        $('.slider-paging').each(function() {
            if ($(this).length > 0 && $(this)[0].hasAttribute('data-slide-rank') && parseInt($(this).data('slide-rank')) > 1) {
                var slideRank = $(this).data('slide-rank');
                var slideQuantity = $(this).find('.slide-item:last-child').attr('id').replace('slide-item-', '');
                // Check validity of slideRank value
                if (slideRank > 1 && slideRank <= slideQuantity) {
                    $(this).slick('slickGoTo', slideRank, true);
                } else {
                    $(this).slick('slickGoTo', 1, false);
                }
            }
        });
	});

    // -------------------------------------------------------------------------------------------------------

    // Slick slider for contact list paging
    var contactListSlider = $('.contact-list-paging').slick({
        dots: true,
        infinite: false,
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true,
        appendArrows: $('.section-admin-contact-list .slider-navigation'),
        appendDots: $('.section-admin-contact-list .slider-navigation'),
        slide: '.slide-item',
        prevArrow: '<button type="button" class="slick-prev btn btn-link"><i class="btn btn-link now-ui-icons arrows-1_minimal-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next btn btn-link"><i class="now-ui-icons arrows-1_minimal-right"></i></button>',

    });

    // Slick slider for comment list paging
    var commentListSlider = $('.comment-list-paging').slick({
        dots: true,
        infinite: false,
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true,
        appendArrows: $('.section-admin-comment-list .slider-navigation'),
        appendDots: $('.section-admin-comment-list .slider-navigation'),
        slide: '.slide-item',
        prevArrow: '<button type="button" class="slick-prev btn btn-link"><i class="btn btn-link now-ui-icons arrows-1_minimal-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next btn btn-link"><i class="now-ui-icons arrows-1_minimal-right"></i></button>',

    });

    // Hide paging on sliders if there is only one generated slide item (which contains several elements)
    $('.slider-navigation').each(function() {
        if ($(this).find('.slick-dots li').length == 1) {
            $(this).hide();
        }
    });

});
