"use strict";

jQuery(function($) {
    // --- Forms validation ---
    // -------------------------------------------------------------------------------------------------------

    // All forms (except contact form: look at /assets/js/sendContactMessage.js) are declared here:
    var formsInfos = {
        // Single post comment form
        0 : {
            cssClass : '.comment-form',
            identifier : 'pcf'
        },
        1 : {
            cssClass : '.login-form',
            identifier : 'lf'
        },
        2 : {
            cssClass : '.register-form',
            identifier : 'rf'
        },
        3 : {
            cssClass : '.insert-post-form',
            identifier : 'ipf'
        },
        4 : {
            cssClass : '.update-post-form',
            identifier : 'upf'
        },
        5 : {
            cssClass : '.delete-post-form',
            identifier : 'dpf'
        }
    }, forms = '';

    // Initialize forms selectors in one string (if needed)
    for (var item in formsInfos) {
        if (item == 0) {
            forms =  forms + formsInfos[item].cssClass;
        } else {
            forms =  forms + ', ' + formsInfos[item].cssClass;
        }
    }

    // -------------------------------------------------------------------------------------------------------

    // Don't remove (Bootstrap normal behaviour) but hide notice message boxes
    $(document).on('click', '.alert .close', function(e) {
        e.stopPropagation();
        $(this).parent('.alert').slideUp(700, function() { $(this).addClass('form-hide'); });
    })

    // -------------------------------------------------------------------------------------------------------

    // Fix little issue for identical rendering (background-color/border properties, ...) during events on .input-group-addon elements and .form-control fields
    var previousObjectClicked,
        isSameElement = false,
        isInside = false,
        clicked = 0,
        formHTMLElement = '.input-group.phpblog-field-group';

    $(document).on('click', 'body', function(e) {
        clicked ++;
        // Not the first click
        if(clicked > 1) {
            // Previous object clicked is identical to current element clicked
            previousObjectClicked.is($(e.target)) ? isSameElement = true : isSameElement = false;

            // Previous object clicked is inside a ".phpblog-field-group" element
            previousObjectClicked.closest(formHTMLElement).length > 0 ?
            isInside = true : isInside = false;
        }

        // Click is inside a ".phpblog-field-group" element
        if($(e.target).closest(formHTMLElement).length > 0) {
            if(!$(e.target).closest(formHTMLElement).hasClass('active-field')) {
                $(e.target).closest(formHTMLElement).addClass('active-field');
            }
        } else {
             // Click is outside a ".phpblog-field-group" element
            if($(formHTMLElement).hasClass('active-field')) {
                $(formHTMLElement).removeClass('active-field');
            }
        }

        // Previous object clicked exists and was clicked at least twice and is inside a .phpblog-field-group element
        if(previousObjectClicked !== undefined && !isSameElement && isInside) {
            if(previousObjectClicked.closest(formHTMLElement).hasClass('active-field')) {
                previousObjectClicked.closest(formHTMLElement).removeClass('active-field');
            }
        }

        // Store current jQuery object clicked to become the previous element clicked
        previousObjectClicked = $(e.target);
    });

    // Manage focus around this fix
    $(document).on('focusin', '.form-control', function(e) {
        var parent = $(e.target).closest(formHTMLElement);
        if( !parent.hasClass('active-field')) {
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

// Helper: first letter to uppercase
var jsUcFirst = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

// Helper: first letter to lowercase
var jsLcFirst = function(string) {
        return string.charAt(0).toLowerCase() + string.slice(1);
    }

/// callback for Google Recaptcha response
var grcJSONResponse, grcResponse = false,
    verifyCallback = function(response) {
        $('#form-recaptcha').prev('.text-danger').fadeOut(700);
        grcResponse = true;
        grcJSONResponse = response;
        showNoticeMessage(false);
    }

// Call callback
var grc,
    onloadCallback = function() {
        grc = grecaptcha.render('form-recaptcha', {
            'callback' : verifyCallback
        });
    }

// Manage notice boxes display
var showNoticeMessage = function(isGRC, isSubmitted) {
        // !WARNING: Google recaptcha is not used: so behave like its response "grcResponse" is "true".
        if (!isGRC) {
            grcResponse = true; // Cancel Google recaptcha necessary validation
        }

        // Form is not submitted: simple JS validation
        if (!isSubmitted) { // In case of no AJAX MODE: notice box behaviour chosen
            if (success && grcResponse) {
                 // Ready!
                if ($('.form-error').is(':visible')) {
                    $('.form-error').slideUp(700, function() { $(this).addClass('form-hide'); });
                }
            } else {
                // Else case here to show notice error box, each time there is an error on any field.
                if ($('.form-error').is(':hidden')) {
                    $('.form-error').slideDown(700, function() { $(this).removeClass('form-hide'); });
                }
            }
        } else { // In case of AJAX MODE: notice box behaviour chosen
            // Form is submitted in AJAX mode
            if (success && grcResponse) {
                // All fields are completed correctly.
                if ($('.form-error').is(':visible')) {
                    $('.form-error').slideUp(700, function() { $(this).addClass('form-hide'); });
                }
                // AJAX mode: show success notice box
                // $(this) corresponds to form css selector
                if (parseInt($(this).data('ajax')) == 1 && $('.form-success').is(':hidden')) {
                    $('.form-success').slideDown(700, function() { $(this).removeClass('form-hide'); });
                }
            }
            // Errors on fields which prevent form to send user inputs.
            if ((!success && !grcResponse) || (!success && grcResponse) || (success && !grcResponse)) {
                // show error notice box
                if ($('.form-error').is(':hidden')) {
                    if ($('.form-success').is(':visible')) {
                        // Hide success notice box if it already exists (in case of previous success)
                        $('.form-success').slideUp(350, function() {
                            $(this).addClass('form-hide');
                            $('.form-error').slideDown(700, function() { $(this).removeClass('form-hide'); });
                        });
                    } else {
                        // Show error notice box
                        $('.form-error').slideDown(700, function() { $(this).removeClass('form-hide'); });
                    }
                } // Else: don't do anything
            }
        }
    }