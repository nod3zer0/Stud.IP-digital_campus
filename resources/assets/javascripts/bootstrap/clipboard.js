STUDIP.domReady(function () {
    jQuery('.clipboard-draggable-item').draggable({
        cursorAt: {left: 28, top: 15}, appendTo: 'body', helper: function () {
            let dragged_item = jQuery('<div class="dragged-clipboard-item"></div>');
            jQuery(dragged_item).data('id', jQuery(this).data('id'));
            jQuery(dragged_item).data('range_type', jQuery(this).data('range_type'));
            jQuery(dragged_item).text(jQuery(this).data('name'));
            return dragged_item;
        }, revert: true, revertDuration: 0
    });

    jQuery('.clipboard-area').droppable({
        drop: STUDIP.Clipboard.handleItemDrop
    });

    jQuery(document).on('click', '.clipboard-edit-button, .clipboard-edit-cancel', function (event) {
        event.preventDefault();
        STUDIP.Clipboard.toggleEditButtons($(this).data('widget-id'));
    })

    jQuery(document).on('click', '.clipboard-edit-accept', function (event) {
        event.preventDefault();
        STUDIP.Clipboard.rename($(this).data('widget-id'));
    });

    jQuery(document).on('click', '.clipboard-remove-button', function (event) {
        event.preventDefault();
        STUDIP.Dialog.confirm($(this).data('confirm-message'), function() {
            STUDIP.Clipboard.handleRemoveClick(event.target);
        });
    });

    jQuery(document).on('change', '.clipboard-selector', function (event) {
        STUDIP.Clipboard.switchClipboard(event);
    });

    jQuery(document).on('dragend', '.clipboard-draggable-item', function (event) {
        jQuery(this).css({
            'top': '0px', 'left': '0px'
        });
    });

    jQuery(document).on('dragover', '.clipboard-area', function (event) {
        event.preventDefault();
        event.stopPropagation();
    });

    jQuery(document).on('dragenter', '.clipboard-area', function (event) {
        //TODO:rrv2: use CSS classes!
        event.target.style.backgroundColor = '#0F0';
    });

    jQuery(document).on('dragleave', '.clipboard-area', function (event) {
        //TODO:rrv2: use CSS classes!
        event.target.style.backgroundColor = '#FFF';
    });

    jQuery(document).on('click', '.clipboard-item-remove-button', function (event) {
        event.preventDefault();
        STUDIP.Dialog.confirm($(this).data('confirm-message'), function() {
            STUDIP.Clipboard.removeItem(event.target)
        });
    });

    jQuery(document).on('submit', '.clipboard-widget .new-clipboard-form', function (event) {
        event.preventDefault();
        STUDIP.Clipboard.handleAddForm(event);
    });

    jQuery(document).on('click', '.clipboard-add-item-button', function (event) {
        event.preventDefault();
        STUDIP.Clipboard.handleAddItemButtonClick(event);
    });
});
