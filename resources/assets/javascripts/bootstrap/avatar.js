/*global jQuery, STUDIP */
STUDIP.domReady(() => {
    STUDIP.Avatar.init('#avatar-upload');

    // Get file data on drop
    var dropZone = document.getElementById('avatar-overlay');

    if (dropZone) {
        dropZone.addEventListener('dragover', function(e) {
            e.stopPropagation();
            e.preventDefault();
            e.target.parentNode.classList.add("dragging");
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.stopPropagation();
            e.preventDefault();
            e.target.parentNode.classList.remove("dragging");
        });

        dropZone.addEventListener('drop', function(e) {
            e.stopPropagation();
            e.preventDefault();
            e.target.parentNode.classList.remove("dragging");
            var files = e.dataTransfer.files;
            var div = e.target.parentNode;
            var avatar_dialog = div.getElementsByTagName('a')[0];

            if (!div.getAttribute('accept') || !div.getAttribute('accept').includes(files[0].type)) {
                alert(div.getAttribute('data-message-unaccepted'));
                return false;
            }

            if (!div.getAttribute('data-max-size') || files[0].size > div.getAttribute('data-max-size')) {
                alert(div.getAttribute('data-message-too-large'));
                return false;
            }

            avatar_dialog.click();
            div.files = files;
            STUDIP.dialogReady(() => {
                STUDIP.Avatar.readFile(div);
            });
        });
    }

    //"Redirecting" the event is necessary so that the avatar image upload
    //is accessible by pressing the enter key when its focused.
    jQuery(document).on('keydown', 'form.settings-avatar label.file-upload a.button', function(event) {
        if (event.code == "Enter") {
            //The enter key has been pressed.
            jQuery(this).parent('.file-upload').trigger('click');
        }
    });
});
