import { $gettext } from '../lib/gettext';

/**
 * This file provides a set of global handlers.
 */

var proxy_elements_selector = ':checkbox[data-proxyfor], :radio[data-proxyfor]';
var proxied_elements_selector = ':checkbox[data-proxiedby], :radio[data-proxiedby]';

function connectProxyAndProxied(event) {
    $(proxy_elements_selector).each(function () {
        const proxy = $(this);
        const proxyId = proxy.uniqueId().attr('id');
        const proxied = proxy.data('proxyfor');
        // The following seems like a hack but works perfectly fine.
        $(proxied).each(function () {
            const proxiedBy = ($(this).attr('data-proxiedby') || '').split(',').filter(a => a.length > 0);
            proxiedBy.push(`#${proxyId}`);

            $(this)
                .attr('data-proxiedby', proxiedBy.join(','))
                .data('proxiedby', `#${proxyId}`);
        });
    }).trigger('update.proxy');
}

// Use a checkbox as a proxy for a set of other checkboxes. Define
// proxied elements by a css selector in attribute "data-proxyfor".
$(document).on('change', proxy_elements_selector, function (event, force) {
    // Detect if event was triggered natively (triggered events have no
    // originalEvent)
    if (event.originalEvent !== undefined || !!force) {
        const proxied = $(this).data('proxyfor');
        $(proxied)
            .filter(':not(:disabled)')
            .prop('checked', this.checked)
            .prop('indeterminate', false)
            .each((index, element) => {
                const proxiedBy = $(element).attr('data-proxiedby');
                $(proxiedBy)
                    .filter((idx, item) => item !== this)
                    .trigger('update.proxy');
            })
            .filter('[data-proxyfor]')
            .trigger('change', [true]);
    }
}).on('update.proxy', proxy_elements_selector, function () {
    const proxied = $(this).data('proxyfor');
    const $proxied = $(proxied).filter(':not(:disabled)');
    const $checked = $proxied.filter(':checked');
    const $indeterminate = $proxied.filter(function () {
        return $(this).prop('indeterminate');
    });
    $(this).prop('checked', $proxied.length > 0 && $proxied.length === $checked.length);
    $(this).prop(
        'indeterminate',
        ($checked.length > 0 && $checked.length < $proxied.length) || $indeterminate.length > 0
    );
    $(this).trigger('change');
}).on('change', proxied_elements_selector, function () {
    //In case of radio buttons in a group that are deselected,
    //we must trigger the update.proxy event for each radio
    //button in the group, if the proxy is another element
    //than the proxy for "this" element.
    if ($(this).is(':radio')) {
        var proxy = $(this).data('proxiedby');
        var name = $(this).attr('name');
        var radio_button_group = $(`:radio[name="${name}"]`);
        $(radio_button_group).each(function () {
            var button_proxy = $(this).data('proxiedby');
            if (button_proxy != proxy) {
                $(button_proxy).trigger('update.proxy');
            }
        });
    } else {
        const proxy = $(this).attr('data-proxiedby');
        $(proxy).trigger('update.proxy');
    }
});

STUDIP.ready(connectProxyAndProxied);
$(document).on('refresh-handlers', connectProxyAndProxied);

// Use a checkbox or radiobox as a toggle switch for the disabled attribute of
// another set of elements. Define set of elements to disable/enable if item is
// neither :checked nor :indeterminate by a css selector in attribute
// "data-activates" / "deactivates".
$(document).on('change', '[data-activates],[data-deactivates]', function() {
    if (!$(this).is(':checkbox,:radio')) {
        return;
    }

    ['activates', 'deactivates'].forEach((type) => {
        var selector = $(this).data(type);
        if (selector === undefined || $(this).prop('disabled')) {
            return;
        }

        var state = $(this).prop('checked') || $(this).prop('indeterminate') || false;
        $(selector).each(function() {
            var condition = $(this).data(`${type}Condition`),
                toggle = state && (!condition || $(condition).length > 0);
            $(this)
                .attr('disabled', type === 'activates' ? !toggle : toggle)
                .trigger('update.proxy');
        });
    });
});

STUDIP.ready((event) => {
    $('[data-activates],[data-deactivates]', event.target).trigger('change');
});

// Use a select as a toggle switch for the disabled attribute of another
// element. Define element to disable if select has a value different from
// an empty string by a css selector in attribute "data-activates".
$(document).on('change update.proxy', 'select[data-activates]', function() {
    var activates = $(this).data('activates'),
        disabled = $(this).is(':disabled') || $(this).val().length === 0;
    $(activates).attr('disabled', disabled);
});

STUDIP.ready((event) => {
    $('select[data-activates]', event.target).trigger('change');
});

//
$(document).on('change', '[data-hides],[data-shows]', function () {
    if (!$(this).is(':checkbox,:radio')) {
        return;
    }

    ['hides', 'shows'].forEach((type) => {
        var selector = $(this).data(type);
        if (selector === undefined || $(this).prop('disabled')) {
            return;
        }

        var state = $(this).prop('checked') || $(this).prop('indeterminate') || false;
        $(selector).each(function() {
            var condition = $(this).data(`${type}Condition`),
                toggle = state && (!condition || $(condition).length > 0);
            $(this)
                .toggle(type === 'shows' ? toggle : !toggle)
                .trigger('update.proxy');
        });
    });
});
STUDIP.ready(event => {
    $('[data-hides],[data-shows]', event.target).trigger('change');
});

// Enable the user to set the checked state on a subset of related
// checkboxes by clicking the first checkbox of the subset and then
// clicking the last checkbox of the subset while holding down the shift
// key, thus toggling all the checkboxes in between.
// This only works if the first and last checkbox of the subset are set
// to the same state.
var last_element = null;
$(document).on('click', '[data-shiftcheck] :checkbox', function(event) {
    if (!event.originalEvent || last_element === event.target) {
        return;
    }

    if (last_element !== null && event.shiftKey) {
        var $this = $(event.target),
            $form = $this.closest('form'),
            name = $this.attr('name'),
            state = $this.prop('checked'),
            $last = $(last_element),
            children,
            idx0,
            idx1,
            tmp;

        if ($form.is($last.closest('form')) && name === $last.attr('name') && state === $last.prop('checked')) {
            children = $form.find(':checkbox[name="' + name + '"]:not(:disabled)');
            idx0 = children.index(event.target);
            idx1 = children.index(last_element);
            if (idx0 > idx1) {
                tmp = idx0;
                idx0 = idx1;
                idx1 = tmp;
            }
            children.slice(idx0, idx1).prop('checked', state);
        }
    }

    last_element = event.target;
});

// Lets the user confirm a specific action (submit or click event).
function confirmation_handler(event) {
    if (!event.isDefaultPrevented()) {
        event.stopPropagation();
        event.preventDefault();

        var element = $(event.currentTarget).closest('[data-confirm]'),
            question =
                element.data().confirm ||
                element.attr('title') ||
                element.find('[title]:first').attr('title') ||
                $gettext('Wollen Sie die Aktion wirklich ausführen?');

        STUDIP.Dialog.confirm(question).done(function() {
            var content = element.data().confirm;

            // We need to trigger the native event because for
            // some reason, jQuery's .trigger() won't always
            // work. Thus the data-confirm attribute will be removed
            // so that the original event can be executed
            element
                .removeAttr('data-confirm')
                .get(0)[event.type]();

            // Reapply the data-confirm attribute
            window.setTimeout(function() {
                element.attr('data-confirm', content);
            }, 0);
        });
    }
}
$(document).on(
    'click',
    'a[data-confirm],input[data-confirm],button[data-confirm],img[data-confirm]',
    confirmation_handler
);
$(document).on('submit', 'form[data-confirm]', confirmation_handler);

// Ensures an element has the same value as another element.
$(document).on('change', 'input[data-must-equal]', function() {
    var value = $(this).val(),
        rel = $(this).data().mustEqual,
        other = $(rel).val(),
        labels = $.map([this, rel], function(element) {
            var label = $(element)
                .closest('label')
                .text();
            label = label || $('label[for="' + $(element).attr('id') + '"]').text();
            return $.trim(label.split(':')[0]);
        }),
        error_message = $gettext('Die beiden Werte "$1" und "$2" stimmen nicht überein. '),
        matches = error_message.match(/\$\d/g);

    $.each(matches, function(i) {
        error_message = error_message.replace(this, labels[i]);
    });

    if (value !== other) {
        this.setCustomValidity(error_message);
    } else {
        this.setCustomValidity('');
    }
});

//Generalisation: The enter-accessible class allows an element to be accessible via keyboard
//by triggering the click event when the enter key is pressed.
$(document).on('keydown', '.enter-accessible', function(event) {
    if (event.code == 'Enter') {
        //The enter key has been pressed.
        event.preventDefault();
        $(this).trigger('click');
    }
});

$(document).on('click keydown', '[data-toggles]', function (event) {
    if ((event.type === 'keydown' && event.key === 'Enter') || event.type === 'click') {
        const target = event.currentTarget.dataset.toggles;
        //Check if the target is a checkbox. These have to be toggled differently than
        //other elements:
        if ($(target).is(':checkbox')) {
            //Toggle the checked state of the checkbox:
            $(target).prop('checked', !$(target).prop('checked'));
        } else {
            //Do the normal toggle operation:
            $(target).toggle();
        }

        event.preventDefault();
    }
});
