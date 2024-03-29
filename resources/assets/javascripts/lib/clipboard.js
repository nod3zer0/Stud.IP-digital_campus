import {$gettext} from './gettext';

const Clipboard = {
    switchClipboard: function(event) {
        let select = jQuery(event.target);

        if (!select) {
            return;
        }

        let selected_clipboard_id = jQuery(select).val();

        //Make all clipboard areas of that clipboard invisible, except the one
        //that has been selected:
        let clipboard_areas = jQuery(select).parent().parent().find('.clipboard-area');

        for (let clipboard of clipboard_areas) {
            let current_clipboard_id = jQuery(clipboard).attr('data-id');

            if (current_clipboard_id) {
                if (current_clipboard_id === selected_clipboard_id) {
                    jQuery(clipboard).removeClass('invisible');
                    if (jQuery(clipboard).find(".empty-clipboard-message").hasClass("invisible")) {
                        jQuery("#clipboard-group-container").find('.widget-links').removeClass('invisible');
                    } else {
                        jQuery("#clipboard-group-container").find('.widget-links').addClass('invisible');
                    }
                } else {
                    jQuery(clipboard).addClass('invisible');
                }
            }
        }
    },

    handleAddForm: function(event) {
        if (!event) {
            return false;
        }



        //Check if a name is entered in the form:
        let name_input = jQuery(event.target).find('input[type="text"][name="name"]');
        if (!name_input) {
            //Something is wrong with the HTML:
            return false;
        }
        let name = jQuery(name_input).val().trim();
        if (!name) {
            //The name field is empty. Why send an empty field?
            return false;
        }

        //Submit the form via AJAX:
        STUDIP.api.POST(
            'clipboard/add',
            {
                data: jQuery(event.target).serialize()
            }
        ).done(STUDIP.Clipboard.add);
    },

    add: function(data) {
        if (!data['id'] || !data['name'] || !data['widget_id']) {
            //Required data are missing!
            return;
        }

        //Get the clipboard template:
        let widget_node = jQuery('#ClipboardWidget_' + data['widget_id'])[0];
        if (!widget_node) {
            //No widget? No clipboard.
            return;
        }

        let clipboard_template = jQuery(widget_node).find(
            '.clipboard-area.clipboard-template'
        )[0];

        if (!clipboard_template) {
            //Something is wrong with the HTML
            return;
        }

        let clipboard_node = jQuery(clipboard_template).clone();

        //Remove classes:
        jQuery(clipboard_node).removeClass('clipboard-template');
        jQuery(clipboard_node).removeClass('invisible');

        let clipboard_html = jQuery('<div></div>').append(clipboard_node).html();

        //Replace placeholders for CLIPBOARD_ID:
        clipboard_html = clipboard_html.replace(/CLIPBOARD_ID/g, data['id']);

        //Get the widget content element to append the clipboard:
        let content_node = jQuery(widget_node).find('.sidebar-widget-content');

        //Append the new clipboard's HTML code to the last clipboard:
        let clipboards = jQuery(content_node).find('.clipboard-area');
        let last_clipboard = undefined;
        if (clipboards.length > 0) {
            last_clipboard = clipboards[clipboards.length -1];
        } else {
            //No clipboards: Something is wrong with the HTML.
            return;
        }

        //Add the select option:
        let clipboard_selector = jQuery(widget_node).find('.clipboard-selector')[0];
        if (!clipboard_selector) {
            //Something is wrong with the HTML.
            return;
        }
        let old_options = jQuery(clipboard_selector).find('option');
        jQuery(old_options).removeAttr('selected');

        let new_option = jQuery('<option></option>');
        jQuery(new_option).val(data['id']);
        jQuery(new_option).text(data['name']);
        jQuery(new_option).attr('selected', 'selected');
        jQuery(clipboard_selector).append(new_option);
        //Remove the "disabled" attribute, if it exists
        //for the clipboard selector:
        jQuery(clipboard_selector).removeAttr('disabled');
        //Change the icon next to the clipboard selector:
        jQuery('.clipboard-edit-button').removeClass('invisible');
        jQuery('.clipboard-remove-button').removeClass('invisible');

        //Make all the other clipboards invisible and add the new one:
        clipboard_node = jQuery(clipboard_html);
        jQuery(clipboards).addClass('invisible');
        jQuery(last_clipboard).after(clipboard_node);
        jQuery(widget_node).find('#clipboard-group-container').removeClass('invisible');

        //Call the droppable jQuery method on the new clipboard area:
        jQuery(clipboard_node).droppable(
            {
                drop: STUDIP.Clipboard.handleItemDrop
            }
        );

        //Clear the text input in the "add clipboard" form:
        jQuery(widget_node).find(
            'form.new-clipboard-form input[type="text"][name="name"]'
        ).val('');
    },

    handleItemDrop: function(event, ui_element) {

        event.preventDefault();

        let range_id = jQuery(ui_element.helper).data('id');
        let range_type = jQuery(ui_element.helper).data('range_type');

        let clipboard = event.target;
        if (!clipboard) {
            //An event without a target. Nothing to do here.
            return;
        }

        STUDIP.Clipboard.prepareAddingItem(clipboard, range_id, range_type);
    },

    handleAddItemButtonClick: function (event) {
        if (!event) {
            return;
        }

        let button = event.target;
        if (!button) {
            return;
        }

        let clipboard_id = jQuery(button).data('clipboard_id');
        let clipboard_widget = jQuery('#ClipboardWidget_' + clipboard_id)[0];
        if (!clipboard_widget) {
            return;
        }
        let clipboard = jQuery(clipboard_widget).find('.clipboard-area:not(.invisible)')[0];
        if (!clipboard) {
            return;
        }

        let range_id = jQuery(button).data('range_id');
        let range_type = jQuery(button).data('range_type');

        STUDIP.Clipboard.prepareAddingItem(clipboard, range_id, range_type);
        STUDIP.ActionMenu.confirmJSAction(event.target);
    },

    prepareAddingItem: function(clipboard = null, range_id = null, range_type = null) {
        if (!clipboard || !range_id || !range_type) {
            return false;
        }

        let clipboard_id = clipboard.getAttribute('data-id');
        let widget_id = jQuery(clipboard).parents('.clipboard-widget').data('widget_id');

        let allowed_classes = clipboard.getAttribute('data-allowed_classes');
        if (allowed_classes) {
            //A list of allowed classes is set. Check if the specified
            //range_type is in the list of allowed classes.
            //Although this check can easily be overridden by users
            //it doesn't matter in this case since in the database
            //the classes whose objects can be linked in a specific clipboard
            //are not stored so that every clipboard can contain IDs
            //of any SORM object that implements the StudipItem interface.
            //If a user overrides the check for allowed classes then
            //the clipboard widget may display objects of classes who
            //don't belong on the displayed page. That's all.

            allowed_classes = allowed_classes.replace(' ', '').split(',');
            if (allowed_classes.indexOf(range_type) === -1) {
                //The dropped item does not belong to the right class.
                //Set the "not allowed" CSS class
                //for the "not allowed" animation.

                jQuery(clipboard).removeClass('invalid-drop');
                jQuery(clipboard).addClass('invalid-drop');
                return false;
            }
        }

        if (!clipboard_id || !widget_id) {
            //We can't do anything without the clipboard's ID
            //or the ID of the widget it is inside!
            return false;
        }

        //Check for duplicates:
        let already_existing_entry = jQuery(clipboard).find(
            ".clipboard-item[data-range_id='" + range_id + "']"
        ).length > 0;
        if (already_existing_entry) {
            //Nothing to do here.
            return false;
        }

        //Add the item to the clipboard via AJAX:
        STUDIP.api.POST(
            'clipboard/' + clipboard_id + '/item',
            {
                data: {
                    'range_id': range_id,
                    'range_type': range_type,
                    'widget_id': widget_id
                }
            }
        ).done(function(data) {
            STUDIP.Clipboard.addDroppedItem(data);
        });
    },

    addDroppedItem: function(response_data) {
        if (!response_data['id'] || !response_data['range_id']
            || !response_data['name'] || !response_data['widget_id']) {
            //We cannot create a new entry if at least one of those fields
            //is missing.
            return;
        }

        let widget = jQuery('#ClipboardWidget_' + response_data['widget_id']);
        let clipboard_id = jQuery(widget).find(".clipboard-selector").val();

        if (!widget) {
            //The widget with the speicified widget-ID
            //is not present on the current page.
            return;
        }

        let clipboard = jQuery(widget).find(
            '.clipboard-area[data-id="' + clipboard_id + '"]'
        )[0];
        if (!clipboard) {
            //We need the clipboard node!
            return;
        }

        //Check for duplicates:
        let already_existing_entry = jQuery(clipboard).find(
            ".clipboard-item[data-range_id='" + response_data['range_id'] + "']"
        ).length > 0;
        if (already_existing_entry) {
            //Nothing to do here.
            return;
        }

        let template = jQuery(clipboard).find('.clipboard-item-template')[0];
        if (!template) {
            //What is the use of continuing when there is no template?
            return;
        }

        let new_item_node = jQuery(template).clone();
        let checkbox_id = "item_" + clipboard_id + "_" + response_data['range_type'] + "_" + response_data['range_id'];

        //Set some HTML attributes of the template:
        jQuery(new_item_node).attr('data-range_id', response_data['range_id']);
        jQuery(new_item_node).attr('id', checkbox_id);
        jQuery(new_item_node).removeClass('clipboard-item-template');
        jQuery(new_item_node).removeClass('invisible');

        let name_column = jQuery(new_item_node).find('td.item-name');
        console.log(name_column);
        jQuery('<span/>').text(response_data['name']).appendTo(name_column)
        let id_field = jQuery(new_item_node).find("input[name='selected_clipboard_items[]']");
        jQuery(id_field).val(checkbox_id);

        let new_item_html = jQuery('<div></div>').append(new_item_node).html();
        //Replace RANGE_ID with an escaped real range-ID:
        new_item_html = new_item_html.replace(/RANGE_ID/g, _.escape(response_data['range_id']));
        //Append the template to the clipboard:
        jQuery(clipboard).append(jQuery(new_item_html));

        jQuery(clipboard).find('.empty-clipboard-message').addClass('invisible');
        jQuery("#clipboard-group-container").find('.widget-links').removeClass('invisible');

        //Run the item drop animation:
        jQuery(clipboard).addClass('animated-drop');
        //Remove the animation class after the end of the animation:
        window.setTimeout(
            function() {jQuery(clipboard).removeClass('animated-drop');},
            500
        );
    },

    rename: function(widget_id) {
         if (!widget_id) {
            //Required data are missing!
            return;
        }

        let widget = jQuery('#ClipboardWidget_' + widget_id);
        let clipboard_id = jQuery(widget).find(".clipboard-selector").val();
        let namer = jQuery(widget).find("input.clipboard-name");

        STUDIP.api.PUT(
            'clipboard/' + clipboard_id,
            {
                data: {
                    name: namer.val()
                }
            }
        ).done(function(data) {
            STUDIP.Clipboard.update(data, widget_id)
        });
    },

    update: function(data, widget_id) {
        if (!widget_id || !data['id'] || !data['name']) {
            //Required data are missing!
            return;
        }
        let widget = jQuery('#ClipboardWidget_' + widget_id);
        let selector = jQuery(widget).find("select.clipboard-selector");
        selector.find("option[value=" + data['id'] + "]").text(data['name']);
        STUDIP.Clipboard.toggleEditButtons(widget_id);
    },

    remove: function(clipboard_id, widget_id) {
        if (!clipboard_id || !widget_id) {
            //Required data are missing!
            return;
        }

        let widget = jQuery('#ClipboardWidget_' + widget_id);

        let clipboard_selector = jQuery(widget).find('.clipboard-selector')[0];
        if (!clipboard_selector) {
            //Something is wrong with the HTML.
            return;
        }

        //Get the option and the corresponding clipboard area
        //for the deleted clipboard:
        let clipboard_select_option = jQuery(clipboard_selector).find(
            'option[value="' + clipboard_id + '"]'
        )[0];
        let clipboard_area = jQuery(widget).find(
            '.clipboard-area[data-id="' + clipboard_id + '"]'
        )[0];

        jQuery(clipboard_select_option).addClass('invisible');
        jQuery(clipboard_area).addClass('invisible');

        //Display the previous or the next select option
        //and the previous or next clipboard area:
        let new_selected_clipboard_id = null;
        let predecessor = jQuery(clipboard_select_option).prev();
        if (predecessor.length > 0) {
            jQuery(predecessor).attr('selected', 'selected');
            new_selected_clipboard_id = jQuery(predecessor).val();
        } else {
            let successor = jQuery(clipboard_select_option).next();
            if (successor.length > 0) {
                jQuery(successor).attr('selected', 'selected');
                new_selected_clipboard_id = jQuery(successor).val();
            }
            //No else here: If no select options are left
            //we have an empty select element.
        }

        //Now make the clipboard area visible which corresponds to the
        //selected option:
        if (new_selected_clipboard_id) {
            //Another clipboard has been selected: Make it visible.
            jQuery(widget).find(
                '.clipboard-area[data-id="' + new_selected_clipboard_id + '"]'
            ).removeClass('invisible');
        } else {
            //No other clipboard selected: Display the "no clipboards" message
            //and disable the clipboard select field:
            jQuery(widget).find('#clipboard-group-container').addClass('invisible');
            jQuery(clipboard_selector).attr('disabled', 'disabled');
            //Change the icon next to the clipboard selector:
            let active_icon = jQuery(clipboard_selector).next();
            let inactive_icon = jQuery(active_icon).next();
            jQuery(active_icon).addClass('invisible');
            jQuery(inactive_icon).removeClass('invisible');
        }

        //We have no need for the elements of the removed clipboard anymore.
        //Now we can remove them:
        jQuery(clipboard_select_option).remove();
        jQuery(clipboard_area).remove();
    },

    handleRemoveClick: function(delete_icon) {
        if (!delete_icon) {
            return;
        }

        //Get the data of the clipboard:
        let clipboard_select = jQuery(delete_icon).siblings('.clipboard-selector')[0];
        if (!clipboard_select) {
            //Something is wrong with the HTML.
            return;
        }

        let clipboard_id = jQuery(clipboard_select).val();
        let widget = jQuery(delete_icon).parents('.clipboard-widget')[0];
        if (!widget) {
            //Another case where something is wrong with the HTML.
            return;
        }
        let widget_id = jQuery(widget).data('widget_id');

        STUDIP.api.DELETE(
            'clipboard/' + clipboard_id,
            {
                data: {
                    widget_id: widget_id
                }
            }
        ).done(function() {
            STUDIP.Clipboard.remove(clipboard_id, widget_id);
        });
    },

    removeItem: function(delete_icon) {
        if (!delete_icon) {
            return;
        }

        //Get the item-ID:
        let item_html = jQuery(delete_icon).parents('tr');
        let range_id = jQuery(item_html).data('range_id');
        let clipboard_element = jQuery(item_html).parents('table');
        let clipboard_id = jQuery(clipboard_element).data('id');

        if (!range_id || !clipboard_id) {
            //We cannot proceed without the item-ID and the clipboard-ID!
            return;
        }

        STUDIP.api.DELETE(
            'clipboard/' + clipboard_id + '/item/' + range_id
        ).done(function() {
            //Check if the item has siblings:
            let siblings = jQuery(item_html).siblings();
            if (siblings.length < 3) {
                //Only the "no items" element and the template
                //are siblings of the item.
                //We must display the "no items" element:
                jQuery(item_html).siblings(
                    '.empty-clipboard-message'
                ).removeClass('invisible');
                jQuery("#clipboard-group-container").find('.widget-links').addClass('invisible');
            }
            //Finally remove the item:
            jQuery(item_html).remove();
        });
    },

    toggleEditButtons: function(widget_id) {
        if (!widget_id) {
           //Required data are missing!
           return;
       }

       let widget = jQuery('#ClipboardWidget_' + widget_id);
       jQuery(widget).find(".clipboard-edit-accept").toggle();
       jQuery(widget).find(".clipboard-edit-cancel").toggle();
       jQuery(widget).find(".clipboard-edit-button").toggle();
       jQuery(widget).find(".clipboard-remove-button").toggle();

       let selector = jQuery(widget).find("select.clipboard-selector");
       let namer = jQuery(widget).find("input.clipboard-name");
       selector.toggle();
       namer.val(selector.find("option:selected").text().trim());
       namer.toggle();
       namer.focus();
    },
};

export default Clipboard;
