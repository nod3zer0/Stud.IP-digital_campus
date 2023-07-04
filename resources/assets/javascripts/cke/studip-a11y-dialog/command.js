import { Command } from '@ckeditor/ckeditor5-core';
import Dialog from '../../lib/dialog.js';

export default class A11YDialogCommand extends Command {
    refresh() {
        this.isEnabled = true;
    }

    execute() {
        const activeElement = document.activeElement;
        const id = 'cke-a11y-help';
        Dialog.fromURL(STUDIP.URLHelper.getURL('dispatch.php/wysiwyg/a11yhelp'), { id });
        $(document).one('dialog-close', function (event, { dialog, options }) {
            if (options.id === id) {
                activeElement?.focus();
            }
        });
    }
}
