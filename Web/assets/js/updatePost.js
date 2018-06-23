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

    $(window).on('load', function(e) {
        // Better user experience with scroll
        // Scroll to comment form notice message box if it is visible (obviously, in case of no AJAX mode).
        $('.form-error, .form-success').each(function() {
            if ($(this).is(':visible')) {
                $('html, body').animate({
                    scrollTop: ($(this).offset().top - 125) + 'px'
                }, '700');
            }
        });
        // Render switch block later to improve UX
        $('.phpblog-switch-block').removeClass('form-hide').hide().fadeIn(700);
        // Scroll to bloc-"name-of-bloc"
        var hash = window.location.hash;
        if (hash) {
            $('html, body').animate({
                scrollTop: ($('#bloc-' + hash.replace('#', '')).offset().top - 125) + 'px'
            }, '700');
        }
        // Initialize form loaded state
        formJustLoaded = true;
        // Update fields error state on "fieldType"
        fieldsToUpdate($(fieldType));
    });

    // Manage errors on fields
    $(document).on('change keyup input paste', fieldType, function(e) {
        // Hide previous success message
        if ($('.form-success').is(':visible')) {
            $('.form-success').slideUp(700, function() {
                $(this).addClass('form-hide');
            });
        }
        // Generate slug
        if ($(this).attr('id') == formIdentifier + 'slug' &&  parseInt($('#' + formIdentifier + 'customSlug').val()) == 1) {
            slug = slugify($('#' + formIdentifier + 'slug').val());
            $('#' + formIdentifier + 'slug').val(slug);
            // Show futur permalink to user
            $('.post-slug-notice').text(slug);
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

    // -------------------------------------------------------------------------------------------------------

    // Set input value on bootstrap switch UI
    $(document).on('switchChange.bootstrapSwitch', 'input[name="' + formIdentifier + 'customSlug"]', function(event, state) {
        // Hide previous success message
        if ($('.form-success').is(':visible')) {
            $('.form-success').slideUp(700, function() {
                $(this).addClass('form-hide');
            });
        }
        // Look at http://bootstrapswitch.com/events.html for events explanations:
        if (state) {
            $(this).attr('value', '1');
            $(this).attr('checked', 'checked');
            $(this).parents('.phpblog-switch-block').find('.slug-element').fadeIn(700, function() {
                $(this).removeClass('form-hide');
                // Show slug notice message
                $('.slug-info').fadeIn(700);
                // Render slug in field with its proper value and escape html characters
                slug = slugify($('#' + formIdentifier + 'slug').val().replace(/<[^>]+>/gm, ''));
                $('#' + formIdentifier + 'slug').val(slug);
                // Show futur permalink to user
                $('.post-slug-notice').text(slug);
            });
        } else {
            $(this).attr('value', '0');
            $(this).removeAttr('checked');
            $(this).parents('.phpblog-switch-block').find('.slug-element').fadeOut(700, function() {
                $(this).addClass('form-hide');
                // Hide slug notice message
                $('.slug-info').fadeOut(700);
                // Render slug in field with "title" value and escape html characters
                slug = slugify($('#' + formIdentifier + 'title').val().replace(/<[^>]+>/gm, ''));
                $('#' + formIdentifier + 'slug').val(slug);
                // Show futur permalink to user
                $('.post-slug-notice').text(slug);
            });
        }
    });

    // -------------------------------------------------------------------------------------------------------

    // Show file name value after file select
    $('.custom-file-input').attr('title', '');
    $(document).on('change', '.custom-file-input', function(e) {
        var files = e.target.files;
        if (files[0] !== undefined) {
            var filename = files[0].name.replace(/[\u00A0-\u9999<>\&]/gim, function(i) { // escape html entities
              return '&#' + i.charCodeAt(0) + ';';
            });
            var extension = files[0].type; // not used here!
            var filePath = $(this).val(); // not used here!
        } else {
            var filename = '';
        }
        $(this).attr('value', $(this).val());
        if (filename != '') {
            // Filename is not empty.
            if ($(this).parents('.input-group').prev('p.selected-image').length > 0) {
                $(this).parents('.input-group').prev('p.selected-image').addClass('form-hide');
            }
            $(this).parents('.input-group').prev('.text-danger').addClass('form-hide').html('');
            $(this).parents('.input-group').prev('.text-danger').prev('.selected-image').removeClass('form-hide').find('em').text(filename);
            // Reset user image removing action if necessary
            $(this).prev('input[type="hidden"]').val('0').attr('value', '0');
            $(this).next('.form-control-file').addClass('selected');
            // Remove default preview
            $(this).parents('.input-group').prev('.text-danger').prev('.selected-image').find('.image-preview').addClass('form-hide');
            // Remove default no preview message
            $(this).parents('.input-group').prev('.text-danger').prev('.selected-image').find('.image-no-preview').removeClass('form-hide');
        } else {
            // Nothing is selected.
            $(this).next('.form-control-file').removeClass('selected');
            // Remove previous selected file
            $(this).trigger('delete');
            // Remove default preview
            $(this).parents('.input-group').prev('.text-danger').prev('.selected-image').find('.image-preview').removeClass('form-hide');
            // Remove default no preview message
            $(this).parents('.input-group').prev('.text-danger').prev('.selected-image').find('.image-no-preview').addClass('form-hide');
        }
    });

    // Delete/Cancel selected file
    $(document).on('delete', '.custom-file-input', function(e) {
        $(this).parents('.input-group').prev('.text-danger').prev('.selected-image').addClass('form-hide').find('em').text('');
        $(this).parents('.input-group').prev('.text-danger').prev('.selected-image').next('.text-danger').next('.post-custom-image').find('.custom-file-input').val('').attr('value', '');
        // Set data for user image removing action
        $(this).parents('.input-group').prev('.text-danger').prev('.selected-image').next('.text-danger').next('.post-custom-image').find('.custom-file-input').prev('input[type="hidden"]').val('1').attr('value', '1');
        $(this).parents('.input-group').prev('.text-danger').prev('.selected-image').next('.text-danger').next('.post-custom-image').find('.form-control-file').removeClass('selected');
    });

    // Remove selected file
    $(document).on('click', '.selected-image .btn-danger', function(e) {
        e.preventDefault();
        // Call "delete" event behaviour
        $(this).parent('.selected-image').next('.text-danger').next('.post-custom-image').find('.custom-file-input').trigger('delete');
        // Call "change" event behaviour
        $(this).parent('.selected-image').next('.text-danger').next('.post-custom-image').find('.custom-file-input').trigger('change');
    });

    // -------------------------------------------------------------------------------------------------------

    // Initialize tiny MCE WYSIWYG editor / Manage errors on fields
    tinymce.init({
        branding: false,
        selector: 'textarea',
        schema: 'html5-strict',
        theme: 'modern',
        menubar: false,
        forced_root_block: false, // don't generate <p> tag when typing "enter"
        entity_encoding: 'raw', // don't encode special chars with html
        valid_elements: 'a[title|href|target|rel|style|class],span[style|class],ul[style|class],ol[style|class],li[style|class],em,strong,br',
        element_format: 'html',
        remove_trailing_brs: true,
        relative_urls: false,
        target_list: [
            { title: 'None', value: '' },
            { title: 'Same page', value: '_self' },
            { title: 'New page', value: '_blank'}
        ],
        default_link_target: '_self',
        link_class_list: [
            {title: 'Link with default red text', value: ''},
            {title: 'Link with primary effect text', value: 'btn-primary btn-link'},
            {title: 'Link with grey effect text', value: 'btn btn-link'}
        ],
        paste_as_text: true,
        formats: {
            div: { inline: 'span', classes: 'block-container', styles: { display: 'block' } },
            alignleft: { inline: 'span', classes: 'text-format', styles: { display: 'block', textAlign: 'left' } },
            aligncenter: { inline: 'span', classes: 'text-format', styles: { display: 'block', textAlign: 'center' } },
            alignright: { inline: 'span', classes: 'text-format', styles: { display: 'block', textAlign: 'right' } },
            alignjustify: { inline: 'span', classes: 'text-format', styles: { display: 'block', textAlign: 'justify' } }
        },
        style_formats_autohide: true,
        style_formats: [
            { title: 'Normal text', inline: 'span', styles: { color: '#212529' } },
            { title: 'Warning text', inline: 'span', styles: { color: '#ffb236' } },
            { title: 'Muted text', inline: 'span', styles: { color: '#868e96' } },
            { title: 'Info text', inline: 'span', styles: { color: '#2ca8ff' } },
            { title: 'Success text', inline: 'span', styles: { color: '#18ce0f' } },
            { title: 'Danger text', inline: 'span', styles: { color: '#ff3636' } }
        ],
        skin_url: '/assets/css/tinymce',
        content_css: '/assets/css/phpblog-tinymce.css?v=' + new Date().getTime(),
        setup: function(editor) {
            // Get loaders for each textarea
            editor.on('beforeRenderUI', function(e) {
                $('#' + editor.id).parent('.input-group').prev('.text-danger').before('<img class="ajax-loader" width="25" height="25" src="/assets/images/phpblog/ajax-loader.gif" alt="Loading">');
            });
            // Load editors with loader
            editor.on('load', function(e) {
                $(document).delay(20).queue(function() {
                    $('#' + editor.id).parent('.input-group').prev('.text-danger').prev('.ajax-loader').fadeOut(function() {
                        $(document).dequeue();
                        $('#' + editor.id).parent('.input-group').removeClass('phpblog-tinymce');
                    });
                });
            });
            // Customize or fix "Indent", "Outdent", "mceToggleFormat" (bold, italic) toolbar button commands
            editor.on('execCommand', function(args) {
                var node = tinymce.activeEditor.selection.getNode();
                switch (args.command) {
                    case 'Indent':
                        if ($(node).hasClass('mce-content-body')) {
                            node = $(node).find('.block-container')[0];
                        } else if ($(node).hasClass('block-container')) {
                            node = $(node)[0];
                        }
                        // Set padding-left value as wanted
                        if ($(node)[0].style.paddingLeft === '') {
                            $(node)[0].style.paddingLeft = '0%';
                        }
                        // Increase value
                        if (cleanInt($(node)[0].style.paddingLeft.replace(/%/g, '')) < 100) {
                            $(node).css({
                                'padding-left': cleanInt($(node)[0].style.paddingLeft.replace(/%/g, '')) + 1 + '%',
                                'display': 'block',
                            });
                            $(node).attr('data-mce-style', $(node).attr('style'));
                        }
                        break;
                    case 'Outdent':
                        if ($(node).hasClass('mce-content-body')) {
                            node = $(node).find('.block-container')[0];
                        } else if ($(node).hasClass('block-container')) {
                            node = $(node)[0];
                        }
                        // Set padding-left value as wanted
                        if ($(node)[0].style.paddingLeft === '') {
                            $(node)[0].style.paddingLeft = '0%';
                        }
                        // Decrease value
                        if (cleanInt($(node)[0].style.paddingLeft.replace(/%/g, '')) > 0) {
                            $(node).css({
                                'padding-left': cleanInt($(node)[0].style.paddingLeft.replace(/%/g, '')) - 1 + '%',
                                'display': 'block',
                            });
                            $(node).attr('data-mce-style', $(node).attr('style'));
                        }
                        break;
                    case 'mceToggleFormat':
                        // Fix bug with a personal trick for bold, italic formatting which happens sometimes (one letter selected, first letter selected, ...)
                        if (args.value == 'bold') {
                            if (editor.editorCommands.queryCommandState('Bold')) {
                                tinymce.activeEditor.selection.setContent('<strong>' + tinymce.activeEditor.selection.getContent().replace(/\<\/?strong\>/g,'') + '</strong>');
                            } else {
                                tinymce.activeEditor.selection.setContent(tinymce.activeEditor.selection.getContent().replace(/\<\/?strong\>/g,''));
                            }
                        } else if (args.value == 'italic') {
                            if (editor.editorCommands.queryCommandState('Italic')) {
                                tinymce.activeEditor.selection.setContent('<em>' + tinymce.activeEditor.selection.getContent().replace(/\<\/?em\>/g,'') + '</em>');
                            } else {
                                tinymce.activeEditor.selection.setContent(tinymce.activeEditor.selection.getContent().replace(/\<\/?em\>/g,''));
                            }
                        }
                        break;
                }
            });
        },
        init_instance_callback: function(editor) {
            editor.on('change keyup input paste', function(e) {
                // Hide previous success message
                if ($('.form-success').is(':visible')) {
                    $('.form-success').slideUp(700, function() {
                        $(this).addClass('form-hide');
                    });
                }
                var tinymceContent = '';
                var domElement = $('#' + editor.id);
                var tinymceElement = $('#' + $(this.getElement()).attr('id') + '_ifr').contents().find('body#tinymce');
                // Set content from tinymce element to textarea element
                //domElement.html(this.getContent());
                // Render first letter in uppercase immediately when typing "title", "intro" and "content" inputs
                if ((tinymceElement.text().length == 1) && (domElement.attr('id') == formIdentifier + 'title' || domElement.attr('id') == formIdentifier + 'intro' || domElement.attr('id') == formIdentifier + 'content')) {
                    tinymceContent = jsUcFirst(this.getContent());
                    // Modify editor content by preserving caret position
                    var bookmark = this.selection.getBookmark(2, true);
                    this.setContent(tinymceContent);
                    this.selection.moveToBookmark(bookmark);
                }
                // Generate slug
                if (domElement.attr('id') == formIdentifier + 'title') {
                    // Remove html tags
                    var stripedHtml = this.getContent().replace(/<[^>]+>/gm, '');
                    slug = slugify(stripedHtml);
                    if (parseInt($('#' + formIdentifier + 'customSlug').val()) == 0) {
                        $('#' + formIdentifier + 'slug').val(slug);
                        // Show futur permalink to user
                        $('.post-slug-notice').text(slug);
                    }
                }
                // Look at /assets/js/phpblog.js for declared functions
                // Avoid multiple call to form check on the same element: one call is queued each time.
                if (fieldsInQueue.indexOf(domElement.attr('id')) != -1) {
                    return false;
                // Store field to be queued once a time
                } else {
                    fieldsInQueue.push(domElement.attr('id'));
                    $(document).queue(function() {
                        currentElement = domElement;
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
        },
        plugins: 'lists, link, autolink, paste',
        // Don't show "paste" plugin
        toolbar: 'undo redo styleselect | bold italic underline strikethrough link unlink autolink | alignleft aligncenter alignright alignjustify removeformat | bullist numlist outdent indent',
    });
});

// -------------------------------------------------------------------------------------------------------

// Form identifiers
var formSelector = '.post-update-form',
    formIdentifier = 'puf_';

// -------------------------------------------------------------------------------------------------------

// User inputs are modified.
// Field type 'check' (CSRF token) is not checked on client side (but can be if AJAX is implemented)
var formJustLoaded = false,
    currentElement,
    elements,
    slug,
    fieldsInQueue = [],
    fieldsToCheck,
    fieldType = formSelector + ' .input-group input[type="text"],' +
                formSelector + ' .input-group input[type="checkbox"],' +
                formSelector + ' .input-group select,' +
                formSelector + ' .input-group textarea,' +
                formSelector + ' .input-group input[type="file"]';

// -------------------------------------------------------------------------------------------------------

// Main variables used in functions declaration
var fieldErrorMessage,
    onlyUpdateErrorsState = false,
    success = false,
    errorsOnFields = [];

// Verify validity on fields
var checkForm = function(element, functionsArray) {
    // Other fields
    fieldErrorMessage = element.parents('.input-group').prev('.text-danger');
    switch (element.attr('id')) {
        case formIdentifier + 'title':
        case formIdentifier + 'slug':
        case formIdentifier + 'intro':
        case formIdentifier + 'content':
        case formIdentifier + 'image':
            if (element.val().replace(/<[^>]+>/gm, '').replace(/^\s+|\s+$/gm, '') == '') {
                // particular "change" event for image is managed above.
                if (element.attr('id') != formIdentifier + 'image') {
                    // Use jsLcFirst()
                    var elementLabel = functionsArray[0](element.attr('aria-label'));
                    fieldErrorMessage.html('&nbsp;Please fill in ' + elementLabel +
                                  '.&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i>');
                } else {
                    // Enable other error messages
                    if (formJustLoaded === false) {
                        fieldErrorMessage.html('<span class="text-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;No new file is selected.<br>Previous validated image will be displayed!&nbsp;<i class="fa fa-long-arrow-down" aria-hidden="true"></i></span>');
                    }
                }
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

// Create a slug with a string
var slugify = function(str) {
    str = str.toLowerCase();
    // Remove accents, swap ñ for n, etc
    var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
    var to   = "aaaaaeeeeeiiiiooooouuuunc------";
    for (var i = 0, l = from.length; i < l; i ++) {
         str = str.replace(/&lt;|&gt;|&amp;/gm, '')
         .replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
    }
    str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
    .replace(/\s+/g, '-') // collapse whitespace and replace by -
    .replace(/-+/g, '-'); // collapse dashes
    return str;
}

// Parse integer: avoid issues with parseInt() function
var cleanInt = function(x) {
  x = Number(x);
  return Math[x >= 0 ? 'floor' : 'ceil'](x);
}