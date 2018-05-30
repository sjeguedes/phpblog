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
                // Scroll to comment form notice message box if it is visible (obviously, in case of no AJAX mode).
                $('.form-error, .form-success').each(function() {
                    if ($(this).is(':visible')) {
                        $('html, body').animate({
                            scrollTop: ($(this).offset().top - 125) + 'px'
                        }, '700');
                    }
                });
                // Scroll to element with "hash" css id name
                var hash = window.location.hash;
                // Scroll to a single comment on single post page
                if (hash && /^#comment-\d{1,}$/g.test(hash)) {
                    $('html, body').animate({
                        scrollTop: $(hash).offset().top + 'px'
                    }, '700');
                }
            }
        }, 100);
    }

    $(window).on('load hashchange', function(e) {
        // Better user experience with scroll
        // Scroll to comment form notice message box if it is visible (obviously, in case of no AJAX mode).
        $('.form-error, .form-success').each(function() {
            if ($(this).is(':visible')) {
                $('html, body').animate({
                    scrollTop: ($(this).offset().top - 125) + 'px'
                }, '700');
            }
        });
        // Scroll to element with "hash" css id name
        var hash = window.location.hash;
        // Scroll to a single comment on single post page
        if (hash && /^#comment-\d{1,}$/g.test(hash)) {
            $('html, body').animate({
                scrollTop: $(hash).offset().top + 'px'
            }, '700');
        }
        // Initialize form loaded state
        formJustLoaded = true;
        // Initialize error state on switch input
        $('#' + formIdentifier + 'hsi').trigger('switchChange.bootstrapSwitch');
        // Update fields error state on "fieldType"
        fieldsToUpdate($(fieldType));
    });

    // -------------------------------------------------------------------------------------------------------

    // Set input value on bootstrap switch UI
    $(document).on('switchChange.bootstrapSwitch', 'input[name="' + formIdentifier + 'hsi"]', function(event, state) {
        // Look at http://bootstrapswitch.com/events.html for events explanations:
        if (state) {
            $(this).attr('value', 'on');
            $(this).attr('checked', 'checked');
        } else {
            $(this).attr('value', 'off');
            $(this).removeAttr('checked');
        }
    });

    // -------------------------------------------------------------------------------------------------------

    // Manage errors on fields
    $(document).on('change keyup input paste switchChange.bootstrapSwitch', fieldType, function(e) {
        // Cancel "switchChange.bootstrapSwitch" event here when page is simply loaded!
        if (formJustLoaded && $(this).attr('id') == formIdentifier + 'hsi') {
            return false;
        }
        // Render first letter in uppercase immediately when typing for "nickname", "title" and "content" inputs
        if ($(this).attr('id') == formIdentifier + 'nickName' || $(this).attr('id') == formIdentifier + 'title' || $(this).attr('id') == formIdentifier + 'content') {
            $(this).val(jsUcFirst($(this).val()));
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
                        showErrorNoticeMessage(false);
                    }
                    // Dequeue event
                    $(document).dequeue();
                }, 1000);
            });
        }
    });
});

// -------------------------------------------------------------------------------------------------------

// Form identifiers
var formSelector = '.comment-form',
    formIdentifier = 'pcf_';

// -------------------------------------------------------------------------------------------------------

// User inputs are modified.
// Field types 'hpi' (honeypot), 'tli' (time check), 'check' (CSRF token) are not checked on client side (but can be if AJAX is implemented)
var formJustLoaded = false,
    currentElement,
    elements,
    fieldsInQueue = [],
    fieldsToCheck,
    fieldType = formSelector + ' .input-group input[type="text"],' +
                formSelector + ' .input-group input[type="email"],' +
                formSelector + ' .input-group textarea,' +
                formSelector + ' input[type="checkbox"]';

// -------------------------------------------------------------------------------------------------------

// Main variables used in functions declaration
var fieldErrorMessage,
    onlyUpdateErrorsState = false,
    success = false,
    errorsOnFields = [];

// Verify validity on fields
var checkForm = function(element, functionsArray) {
        // Check element field
        if (element.attr('name').match(/_hsi$/g)) {
            // Human antispam checkbox field
            fieldErrorMessage = element.parents('.nospam-container').prev('.text-danger');
        } else {
            // Other fields
            fieldErrorMessage = element.parent('.input-group').prev('.text-danger');
        }
        switch (element.attr('id')) {
            case formIdentifier + 'nickName':
            case formIdentifier + 'title':
            case formIdentifier + 'content':
                if (element.val().replace(/^\s+|\s+$/gm, '') == '') {
                    // Use jsLcFirst()
                    var elementLabel = functionsArray[0](element.attr('aria-label'));
                    fieldErrorMessage.html('&nbsp;Please fill in ' + elementLabel +
                                      '.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    errorsOnFields[element.attr('id')] = true;
                } else {
                    errorsOnFields[element.attr('id')] = false;
                }
                break;
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
            // Human check antispam tools
             case formIdentifier + 'hsi':
                if (element.val() != 'on') {
                    fieldErrorMessage.html('&nbsp;Spam bot behaviour seems to be detected!<br>Form can not be validated.<br>Please confirm you are a human.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
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