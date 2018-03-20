"use strict";

jQuery(function($) {
    // --- JQuery validation ---

    // -------------------------------------------------------------------------------------------------------

    var formSelector = '.login-form',
        formIdentifier = 'lif_';

    // -------------------------------------------------------------------------------------------------------

    // Better user experience with scroll
    // Scroll to comment form notice message box if it is visible (obviously, in case of no AJAX mode).
    $('.form-error, .form-success').each(function() {
        if ($(this).is(':visible')) {
            $('html, body').animate({
                scrollTop: ($(this).offset().top - 125) + 'px'
            }, '700');
        }
    });

    // -------------------------------------------------------------------------------------------------------

    // User inputs are modified.
    // Field type 'check' (CSRF token) is not checked on client side (but can be if AJAX is implemented)
    var currentElement,
        elements,
        recaptchaType = formSelector + ' #form-recaptcha',
        fieldType = formSelector + ' .input-group input[type="email"],' +
        formSelector + ' .input-group input[type="text"],' + // password field type changed with changeType() function when password is shown!
        formSelector + ' .input-group input[type="password"]';

    // Manage errors on fields but not for current field which is modified
    $(document).on('change keyup input paste recaptchaResponse', fieldType + ',' + recaptchaType, function(e) {
        // Look at /assets/js/phpblog.js for declared functions
        // Particular case to exclude Google Recaptcha widget
        if ($(this)[0] == $(recaptchaType)[0]) {
            elements = $(fieldType);
        } else { // Exclude $(this)
            currentElement = $(this);
            elements = $(fieldType).not(currentElement);
        }
        // Update show/hide notice message box only after submission failed
        if (parseInt($(formSelector).data('try-validation')) == 1) {
            elements.each(function() {
                // Here, $(this) corresponds to each element in loop
                var elementInLoop = $(this);
                // Check all fields but not current element
                checkForm(formIdentifier, elementInLoop, null);
            });
            // Check current field with event trigger but not for Google Recaptcha
            if ($(this)[0] != $(recaptchaType)[0]) {
                $(fieldType).trigger('otherFieldsChecked');
            }
        } else {
            // Check current field but not for Google Recaptcha
            if ($(this)[0] != $(recaptchaType)[0]) {
                delay(function() {
                    checkForm(formIdentifier, currentElement, null);
                }, 1000);
            }
        }
    });

     // Manage error notice box and error on current field, only if user already tried to validate the form
     $(document).on('otherFieldsChecked', fieldType, function(e) {
        delay(function() {
            // Check current element
            checkForm(formIdentifier, currentElement, null);
            // Update show/hide notice message box
            showNoticeMessage(true, false);
        }, 1000);
     });

    // Mask/unmask password to help user
    var checked = false;
    $(document).on('click', '.unmask-pwd', function() {
        $('#lif_show_password').trigger('change');
        if (checked) {
            changeType($('input#lif_password'), 'text');
        } else {
            changeType($('input#lif_password'), 'password');
            return false;
        }
    });

    // Manage custom checkbox "change" event to switch show/hide password
    $(document).on('change', '#lif_show_password', function() {
        var attr = $(this).prop('checked');
        if ($(this).is(":checked")) {
            $(this).prop('checked', false);
            checked = false;
        } else {
            $(this).prop('checked', true);
            checked = true;
        }
    });
});

// -------------------------------------------------------------------------------------------------------
// Main variables used in functions declaration
var fieldErrorMessage,
    success = false,
    errorsOnFields = [];

// Verify validity on fields
var checkForm = function(formIdentifier, element, functionsArray) {
        // Check element field
        fieldErrorMessage = element.parent('.input-group').prev('.text-danger');
        switch (element.attr('id')) {
            case formIdentifier + 'email':
                var pattern = /^\s*\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+\s*$/g;
                var is_email = pattern.test(element.val());
                if (element.val().replace(/^\s+|\s+$/gm,'') == '') {
                    fieldErrorMessage.html('&nbsp;Please fill in your email address.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    if (fieldErrorMessage.hasClass('form-hide')) { fieldErrorMessage.removeClass('form-hide').hide(); }
                    fieldErrorMessage.fadeIn(700);
                    errorsOnFields[element.attr('id')] = true;
                } else if (!is_email) {
                    fieldErrorMessage.html('&nbsp;Sorry, "<span class="text-muted">' + element.val() +
                    '</span>" is not a valid email address!<br>Please check its format.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    if (fieldErrorMessage.hasClass('form-hide')) { fieldErrorMessage.removeClass('form-hide').hide(); }
                    fieldErrorMessage.fadeIn(700);
                    errorsOnFields[element.attr('id')] = true;
                } else {
                    fieldErrorMessage.fadeOut(700);
                    errorsOnFields[element.attr('id')] = false;
                }
                break;
            case formIdentifier + 'password':
                // At least 1 number, 1 lowercase letter, 1 uppercase letter, 1 special character, a minimum of 8 characters
                var pattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}$/gm;
                var is_password = pattern.test(element.val());

                if (element.val().replace(/^\s+|\s+$/gm,'') == '') {
                    fieldErrorMessage.html('&nbsp;Please fill in your password.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    if (fieldErrorMessage.hasClass('form-hide')) { fieldErrorMessage.removeClass('form-hide').hide(); }
                    fieldErrorMessage.fadeIn(700);
                    errorsOnFields[element.attr('id')] = true;
                } else if (element.val().length < 8) {
                    fieldErrorMessage.html('&nbsp;Sorry, your password must contain<br>at least 8 characters!<br>Please check it before login try.' +
                    '&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    if (fieldErrorMessage.hasClass('form-hide')) { fieldErrorMessage.removeClass('form-hide').hide(); }
                    fieldErrorMessage.fadeIn(700);
                    errorsOnFields[element.attr('id')] = true;
                } else if (!is_password) {
                    fieldErrorMessage.html('&nbsp;Sorry, your password format is not valid!<br>Please check it or verify required characters<br>before login try.' +
                    '&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    if (fieldErrorMessage.hasClass('form-hide')) { fieldErrorMessage.removeClass('form-hide').hide(); }
                    fieldErrorMessage.fadeIn(700);
                    errorsOnFields[element.attr('id')] = true;
                } else {
                    fieldErrorMessage.fadeOut(700);
                    errorsOnFields[element.attr('id')] = false;
                }
                break;
        }
        // Is it a success state?
        for (var i in errorsOnFields) {
           if (errorsOnFields[i] == true) {
                success = false;
                break;
           } else {
                success = true;
           }
        }
    }

// Zurb foundation add on: show
// https://foundation.zurb.com/building-blocks/blocks/show-password.html
var changeType = function(x, type) {
        if (x.prop('type') == type) return x; // That was easy.
        if (timer === undefined) {
            var timer = 0;
        } else {
            clearTimeout(timer);
        }
        try {
            return x.prop('type', type); // Stupid IE security will not allow this
        } catch(e) {
            // Try re-creating the element
            // jQuery has no html() method for the element, so we have to put into a div first
            var html = $("<div>").append(x.clone()).html();
            var regex = /type=(\")?([^\"\s]+)(\")?/; // matches type=text or type="text"
            // If no match, we add the type attribute to the end; otherwise, we replace
            var tmp = $(html.match(regex) == null ?
            html.replace(">", ' type="' + type + '">') :
            html.replace(regex, 'type="' + type + '"') );
            // Copy data from old element
            tmp.data('type', x.data('type'));
            var events = x.data('events');
            var cb = function(events) {
                return function() {
                    // Bind all prior events
                    for (var i in events) {
                        var y = events[i];
                        for (var j in y) {
                            tmp.bind(i, y[j].handler);
                        }
                    }
                  }
                }(events);
            x.replaceWith(tmp);
            // Wait a bit to call function
            timer = setTimeout(cb, 10);
            return tmp;
        }
    }