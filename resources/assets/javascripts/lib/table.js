/**
 * This class is used to enhance sortable tables in Stud.IP by using the
 * tablesorter plugin.
 *
 * @see https://mottie.github.io/tablesorter/docs/
 */
class Table
{
    /**
     * This method will make the given sortable. The table my be given as a
     * DOM element or wrapped in a jQuery object.
     *
     * Additional widgets for the tablesorter may be passed as an array via
     * the attribute [data-sort-widgets].
     * (see https://mottie.github.io/tablesorter/docs/example-widgets.html)
     *
     * @param table
     */
    static async enhanceSortableTable(table)
    {
        // Unjquerify table
        if (table instanceof jQuery) {
            table = table.get(0);
        }

        await STUDIP.loadChunk('tablesorter');

        // Iterate over the header columns and determine sorting mechanism
        let headers = {};
        $('thead tr:last th', table).each((index, element) => {
            headers[index] = {
                sorter: element.dataset.sort ?? false
            };
        });

        // Handle potential fixed rows
        if ($('tbody tr[data-sort-fixed]', table).length > 0) {
            $('tbody tr[data-sort-fixed]', table).each(function () {
                $(this).data('sort-fixed', {
                    index: $(this).index(),
                    tbody: $(this).closest('table').find('tbody').index($(this).parent())
                });
            });
            $(table).on('sortStart', () => {
                $('tbody tr[data-sort-fixed]', table).each(function () {
                    const hidden = $(this).is(':hidden');
                    $(this).data('sort-hidden', hidden);
                });
            }).on('sortEnd', () => {
                $('tbody tr[data-sort-fixed]', table).detach().each(function () {
                    const pos = $(this).data('sort-fixed');
                    if ($(`tbody:eq(${pos.tbody}) tr:eq(${pos.index})`, table).length > 0) {
                        $(`tbody:eq(${pos.tbody}) tr:eq(${pos.index})`, table).before(this);
                    } else {
                        $(`tbody:eq(${pos.tbody})`, table).append(this);
                    }

                    if ($(this).data('sort-hidden')) {
                        setTimeout(() => $(this).hide(), 100);
                    }
                });
            });
        }

        // Get additional widgets
        const widgets = $(table).data().sortWidgets ?? [];

        // Actually activate table sorter
        $(table).tablesorter({
            headers: headers,
            sortLocaleCompare : true,
            sortRestart: true,
            widthFixed: false,
            widgets: Array.isArray(widgets) ? widgets : []
        });
    }
}

export default Table;
