import { $gettext } from '../lib/gettext.js'

import "tablesorter/dist/js/jquery.tablesorter"
import "tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js"
import "tablesorter/dist/js/jquery.tablesorter.widgets.js"

jQuery.tablesorter.addParser({
    id: 'htmldata',
    is: function (s, table, cell, $cell) {
        var c = table.config,
            p = c.parserMetadataName || 'sortValue';
        return $(cell).data(p) !== undefined;
    },
    format: function (s, table, cell) {
        var c = table.config,
            p = c.parserMetadataName || 'sortValue';
        return $(cell).data(p);
    },
    type: 'text'
});

jQuery.tablesorter.language = {
    sortAsc      : $gettext('Aufsteigend sortiert, '),
    sortDesc     : $gettext('Absteigend sortiert, '),
    sortNone     : $gettext('Keine Sortierung angewandt, '),
    sortDisabled : $gettext('Sortieren ist deaktiviert'),
    nextAsc      : $gettext('aktivieren, um eine aufsteigende Sortierung anzuwenden'),
    nextDesc     : $gettext('aktivieren, um eine absteigende Sortierung anzuwenden'),
    nextNone     : $gettext('aktivieren, um keine Sortierung anzuwenden')
};
