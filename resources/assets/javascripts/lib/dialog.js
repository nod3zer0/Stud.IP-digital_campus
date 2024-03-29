import { $gettext } from '../lib/gettext';
import parseOptions from './parse_options.js';
import extractCallback from './extract_callback.js';
import Overlay from './overlay.js';
import PageLayout from './page_layout.js';
import Report from './report.js';

/**
 * Specialized dialog handler
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @version     1.0
 * @since       Stud.IP 3.1
 * @license     GLP2 or any later version
 * @copyright   2014 Stud.IP Core Group
 * @todo        Handle file uploads <http://goo.gl/PnSra8>
 */


var dialog_margin = 0;

/**
 * Extract buttons from given element.
 */
function extractButtons(element) {
    var buttons = {};
    $('[data-dialog-button]', element)
        .hide()
        .find('a,button')
        .addBack()
        .filter('a,button')
        .each(function() {
            var label = $(this).text();
            var cancel = $(this).is('.cancel');
            var index = cancel ? 'cancel' : label;
            var classes = $(this).attr('class') || '';
            var name = $(this).attr('name') || '';
            var disabled = $(this).is(':disabled');

            classes = classes.replace(/\bbutton\b/, '').trim();

            buttons[index] = {
                text: label,
                class: classes,
                name: name,
                disabled: disabled,
                click: () => this.click()
            };
        });

    return buttons;
}

const Dialog = {
    instances: {},
    stack: [],
    hasInstance: function(id) {
        id = id || 'default';
        return this.instances[id] !== undefined;
    },
    getInstance: function(id) {
        id = id || 'default';
        if (!this.hasInstance(id)) {
            this.instances[id] = {
                open: false,
                fixedDimensions: false,
                element: $('<div>'),
                options: {},
                previous: this.stack[0] || false
            };

            this.stack.unshift(id);
        }
        return this.instances[id];
    },
    removeInstance: function(id) {
        id = id || 'default';
        if (this.hasInstance(id)) {
            delete this.instances[id];

            var index = this.stack.indexOf(id);
            this.stack.splice(index, 1);
        }
    },
    /**
     * legacy method, remove in future
     * @return bool
     */
    shouldOpen: function() {
        return true;
//        return !$('html').is('.responsive-display') && $(window).innerHeight() >= 400;
    },
    handlers: {
        header: {}
    }
};

// Handler for HTTP header X-Location: Relocate to another location
Dialog.handlers.header['X-Location'] = function(location, options) {
    location = decodeURI(location);

    if (document.location.href === location) {
        document.location.reload(true);
    } else {
        $(window)
            .on('hashchange', function() {
                document.location.reload(true);
            })
            .on('unload', function() {
                $(window).off('hashchange');
            });
    }

    Dialog.close(options);
    document.location = location;

    return false;
};
// Handler for HTTP header X-Dialog-Execute: Execute arbitrary function
Dialog.handlers.header['X-Dialog-Execute'] = function(value, options, xhr) {
    var callback = window,
        payload = xhr.getResponseHeader('Content-Type').match(/json/)
            ? $.parseJSON(xhr.responseText)
            : xhr.responseText;

    // Try to parse value as JSON (value might be {func: 'foo', payload: {}})
    try {
        value = $.parseJSON(value);
    } catch (e) {
        value = { func: value };
    }

    // Check for invalid call
    if (value.func === undefined) {
        throw 'Dialog: Invalid value for X-Dialog-Execute';
    }

    // Populate payload if not set
    if (value.payload === undefined) {
        value.payload = xhr.getResponseHeader('Content-Type').match(/json/)
            ? $.parseJSON(xhr.responseText)
            : xhr.responseText;
    }

    // Find callback
    callback = extractCallback(value.func, payload);

    // Check callback
    if (typeof callback !== 'function') {
        throw 'Dialog: Given callback is not a valid function';
    }

    // Execute callback
    return callback(value.payload, xhr);
};
// Handler for HTTP header X-Dialog-Close: Close the dialog
Dialog.handlers.header['X-Dialog-Close'] = function(value, options) {
    Dialog.close(options);
    return false;
};
// Handler for HTTP header X-Wikilink: Set the options' wiki link
Dialog.handlers.header['X-Wikilink'] = function(link, options) {
    options.wiki_link = link;
};
// Handler for HTTP header X-Title: Set the dialog title
Dialog.handlers.header['X-Title'] = function(title, options) {
    title = decodeURIComponent(title);
    if (title !== $('title').data().original) {
        options.title = title || options.title;
    }
};
// Handler for HTTP header X-No-Buttons: Decide whether to show dialog buttons
Dialog.handlers.header['X-No-Buttons'] = function(value, options) {
    options.buttons = false;
};

// Creates a dialog from an anchor, a button or a form element.
// Will update the dialog if it is already open
Dialog.fromElement = function(element, options) {
    options = options || {};

    if ($(element).is(':disabled') || !Dialog.shouldOpen()) {
        return;
    }

    if (options.close) {
        Dialog.close(options);
        return;
    }

    if (!$(element).is('a,button,form,input[type=image],input[type=submit]')) {
        throw 'Dialog.fromElement called on an unsupported element.';
    }

    options.origin = element;
    options.title =
        options.title ||
        Dialog.getInstance(options.id).options.title ||
        $(element).attr('title') ||
        $(element).find('[title]').first().attr('title') ||
        $(element).filter('a,button').text();
    options.method = 'get';
    options.data = {};

    var url, fd;

    // Predefine options
    if ($(element).is('form,button,input')) {
        url = $(element).attr('formaction') ||
              $(element).closest('form').data('formaction') ||
              $(element).closest('form').attr('action');
        options.method = $(element).closest('form').attr('method');
        options.data = $(element).closest('form').serializeArray();

        if ($(element).is('button,input')) {
            options.data.push({
                name: $(element).attr('name'),
                value: $(element).val()
            });
        } else if ($(element).data().triggeredBy) {
            options.data.push($(element).data().triggeredBy);
        }
        $(element).closest('form').removeData('formaction');

        if ($(element).closest('form').attr('enctype') === 'multipart/form-data') {
            options.processData = false;

            fd = new FormData();
            options.data.forEach(function(item) {
                fd.append(item.name, item.value);
            });

            $(element).closest('form').find('input[type=file]').each(function() {
                var name = $(this).attr('name'),
                    i;
                for (i = 0; i < this.files.length; i += 1) {
                    fd.append(name, this.files[i]);
                }
            });

            options.data = fd;
        }
    } else {
        url = $(element).attr('href');
    }

    return Dialog.fromURL(url, options);
};

// Creates a dialog from a passed url
Dialog.fromURL = function(url, options) {
    options = options || {};

    // Check if dialog should actually open
    if (!Dialog.shouldOpen()) {
        location.href = url;
    }

    // Append overlay
    if (Dialog.getInstance(options.id).open) {
        Overlay.show(true, Dialog.getInstance(options.id).element.parent());
    } else {
        Overlay.show(true);
    }

    // Send ajax request
    $.ajax({
        url: url,
        type: (options.method || 'get').toUpperCase(),
        data: options.data || {},
        headers: { 'X-Dialog': true },
        cache: false,
        contentType:
            options.processData !== undefined && !options.processData
                ? false
                : 'application/x-www-form-urlencoded; charset=UTF-8',
        processData: options.processData ?? true
    })
        .done(function(response, status, xhr) {
            var advance = true;

            // Trigger event
            $(options.origin || document).trigger('dialog-load', { xhr: xhr, options: options });

            // Execute all defined header handlers
            var handlers = Object.assign(
                Dialog.handlers.header,
                STUDIP.Dialog.handlers.header
            );
            $.each(handlers, (header, handler) => {
                var value = xhr.getResponseHeader(header),
                    result = true;
                if (value !== null) {
                    result = handler(value, options, xhr);
                }
                advance = advance && result !== false;
                return result;
            });

            Overlay.hide(0);

            if (advance) {
                Dialog.show(response, options);
            }
        })
        .fail((jqXHR, textStatus, errorThrown) => {
            Report.error($gettext('Es ist ein Fehler aufgetreten'), jqXHR.responseJSON?.message ?? errorThrown);
            Overlay.hide();

        });

    return true;
};

// Opens or updates the dialog
Dialog.show = function(content, options = {}) {
    options = Object.assign({}, Dialog.options, options);

    options.wikilink = options.wikilink === undefined ? true : options.wikilink;

    var scripts = $('<div>' + content + '</div>').filter('script'); // Extract scripts
    var dialog_options = {};
    var instance = Dialog.getInstance(options.id);

    if (instance.open) {
        options.title = options.title || instance.element.dialog('option', 'title');
    }

    if (options['center-content']) {
        content = '<div class="studip-dialog-centered-helper">' + content + '</div>';
    }

    // Hide and update container
    instance.element.hide().html(content);

    // Store options and dimensions
    instance.options = options;
    instance.dimensions = Dialog.calculateDimensions(instance, content, options);
    instance.previous_title = instance.previous_title || PageLayout.title;

    // Set dialog options
    dialog_options = $.extend(dialog_options, {
        width: instance.dimensions.width,
        height: instance.dimensions.height,
        dialogClass: Dialog.getClasses(options),
        buttons: options.buttons || {},
        title: options.title,
        modal: true,
        resizable: options.resize ?? true,
        create: function(event) {
            $(event.target)
                .parent()
                .css('position', 'fixed');
        },
        resizeStop: function(event, ui) {
            var position = [
                Math.floor(ui.position.left) - $(window).scrollLeft(),
                Math.floor(ui.position.top) - $(window).scrollTop()
            ];
            $(event.target)
                .parent()
                .css('position', 'fixed');
            $(event.target).dialog('option', 'position', position);

            instance.fixedDimensions = true;
            instance.dimensions = ui.size;
        },
        open: function() {
            PageLayout.title = dialog_options.title;

            var helpbar_element = $('.helpbar a[href*="hilfe.studip.de"]');
            var tooltip = helpbar_element.text();
            var link = options.wiki_link || helpbar_element.attr('href');
            var element = $('<a class="ui-dialog-titlebar-wiki"' + ' target="_blank" rel="noopener noreferrer">')
                    .attr('href', link)
                    .attr('title', tooltip);
            var buttons = $(this)
                    .parent()
                    .find('.ui-dialog-buttonset .ui-button');

            if (options.wikilink) {
                $(this)
                    .siblings('.ui-dialog-titlebar')
                    .addClass('with-wiki-link')
                    .find('.ui-dialog-titlebar-close')
                    .before(element);
            }

            $(this).parent().find('.ui-dialog-title').attr('title', options.title);

            instance.open = true;
            // Execute scripts
            $('head').append(scripts);

            $(options.origin || document).trigger('dialog-open', { dialog: this, options: options });

            // Transfer defined classes from options to actual displayed buttons
            // This should work natively, but it kinda does not
            Object.keys(dialog_options.buttons).forEach(function(label, index) {
                var classes = dialog_options.buttons[label]['class'];
                $(buttons.get(index)).addClass(classes);
            });
        },
        close: function(event) {
            $(options.origin || document).trigger('dialog-close', { dialog: this, options: options });

            PageLayout.title = instance.previous_title;

            Dialog.close(options);
        }
    });

    // Create buttons
    if (options.buttons === undefined || (options.buttons && !$.isPlainObject(options.buttons))) {
        dialog_options.buttons = extractButtons.call(this, instance.element);
        // Create 'close' button
        if (dialog_options.buttons.cancel === undefined) {
            dialog_options.buttons.cancel = {
                text: $gettext('Schließen'),
                'class': 'cancel'
            };
        }
        dialog_options.buttons.cancel.click = function() {
            Dialog.close(options);
        };
    }

    // Create/update dialog
    instance.element.dialog(dialog_options);
    instance.element.scrollTo(0, 0);

    // Trigger update event on document since options.origin might have been removed
    $(document).trigger('dialog-update', { dialog: instance.element, options: options });
};

// Closes the dialog for good
Dialog.close = function(options) {
    options = options || {};

    if (Dialog.hasInstance(options.id)) {
        var instance = Dialog.getInstance(options.id);

        if (instance.open) {
            instance.open = false;
            try {
                instance.element.dialog('close');
                instance.open = instance.element.dialog('isOpen');
            } catch (ignore) {
                // No action necessary
            }

            // Apparently the close event has been canceled, so don't force
            // a close
            if (instance.open) {
                return false;
            }

            try {
                instance.element.dialog('destroy');
                instance.element.remove();
            } catch (ignore) {
                // No action necessary
            }
        }

        Dialog.removeInstance(options.id);
    }

    if (options['reload-on-close'] && options['is-reloading'] === undefined) {
        window.location.reload();
        options['is-reloading'] = true;
    }
};

Dialog.getClasses = function (options) {
    var classes = ['studip-dialog'];

    if (options.dialogClass) {
        classes.push(options.dialogClass);
    } else if (options['center-content']) {
        classes.push('studip-dialog-centered');
    }

    return classes.join(' ');
};

Dialog.calculateDimensions = function (instance, content, options) {
    var previous = instance.previous !== false ? Dialog.getInstance(instance.previous) : false;
    var width = options.width || ($(window).width() * 2) / 3;
    var height = options.height || ($(window).height() * 2) / 3;
    var max_width  = $(window).width() * 0.95;
    var max_height = $(window).height() * 0.9;
    var helper;
    var temp;

    if (instance.fixedDimensions) {
        return instance.dimensions;
    }

    if ($('html').is('.responsive-display')) {
        max_width  = $(window).width() - 6; // Subtract border
        max_height = $(window).height();

        if (options.width === undefined) {
            width  = $(window).width() * 0.95;
            height = $(window).height() * 0.98;
        }
    }

    // Adjust size if neccessary
    if (!options.size) {
        width  = instance.dimensions?.width ?? width;
        height = instance.dimensions?.height ?? height;
    } else if (options.size === 'auto' || options.size === 'fit') {
        // Render off screen
        helper = $('<div class="ui-dialog ui-widget ui-widget-content">');
        helper.addClass(Dialog.getClasses(options));

        var helper_title = $('<span class="ui-dialog-title">')
            .text(options.title)
            .appendTo(helper)
            .wrap('<div class="ui-dialog-titlebar ui-helper-clearfix">')
            .after('<button class="ui-button ui-button-icon-only ui-dialog-titlebar-close">close</button>');
        if (options.wikilink) {
            helper_title.parent().append('<a class="ui-dialog-titlebar-wiki"></a>').addClass('with-wiki-link');
        }


        $('<div class="ui-dialog-content">').html($.parseHTML(content)).appendTo(helper);
        // Prevent buttons from wrapping
        $('[data-dialog-button]', helper).css('white-space', 'nowrap');
        // Add cancel button if missing
        if ((options.buttons === undefined || options.buttons !== false)) {
            $('<div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"></div>')
                .append('<div class="ui-dialog-buttonset"><button class="ui-button ui-widget ui-corner-all cancel">Foo</button></div>')
                .appendTo(helper)
        }

        helper.css({
            position: 'absolute',
            left: '-10000px',
            top: '-10000px',
            width: 'auto'
        }).appendTo('body');

        // Calculate width and height
        width = Math.min(helper.outerWidth(true) + dialog_margin, max_width);
        height = Math.min(helper.outerHeight(true), max_height);

        if (options.size === 'auto') {
            width = Math.max(300, width);
            height = Math.max(200, height);
        }
        // Remove helper element
        helper.remove();
    } else if (options.size === 'big') {
        width = $(window).width() * 0.9;
        height = $(window).height() * 0.8;
    } else if (options.size === 'medium') {
        width = $(window).width() * 0.6;
        height = $(window).height() * 0.5;
    } else if (options.size === 'medium-43') {
        //Medium size in 4:3 aspect ratio
        height = $(window).height() * 0.8;
        width = parseInt(height) * 1.33333333;
        if (width > $(window).width()) {
            width = $(window).width() * 0.9;
        }
    } else if (options.size === 'small') {
        width = 300;
        height = 200;
    } else if (options.size.match(/^\d+x\d+$/)) {
        temp = options.size.split('x');
        width = temp[0];
        height = temp[1];
    } else if (!options.size.match(/\D/)) {
        width = height = options.size;
    }

    // Ensure dimensions fit in viewport
    width = Math.min(width, max_width);
    height = Math.min(height, max_height);
    if (
        previous &&
        previous.dimensions !== undefined &&
        width > previous.dimensions.width &&
        height > previous.dimensions.height
    ) {
        width = width > previous.dimensions.width ? previous.dimensions.width * 0.95 : width;
        height = height > previous.dimensions.height ? previous.dimensions.height * 0.95 : height;
    }

    return {
        width: width,
        height: height
    };
};

// Specialized confirmation dialog
Dialog.confirm = function(question, yes_callback, no_callback) {
    return $.Deferred(function(defer) {
        if (question === true) {
            defer.resolve();
        } else if (question === false) {
            defer.reject();
        } else {
            Dialog.show(_.escape(question).replace("\n", '<br>'), {
                id: 'confirmation-dialog',
                title: $gettext('Bitte bestätigen Sie die Aktion'),
                size: 'fit',
                wikilink: false,
                dialogClass: 'studip-confirmation',
                buttons: {
                    accept: {
                        text: $gettext('Ja'),
                        click: defer.resolve,
                        class: 'accept'
                    },
                    cancel: {
                        text: $gettext('Nein'),
                        click: defer.reject,
                        class: 'cancel'
                    }
                }
            });
        }
        $(document).one('dialog-close', function() {
            if (defer.state() === 'pending') {
                defer.reject();
            }
        });
    })
        .then(yes_callback, no_callback)
        .always(function() {
            Dialog.close({ id: 'confirmation-dialog' });
        })
        .promise();
};

Dialog.confirmAsPost = function(question, action) {
    var form = $('<form/>', {
        action: action,
        method: 'post'
    });
    $('<input/>', {
        type: 'hidden',
        name: STUDIP.CSRF_TOKEN.name,
        value: STUDIP.CSRF_TOKEN.value
    }).appendTo(form);

    $('body').append(form);

    Dialog.confirm(question).done(function() {
        form.submit();
    });

    return false;
};

Dialog.registerHeaderHandler = function (header, handler) {
    Dialog.handlers.header[header] = handler;
};
Dialog.removeHeaderHandler = function (header) {
    if (Dialog.handlers.header[header] !== undefined) {
        delete Dialog.handlers.header[header];
    }
};

Dialog.initialize = function() {
    function checkValidity(element) {
        if (element.matches('a')) {
            return true;
        }

        const form = element.closest('form');
        if (form === null) {
            return true;
        }
        return form.checkValidity();
    }

    // Actual dialog handler
    function dialogHandler(event) {
        if (!event.isDefaultPrevented() && checkValidity(event.currentTarget)) {
            let target = $(event.target).closest('[data-dialog]');
            let options = target.data().dialog;

            if (
                target.is('form')
                && event.originalEvent?.submitter
                && $(event.originalEvent.submitter).attr('formaction')
            ) {
                target.data('formaction', $(event.originalEvent.submitter).attr('formaction'));
            }

            if (Dialog.fromElement(target, parseOptions(options))) {
                event.preventDefault();
            }
        }
    }

    function clickHandler(event) {
        if (!event.isDefaultPrevented() && checkValidity(event.currentTarget)) {
            var element = $(event.target).closest(':submit,input[type="image"]');
            var form = element.closest('form');
            var action = element.attr('formaction');
            form.data('triggeredBy', {
                name: $(event.target).attr('name'),
                value: $(event.target).val()
            });
            if (action) {
                form.data('formaction', action);
            }
        }
    }

    // Calculate dialogs margins (outer width - inner width of the dialog) in
    // order to properly calculated needed dialog widths. Otherwise horizontal
    // scrollbars will occur. This is located here because it is only
    // used in Dialog.show().
    var temp = $('<div class="ui-dialog" style="position: absolute;left:-1000px;top:-1000px;"></div>');
    temp.html('<div class="ui-dialog-content ui-widget-content"><div style="width: 100%">foo</div></div>');
    temp.appendTo('body');
    dialog_margin = temp.outerWidth(true) - $('.ui-dialog-content', temp).width();
    temp.remove();

    // Handle links, buttons and forms
    $(document)
        .on(
            'click',
            'a[data-dialog],button[data-dialog],input[type=image][data-dialog],input[type=submit][data-dialog]',
            dialogHandler
        )
        .on('click', 'form[data-dialog] :submit', clickHandler)
        .on('click', 'form[data-dialog] input[type=image]', clickHandler)
        .on('submit', 'form[data-dialog]', dialogHandler);

    // Close dialog on click outside of it
    $(document).on('click', '.ui-widget-overlay', function() {
        if ($('.ui-dialog').length > 0 && Dialog.stack.length) {
            Dialog.close({
                id: Dialog.stack[0]
            });
        }
    });

    // Recalculate dialog dimensions upon window resize. This is throttled
    // since the resize event keeps on firing during the resizing.
    var timeout = null;
    $(window).on('resize', (event) => {
        if (event.target !== window) {
            return;
        }

        clearTimeout(timeout);
        setTimeout(() => {
            Dialog.stack.forEach((id) => {
                var instance = Dialog.getInstance(id);
                instance.dimensions = Dialog.calculateDimensions(
                    instance,
                    $(instance.element).html(),
                    instance.options
                );

                $(instance.element).dialog('option', 'width', instance.dimensions.width);
                $(instance.element).dialog('option', 'height', instance.dimensions.height);
            });
        }, 10);
    });
};

export default Dialog;
