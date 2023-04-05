import { $gettext } from '../lib/gettext.js';
import eventBus from "../lib/event-bus.ts";

eventBus.on('studip:set-locale', () => {
    $.extend($.ui.multiselect, {
        locale: {
            addAll: $gettext('Alle hinzufügen'),
            removeAll: $gettext('Alle entfernen'),
            itemsCount: $gettext('ausgewählt')
        }
    });
});
