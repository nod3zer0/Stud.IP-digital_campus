STUDIP.ready(function() {
    STUDIP.Statusgroups.ajax_endpoint = $('meta[name="statusgroups-ajax-movable-endpoint"]').attr('content');
    STUDIP.Statusgroups.apply();

    $('a.get-group-members').on('click', function() {
        var dataEl = $('article#group-members-' + $(this).data('group-id')),
            url;
        if ($.trim(dataEl.html()).length === 0) {
            url = $(this).data('get-members-url');

            dataEl.html(
                $('<img>').attr({
                    width: 32,
                    height: 32,
                    src: STUDIP.ASSETS_URL + 'images/loading-indicator.svg'
                })
            );

            $.get(url).done(function(html) {
                dataEl.html(html);
                $(document).trigger('refresh-handlers');
            });
        }
    });

    // Check for touch device
    if (window.matchMedia('(hover: none)').matches) {
        $('.course-statusgroups[data-sortable]').addClass('by-touch');
    }

    var index_before = null;
    $('.course-statusgroups[data-sortable]')
        .sortable({
            axis: 'y',
            containment: 'parent',
            forcePlaceholderSize: true,
            handle: '.drag-handle',
            items: '> .draggable',
            placeholder: 'sortable-placeholder',
            start: function(event, ui) {
                index_before = ui.item.index();
            },
            stop: function(event, ui) {
                if (index_before === ui.item.index()) {
                    return;
                }

                var url = $(this).data('sortable');
                $.post(url, {
                    id: ui.item.attr('id'),
                    index: ui.item.index() - 1
                });
            }
        });
});

STUDIP.ready(function() {
    $('.nestable').each(function() {
        $(this).nestable({
            rootClass: 'nestable',
            maxDepth: $(this).data('max-depth') || 5
        });
    });
});

$(document).on('submit', '#order_form', function() {
    let structure = $('.nestable').nestable('serialize');
    let json_data = JSON.stringify(structure);
    $('#ordering').val(json_data);
});
