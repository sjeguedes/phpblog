"use strict";

jQuery(function($) {
    // --- JQuery validation ---

    // -------------------------------------------------------------------------------------------------------

    var formSelector = '.forget-password-form',
        formIdentifier = 'fpf_';

    // -------------------------------------------------------------------------------------------------------

    // Better user experience with scroll
    // Scroll to comment form notice message box if it is visible (obviously, in case of no AJAX mode).
    $('.form-error, .form-success').each(function() {
        if($(this).is(':visible')) {
            $('html, body').animate({
                scrollTop: ($(this).offset().top - 125) + 'px'
            }, '700');
        }
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

    // User inputs are modified.
    // Field types 'hpi' (honeypot), 'tli' (time check), 'check' (CSRF token) are not checked on client side (but can be if AJAX is implemented)
    var currentElement,
        fieldType = formSelector + ' .input-group input[type="email"],' +
                    formSelector + ' input[type="checkbox"]';

    // Manage errors on fields but not for current field which is modified
    $(document).on('change keyup input paste switchChange.bootstrapSwitch', fieldType, function(e) {
        // Look at /assets/js/phpblog.js for declared functions
        currentElement = $(this);
        if (parseInt($(formSelector).data('try-validation')) == 1) {
            $(fieldType).not(currentElement).each(function() {
                checkForm(formIdentifier, $(this), []);
            });
            $(fieldType).trigger('otherFieldsChecked');
        } else {
            delay(function() {
                checkForm(formIdentifier, currentElement, []);
            }, 1000);
        }
    });

     // Manage error notice box and error on current field, only if user already tried to validate the form
     $(document).on('otherFieldsChecked', fieldType, function(e) {
        delay(function() {
            checkForm(formIdentifier, currentElement, []);
            // Update show/hide notice message box
            showNoticeMessage(false, false);
         }, 1000);
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
        if (element.attr('name').match(/_hsi$/g)) {
            // Human antispam checkbox field
            fieldErrorMessage = element.parents('.nospam-container').prev('.text-danger');
        } else {
            // Other fields
            fieldErrorMessage = element.parent('.input-group').prev('.text-danger');
        }
        switch (element.attr('id')) {
            case formIdentifier + 'email':
                var pattern = /^\s*\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+\s*$/;
                var is_email = pattern.test(element.val());
                if (element.val().replace(/^\s+|\s+$/gm,'') == '') {
                    fieldErrorMessage.html('&nbsp;Please fill in your email address.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                    if (fieldErrorMessage.hasClass('form-hide')) { fieldErrorMessage.removeClass('form-hide').hide(); }
                    fieldErrorMessage.fadeIn(700);
                    errorsOnFields[element.attr('id')] = true;
                }
                else if (!is_email) {
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
            // Human check antispam tools
            case formIdentifier + 'hsi':
                if (element.val() != 'on') {
                    fieldErrorMessage.html('&nbsp;Spam bot behaviour seems to be detected!<br>Form can not be validated.<br>Please confirm you are a human.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
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