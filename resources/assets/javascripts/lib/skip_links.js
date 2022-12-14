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
    /**
     * Inserts the list with skip links
     */
    insertSkipLinks: function() {
        jQuery('#skip_link_navigation').prepend(jQuery('#skiplink_list'));
        jQuery('#skip_link_navigation').attr('aria-busy', 'false');
    },

    /**
     * sets the area (of the id) as the current area for tab-navigation
     * and highlights it
     */
    setActiveTarget (id) {
        let fragment = null;
        // set active area only if skip links are activated
        if (!document.getElementById('skip_link_navigation')) {
            return false;
        }
        if (id) {
            fragment = id;
        } else {
            fragment = document.location.hash;
        }

        if (fragment.length > 0 && isSelectorValid(fragment) && document.querySelector(fragment)) {
            if (jQuery(fragment).is(':focusable')) {
                jQuery(fragment)
                    .click()
                    .focus();
            } else {
                //Set the focus on the first focusable element:
                jQuery(fragment).find(':focusable').eq(0).focus();
            }
            return true;
        }
        return false;
    },

    initialize: function() {
        SkipLinks.insertSkipLinks();
    }
};

export default SkipLinks;
