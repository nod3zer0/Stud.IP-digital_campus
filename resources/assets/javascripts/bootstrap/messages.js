jQuery(document).on('dialog-load', 'form#message-tags', function(event, data) {
    var tags = jQuery.parseJSON(data.xhr.getResponseHeader('X-Tags')),
        all_tags = jQuery.parseJSON(data.xhr.getResponseHeader('X-All-Tags')),
        message_id = jQuery(this)
            .closest('table')
            .data().message_id;
    STUDIP.Messages.setTags(message_id, tags);
    STUDIP.Messages.setAllTags(all_tags);
});

jQuery(document).on('dialog-open', '#messages .title a', function() {
    STUDIP.Messages.whenMessageIsShown(this);
});

STUDIP.domReady(() => {
    /*********** infinity-scroll in the overview ***********/
    if (jQuery('#messages').length > 0) {
        STUDIP.Messages.init();
        jQuery(window.document).on(
            'scroll',
            _.throttle(function(event) {
                if (
                    jQuery(window).scrollTop() + jQuery(window).height() > jQuery(window.document).height() - 500 &&
                    jQuery('#reloader').hasClass('more')
                ) {
                    //nachladen
                    jQuery('#reloader')
                        .removeClass('more')
                        .addClass('loading');
                    jQuery.ajax({
                        url: STUDIP.ABSOLUTE_URI_STUDIP + 'dispatch.php/messages/more',
                        data: {
                            received: jQuery('#received').val(),
                            offset: jQuery('#messages > tbody > tr').length - 1,
                            tag: jQuery('#tag').val(),
                            search: jQuery('#search').val(),
                            search_autor: jQuery('#search_autor').val(),
                            search_subject: jQuery('#search_subject').val(),
                            search_content: jQuery('#search_content').val(),
                            limit: 50
                        },
                        dataType: 'json',
                        success: function(response) {
                            var more_indicator = jQuery('#reloader').detach();

                            jQuery('#loaded').val(parseInt(jQuery('#loaded').val(), 10) + 1);
                            jQuery.each(response.messages, (index, message) => {
                                jQuery(message).appendTo('#messages > tbody');
                            });

                            if (response.more) {
                                jQuery('#messages > tbody').append(
                                    more_indicator.addClass('more').removeClass('loading')
                                );
                            }
                        }
                    });
                }
            }, 30)
        );
    }

    jQuery(document).on('click', '.adressee .remove_adressee', STUDIP.Messages.remove_adressee);
    jQuery(document).on('click', '.file .remove_attachment', STUDIP.Messages.remove_attachment);
});
