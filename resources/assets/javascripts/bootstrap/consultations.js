import { $gettext } from '../lib/gettext.js';

$(document).on('click', '.consultation-delete-check:not(.ignore)', event => {
    const form       = $(event.target).closest('form');
    const checkboxes = form.find(':checkbox[name="slot-id[]"]:checked');
    const ids        = checkboxes.map((index, element) => element.value.split('-').pop()).get();

    if (!ids.length) {
        return false;
    }

    let requests = ids.map(id => {
        return STUDIP.jsonapi.GET(`consultation-slots/${id}/bookings`).then(result => result.data.length);
    });
    $.when(...requests).done((...results) => {
        if (results.some(result => result > 0)) {
            $(event.target).addClass('ignore').click().removeClass('ignore');
        } else {
            STUDIP.Dialog.confirm($gettext('Wollen Sie diese Termine wirklich löschen?')).done(() => {
                $('<input type="hidden" name="delete" value="1"/>').appendTo(form);
                form.submit();
            });
        }
    });

    event.preventDefault();
});
