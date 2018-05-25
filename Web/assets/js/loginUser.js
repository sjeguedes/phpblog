"use strict";

jQuery(function($) {
    // --- JQuery validation ---

    // -------------------------------------------------------------------------------------------------------

    // Fix call to scroll when page loaded for Safari: look at next script!
    if (navigator.userAgent.search('Safari') >= 0) {
        var interval = setInterval(function() {
            if (document.readyState === 'complete') {
                clearInterval(interval);
                // Redirect to /admin/login if a previous hash exists from admin pages lists (comments, contacts, posts...)
                var hash = window.location.hash;
                if (hash && /^#[\w\d-]+-list$/g.test(hash)) {
                    window.location.href = window.location.href.substr(0, window.location.href.indexOf('#'));
                }
                // Better user experience with scroll
                // Scroll to form notice message box if it is visible (obviously, in case of no AJAX mode).
                $('.form-error, .form-success').each(function() {
                    if ($(this).is(':visible')) {
                        $('html, body').animate({
                            scrollTop: ($(this).offset().top - 125) + 'px'
                        }, '700');
                    }
                });
            }
        }, 100);
    }

    $(window).on('load', function(e) {
        // Redirect to /admin/login if a previous hash exists from admin pages lists (comments, contacts, posts...)
        var hash = window.location.hash;
        if (hash && /^#[\w\d-]+-list$/g.test(hash)) {
            window.location.href = window.location.href.substr(0, window.location.href.indexOf('#'));
        }
        // Better user experience with scroll
        // Scroll to form notice message box if it is visible (obviously, in case of no AJAX mode).
        $('.form-error, .form-success').each(function() {
            if ($(this).is(':visible')) {
                $('html, body').animate({
                    scrollTop: ($(this).offset().top - 125) + 'px'
                }, '700');
            }
        });
        // Initialize form loaded state
        formJustLoaded = true;
        // Update fields error state on "fieldType"
        fieldsToUpdate($(fieldType));
    });

    // -------------------------------------------------------------------------------------------------------

    // Manage errors on fields
    $(document).on('change keyup input paste', fieldType, function(e) {
        // Hide disconnection error message
        if ($('.form-error.expired-session').is(':visible')) {
           $('.form-error.expired-session').slideUp(700).removeClass('expired-session');
        }
        // Look at /assets/js/phpblog.js for declared functions
        // Avoid multiple call to form check on the same element: one call is queued each time.
        if (fieldsInQueue.indexOf($(this).attr('id')) != -1) {
            return false;
        // Store field to be queued once a time
        } else {
            fieldsInQueue.push($(this).attr('id'));
            $(document).queue(function() {
                currentElement = $(e.target);
                // Check current field but not for Google Recaptcha
                // Apply a delay
                delay(function() {
                    // Check current element
                    checkForm(currentElement, null);
                    // Delete field stored in queue
                    var i = fieldsInQueue.indexOf(currentElement.attr('id'));
                    if (i != -1) {
                        fieldsInQueue.splice(i, 1);
                    }
                    // Show error notice message if user already tried to submit (no input in queue)
                    if ($(formSelector).data('try-validation') == 1 && fieldsInQueue.length == 0) {
                        showErrorNoticeMessage(true);
                    }
                    // Dequeue event
                    $(document).dequeue();
                }, 1000);
            });
        }
    });

    // Mask/unmask password to help user
    $(document).on('click', '.unmask-pwd', function() {
        $('#lif_show_password').trigger('change');
        if (showPasswordChecked) {
            changeType($('input#lif_password'), 'text');
        } else {
            changeType($('input#lif_password'), 'password');
            return false;
        }
    });

    // Manage custom checkbox "change" event to switch show/hide password
    $(document).on('change', '#lif_show_password', function() {
        if ($(this).is(":checked")) {
            $(this).prop('checked', false);
            showPasswordChecked = false;
        } else {
            $(this).prop('checked', true);
            showPasswordChecked = true;
        }
    });
});

// -------------------------------------------------------------------------------------------------------

// Form identifiers
var formSelector = '.login-form',
    formIdentifier = 'lif_';

// -------------------------------------------------------------------------------------------------------

// User inputs are modified.
// Field type 'check' (CSRF token) is not checked on client side (but can be if AJAX is implemented)
var formJustLoaded = false,
    currentElement,
    elements,
    fieldsInQueue = [],
    fieldsToCheck,
    fieldType = formSelector + ' .input-group input[type="email"],' +
    formSelector + ' .input-group input[type="text"],' + // password field type changed with changeType() function when password is shown!
    formSelector + ' .input-group input[type="password"]';

// -------------------------------------------------------------------------------------------------------

// Variable used to show/hide passwords
var showPasswordChecked = false;

// -------------------------------------------------------------------------------------------------------

// Main variables used in functions declaration
var fieldErrorMessage,
    onlyUpdateErrorsState = false,
    success = false,
    errorsOnFields = [];

// Verify validity on fields
var checkForm = function(element, functionsArray) {
        // Check element field
        fieldErrorMessage = element.parent('.input-group').prev('.text-danger');
        switch (element.attr('id')) {
            case formIdentifier + 'email':
                var emailPattern = /^\s*[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:(\.[a-zA-Z0-9-]+)+)\s*$/g,
                    is_email = emailPattern.test(element.val());
                if (element.val().replace(/^\s+|\s+$/gm, '') == '') {
                    fieldErrorMessage.html('&nbsp;Please fill in your email address.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    errorsOnFields[element.attr('id')] = true;
                } else if (!is_email) {
                    fieldErrorMessage.html('&nbsp;Sorry, "<span class="text-muted">' + element.val() +
                    '</span>" is not a valid email address!<br>Please check its format.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    errorsOnFields[element.attr('id')] = true;
                } else {
                    errorsOnFields[element.attr('id')] = false;
                }
                break;
            case formIdentifier + 'password':
                // At least 1 number, 1 lowercase letter, 1 uppercase letter, 1 special character, a minimum of 8 characters
                var pwdPattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}$/gm;
                var is_password = pwdPattern.test(element.val());
                if (element.val().replace(/^\s+|\s+$/gm, '') == '') {
                    fieldErrorMessage.html('&nbsp;Please fill in your password.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    errorsOnFields[element.attr('id')] = true;
                } else if (element.val().length < 8) {
                    fieldErrorMessage.html('&nbsp;Sorry, your password must contain<br>at least 8 characters!<br>Please check it before login try.' +
                    '&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    errorsOnFields[element.attr('id')] = true;
                } else if (!is_password) {
                    fieldErrorMessage.html('&nbsp;Sorry, your password format is not valid!<br>Please check it or verify required characters<br>before login try.' +
                    '&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    errorsOnFields[element.attr('id')] = true;
                } else {
                    errorsOnFields[element.attr('id')] = false;
                }
                break;
        }
        // Apply fade effect on error message during complete check
        if (onlyUpdateErrorsState === false) {
            if (errorsOnFields[element.attr('id')] === true) {
                if (fieldErrorMessage.is(':hidden')) {
                    fieldErrorMessage.removeClass('form-hide').hide();
                    fieldErrorMessage.fadeIn(700);
                }
            } else {
                if (fieldErrorMessage.is(':visible')) {
                    fieldErrorMessage.fadeOut(700, function() {
                        fieldErrorMessage.addClass('form-hide');
                    });
                }
            }
        }
        // Is it a success state?
        for (var i in errorsOnFields) {
            if (errorsOnFields[i] === true) {
                success = false;
                break;
            } else {
                success = true;
            }
        }
        return errorsOnFields;
    }

// Update fields error state only
var fieldsToUpdate = function(fieldsToCheck) {
    elements = fieldsToCheck;
    // Initialize condition
    onlyUpdateErrorsState = true;
    var count = elements.length;
    elements.each(function(i) {
        // Check all fields but not recaptcha
        checkForm($(this), [jsLcFirst]);
        if (i + 1 === count) {
            // Reset condition
            onlyUpdateErrorsState = false;
            // Reset form loaded state just after page loaded: see window load event above
            if (formJustLoaded) {
                formJustLoaded = false;
            }
        }
    });
    return errorsOnFields;
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