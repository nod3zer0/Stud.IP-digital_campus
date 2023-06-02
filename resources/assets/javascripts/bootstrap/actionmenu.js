(function ($) {
    'use strict';

    var last = null;

    // Open action menu on click on the icon
    $(document).on('click', '.action-menu-icon', function (event) {
        // Choose correct root element if menu was positioned absolutely
        let root_element = $(this).closest('.action-menu');
        if ($(this).closest('.action-menu-wrapper').length > 0) {
            root_element = $(this).data('action-menu-element');
        }

        var position = root_element.data('action-menu-reposition');
        if (position === undefined) {
            position = true;
        }
        // Obtain unique id for the root element and close other menus if neccessary
        const id = root_element.uniqueId().attr('id');
        if (last !== id) {
            STUDIP.ActionMenu.closeAll();
            last = id;
        }

        STUDIP.ActionMenu.create(root_element, position).toggle();

        // Stop event so the following close event will not be fired
        return false;
    });

    // Close action menu on click outside
    $(document).on('click', (event) => {
        if ($(event.target).closest('.action-menu-content').length === 0) {
            STUDIP.ActionMenu.closeAll();
        }
    });

    // Close all action menus when the escape key is pressed and rotate through all its items
    // when TAB or SHIFT + TAB is pressed.
    $(document).on('keydown', function(event) {
        if (event.key === 'Escape') {
            STUDIP.ActionMenu.closeAll();
        } else if (event.key === 'Tab') {
            //Check if the focus is inside an action menu:
            let menu = $(event.target).closest('.action-menu');
            if (menu.hasClass('is-open') && STUDIP.ActionMenu.tabThroughItems(menu, event.shiftKey)) {
                event.preventDefault();
            }
        }
    });

}(jQuery));
