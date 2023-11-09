const startpage = {
    init() {
        $('.start-widgetcontainer .portal-widget-list').sortable({
            handle: '.widget-header',
            connectWith: 'ul.portal-widget-list',
            start() {
                $(this)
                    .closest('.start-widgetcontainer')
                    .find('.portal-widget-list')
                    .addClass('move');
            },
            update(event, ui) {
                if (ui.item.parent().is(this)) {
                    let lanes = [];
                    $(this)
                        .closest('.start-widgetcontainer')
                        .children('.portal-widget-list')
                        .each((index, element) => {
                            lanes[index] = $('.studip-widget-wrapper', element)
                                .map((i, el) => el.getAttribute('id'))
                                .get(); // Ensure we have an array
                        });

                    $.post(
                        STUDIP.URLHelper.getURL('dispatch.php/start/storeNewOrder'),
                        {lanes}
                    );
                }
            },
            stop() {
                $(this)
                    .closest('.start-widgetcontainer')
                    .find('.portal-widget-list')
                    .removeClass('move');
            }
        });
    },

    init_edit(perm) {
        $('.edit-widgetcontainer .portal-widget-list').sortable({
            handle: '.widget-header',
            connectWith: '.edit-widgetcontainer .portal-widget-list',
            start: function() {
                $(this)
                    .closest('.edit-widgetcontainer')
                    .find('.portal-widget-list')
                    .addClass('ui-sortable move');
            },
            stop: function() {
                // store the whole widget constellation
                var widgets = {
                    left: {},
                    right: {}
                };

                $('.edit-widgetcontainer .start-widgetcontainer .portal-widget-list:first-child > li').each(function() {
                    widgets.left[$(this).attr('id')] = $(this).index();
                });

                $('.edit-widgetcontainer .start-widgetcontainer .portal-widget-list:last-child > li').each(function() {
                    widgets.right[$(this).attr('id')] = $(this).index();
                });

                $.post(STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/start/update_defaults/' + perm, widgets);

                $(this)
                    .closest('.edit-widgetcontainer')
                    .find('.portal-widget-list')
                    .removeClass('move');
            }
        });
    }
};

export default startpage;
