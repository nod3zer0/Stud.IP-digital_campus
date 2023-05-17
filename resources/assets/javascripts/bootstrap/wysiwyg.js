STUDIP.domReady(() => {
    if (STUDIP.editor_enabled) {
        // replace areas visible on page load
        replaceVisibleTextareas();

        // update the height of the editor view on focus changes in dialogs
        $(document).on('focus blur', '.studip-dialog .ck-editor__editable_inline', function(event) {
            let height = this.clientHeight;
            let editor = this.ckeditorInstance;
            editor.editing.view.change(writer => {
                writer.setStyle('height', height + 'px', editor.editing.view.document.getRoot());
            });
        });

        // replace areas that are created or shown after page load
        // remove editors that become hidden after page load
        // show, hide and create do not raise an event, use interval timer
        setInterval(replaceVisibleTextareas, 300);
    }

    // when attaching to hidden textareas, or textareas who's parents are
    // hidden, the editor does not function properly; therefore attach to
    // visible textareas only
    function replaceVisibleTextareas() {
        const textareas = document.querySelectorAll('textarea.wysiwyg');
        textareas.forEach(STUDIP.wysiwyg.replace);
    }
});
