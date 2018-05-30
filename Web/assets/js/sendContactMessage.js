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
                // Scroll to bloc-"name-of-bloc"
                var hash = window.location.hash;
                if (hash) {
                    $('html, body').animate({
                        scrollTop: ($('#bloc-' + hash.replace('#', '')).offset().top - 125) + 'px'
                    }, '700');
                }
            }
        }, 100);
    }

    $(window).on('load hashchange', function(e) {
        // Better user experience with scroll
        // Scroll to contact form notice message box if it is visible (obviously, in case of no AJAX mode).
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
        // Initialize form loaded state
        formJustLoaded = true;
        // Update success state if Google recaptcha is the only one to validate after form loaded.
        // Update fields error state on "fieldType"
        fieldsToUpdate($(fieldType));
    });

    // -------------------------------------------------------------------------------------------------------

    // Don't remove (Bootstrap normal behaviour) but hide notice message boxes when closed
    $(document).on('close.bs.alert', '.section-contact-us .alert', function() {
        $(this).slideUp(700, function() { $(this).addClass('form-hide'); });
        return false;
    });

	// -------------------------------------------------------------------------------------------------------

    $(document).on('change keyup input paste', fieldType, function(e) {
        // Particular cases to manage "live" format:
        // Render uppercase immediately when typing for "family name" input
        if ($(this).attr('id') == formIdentifier + 'familyName') {
            $(this).val($(this).val().toUpperCase());
        // Render first letter in uppercase immediately when typing for "first name" and "message" inputs
        } else if ($(this).attr('id') == formIdentifier + 'firstName' || $(this).attr('id') == formIdentifier + 'message') {
            $(this).val(jsUcFirst($(this).val()));
        }
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

	// -------------------------------------------------------------------------------------------------------

    // Avoid submit before page was correctly loaded
    var interval2 = setInterval(function() {
        if (document.readyState === 'complete') {
            clearInterval(interval2);
            // User submits contact form.
            $(document).on('submit', '.contact-form', function(e) {
                // Only AJAX mode
                if (parseInt($(this).data('ajax')) == 1) {
                    // Hide error message box each time, to create a visible effect on submission
                    if ($('.form-error').is(':visible')) {
                        $('.form-error').fadeOut(350, function() { $(this).addClass('form-hide'); });
                    }
                    // Prevent submit action
                    e.preventDefault();
                    // Set validation try
                    $(this).attr('data-try-validation', 1).data('try-validation', 1);
                    // Check form check seperately
                    getCurrentCheck();
                    // Check all fields but not form check!
                    $(this).find('.input-group input[type="text"], .input-group input[type="email"], .input-group textarea').each(function() {
                        // Avoid bug for queued events
                        fieldsInQueue = [];
                        // Check all visible fields
                        checkForm($(this), [jsLcFirst]);
                    });
                    // Form is validated.
                    if (success && grcResponse) {
                        // Add loader
                        $('button[name="' + formIdentifier + 'submit"]').prepend('<img class="ajax-loader" src="/assets/images/phpblog/ajax-loader.gif" alt="Loading">');
                        // POST data
                        var data = {
                            cf_call: 'contact-ajax',
                            cf_familyName: $('#' + formIdentifier + 'familyName').val(),  // spaces before and after are deleted thanks to server side filters
                            cf_firstName: $('#' + formIdentifier + 'firstName').val(),  // spaces before and after are deleted thanks to server side filters
                            cf_email: $('#' + formIdentifier + 'email').val(), // spaces before and after are deleted thanks to server side filters
                            cf_message: $('#' + formIdentifier + 'message').val(),  // spaces before and after are deleted thanks to server side filters
                            cf_submit: 1
                        };
                        // Get dynamic "cf_check" var name and create recaptcha value
                        var checkInputName = $('#' + formIdentifier + 'check').attr('name'), captcha = 'g-recaptcha-response';
                        data[checkInputName] = $('#' + formIdentifier + 'check').val();
                        data[captcha] = grcJSONResponse;
                        $.post({
                            url: $(formSelector).attr('action'),
                            data,
                            dataType: 'html',
                            statusCode: {
                                401: function(data) {
                                    // Render "Unauthorized" page
                                    getUnauthorizedResponse();
                                }
                            },
                            success: function(data) {
                                // Empty ajax container and update html content with data
                                if ( $('#ajax-wrapper').length > 0) {
                                    $('#ajax-wrapper').empty().html(data).hide().fadeIn();
                                }
                                // Reload Recaptcha
                                if ( $('#form-recaptcha').length > 0) {
                                    onloadCallback();
                                }
                                // Reset initial values
                                success = false;
                                grcResponse = false;
                                errorsOnFields = [];
                                // "check" array was already reset after request.
                                checkAjaxReturn = false;
                                // Remove loader
                                $('.ajax-loader').fadeOut(function() {
                                    $('button[name="' + formIdentifier + 'submit"]').text().replace('&nbsp;&nbsp;', '');
                                });
                                $('button[name="' + formIdentifier + 'submit"]').remove('.ajax-loader');
                                // Is message really sent? $(data) corresponds to $('.contact-form').
                                if (parseInt($(data).data('not-sent')) == 1) {
                                    $('.form-success').slideUp(700);
                                    if ($('.form-error').is(':hidden')) {
                                        $('.form-error').slideDown(700, function() { $(this).removeClass('form-hide'); });
                                    }
                                    $('.form-error').empty().html('<i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERROR!</strong>' +
                                    '&nbsp;Sorry, a technical error happened!<br>Your message was not sent.<br>' +
                                    'Please, try again later or <strong class="text-lower">contact us by phone</strong> if it\'s necessary.' +
                                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                    '<span aria-hidden="true"><i class="now-ui-icons ui-1_simple-remove"></i></span>' +
                                    '</button>');
                                    return false;
                                } else {
                                    // Show success message box
                                    $('.form-success').slideDown(700, function() {
                                        $(this).removeClass('form-hide');
                                        $('html, body').animate({
                                            scrollTop: ($(this).offset().top - 125) + 'px'
                                        }, '700');
                                    });
                                    return false;
                                }
                            },
                            error: function(xhr, error, status) {
                                // Manage error
                                // console.warn(xhr, xhr.responseText, error, status);
                                if ($('.form-error').is(':hidden')) {
                                    $('.form-error').slideDown(700, function() { $(this).removeClass('form-hide'); });
                                }
                                $('.form-error').empty().html('<i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERROR!</strong>' +
                                '&nbsp;Sorry, a technical error happened when the form was submitted!<br>' +
                                'Please try again later or <strong class="text-lower">contact us by phone</strong> if it\'s necessary.' +
                                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                '<span aria-hidden="true"><i class="now-ui-icons ui-1_simple-remove"></i></span>' +
                                '</button>');
                                return false;
                            }
                        });
                    } else {
                        noGoogleRecaptchaResponse();
                        // After a success state, hide success notice
                        if ($('.form-success').is(':visible')) {
                            $('.form-success').slideUp(700, function() {
                                $(this).addClass('form-hide');
                            });
                        }
                        // Show error message box each time, to create a visible effect on submission
                        $('.form-error').fadeIn(700, function() {
                            $(this).removeClass('form-hide');
                            $('html, body').animate({
                                scrollTop: ($(this).offset().top - 125) + 'px'
                            }, '700');
                        });
                    }
                }
             });
        }
    }, 100);

	// -------------------------------------------------------------------------------------------------------

	$(document).on('click', 'body', function(e) {
		clicked ++;
		// Not the first click
		if (clicked > 1) {
			// Previous object clicked is identical to current element clicked
			previousObjectClicked.is($(e.target)) ? isSameElement = true : isSameElement = false;
			// Previous object clicked is inside a ".phpblog-field-group" element
			previousObjectClicked.closest('.contact-form .input-group.phpblog-field-group').length > 0 ?
			isInside = true : isInside = false;
		}
        if ($(e.target).closest('.contact-form .input-group.phpblog-field-group').length > 0) {
            // Click is inside a ".phpblog-field-group" element
           	if (!$(e.target).closest('.contact-form .input-group.phpblog-field-group').hasClass('active-field')) {
           		$(e.target).closest('.contact-form .input-group.phpblog-field-group').addClass('active-field');
           	}
        } else {
            // Click is outside a ".phpblog-field-group" element
        	if ($('.contact-form .input-group.phpblog-field-group').hasClass('active-field')) {
        		$('.contact-form .input-group.phpblog-field-group').removeClass('active-field');
        	}
        }
        // Previous object clicked exists and was clicked at least twice and is inside a .phpblog-field-group element
        if (previousObjectClicked !== undefined && !isSameElement && isInside) {
       		if (previousObjectClicked.closest('.contact-form .input-group.phpblog-field-group').hasClass('active-field')) {
        		previousObjectClicked.closest('.contact-form .input-group.phpblog-field-group').removeClass('active-field');
        	}
       	}
        // Store current jQuery object clicked to become the previous element clicked
        previousObjectClicked = $(e.target);
    });

    // Manage focus around this fix
    $(document).on('focusin', '.contact-form .form-control', function(e) {
    	var parent = $(e.target).closest('.contact-form .input-group.phpblog-field-group');
    	if (!parent.hasClass('active-field')) {
       		parent.addClass('active-field');
      	}
    });

    $(document).on('focusout', '.contact-form .form-control', function(e) {
     	var parent = $(e.target).closest('.contact-form .input-group.phpblog-field-group');
     	if (parent.hasClass('active-field')) {
       		parent.removeClass('active-field');
       	}
    });
});

// -------------------------------------------------------------------------------------------------------

// Form identifiers
var formSelector = '.contact-form',
    formIdentifier = 'cf_';

// -------------------------------------------------------------------------------------------------------

// User inputs are modified.
var formJustLoaded = false,
    currentElement,
    fieldsInQueue = [],
    elements,
    fieldsToCheck,
    fieldType = '.contact-form .input-group input[type="text"], .contact-form .input-group input[type="email"], .contact-form .input-group textarea, .contact-form input[id="' + formIdentifier + 'check"]';

// -------------------------------------------------------------------------------------------------------

// Fix little issue for identical background-color property on .input-group-addon elements and .form-control fields
var previousObjectClicked,
    isSameElement = false,
    isInside = false,
    clicked = 0;

// -------------------------------------------------------------------------------------------------------

// Main variables
var grcJSONResponse, grcResponse = false,
    success = false,
	fieldErrorMessage,
    onlyUpdateErrorsState = false,
    otherFieldsChecked = false,
	errorsOnFields = [];

// Helper: first letter to uppercase
var jsUcFirst =	function(string) {
    	return string.charAt(0).toUpperCase() + string.slice(1);
	}

// Helper: first letter to lowercase
var jsLcFirst =	function(string) {
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

// Callback for Google Recaptcha response
var grcJSONResponse, grcResponse = false,
    verifyCallback = function(response) {
        $('#form-recaptcha').prev('.text-danger').fadeOut(700);
        $('#form-recaptcha').trigger('recaptchaResponse');
        grcResponse = true;
        grcJSONResponse = response;
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

// Get check data with AJAX
var check = [],
	checkAjaxReturn,
	getCurrentCheck = function() {
		$.get({
		 	url: '/',
		 	data: { cf_call: 'check-ajax' },
		 	dataType: 'json',
		 	success: function(json) {
		 		check.push(json.key, json.value);
                checkAjaxReturn = true;
                // Compare form check value
                checkForm($('#' + formIdentifier + 'check'), [jsLcFirst]);
                // Reset array to always keep 2 values
                check = [];
		 	},
		 	error: function(xhr, error, status) {
		 		// Manage error
		 		// console.warn(xhr, xhr.responseText, error, status);
		 		if ($('.form-error').is(':hidden')) {
				 	$('.form-error').slideDown(700, function() { $(this).removeClass('form-hide'); });
				}
				$('.form-error').empty().html('<i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;' +
				'<strong>ERROR!</strong>&nbsp;Sorry, a technical error happened!<br>We can not validate your inputs for the moment.<br>' +
				'Please try again later or <strong class="text-lower">contact us by phone</strong> if it\'s necessary.' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true"><i class="now-ui-icons ui-1_simple-remove"></i></span>' +
                '</button>');
		 		checkAjaxReturn = false;
                return false;
		 	}
		});
	}

// Get unauthorized response
var getUnauthorizedResponse = function() {
        // Get 401 error page and show it.
        $.get({
            url: '/',
            data: { cf_call: 'unauthorized' },
            dataType: 'html',
            statusCode: {
                401: function(data) {
                    // Render page error with effect
                    $('html').fadeTo(null, 0, function() {
                        $(this).hide();
                        // Refresh DOM
                        document.getElementsByTagName('html')[0].innerHTML = data.responseText.replace(/^<\!DOCTYPE html><html\.*>(\.*)<\/html>$/gm, '$1');
                        $('html').show().fadeTo(700, 1);
                    });
                    return false;
                }
            },
            error: function(xhr, error, status) {
                // Remove loader
                $('.ajax-loader').fadeOut(function() {
                    $('button[name="' + formIdentifier + 'submit"]').text().replace('&nbsp;&nbsp;', '');
                });
                $('button[name="' + formIdentifier + 'submit"]').remove('.ajax-loader');
                // Show technical error message
                if ($('.form-error').is(':hidden')) {
                    $('.form-error').slideDown(700, function() { $(this).removeClass('form-hide'); });
                }
                $('.form-error').empty().html('<i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;' +
                '<strong>ERROR!</strong>&nbsp;Sorry, a technical error happened!<br>We can not validate your inputs for the moment.<br>' +
                'Please, try again later or <strong class="text-lower">contact us by phone</strong> if it\'s necessary.' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true"><i class="now-ui-icons ui-1_simple-remove"></i></span>' +
                '</button>');
                return false;
            }
        });
    }

// Verify validity on fields
var checkForm = function(element, functionsArray) {
		// Check field element
		fieldErrorMessage = element.parent('.input-group').prev('.text-danger');
		switch (element.attr('id')) {
			case formIdentifier + 'familyName':
			case formIdentifier + 'firstName':
			case formIdentifier + 'message':
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
			case formIdentifier + 'check':
				// Check control with AJAX success state concerning returned JSON
				if (checkAjaxReturn !== undefined && checkAjaxReturn) {
					if (element.val() != check[1] || element.attr('name') != check[0]) {
                        // Wrong form check value or wrong check index, so render "Unauthorized" page
                        getUnauthorizedResponse();
                        errorsOnFields[element.attr('id')] = true;
                    } else {
						errorsOnFields[element.attr('id')] = false;
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