"use strict";

jQuery(function($) {
    // --- JQuery validation ---

    // -------------------------------------------------------------------------------------------------------

    // Fix call to scroll when page loaded for Safari: look at next script!
    if (navigator.userAgent.search('Safari') >= 0) {
        var interval = setInterval(function() {
            if (document.readyState === 'complete') {
                clearInterval(interval);
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
        // Particular case:
        // Render in uppercase immediately when typing for "password update token" input
        if ($(this).attr('id') == formIdentifier + 'passwordUpdateToken') {
            $(this).val($(this).val().toUpperCase());
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
                    checkForm(currentElement, [jsLcFirst]);
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

    // Manage fields error state update on password confirmation if password is changed
    $(document).on('passwordChecked', '#' + formIdentifier + 'password', function() {
        // Update field error state on password confirmation
        $('#' + formIdentifier + 'passwordConfirmation').trigger('input');
    });

    // Mask/unmask password to help user
    $(document).on('click', '.unmask-pwd', function() {
        $('#rpf_show_password').trigger('change');
        if (showPasswordChecked) {
            changeType($('input#rpf_password'), 'text');
            changeType($('input#rpf_passwordConfirmation'), 'text');
        } else {
            changeType($('input#rpf_password'), 'password');
            changeType($('input#rpf_passwordConfirmation'), 'password');
            return false;
        }
    });

    // Manage custom checkbox "change" event to switch show/hide password
    $(document).on('change', '#rpf_show_password', function() {
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
var formSelector = '.renew-password-form',
    formIdentifier = 'rpf_';

// -------------------------------------------------------------------------------------------------------

// User inputs are modified.
// Field type 'check' (CSRF token) is not checked on client side (but can be if AJAX is implemented)
var formJustLoaded = false,
    currentElement,
    elements,
    fieldsInQueue = [],
    fieldsToCheck,
    fieldType = formSelector + ' .input-group input[type="email"],' +
    formSelector + ' .input-group input[type="text"],' + // valid for textfields and password field type changed with changeType() function when password is shown!
    formSelector + ' .input-group input[type="password"]';

// -------------------------------------------------------------------------------------------------------

// Variable used to show/hide passwords
var showPasswordChecked = false;

// -------------------------------------------------------------------------------------------------------

// Main variables used in functions declaration
var fieldErrorMessage,
    success = false,
    onlyUpdateErrorsState = false,
    otherFieldsChecked = false,
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
            case formIdentifier + 'passwordUpdateToken':
                if (element.val().replace(/^\s+|\s+$/gm, '') == '') {
                    var elementLabel = functionsArray[0](element.attr('aria-label'));
                    fieldErrorMessage.html('&nbsp;Please fill in ' + elementLabel +
                                      '.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    errorsOnFields[element.attr('id')] = true;
                } else if (element.val().length != 15) {
                    fieldErrorMessage.html('&nbsp;Sorry, your token must contain<br>exactly 15 characters!<br>Please check it.' +
                    '&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    errorsOnFields[element.attr('id')] = true;
                } else {
                    errorsOnFields[element.attr('id')] = false;
                }
                break;
            case formIdentifier + 'password':
            case formIdentifier + 'passwordConfirmation':
                // At least 1 number, 1 lowercase letter, 1 uppercase letter, 1 special character, a minimum of 8 characters
                var pwdPattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}$/gm;
                var is_password = pwdPattern.test(element.val());
                if (element.val().replace(/^\s+|\s+$/gm, '') == '') {
                    fieldErrorMessage.html('&nbsp;Please fill in your password.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    errorsOnFields[element.attr('id')] = true;
                } else if (element.val().length < 8) {
                    fieldErrorMessage.html('&nbsp;Sorry, your password must contain<br>at least 8 characters!<br>Please check it.' +
                    '&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    errorsOnFields[element.attr('id')] = true;
                } else if (!is_password) {
                    fieldErrorMessage.html('&nbsp;Sorry, your password format is not valid!<br>Please check it or verify required characters.' +
                    '&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    errorsOnFields[element.attr('id')] = true;
                } else {
                    errorsOnFields[element.attr('id')] = false;
                }
                // Particular cases for password confirmation
                // Password and password confirmation format are valid, then check if both match.
                if (errorsOnFields[formIdentifier + 'password'] === false && errorsOnFields[formIdentifier + 'passwordConfirmation'] === false) {
                    // Password and password confirmation do not match.
                    if ($('#' + formIdentifier + 'password').val() != $('#' + formIdentifier + 'passwordConfirmation').val()) {
                        // Prepare error message
                        $('#' + formIdentifier + 'passwordConfirmation').parent('.input-group').prev('.text-danger').html('&nbsp;Password confirmation does not match<br>your password!<br>Please check both to be identical.<br>Unwanted authorized space character(s) " "<br>may be an issue!' +
                    '&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                        // Show password confirmation error message
                        delay(function() {
                            if ($('#' + formIdentifier + 'passwordConfirmation').parent('.input-group').prev('.text-danger').is(':hidden')) {
                                $('#' + formIdentifier + 'passwordConfirmation').parent('.input-group').prev('.text-danger').removeClass('form-hide').hide();
                                $('#' + formIdentifier + 'passwordConfirmation').parent('.input-group').prev('.text-danger').fadeIn(700);
                            }
                        }, 1000);
                        errorsOnFields[formIdentifier + 'password'] = false;
                        errorsOnFields[formIdentifier + 'passwordConfirmation'] = true;
                    // Password and password confirmation match.
                    } else {
                        // Hide password confirmation error message
                        delay(function() {
                            if ($('#' + formIdentifier + 'passwordConfirmation').parent('.input-group').prev('.text-danger').is(':visible')) {
                                $('#' + formIdentifier + 'passwordConfirmation').parent('.input-group').prev('.text-danger').fadeOut(700, function() {
                                    $('#' + formIdentifier + 'passwordConfirmation').parent('.input-group').prev('.text-danger').addClass('form-hide');
                                });
                            }
                        }, 1000);
                        errorsOnFields[formIdentifier + 'password'] = false;
                        errorsOnFields[formIdentifier + 'passwordConfirmation'] = false;
                    }
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
        // Particular cases:
        // Update password confirmation if form loaded state is set to false and password is changed.
        if (!formJustLoaded && element.attr('id') == formIdentifier + 'password') {
            element.trigger('passwordChecked');
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