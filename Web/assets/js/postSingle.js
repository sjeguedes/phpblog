"use strict";

jQuery(function($) {

    // -------------------------------------------------------------------------------------------------------

    // Slick slider for single post comment list paging
    var commentListSlider = $('.post-comment-list-paging').slick({
        dots: true,
        infinite: false,
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true,
        appendArrows: $('.section-post-comment-list .slider-navigation'),
        appendDots: $('.section-post-comment-list .slider-navigation'),
        slide: '.slide-item',
        prevArrow: '<button type="button" class="slick-prev btn btn-link"><i class="btn btn-link now-ui-icons arrows-1_minimal-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next btn btn-link"><i class="now-ui-icons arrows-1_minimal-right"></i></button>',

    });

    // -------------------------------------------------------------------------------------------------------

    // Hide paging on slider if there is only one generated slide item (which contains several elements)
    $('.section-post-comment-list .slider-navigation').each(function() {
        if ($(this).find('.slick-dots li').length == 1) {
            $(this).hide();
        }
    });

    // -------------------------------------------------------------------------------------------------------

});