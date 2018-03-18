"use strict";

jQuery(function($) {
	// --- JQuery validation ---
	var element;

    // -------------------------------------------------------------------------------------------------------

    $(window).on('load hashchange', function(e) {
        // Scroll to contact form notice message box if it is visible (obviously, in case of no AJAX mode).
        $('.form-error, .form-success').each(function() {
            if ($(this).is(':visible')) {
                $('html, body').animate({
                    scrollTop: ($(this).offset().top - 125) + 'px'
                }, '700');
                return false;
            }
        });

        // Scroll to bloc-"name-of-bloc"
        var hash = window.location.hash;
        if (hash) {
            $('html, body').animate({
                scrollTop: ($('#bloc-' + hash.replace('#', '')).offset().top - 125) + 'px'
            }, '700');
        }
        // Update success state if Google recaptcha is the only one to validate after reload (no AJAX mode).
        if (parseInt($('.contact-form').data('ajax')) != 1) {
            $('.contact-form').find('.input-group input[type="text"], .input-group input[type="email"], .input-group textarea, input[id="cf_check"]').each(function() {
                element = $(this);
                if (element.val() != '') {
                    checkForm(element, [getCurrentCheck, jsLcFirst]);
                    return false;
                }
            });
        }
    });

    // -------------------------------------------------------------------------------------------------------

    // Don't remove (Bootstrap normal behaviour) but hide notice message boxes
	$(document).on('click', '.section-contact-us .alert .close', function(e) {
		e.stopPropagation();
		$(this).parent('.alert').slideUp(700, function() { $(this).addClass('form-hide'); });
	})

	// -------------------------------------------------------------------------------------------------------

	// User inputs are modified.
	var fieldType = '.contact-form .input-group input[type="text"], .contact-form .input-group input[type="email"], .contact-form .input-group textarea, .contact-form input[id="cf_check"]';

	$(document).on('change keyup input paste', fieldType, function(e) {
		element = $(this);
		checkForm(element, [getCurrentCheck, jsLcFirst]);
	    showNoticeMessage(false);
	});

	// -------------------------------------------------------------------------------------------------------
	// User submits contact form.
	$(document).on('submit', '.contact-form', function(e) {
		var contacForm = $(this);
		if (parseInt(contacForm.data('ajax')) == 1) {
			e.preventDefault();
			contacForm.find('.input-group input[type="text"], .input-group input[type="email"], .input-group textarea, input[id="cf_check"]').each(function() {
				element = $(this);
				checkForm(element, [getCurrentCheck, jsLcFirst]);
			});

			// Form is validated.
			if (success && grcResponse) {
				// Add loader
				$('button[name="cf_submit"]').prepend('<img class="ajax-loader" src="/assets/images/phpblog/ajax-loader.gif" alt="Loading">');
				// POST data
				var data = {
					cf_call: 'contact-ajax',
					cf_familyName: $('#cf_familyName').val(),  // spaces before and after are deleted thanks to server side filters
				 	cf_firstName: $('#cf_firstName').val(),  // spaces before and after are deleted thanks to server side filters
				 	cf_email: $('#cf_email').val(), // spaces before and after are deleted thanks to server side filters
				 	cf_message: $('#cf_message').val(),  // spaces before and after are deleted thanks to server side filters
				 	cf_submit: 1
				};
				// Get dynamic "cf_check" var name and create recaptcha value
				var checkInputName = $('#cf_check').attr('name'), captcha = 'g-recaptcha-response';
				data[checkInputName] = $('#cf_check').val();
				data[captcha] = grcJSONResponse;

				$.post({
				 	url: contacForm.attr('action'),
				 	data,
				 	dataType: 'html',
				 	success: function(data) {
					 	// Empty ajax container and update html content with data
					 	if ( $('#cf-ajax-wrapper').length > 0) {
					 		$('#cf-ajax-wrapper').empty().html(data).hide().fadeIn();
					 	}

						// Reload Recaptcha
						if ( $('#cf-recaptcha').length > 0) {
							onloadCallback();
						}

						// Reset initial values
						success = false;
						grcResponse = false;
						errorsOnFields = [];

						// Reset check AJAX values
						check = [];
						checkAjaxReturn = false;
						ajaxCheckCount = 0;

						// Remove loader
						$('.ajax-loader').fadeOut(function() {
							$('button[name="cf_submit"]').text().replace('&nbsp;&nbsp;', '');
						});
						$('button[name="cf_submit"]').remove('.ajax-loader');
						// Is message really sent? $(data) corresponds to $('.contact-form').
						if (parseInt($(data).data('not-sent')) == 1) {
							$('.form-success').slideUp(700);
							if ($('.form-error').is(':hidden')) {
							 	$('.form-error').slideDown(700, function() { $(this).removeClass('form-hide'); });
							}
							$('.form-error').empty().html('<i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;<strong>ERROR!</strong>' +
							'&nbsp;Sorry, a technical error happened!<br>Your message was not sent.<br>' +
							'Please, try again later.' +
		                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
		                    '<span aria-hidden="true"><i class="now-ui-icons ui-1_simple-remove"></i></span>' +
		                    '</button>');
							}
						else {
							$('.form-success').slideDown(700);
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
						'Please, try again later.' +
	                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
	                    '<span aria-hidden="true"><i class="now-ui-icons ui-1_simple-remove"></i></span>' +
	                    '</button>');
				  	}
				});
			}
			noGoogleRecaptchaResponse();
			showNoticeMessage(true);
		}
	});

	// -------------------------------------------------------------------------------------------------------

	// Fix little issue for identical background-color property on .input-group-addon elements and .form-control fields
	var previousObjectClicked,
		isSameElement = false,
		isInside = false,
		clicked = 0;

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

// Main variables
var grcJSONResponse, grcResponse = false,
	fieldErrorMessage,
	success = false,
	errorsOnFields = [];

// Helper: first letter to uppercase
var jsUcFirst =	function(string) {
    	return string.charAt(0).toUpperCase() + string.slice(1);
	}

// Helper: first letter to lowercase
var jsLcFirst =	function(string) {
    	return string.charAt(0).toLowerCase() + string.slice(1);
	}

// callback for Google Recaptcha response
var verifyCallback = function(response) {
		jQuery('#cf-recaptcha').prev('.text-danger').fadeOut(700);
		grcResponse = true;
		grcJSONResponse = response;
		showNoticeMessage(false);
	}

// Call callback
var grc,
	onloadCallback = function() {
		grc = grecaptcha.render('cf-recaptcha', {
      		'callback' : verifyCallback
    	});
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
		 		$('.form-error').empty().html('<i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;' +
		 		'<strong>ERRORS!</strong>&nbsp;Change a few things up and try submitting again.' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true"><i class="now-ui-icons ui-1_simple-remove"></i></span>' +
                '</button>');
                checkAjaxReturn = true;
		 	},
		 	error: function(xhr, error, status) {
		 		// Manage error
		 		// console.warn(xhr, xhr.responseText, error, status);
		 		if ($('.form-error').is(':hidden')) {
				 	$('.form-error').slideDown(700, function() { $(this).removeClass('form-hide'); });
				}
				$('.form-error').empty().html('<i class="now-ui-icons ui-1_bell-53"></i>&nbsp;&nbsp;' +
				'<strong>ERROR!</strong>&nbsp;Sorry, a technical error happened!<br>We can not validate your inputs for the moment.<br>' +
				'Please, try again later.' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true"><i class="now-ui-icons ui-1_simple-remove"></i></span>' +
                '</button>');
                $('.form-error').append('<span class="form-check-notice">Form may be outdated due to inactivity.<br>Reload the page could solve this issue.<br>' +
                'Otherwise, please <strong class="text-lower">contact us by phone</strong> if it\'s necessary.</span>');
		 		checkAjaxReturn = false;
		 	}
		});
	}

// Verify validity on fields
var ajaxCheckCount = 0,
	checkForm = function(element, functionsArray) {
		// Get current check to compare values in form
		if (ajaxCheckCount == 0) {
			functionsArray[0]();
			ajaxCheckCount ++;
		}

		// Check element field
		fieldErrorMessage = element.parent('.input-group').prev('.text-danger');
		switch (element.attr('id')) {
			case 'cf_familyName':
				element.val(element.val().toUpperCase());
			case 'cf_firstName':
			case 'cf_message':
				if (element.val().replace(/^\s+|\s+$/gm,'') == '') {
					var elementLabel = functionsArray[1](element.attr('aria-label'));
					fieldErrorMessage.html('&nbsp;Please fill in ' + elementLabel +
									  '.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
					if (fieldErrorMessage.hasClass('form-hide')) { fieldErrorMessage.removeClass('form-hide').hide(); }
					fieldErrorMessage.fadeIn(700);
					errorsOnFields[element.attr('id')] = true;
				} else {
					fieldErrorMessage.fadeOut(700);
					errorsOnFields[element.attr('id')] = false;
				}
			break;
			case 'cf_email':
				var pattern = /^\s*\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+\s*$/;
				var is_email = pattern.test(element.val());

				if (element.val().replace(/^\s+|\s+$/gm,'') == '') {
					fieldErrorMessage.html('&nbsp;Please fill in your email address.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
					if(fieldErrorMessage.hasClass('form-hide')) { fieldErrorMessage.removeClass('form-hide').hide(); }
					fieldErrorMessage.fadeIn(700);
					errorsOnFields[element.attr('id')] = true;
				} else if (!is_email) {
					fieldErrorMessage.html('&nbsp;Sorry, "<span class="text-muted">' + element.val() +
					'</span>" is not a valid email address!<br>Please check its format.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
					if(fieldErrorMessage.hasClass('form-hide')) { fieldErrorMessage.removeClass('form-hide').hide(); }
					fieldErrorMessage.fadeIn(700);
					errorsOnFields[element.attr('id')] = true;
				} else {
					fieldErrorMessage.fadeOut(700);
					errorsOnFields[element.attr('id')] = false;
				}
			break;
			case 'cf_check':
				if ($('.form-error .form-check-notice').length > 0) {
					$('.form-check-notice').remove();
				}
				// Check control with AJAX success state concerning returned JSON
				if (checkAjaxReturn !== undefined && checkAjaxReturn) {
					if (element.attr('name') != check[0] || element.val() != check[1]) {
						$('.form-error').append('<span class="form-token-notice">You are not allowed to use the form like this!<br>Please do not follow the dark side of the force... ;-)</span>');
						errorsOnFields[element.attr('id')] = true;
					} else {
						errorsOnFields[element.attr('id')] = false;
					}
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

// Manage error display when there is no response for Google Recaptcha
var noGoogleRecaptchaResponse = function() {
		if (!grcResponse) {
			fieldErrorMessage = $('#cf-recaptcha').prev('.text-danger');
			fieldErrorMessage.html('&nbsp;Please confirm you are a human.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
			$('#cf-recaptcha').prev('.text-danger').fadeIn(700);
		}
	}

// Manage notice boxes display
var	showNoticeMessage = function(isSubmitted) {
		// Contact form is not submitted: simple JS validation
		if (!isSubmitted) {
			if (success && grcResponse) {
                // Ready to send!
				if ($('.form-error').is(':visible')) {
					$('.form-error').slideUp(700, function() { $(this).addClass('form-hide'); });
				}
			} else {
                // Else case here to show notice error box, each time there is an error on any field.
                if ($('.form-error').is(':hidden')) {
                    $('.form-error').slideDown(700, function() { $(this).removeClass('form-hide'); });
                }
            }
		} else { // Contact form is submitted in AJAX mode
			// All fields are completed correctly.
			if (success && grcResponse) {
				if ($('.form-error').is(':visible')) {
					$('.form-error').slideUp(700, function() { $(this).addClass('form-hide'); });
				}
				// AJAX mode: show success notice box
				if (parseInt($(this).data('ajax')) == 1 && $('.form-success').is(':hidden')) {
					$('.form-success').slideDown(700, function() { $(this).removeClass('form-hide'); });
				}
			}
			// Errors on fields which prevent form to send user inputs.
			if ((!success && !grcResponse) || (!success && grcResponse) || (success && !grcResponse)) {
				// Show error notice box
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
				}
			}
		}
	}