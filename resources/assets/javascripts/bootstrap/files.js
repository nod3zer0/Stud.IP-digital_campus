function searchMoreFiles (button) {
    var table = $(button).closest('table');
    var loading = $('<div class="loading" style="padding: 10px">').html(
        $('<img>')
            .attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg')
            .css('width', '24')
            .css('height', '24')
    );

    $(button).replaceWith(loading);

    $.get(button.href).done((output) => {
        table.find('tbody').append($('tbody tr', output));
        table.find('tfoot').replaceWith($('tfoot', output));
    });

    return false;
}

STUDIP.domReady(() => {

    STUDIP.Files.init();

    $('form.drag-and-drop.files').on('dragover dragleave', (event) => {
        $(event.target).toggleClass('hovered', event.type === 'dragover');

        event.preventDefault();
    }).on('drop', (event) => {
        var filelist = event.originalEvent.dataTransfer.files || {};
        STUDIP.Files.upload(filelist);

        event.preventDefault();
    }).on('click', function() {
        $('.file_selector input[type=file]').first().click();
    });

    $(document).trigger('refresh-handlers');

    $(document).on(
        'click',
        '#file_license_chooser_1 > input[type=radio]',
        STUDIP.Files.updateTermsOfUseDescription
    );

    $(document).on('click', '.files-search-more', (event) => {
        searchMoreFiles(event.target);

        event.preventDefault();
    });
});

$(document).on('files-vue-app-loaded', () => {
    const lightboxImages = $('.lightbox-image');
    $('#sidebar-actions a[onclick*="Files.openGallery"]').attr('disabled', lightboxImages.length === 0);
});

jQuery(document).on('ajaxComplete', (event, xhr) => {
    if (!xhr.getResponseHeader('X-Filesystem-Changes')) {
        return;
    }

    var changes = JSON.parse(xhr.getResponseHeader('X-Filesystem-Changes'));
    var payload = false;

    function process(key, handler) {
        if (changes[key] === undefined) {
            return;
        }

        var values = changes[key];
        if (values === null && xhr.getResponseHeader('Content-Type').match(/json/)) {
            try {
                if (payload === false) {
                    payload = JSON.parse(xhr.responseText);
                }
                if (payload[key] !== undefined) {
                    values = payload[key];
                }
            } catch (e) {
                console.log('Error parsing payload', e);
            }
        }
        let options = {};
        if (key === 'redirect') {
            options.size = 'default';
        }
        handler(values, options);
    }

    process('added_files', STUDIP.Files.addFileDisplay);
    process('added_folders', STUDIP.Files.addFolderDisplay);
    process('removed_files', STUDIP.Files.removeFileDisplay);
    process('redirect', STUDIP.Dialog.fromURL);
    process('message', (html) => {
        $('.file_upload_window .uploadbar').hide().parent().append(html);
    });
    process('close_dialog', STUDIP.Dialog.close);

});
