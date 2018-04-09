"use strict";

jQuery(function($) {

    // -------------------------------------------------------------------------------------------------------

    $(window).on('load hashchange', function(e) {
        // Better user experience with scroll
        // Scroll to form notice messages boxes if it is visible (obviously, in case of no AJAX mode).
        $('.form-error, .form-success').each(function() {
            if ($(this).is(':visible')) {
                $('html, body').animate({
                    scrollTop: ($(this).offset().top - 125) + 'px'
                }, '700');
            }
        });
        // Scroll to bloc-"name-of-bloc"
        var hash = window.location.hash;
        if (hash) {
            $('html, body').animate({
                scrollTop: ($('#bloc-' + hash.replace('#', '')).offset().top - 125) + 'px'
            }, '700');
        }
        // Position paging sliders correctly after form action submission (after error or success redirection)
        $('.slider-paging').each(function() {
            if ($(this).length > 0 && $(this)[0].hasAttribute('data-slide-rank') && parseInt($(this).data('slide-rank')) > 1) {
                // Slick slider starts at 0 and var slide-rank starts at 1
                var slideRank = parseInt($(this).data('slide-rank'));
                var slideQuantity = parseInt($(this).find('.slide-item:last-child').data('slide-item'));
                // Check validity of slideRank value
                if (slideRank > 1 && slideRank <= slideQuantity) {
                    // Position on slide with rank "slideRank - 1": slick slider starts at 0
                    $(this).slick('slickGoTo', slideRank - 1, true);
                // This case happens if slideRank corresponds to the last slide and does not exist anymore after deleting action
                // "slideRank" is not updated on client side contrary to "slideQuantity".
                // (no more slide with "slideRank" rank exists because no more items are inside)
                } else if (slideRank > slideQuantity) {
                    // Position on last slide
                    $(this).slick('slickGoTo', slideQuantity, false);
                } else {
                    // Position on slide with rank 0: number 0 corresponds to slide 1
                    $(this).slick('slickGoTo', 0, false);
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

    // Hide paging on sliders if there is only one generated slide item (which contains several elements)
    $('.slider-navigation').each(function() {
        if ($(this).find('.slick-dots li').length == 1) {
            $(this).hide();
        }
    });
});