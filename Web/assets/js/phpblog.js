"use strict";

jQuery(function($) {

    // -------------------------------------------------------------------------------------------------------

    // Don't remove (Bootstrap normal behaviour) but hide notice message boxes when closed
    $(document).on('close.bs.alert', '.alert', function() {
        $(this).slideUp(700, function() { $(this).addClass('form-hide'); });
        return false;
    });

    // -------------------------------------------------------------------------------------------------------

    $(document).on('click', 'body', function(e) {
        clicked ++;
        // Not the first click
        if (clicked > 1) {
            // Previous object clicked is identical to current element clicked
            previousObjectClicked.is($(e.target)) ? isSameElement = true : isSameElement = false;

            // Previous object clicked is inside a ".phpblog-field-group" element
            previousObjectClicked.closest(formHTMLElement).length > 0 ?
            isInside = true : isInside = false;
        }
        // Click is inside a ".phpblog-field-group" element
        if ($(e.target).closest(formHTMLElement).length > 0) {
            if (!$(e.target).closest(formHTMLElement).hasClass('active-field')) {
                $(e.target).closest(formHTMLElement).addClass('active-field');
            }
        } else {
             // Click is outside a ".phpblog-field-group" element
            if ($(formHTMLElement).hasClass('active-field')) {
                $(formHTMLElement).removeClass('active-field');
            }
        }
        // Previous object clicked exists and was clicked at least twice and is inside a .phpblog-field-group element
        if (previousObjectClicked !== undefined && !isSameElement && isInside) {
            if (previousObjectClicked.closest(formHTMLElement).hasClass('active-field')) {
                previousObjectClicked.closest(formHTMLElement).removeClass('active-field');
            }
        }
        // Store current jQuery object clicked to become the previous element clicked
        previousObjectClicked = $(e.target);
    });

    // Manage focus around this fix
    $(document).on('focusin', '.form-control', function(e) {
        var parent = $(e.target).closest(formHTMLElement);
        if (!parent.hasClass('active-field')) {
            parent.addClass('active-field');
        }
    });

    $(document).on('focusout', '.form-control', function(e) {
        var parent = $(e.target).closest(formHTMLElement);
        if (parent.hasClass('active-field')) {
            parent.removeClass('active-field');
        }
    });
});

// -------------------------------------------------------------------------------------------------------

// Fix little issue for identical rendering (background-color/border properties, ...) during events on .input-group-addon elements and .form-control fields
var previousObjectClicked,
    isSameElement = false,
    isInside = false,
    clicked = 0,
    formHTMLElement = '.input-group.phpblog-field-group';

// Helper: first letter to uppercase
var jsUcFirst = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

// Helper: first letter to lowercase
var jsLcFirst = function(string) {
        return string.charAt(0).toLowerCase() + string.slice(1);
    }

// Start a delay with a callback
var delayed = null,
    delay = (function() {
        return function(callback, ms) {
            if (delayed != null) clearTimeout(delayed);
            delayed = setTimeout(callback, ms);
        };
    })();

// callback for Google Recaptcha response
var grcJSONResponse, grcResponse = false,
    verifyCallback = function(response) {
        $('#form-recaptcha').prev('.text-danger').fadeOut(700);
        grcResponse = true;
        grcJSONResponse = response;
        // "formSelector" is declared in each form validation JS file.
        if (parseInt($(formSelector).data('try-validation')) == 1) {
            showErrorNoticeMessage(true);
        }
    }

// Call Google Recaptcha callback
var grc,
    onloadCallback = function() {
        var mq = window.matchMedia("(max-width: 575px)");
        mq.addListener(recaptchaRenderer);
        recaptchaRenderer(mq);
    }

// Render Google Recaptcha with compact mode for mobile
var recaptchaRenderer = function(mq) {
        var recaptcha = $('#form-recaptcha'),
            data = recaptcha.data(),
            errorFieldElement = recaptcha.prev('.text-danger');
            parent = recaptcha.parent();
        recaptcha.empty().remove();
        var recaptchaClone = recaptcha.clone();
        errorFieldElement.after(recaptchaClone);
        recaptchaClone.data(data);
        var options = {
            'callback' : verifyCallback,
            'sitekey': data['sitekey'],
            'size': 'compact'
        };
        if (!mq.matches) {
            options['size'] = 'normal';
        }
        grecaptcha.render(recaptchaClone.get(0), options);
    }

// Manage error display when there is no response for Google Recaptcha
var noGoogleRecaptchaResponse = function() {
        if (!grcResponse) {
            fieldErrorMessage = $('#form-recaptcha').prev('.text-danger');
            fieldErrorMessage.html('&nbsp;Please confirm you are a human.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
            $('#form-recaptcha').prev('.text-danger').fadeIn(700);
        }
    }

// Manage error notice message box display
var showErrorNoticeMessage = function(useGoogleRecaptcha) {
        // Ready to send!
        if (success && grcResponse || success && !useGoogleRecaptcha) {
             // Hide error notice message
            if ($('.form-error').is(':visible')) {
                $('.form-error').slideUp(700, function() {
                    $(this).addClass('form-hide');
                    // Delete secondary message not to reshow it, if another error call error box slide down.
                    if ($('.form-token-notice, .form-check-notice').length > 0) {
                        $('.form-token-notice, .form-check-notice').each(function() {
                            $(this).remove();
                        });
                    }
                });
            }
        // Else case here to show notice error box, each time there is an error on any field.
        } else {
            // Show error notice message
            if ($('.form-error').is(':hidden')) {
                $('.form-error').slideDown(700, function() { $(this).removeClass('form-hide'); });
            }
        }
    }