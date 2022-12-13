// Taken from https://stackoverflow.com/a/42149818
const isSelectorValid = (dummyElement =>
    selector => {
        try {
            dummyElement.querySelector(selector);
        } catch {
            return false;
        }
        return true;
})(document.createDocumentFragment());

const SkipLinks = {
    activeElement: null,
    navigationStatus: 0,

    /**
     * Displays the skip link navigation after first hitting the tab-key
     * @param event: event-object of type keyup
     */
    showSkipLinkNavigation: function(event) {
        if (event.keyCode === 9) {
            //tab-key
            SkipLinks.moveSkipLinkNavigationIn();
        }
    },

    /**
     * shows the skiplink-navigation window by moving it from the left
     */
    moveSkipLinkNavigationIn: function() {
        if (SkipLinks.navigationStatus === 0) {
            var VpWidth = jQuery(window).width();
            jQuery('#skip_link_navigation li:first a').focus();
            jQuery('#skip_link_navigation').css({ left: VpWidth / 2, opacity: 0 });
            jQuery('#skip_link_navigation').animate({ opacity: 1.0 }, 500);
            SkipLinks.navigationStatus = 1;
        }
    },

    /**
     * removes the skiplink-navigation window by moving it out of viewport
     */
    moveSkipLinkNavigationOut: function() {
        if (SkipLinks.navigationStatus === 1) {
            jQuery(SkipLinks.box).hide();
            jQuery('#skip_link_navigation').animate({ opacity: 0 }, 500, function() {
                jQuery(this).css('left', '-600px');
            });
        }
        SkipLinks.navigationStatus = 2;
    },

    /**
     * Inserts the list with skip links
     */
    insertSkipLinks: function() {
        jQuery('#skip_link_navigation').prepend(jQuery('#skiplink_list'));
        jQuery('#skiplink_list').show();
        jQuery('#skip_link_navigation').attr('aria-busy', 'false');
        jQuery('#skip_link_navigation').attr('tabindex', '-1');
        SkipLinks.insertHeadLines();
        return false;
    },

    /**
     * sets the area (of the id) as the current area for tab-navigation
     * and highlights it
     */
    setActiveTarget (id) {
        var fragment = null;
        // set active area only if skip links are activated
        if (!document.getElementById('skip_link_navigation')) {
            return false;
        }
        if (id) {
            fragment = id;
        } else {
            fragment = document.location.hash;
        }

        if (fragment.length > 0 && isSelectorValid(fragment) && fragment !== SkipLinks.activeElement && document.querySelector(fragment)) {
            SkipLinks.moveSkipLinkNavigationOut();
            if (jQuery(fragment).is(':focusable')) {
                jQuery(fragment)
                    .click()
                    .focus();
            } else {
                //Set the focus on the first focusable element:
                jQuery(fragment).find(':focusable').eq(0).focus();
            }
            SkipLinks.activeElement = fragment;
            return true;
        } else {
            jQuery('#skip_link_navigation li a')
                .first()
                .focus();
        }
        return false;
    },

    insertHeadLines: function() {
        var target = null;
        jQuery('#skip_link_navigation a').each(function() {
            target = jQuery(this);
            if (jQuery(target).is('li,td')) {
                jQuery(target).prepend(
                    '<h2 id="' +
                        jQuery(target).attr('id') +
                        '_landmark_label" class="skip_target">' +
                        jQuery(this).text() +
                        '</h2>'
                );
            } else {
                jQuery(target).before(
                    '<h2 id="' +
                        jQuery(target).attr('id') +
                        '_landmark_label" class="skip_target">' +
                        jQuery(this).text() +
                        '</h2>'
                );
            }
            jQuery(target).attr('aria-labelledby', jQuery(target).attr('id') + '_landmark_label');
        });
    },

    initialize: function() {
        SkipLinks.insertSkipLinks();
        SkipLinks.setActiveTarget();
    }
};

export default SkipLinks;
