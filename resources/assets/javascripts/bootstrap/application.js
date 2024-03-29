import { $gettext } from '../lib/gettext';
import eventBus from "../lib/event-bus.ts";

/* ------------------------------------------------------------------------
 * application.js
 * This file is part of Stud.IP - http://www.studip.de
 *
 * Stud.IP is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Stud.IP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Stud.IP; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA  02110-1301  USA
 */

 /* ------------------------------------------------------------------------
  * add classes to html element according to horizontal screen size
  * ------------------------------------------------------------------------ */
(function ($) {
    // These sizes must match the breakpoints defined in breakspoints.less
    // TODO: use same webpack configuration for both
    const sizes = {
        tiny: '0px',
        small: '576px',
        medium: '768px',
        large: '1200px'
    };

    const setScreensizeClasses = function () {
        for (let size in sizes) {
            if (window.matchMedia(`(min-width: ${sizes[size]})`).matches) {
                $('html').addClass(`size-${size}`);
            } else {
                $('html').removeClass(`size-${size}`);
            }
        }
    };

    // Reset screen size classes on window resizes
    $(window).resize(setScreensizeClasses);

    // Set screen size classes initially
    setScreensizeClasses();
}(jQuery));

/* ------------------------------------------------------------------------
 * messages boxes
 * ------------------------------------------------------------------------ */
jQuery(document).on('click', '.messagebox .messagebox_buttons a', function () {
    if (jQuery(this).is('.details')) {
        jQuery(this).closest('.messagebox').toggleClass('details_hidden');
    } else if (jQuery(this).is('.close')) {
        jQuery(this).closest('.messagebox').hide('blind', 'fast', function () {
            jQuery(this).remove();
        });
    }
    return false;
});

STUDIP.ready(function() {
    //Set the focus on the modal overlay dialog, if any:
    $('.modaloverlay').find(':focusable').first().focus();
});


/* ------------------------------------------------------------------------
 * application wide setup
 * ------------------------------------------------------------------------ */
STUDIP.domReady(function () {
    STUDIP.study_area_selection.initialize();

    if (document.createElement('textarea').style.resize === undefined) {
        jQuery('textarea.resizable').resizable({
            handles: 's',
            minHeight: 50,
            zIndex: 1
        });
    }

    jQuery.ajaxSetup({
        beforeSend (jqXHR, settings) {
            const requestUrl = new URL(settings.url, STUDIP.ABSOLUTE_URI_STUDIP);
            const studipUrl = new URL(STUDIP.ABSOLUTE_URI_STUDIP);
            if (requestUrl.hostname === studipUrl.hostname && requestUrl.protocol === studipUrl.protocol) {
                jqXHR.setRequestHeader('X-CSRF-TOKEN', STUDIP.CSRF_TOKEN.value);
            }
        },
    });
});

STUDIP.ready((event) => {
    STUDIP.Forms.initialize(event.target);
    STUDIP.Markup.element(event.target);
});


/* ------------------------------------------------------------------------
 * application collapsable tablerows
 * ------------------------------------------------------------------------ */
STUDIP.domReady(function () {

    $(document).on('click', 'table.collapsable .toggler', function () {
        $(this).closest('tbody').toggleClass('collapsed')
               .filter('.collapsed').find('.action-menu').removeClass('active');
        return false;
    });

    $(document).on('click', 'a.load-in-new-row', function () {
        if ($(this).data('busy')) {
            return false;
        }

        if ($(this).closest('tr').next().hasClass('loaded-details')) {
            $(this).closest('tr').next().remove();
            $('a.load-in-new-row').attr('aria-expanded', 'false');
            return false;
        }
        $(this).showAjaxNotification().data('busy', true);

        var that = this;
        $.get($(this).attr('href'), function (response) {
            var row = $('<tr />').addClass('loaded-details');
            $('<td />')
                .attr('colspan', $(that).closest('td').siblings().length + 1)
                .html(response)
                .appendTo(row);

            $(that)
                .hideAjaxNotification()
                .closest('tr').after(row);

            $(that).data('busy', false);
            $('body').trigger('ajaxLoaded');
            $('a.load-in-new-row').attr('aria-expanded', 'true');

        });

        return false;
    });

    $(document).on('click', '.loaded-details a.cancel', function () {
        $(this).closest('.loaded-details').prev().find('a.load-in-new-row').click();
        return false;
    });

    var elements = $('.load-in-new-row-open');
    elements.click();
    if (elements.length > 0) {
        $(window).scrollTo(elements.first());
    }
});

/* ------------------------------------------------------------------------
 * Toggle dates in seminar_main
 * ------------------------------------------------------------------------ */
(function ($) {
    $(document).on('click', '.more-dates', function () {
        $('.more-dates-infos').toggle();
        $('.more-dates-digits').toggle();
        if ($('.more-dates-infos').is(':visible')) {
            $('.more-dates').text('(weniger)');
            $('.more-dates').attr('title', $gettext('Blenden Sie die restlichen Termine aus'));
        } else {
            $('.more-dates').text('(mehr)');
            $('.more-dates').attr('title', $gettext('Blenden Sie die restlichen Termine ein'));
        }
    });
}(jQuery));

/* ------------------------------------------------------------------------
 * additional jQuery (UI) settings for Stud.IP
 * ------------------------------------------------------------------------ */
jQuery.ui.accordion.prototype.options.icons = {
    header: 'arrow_right',
    activeHeader: 'arrow_down'
};
eventBus.on('studip:set-locale', () => {
    jQuery.extend(jQuery.ui.dialog.prototype.options, {
        closeText: $gettext('Schließen')
    });
});



/* ------------------------------------------------------------------------
 * jQuery timepicker
 * ------------------------------------------------------------------------ */

/* German translation for the jQuery Timepicker Addon */
/* Written by Marvin */
(function ($) {
    $.timepicker.regional.de = {
        timeOnlyTitle: 'Zeit wählen',
        timeText: 'Zeit',
        hourText: 'Stunde',
        minuteText: 'Minute',
        secondText: 'Sekunde',
        millisecText: 'Millisekunde',
        microsecText: 'Mikrosekunde',
        timezoneText: 'Zeitzone',
        currentText: 'Jetzt',
        closeText: 'Fertig',
        timeFormat: "HH:mm",
        amNames: ['vorm.', 'AM', 'A'],
        pmNames: ['nachm.', 'PM', 'P'],
        isRTL: false,
        showTimezone: false
    };
    $.timepicker.setDefaults($.timepicker.regional.de);

    $(document).on('focus', '.has-time-picker', function () {
        $(this).removeClass('has-time-picker').timepicker();
    });
    $(document).on('focus', '.has-time-picker-select', function () {
        $(this).removeClass('has-time-picker-select').timepicker({controlType: 'select'});
    });
}(jQuery));


(function ($) {
    $(document).on('focusout', '.studip-timepicker', function () {
        var time = $(this).val();
        if (time.length > 0 && time.length <= 2) {
            $(this).val(time + ":00");
        } else if (time.indexOf(':') === -1 && time.length > 2) {
            var parts = time.split('');
            parts.splice(-2, 0, ':');
            time = parts.join('');
            $(this).val(time);
        }
    });
}(jQuery))


STUDIP.domReady(function () {
    $(document).on('click', 'a.print_action', function (event) {
        var url_to_print = this.href;
        $('<iframe/>', {
            name: url_to_print,
            src: url_to_print,
            width: '1px',
            height: '1px',
            frameborder: 0
        })
            .css({top: '-99px', position: 'absolute'})
            .appendTo('body')
            .on('load', (function () {
                this.contentWindow.focus();
                this.contentWindow.print();
            }));
        return false;
    });
});

/* Copies a value from a select to another element*/
jQuery(document).on('change', 'select[data-copy-to]', function () {
    var target = jQuery(this).data().copyTo,
        value = jQuery(this).val() || jQuery(target).prop('defaultValue');
    jQuery(target).val(value);
});

STUDIP.domReady(function () {
    $('#checkAll').prop('checked', $('.sem_checkbox:checked').length !== 0);
});

// Fix horizontal scroll issue on domready, window load and window resize.
// This also makes the header and footer sticky regarding horizontal scrolling.
STUDIP.domReady(function () {
    var page_margin = ($('#current-page-structure').outerWidth(true) - $('#current-page-structure').width()) / 2,
        content_margin = $('#content').outerWidth(true) - $('#content').innerWidth(),
        sidebar_width = $('#sidebar').outerWidth(true);

    function fixScrolling() {
        $('#current-page-structure').removeClass('oversized').css({
            minWidth: '',
            marginRight: '',
            paddingRight: ''
        });

        var max_width = 0,
            fix_required = $('html').is(':not(.responsified)') && $('#content').get(0).scrollWidth > $('#content').width();

        if (fix_required) {
            $('#content').children().each(function () {
                var width = $(this).get(0).scrollWidth + ($(this).outerWidth(true) - $(this).innerWidth());
                if (width > max_width) {
                    max_width = width;
                }
            });

            $('#current-page-structure').addClass('oversized').css({
                minWidth: sidebar_width + content_margin + max_width + page_margin,
                marginRight: 0,
                paddingRight: page_margin
            });

            STUDIP.Scroll.addHandler('horizontal-scroll', (function () {
                var last_left = null;
                return function (top, left) {
                    if (last_left !== left) {
                        $('#navigation-level-1,#tabs,#main-footer,#top-bar').css({
                            transform: 'translate3d(' + left + 'px,0,0)'
                        });
                    }
                    last_left = left;
                };
            }()));
        } else {
            STUDIP.Scroll.removeHandler('horizontal-scroll');
        }
    }

    if ($('.no-touch #content').length > 0) {
        window.matchMedia('screen').addListener(function() {
            // Try to fix now
            fixScrolling();

            // and fix again on window load and resize
            $(window).on('resize load', _.debounce(fixScrolling, 100));
        });
    }
});

// Global handler:
// Toggle a table element. The url of the link will be called, an ajax
// indicator will be shown instead of the element and the whole table row
// will be replaced with the row with the same id from the response.
// Thus, in your controller you only have to execute the appropriate
// action and redraw the page with the new state.
jQuery(document).on('click', 'a[data-behaviour~="ajax-toggle"]', function (event) {
    var $that = jQuery(this),
        href  = $that.attr('href'),
        id    = $that.closest('tr').attr('id');

    $that.prop('disabled', true).addClass('ajaxing');
    jQuery.get(href).done(function (response) {
        var row = jQuery('#' + id, response);
        $that.closest('tr').replaceWith(row);
    });

    event.preventDefault();
});

/* Change open-variable on course-basicdata*/
(function ($) {
    $(document).on('click', 'form[name=course-details] fieldset legend', function () {
        $('#open_variable').attr('value', $(this).parent('fieldset').data('open'));
    });
}(jQuery));

STUDIP.domReady(function () {
    const loginForm = document.getElementById('login-form');
    if (!loginForm) {
        return;
    }

    const passwordInput = document.getElementById('password');
    const usernameInput = document.getElementById('loginname');
    const passwordCapsText = document.getElementById('password-caps');
    const iconPasswordVisible = document.getElementById('visible-password');
    const iconPasswordInVisible = document.getElementById('invisible-password');

    [usernameInput, passwordInput].forEach((input) => {
        input.addEventListener('keydown', (event) => {
            if (typeof event.getModifierState === 'function' && event.getModifierState('CapsLock')) {
                passwordCapsText.style.display = 'block';
            } else {
                passwordCapsText.style.display = 'none';
            }
        });
    });

    const toggleLogin = document.getElementById('toggle-login');
    if (toggleLogin) {
        loginForm.addEventListener('transitionend', (event) => {
            if (event.propertyName !== 'max-height') {
                return;
            }

            if (!loginForm.classList.contains('hide')) {
                usernameInput.scrollIntoView({
                    behavior: 'smooth'
                });
                usernameInput.focus();
            } else {
                loginForm.setAttribute('hidden', '');
            }
        });

        toggleLogin.addEventListener('click', (event) => {
            if (loginForm.classList.contains('hide')) {
                loginForm.removeAttribute('hidden');
            }

            setTimeout(() => {
                loginForm.classList.toggle('hide');
            }, 0);

            event.preventDefault();
        });
    }

    document.getElementById('password-toggle').addEventListener('click', () => {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            iconPasswordVisible.style.display = 'none';
            iconPasswordInVisible.style.display = '';
        } else {
            passwordInput.type = 'password';
            iconPasswordVisible.style.display = '';
            iconPasswordInVisible.style.display = 'none';
        }
    });
});
