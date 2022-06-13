STUDIP.domReady(() => {
    STUDIP.News.dialog_width = window.innerWidth * (1 / 2);
    STUDIP.News.dialog_height = window.innerHeight - 60;
    if (STUDIP.News.dialog_width < 550) {
        STUDIP.News.dialog_width = 550;
    }
    if (STUDIP.News.dialog_height < 400) {
        STUDIP.News.dialog_height = 400;
    }
    STUDIP.News.pending_ajax_request = false;

    // open/close categories without ajax-request
    $(document).on('click', '.news_category_header', function(event) {
        event.preventDefault();
        STUDIP.News.toggle_category_view(
            $(this)
                .parent('div')
                .attr('id')
        );
    });
    $(document).on('click', '.news_category_header input[type=image]', function(event) {
        event.preventDefault();
    });
});
